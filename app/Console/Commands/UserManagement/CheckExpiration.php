<?php

namespace App\Console\Commands\UserManagement;

use App\Jobs\PushUserSyncToSsoJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark Users as expired if past expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredUsers = User::where('status', 'active')
            ->where('expiration_date', '<', Carbon::now())
            ->get();

        foreach ($expiredUsers as $user) {
            $user->status = 'inactive';
            $user->remarks = 'Expired';
            $user->save();
            PushUserSyncToSsoJob::syncOrQueue($user->fresh());
        }

        $this->info('Expired Users Updated');
        Log::channel('user_management')->info('Expired Users Updated');
    }
}
