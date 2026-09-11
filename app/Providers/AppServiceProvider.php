<?php

namespace App\Providers;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\Division;
use App\Models\Group;
use App\Models\OutCollection;
use App\Models\OutCollectionDeposit;
use App\Models\Payment;
use App\Models\Section;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $models = [
            User::class,
            Role::class,
            Branch::class,
            Group::class,
            Division::class,
            Department::class,
            Section::class,
            Deposit::class,
            Payment::class,
            Bank::class,
        ];


        foreach ($models as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
