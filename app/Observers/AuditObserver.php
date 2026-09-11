<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Division;
use App\Models\Department;
use App\Models\Deposit;
use App\Models\OutCollection;
use App\Models\OutCollectionDeposit;
use App\Models\Payment;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditObserver
{
    protected $modules = [
        User::class => 'User Management',
        Role::class => 'Role Management',
        Branch::class => 'Branch Management',
        Group::class => 'Group Management',
        Division::class => 'Division Management',
        Department::class => 'Department Management',
        Section::class => 'Section Management',
        Deposit::class => 'Out Collection - Deposit',
        Payment::class => 'Out Collection - Payment',
        Bank::class => 'Bank Management',
    ];

    public function getModuleName(Model $model)
    {
        return $this->modules[get_class($model)] ?? '';
    }

    public function created(Model $model)
    {
        $this->logActivity($model, 'CREATED', $this->getModuleName($model));
    }

    public function updated(Model $model)
    {
        $systemFields = ['session_version', 'failed_attempts', 'remember_token', 'updated_at'];
        $dirty = array_keys($model->getDirty());

        if (!empty($dirty) && empty(array_diff($dirty, $systemFields))) {
            return;
        }

        $this->logActivity($model, 'UPDATED', $this->getModuleName($model), $model->getOriginal());
    }

    public function deleted(Model $model)
    {
        $this->logActivity($model, 'DELETED', $this->getModuleName($model), $model->getOriginal());
    }

    public function retreived(Model $model)
    {
        $this->logActivity($model, 'RETRIEVED', $this->getModuleName($model));
    }

    private function logActivity(Model $model, string $event, $module,  array $oldData = [])
    {
        if (Auth::user()) {
            $modelName = Str::afterLast(get_class($model), '\\');
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => $event,
                'model' => $modelName,
                'module' => $module,
                'old_data' => in_array($event, ['UPDATED', 'DELETED']) ? json_encode($oldData) : null,
                'new_data' => in_array($event, ['CREATED', 'UPDATED', 'RETRIEVED']) ? json_encode($model->getAttributes()) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }
}
