<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Workers extends Command
{
    protected $signature = 'lucas:worker
    {--workers=2}
    {--queues=default,emails}';
    protected $description = 'Multiple workers with auto-restart capability';

    private $processes = [];
    private $running = true;
    private $lockKey = 'worker_supervisor_lock';
    private $restartCounts = [];
    private $maxRestarts = 5;
    private $restartWindow = 300; // 5 minutes

    public function handle()
    {
        // Register signal handlers for graceful shutdown
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
            pcntl_signal(SIGINT, [$this, 'handleShutdown']);
        }

        // Prevent multiple instances
        if (!$this->acquireLock()) {
            $this->log('Another worker supervisor is already running.', 'error');
            return 1;
        }

        $workerCount = $this->option('workers');
        $this->log("Starting {$workerCount} queue workers with auto-restart...");

        // Initialize restart tracking
        for ($i = 1; $i <= $workerCount; $i++) {
            $this->restartCounts[$i] = [
                'count' => 0,
                'first_restart' => null
            ];
        }

        // Start all workers
        for ($i = 1; $i <= $workerCount; $i++) {
            $this->startWorker($i);
        }

        $this->log("All workers started. Monitoring for failures...");
        $this->log("Press Ctrl + C to stop gracefully.");

        // Monitor and restart workers indefinitely
        $this->monitorWorkers();

        return 0;
    }

    private function acquireLock()
    {
        $lockValue = getmypid();

        // Try to acquire lock
        if (Cache::add($this->lockKey, $lockValue, 86400)) {
            return true;
        }

        // Check if existing lock is stale
        $existingPid = Cache::get($this->lockKey);
        if ($existingPid && !$this->isProcessRunning($existingPid)) {
            Cache::forget($this->lockKey);
            return Cache::add($this->lockKey, $lockValue, 86400);
        }

        return false;
    }

    private function releaseLock()
    {
        Cache::forget($this->lockKey);
    }

    private function isProcessRunning($pid)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec("tasklist /FI \"PID eq {$pid}\" 2>NUL");
            return strpos($output, (string)$pid) !== false;
        } else {
            return file_exists("/proc/{$pid}");
        }
    }

    private function startWorker($workerNumber)
    {
        $queues = explode(',', $this->option('queues'));

        $queue = $queues[($workerNumber - 1) % count($queues)];

        $command = [
            PHP_BINARY,
            'artisan',
            'queue:work',
            "--queue={$queue}",
            '--tries=3',
            '--timeout=1800',
            '--sleep=3',
            '--max-jobs=100',
            '--memory=512'
        ];

        $process = new Process($command, base_path());
        $process->setTimeout(null);
        $process->start(function ($type, $buffer) use ($workerNumber) {
            // Log to file instead of console to avoid null output errors
            Log::channel('worker')->info("Worker {$workerNumber} " . ($type === Process::ERR ? 'error' : 'output') . ": {$buffer}");
        });

        $this->processes[$workerNumber] = [
            'process' => $process,
            'started_at' => time()
        ];

        $this->log("Worker {$workerNumber} started (PID: {$process->getPid()})");
    }

    private function monitorWorkers()
    {
        $checkInterval = 5; // Check every 5 seconds
        $lastHealthCheck = time();

        while ($this->running) {
            // Process signals if pcntl is available
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            $currentTime = time();

            foreach ($this->processes as $workerNumber => $workerData) {
                $process = $workerData['process'];

                // Check if process is still running
                if (!$process->isRunning()) {
                    $exitCode = $process->getExitCode();
                    $this->log("Worker {$workerNumber} stopped (exit code: {$exitCode})", 'warning');

                    // Check restart limits
                    if ($this->shouldRestartWorker($workerNumber)) {
                        $this->log("Restarting worker {$workerNumber}...");
                        unset($this->processes[$workerNumber]);
                        $this->startWorker($workerNumber);
                        $this->trackRestart($workerNumber);
                    } else {
                        $this->log("Worker {$workerNumber} has restarted too many times. Manual intervention required.", 'error');
                        $this->running = false;
                        break;
                    }
                }
            }

            // Periodic health check
            if ($currentTime - $lastHealthCheck > 60) {
                $this->performHealthCheck();
                $lastHealthCheck = $currentTime;
            }

            sleep($checkInterval);
        }

        // Clean shutdown
        $this->cleanup();
    }

    private function shouldRestartWorker($workerNumber)
    {
        $now = time();
        $tracking = $this->restartCounts[$workerNumber];

        // Reset counter if outside the restart window
        if (
            $tracking['first_restart'] &&
            ($now - $tracking['first_restart']) > $this->restartWindow
        ) {
            $this->restartCounts[$workerNumber] = [
                'count' => 0,
                'first_restart' => null
            ];
            return true;
        }

        // Check if under max restarts
        return $tracking['count'] < $this->maxRestarts;
    }

    private function trackRestart($workerNumber)
    {
        $now = time();

        if ($this->restartCounts[$workerNumber]['count'] === 0) {
            $this->restartCounts[$workerNumber]['first_restart'] = $now;
        }

        $this->restartCounts[$workerNumber]['count']++;
    }

    private function performHealthCheck()
    {
        $activeWorkers = count($this->processes);
        $this->log("Health check: {$activeWorkers} workers active");

        foreach ($this->processes as $workerNumber => $workerData) {
            $process = $workerData['process'];
            $uptime = time() - $workerData['started_at'];
            $this->log("  Worker {$workerNumber}: PID {$process->getPid()}, uptime {$uptime}s");
        }
    }

    public function handleShutdown()
    {
        $this->log("Received shutdown signal. Stopping workers gracefully...");
        $this->running = false;
    }

    private function cleanup()
    {
        $this->log("Stopping all workers...");

        foreach ($this->processes as $workerNumber => $workerData) {
            $process = $workerData['process'];

            if ($process->isRunning()) {
                $this->log("Stopping worker {$workerNumber} (PID: {$process->getPid()})");
                $process->stop(10, SIGTERM);

                // Force kill if still running
                if ($process->isRunning()) {
                    $process->signal(SIGKILL);
                }
            }
        }

        $this->releaseLock();
        $this->log("All workers stopped.");
    }

    /**
     * Safe logging method that works even when output is null
     */
    private function log($message, $level = 'info')
    {
        // Try to write to console if available
        try {
            if ($this->output) {
                switch ($level) {
                    case 'error':
                        $this->error($message);
                        break;
                    case 'warning':
                        $this->warn($message);
                        break;
                    default:
                        $this->info($message);
                }
            }
        } catch (\Exception $e) {
            // Ignore console output errors
        }

        // Always log to Laravel log file
        Log::channel('worker')->info("[Worker Supervisor] {$message}");
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
