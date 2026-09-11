<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckRedisLastSeen extends Command
{
    protected $signature = 'dev:redis-last-seen
                            {--employee= : Employee ID to check (prompts if omitted)}
                            {--all : Show all user:*:last_seen keys in Redis}
                            {--redis-cli : Print equivalent redis-cli commands instead of running them}';

    protected $description = '[DEV] Check Redis last_seen key and TTL for a user after SSO login';

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->showAll();
        }

        $employeeId = $this->option('employee')
            ?? $this->ask('Employee ID?');

        $user = User::where('employee_id', $employeeId)->first();
        if (!$user) {
            $this->error("No user found with employee_id: {$employeeId}");
            return self::FAILURE;
        }

        $prefix      = config('database.redis.options.prefix', '');
        $key         = "user:{$user->id}:last_seen";
        $fullKey     = $prefix . $key;
        $expectedTtl = (config('session.lifetime') + 2) * 60;

        if ($this->option('redis-cli')) {
            $this->newLine();
            $this->line('<fg=cyan>  Redis CLI commands to test manually:</>');
            $this->line("  <fg=white>redis-cli GET   \"{$fullKey}\"</>");
            $this->line("  <fg=white>redis-cli TTL   \"{$fullKey}\"</>");
            $this->line("  <fg=gray>  Expected TTL: ≤ {$expectedTtl}s   (SESSION_LIFETIME=" . config('session.lifetime') . " → (" . config('session.lifetime') . "+2)×60)</>");
            $this->newLine();
            return self::SUCCESS;
        }

        $value = Redis::get($key);
        $ttl   = Redis::ttl($key);

        $this->newLine();
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</> ');
        $this->line('<fg=cyan> Redis last_seen Check</>');
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</> ');
        $this->line("  User        : <fg=green>{$user->employee_id}</> ({$user->fname} {$user->lname})");
        $this->line("  Redis key   : <fg=white>{$fullKey}</>");
        $this->newLine();

        if ($value === null) {
            $this->line("  Value       : <fg=red>NOT FOUND</> — user has not made an authenticated request yet");
            $this->line("  TTL         : <fg=red>N/A</>");
            $this->line("  <fg=yellow>Tip: Log in via SSO, then navigate to any LUCAS page, then re-run this command.</>");
        } else {
            $humanTime = date('Y-m-d H:i:s', (int) $value);
            $age       = time() - (int) $value;

            $lifetime  = config('session.lifetime');
            $ttlColor  = ($ttl > 0 && $ttl <= $expectedTtl) ? 'green' : 'yellow';
            $this->line("  Value       : <fg=green>{$value}</> ({$humanTime}, {$age}s ago)");
            $this->line("  TTL         : <fg={$ttlColor}>{$ttl}s</> (expected ≤ {$expectedTtl}s)");
            $this->line("  SESSION_LIFETIME : {$lifetime} min → ({$lifetime} + 2) × 60 = {$expectedTtl}s");

            $this->newLine();
            if ($ttl > 0 && $ttl <= $expectedTtl) {
                $this->line("  <fg=green>✓ PASS</> — Key exists and TTL is within expected range.");
            } elseif ($ttl === -1) {
                $this->line("  <fg=yellow>⚠ Key exists but has NO expiry set (TTL = -1). setex may not have been used.</>");
            } elseif ($ttl === -2) {
                $this->line("  <fg=red>✗ Key does not exist (TTL = -2).</>");
            } else {
                $this->line("  <fg=yellow>⚠ TTL ({$ttl}s) differs from expected ({$expectedTtl}s) by more than 5s.</>");
            }
        }

        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</> ');
        $this->newLine();

        return self::SUCCESS;
    }

    private function showAll(): int
    {
        // Build the full pattern including Redis prefix so KEYS matches properly
        $prefix = config('database.redis.options.prefix', '');
        $keys   = Redis::connection()->client()->keys($prefix . 'user:*:last_seen');

        if (empty($keys)) {
            $this->warn('No user:*:last_seen keys found in Redis.');
            $this->line("<fg=gray>  (prefix used: \"{$prefix}\")</>");
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($keys as $key) {
            // Strip prefix to get the clean key for display; use clean key for get/ttl
            // because Redis facade auto-adds prefix on its own
            preg_match('/user:(\d+):last_seen/', $key, $m);
            $userId   = $m[1] ?? '?';
            $cleanKey = "user:{$userId}:last_seen";

            $user  = User::find($userId);
            $value = Redis::get($cleanKey);
            $ttl   = Redis::ttl($cleanKey);

            $rows[] = [
                $userId,
                $user ? $user->employee_id : '—',
                $value ? date('H:i:s', (int) $value) : 'null',
                $ttl > 0 ? "{$ttl}s" : ($ttl === -1 ? 'no expiry' : 'expired'),
            ];
        }

        $this->table(['User ID', 'Employee ID', 'Last Seen (time)', 'TTL'], $rows);

        return self::SUCCESS;
    }
}
