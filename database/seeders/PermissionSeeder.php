<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::updateOrCreate(['id'=>1],['name' => 'Super Admin','guard_name'=> 'admin','user_type' => 'Admin']);
        // $role1 = Role::updateOrCreate(['id'=>2],['name' => 'ADMIN SALES MANAGER','guard_name'=> 'admin','user_type' => 'Admin User']);
        // $role2 = Role::updateOrCreate(['id'=>3],['name' => 'Agent','guard_name'=> 'admin','user_type' => 'Agency User']);
        // $role3 = Role::updateOrCreate(['id'=>4],['name' => 'Travel Agent book & Issue','guard_name'=> 'admin','user_type' => 'Agency User']);

        $permission = Permission::updateOrCreate(['id'=>1],['module_name' => 'Flights', 'name' => 'Availability-Search','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>2],['module_name' => 'Flights', 'name' => 'Book-PNR','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>3],['module_name' => 'Flights', 'name' => 'Issue-PNR','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>4],['module_name' => 'Flights', 'name' => 'Cancell-PNR','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>5],['module_name' => 'Flights', 'name' => 'Void-PNR','guard_name'=> 'admin']);

        $permission = Permission::updateOrCreate(['id'=>6],['module_name' => 'Admin Users', 'name' => 'List-Users','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>7],['module_name' => 'Admin Users', 'name' => 'Add-Users','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>8],['module_name' => 'Admin Users', 'name' => 'Edit-Users','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>9],['module_name' => 'Admin Users', 'name' => 'Delete-Users','guard_name'=> 'admin']);
        
        $permission = Permission::updateOrCreate(['id'=>10],['module_name' => 'Travel Agency', 'name' => 'List-Of-Travel-Agencies','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>11],['module_name' => 'Travel Agency', 'name' => 'Create-Travel-Agency','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>12],['module_name' => 'Travel Agency', 'name' => 'Edit-Travel-Agency','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>13],['module_name' => 'Travel Agency', 'name' => 'Delete-Travel-Agency','guard_name'=> 'admin']);
        
        $permission = Permission::updateOrCreate(['id'=>14],['module_name' => 'Credit Limit', 'name' => 'List-Credit-Limit','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>15],['module_name' => 'Credit Limit', 'name' => 'Add-Credit-Limit','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>16],['module_name' => 'Credit Limit', 'name' => 'Update-Credit-Limit','guard_name'=> 'admin']);
        
        $permission = Permission::updateOrCreate(['id'=>17],['module_name' => 'Travel Agency User', 'name' => 'List-Travel-Agents','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>18],['module_name' => 'Travel Agency User', 'name' => 'Create-Travel-Agents','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>19],['module_name' => 'Travel Agency User', 'name' => 'Edit-Travel-Agents','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>20],['module_name' => 'Travel Agency User', 'name' => 'Delete-Travel-Agents','guard_name'=> 'admin']);
        
        
        $permission = Permission::updateOrCreate(['id'=>21],['module_name' => 'Roles', 'name' => 'List-Of-Roles','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>22],['module_name' => 'Roles', 'name' => 'Create-Roles','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>23],['module_name' => 'Roles', 'name' => 'Edit-Roles','guard_name'=> 'admin']);
        $permission = Permission::updateOrCreate(['id'=>24],['module_name' => 'Roles', 'name' => 'Delete-Roles','guard_name'=> 'admin']);
        
        
        $permission = Permission::updateOrCreate(['id'=>100],['module_name' => 'Settings', 'name' => 'Read-Settings','guard_name'=> 'admin']);

        $permissions = Permission::where('guard_name','admin')->get();
        foreach($permissions as $permission){
            $role->givePermissionTo($permission);
        }
        $admin = Admin::first();
        $admin2 = Admin::find(2);
        $admin->assignRole($role,);
        $admin2->assignRole($role,);
    }
}
