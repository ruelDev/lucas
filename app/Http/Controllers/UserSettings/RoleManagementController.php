<?php

namespace App\Http\Controllers\UserSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Traits\HasDataTable;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RoleManagementController extends Controller
{
    use HasDataTable;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('role_management')->info('Accessed the Role Management Page', ['user_id' => Auth::id()]);

        $results = $this->handleDataTableRequest(
            $request,
            Role::class,
            [
                'searchableColumns' => ['name', 'description', 'updated_at'],
                'allowedSortColumns' => ['name', 'description', 'updated_at'],
                'selectColumns' => ['id', 'name', 'description', 'updated_at',],
                'defaultSortColumn' => 'updated_at',
                'defaultSortDirection' => 'desc',
                'relationshipColumns' => ['permissions' => ['name']],
                'eagerLoad' => ['permissions',],
                'isShowColumns' => true
            ]
        );

        $results['data'] = collect($results['data'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'permissions' => $item->permissions,
                'description' => $item->description,
                'updated_at' => $item->updated_at,
                'users_count' => $item->users_count,
            ];
        })->toArray();

        return inertia('role-management/index', $results);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("role-management/dialog/create", [
            'permissions' => Permission::pluck("name")->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedRole = $request->validate([
            "name" => ['required', Rule::unique('roles', 'name')],
            "description" => "required",
            "permissions" => "required",
        ]);

        try {
            $role = Role::create([
                "name" => $request->name,
                "description" => $request->description,
            ]);
            $role->syncPermissions($request->permissions);

            $newData = $role->getAttributes();

            Log::channel('role_management')->info('Created a new role', ['user_id' => Auth::id(), 'new_data' => json_encode($newData)]);

            return to_route("role-management.index")->with(['success' => 'Role created successfully.']);
        } catch (\Throwable $th) {
            Log::channel('role_management')->error('Error creating new Role', ['user_id' => Auth::id(), 'Reason' => $th, 'Input' => json_encode($validatedRole)]);
            return redirect()->back()->withErrors(['add_role' => 'Failed to create new role.' . $th]);
        }
    }
    
    public function show() {
        return redirect()->route("role-management.index")
            ->with(['error' => 'Role not found']);
    }

    public function view(Request $request, $id = null)
    {
        $id = $id ?? $request->input('id');

        if (!$id) {
            return redirect()->route('role-management.index')->with(['error' => 'Role ID is required']);
        }

        $role_management = Role::find($id);

        return Inertia::render("role-management/dialog/edit", [
            "role" => $role_management,
            "rolePermissions" => $role_management->permissions()->pluck("name"),
            "permissions" => Permission::pluck("name")->toArray(),
            "errors" => session('errors') ?? new \Illuminate\Support\MessageBag(),
            "old" => session('old') ?? [],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role_management)
    {

        $roleValidator = Validator::make($request->all(), [
            "name" => ['required', Rule::unique('roles', 'name')->ignore($role_management->id)],
            "description" => "required",
            "permissions" => "required",
        ]);

        if ($roleValidator->fails()) {
            $newRequest = new Request($request->all());
            $newRequest->merge(['id' => $role_management->id]);

            $errors = $roleValidator->errors()->toArray();

            return $this->view($newRequest)->with([
                'errors' => new \Illuminate\Support\MessageBag($errors),
                'old' => $request->all(),
            ]);
        }

        $validatedRole = $roleValidator->validated();

        $oldRoleData = $role_management->getAttributes();
        $oldPermissionsData = $role_management->permissions();

        try {
            $role_management->name = $request->name;
            $role_management->description = $request->description;
            $role_management->save();

            $role_management->syncPermissions($request->permissions);

            $newRoleData = $role_management->getAttributes();
            $newPermissionsData = $role_management->permissions();

            Log::channel('role_management')->info('Updated a Role', ['user_id' => Auth::id(), 'selected_role_id' => $role_management->id, 'old_data' => json_encode($oldRoleData), 'old_permissions_data' => json_encode($oldPermissionsData), 'new_data' => json_encode($newRoleData), 'new_permissions_data' => json_encode($newPermissionsData)]);

            return to_route("role-management.index")->with(['success' => 'Role updated successfully.']);
        } catch (\Throwable $th) {
            Log::channel('role_management')->error('Error updating Role', ['user_id' => Auth::id(), 'Reason' => $th, 'old_data' => json_encode($oldRoleData), 'old_permissions_data' => json_encode($oldPermissionsData), 'new_data' => $validatedRole]);

            $newRequest = new Request($request->all());
            $newRequest->merge(['id' => $role_management->id]);

            return $this->view($newRequest)->with([
                'errors' => new \Illuminate\Support\MessageBag([
                    'edit_role' => 'Failed to update role. ' . $th->getMessage(),
                ]),
                'old' => $request->all(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role_management)
    {

        if ($role_management->users()->exists()) {
            return redirect()->back()->withErrors(['delete_role' => 'Cannot Delete Role. It is assigned to an existing permission or user.']);
        }

        $id = $role_management->id;
        try {
            $role_management->delete();

            Log::channel('role_management')->info('Deleted a Role', ['user_id' => Auth::id(), 'deleted_role_id' => $id]);
            return redirect()->back()->with(['success' => 'Role deleted successfully.']);
        } catch (\Throwable $th) {
            Log::channel('role_management')->error('Error deleting Role', ['user_id' => Auth::id(), 'Reason' => $th, 'deleted_role_id' => $id]);
            return redirect()->back()->withErrors(['delete_role' => 'Failed to delete role.' . $th]);
        }
    }

    public function getRoleOptions()
    {
        $roles = Role::get();

        return response()->json($roles->map(fn($item) => [
            'name' => $item->name,
        ]));
    }
}
