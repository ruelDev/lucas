<?php

namespace App\Console\Commands\UserManagement;

use App\Mail\AccountExpiryMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WarningExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:warning-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email warning about the User Account Expiration notice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $withExpiryUsers = User::whereNotNull('expiration_date')
            ->whereDate('expiration_date', Carbon::now()->addDays(5)->toDateString())
            ->get();

        try {
            if ($withExpiryUsers->isNotEmpty()) {
                foreach ($withExpiryUsers as $user) {
                    Mail::to($user['email'])->queue(new AccountExpiryMail(
                        $user->fname,
                        'Account Expiration Notice',
                        'account',
                        Carbon::parse($user->expiration_date)
                    ));
                }
                
                $this->info('Users found: ' . $withExpiryUsers->count());

                Log::channel('user_management')->info('Account Expiration Notice Email sent');

                $this->info('email sent successfully');
            } else {
                Log::channel('user_management')->info('No Account Expiration Notice Email sent');

                $this->info('No users with near expiration has been detected');
            }
        } catch (\Throwable $th) {
            $this->info('Error ' . $th);
        }
    }
}
