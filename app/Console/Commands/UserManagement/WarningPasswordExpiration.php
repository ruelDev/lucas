<?php

namespace App\Console\Commands\UserManagement;

use App\Mail\AccountExpiryMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WarningPasswordExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:warning-password-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email warning about the User Account Password Expiration notice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutOffDate = Carbon::now()->subMonths(3)->addDays(5);

        $startOfDay = $cutOffDate->copy()->startOfDay();
        $endOfDay = $cutOffDate->copy()->endOfDay();

        $withExpiryUsers = User::whereNotNull('password_changed_at')
            ->whereBetween('password_changed_at', [$startOfDay, $endOfDay])
            ->get();

        try {
            if ($withExpiryUsers->isNotEmpty()) {
                foreach ($withExpiryUsers as $user) {
                    $passwordExpiryDate = Carbon::parse($user->password_changed_at)->addMonths(3);

                    Mail::to($user['email'])->queue(new AccountExpiryMail(
                        $user->fname,
                        'Password Expiration Notice',
                        'password',
                        $passwordExpiryDate
                    ));
                }

                $this->info('Users found: ' . $withExpiryUsers->count());

                Log::channel('user_management')->info('Password Expiration Notice Email sent');

                $this->info('email sent successfully');
            } else {
                Log::channel('user_management')->info('No Account Password Expiration Notice Email sent');

                $this->info('No users with near password expiration has been detected');
            }
        } catch (\Throwable $th) {
            $this->info('Error ' . $th);
        }
    }
}
