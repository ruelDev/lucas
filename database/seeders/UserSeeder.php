<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Division;
use App\Models\Group;
use App\Models\Section;
use App\Models\User;
use App\Models\UserHasBranchDealer;
use App\Models\UserHasHeadOffice;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Default User for development
         */
        $admin = User::create([
            'fname' => 'Super',
            'mname' => null,
            'lname' => 'User',
            'employee_id' => '0000001',
            'position' => 'Admin',
            'email' => 'super.user@gmail.com',
            'company' => 'BMI',
            'password' => bcrypt('password'),
            'isReset' => 1,
            'isBranchDealer' => 2,
            'status' => 'active',
            'password_changed_at' => now(),
        ]);

        $user = User::create([
            'fname' => 'Guest',
            'mname' => null,
            'lname' => 'User',
            'employee_id' => '0000002',
            'position' => 'User',
            'email' => 'guest.user@gmail.com',
            'company' => 'BFC',
            'password' => bcrypt('password'),
            'isReset' => 1,
            'isBranchDealer' => 1,
            'status' => 'active',
            'password_changed_at' => now(),
        ]);

        $adminRole = Role::where('name', 'Admin')->first();

        $admin->syncRoles($adminRole->name);
        // --------------------------SAMPLE DATA FOR BRANCHES AND HEAD OFFICE ---- DELETE THESE IN THE FUTURE ------------------
        $branch1 = Branch::create([
            'name' => 'Metro Manila Headquarters',
            'branch_code' => '0000001',
            'location' => 'Makati City'
        ]);

        Branch::create([
            'name' => 'Cebu Regional Office',
            'branch_code' => '0000002',
            'location' => 'Cebu City'
        ]);

        Branch::create([
            'name' => 'Davao Satellite Office',
            'branch_code' => '0000003',
            'location' => 'Davao City'
        ]);

        Branch::create([
            'name' => 'Bagiuo Training Center',
            'branch_code' => '0000004',
            'location' => 'Bagiuo City'
        ]);

        Branch::create([
            'name' => 'Iloilo Business Hub',
            'branch_code' => '0000005',
            'location' => 'Iloilo City'
        ]);

        Branch::create([
            'name' => 'Laguna Operations Center',
            'branch_code' => '0000006',
            'location' => 'Sta Rosa, Laguna'
        ]);

        Branch::create([
            'name' => 'Bacolod Service Point',
            'branch_code' => '0000007',
            'location' => 'Bacolod City'
        ]);

        Branch::create([
            'name' => 'Cagayan de Oro Field Office',
            'branch_code' => '0000008',
            'location' => 'Cagayan de Oro City'
        ]);

        $group1 = Group::create([
            'name' => 'Corporate Group',
            'code' => 'G00001',
            'description' => 'Sample Description of the Corporate Group'
        ]);

        $division1 = Division::create([
            'name' => 'Operations Division',
            'code' => 'D00001',
            'description' => 'Sample Description of Operations Division',
            'group_id' => $group1->id,
        ]);

        $department1 = Department::create([
            'name' => 'Logistics Department',
            'code' => 'DD00001',
            'description' => 'Sample Description of Logistics Deparment',
            'group_id' => $group1->id,
            'division_id' => $division1->id
        ]);

        $section1 = Section::create([
            'name' => 'Fleet Management Section',
            'code' => 'S00001',
            'description' => 'Sample Description of Fleet Management Section',
            'group_id' => $group1->id,
            'division_id' => $division1->id,
            'department_id' => $department1->id
        ]);

        UserHasHeadOffice::create([
            'user_id' => $admin->id,
            'location' => 'BUENDIA',
            'group_id' => $group1->id,
            'division_id' => $division1->id,
            'department_id' => $department1->id,
            'section_id' => $section1->id,
        ]);

        UserHasBranchDealer::create([
            'user_id' => $user->id,
            'type' => 'BRANCH',
            'branch_id' => $branch1->id,
        ]);
    }
}
