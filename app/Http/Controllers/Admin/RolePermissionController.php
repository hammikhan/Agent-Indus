<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Requests\UserGroup\StoreRequest;

class RolePermissionController extends Controller
{
    public function roleList(){
        $all_roles = Role::where('name','!=', 'Super Admin')->get();
        $permission_all = Permission::all()->groupBy('module_name');
        $permission_all_array = Permission::pluck('name')->all();
        return view('admin.usergroup.role',compact('all_roles','permission_all','permission_all_array'));
    }
    public function createRole(StoreRequest $request)
    {
        $request->validated();
        try {
            // Check if the role already exists in the database
            $existingRole = Role::where('name', $request->roleName)->first();

            if ($existingRole) {
                return response()->json(['status' => 'error', 'message' => 'Role Name Already Exists']);
            }

            DB::beginTransaction();
            $role = Role::create(['name' => $request->roleName, 'guard_name' => 'admin', 'user_type' => $request->user_type]);
            $permissions = Permission::whereIn('id', $request->permissionArray)->get();

            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Role Created Successfully']);
        } catch (\Exception $exception) {
            DB::rollback();
            if (env('APP_ENV') == "local") {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
        }
    }
    public function deleteRole(Role $role_id)
    {
        // Check if the role is assigned to any user
        $usersWithRole = $role_id->users;

        if (!$usersWithRole->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'This role is already assigned to some users.']);
        }

        try {
            DB::beginTransaction();
            // Detach all permissions from the role before deleting
            $role_id->permissions()->detach();

            // Delete the role
            $role_id->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Role deleted successfully.']);
        } catch (\Exception $exception) {
            DB::rollback();
            if (env('APP_ENV') == "local") {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
        }
    }
    public function editRole(Request $request){
        $role = Role::find($request->roleId);
        $permissionNames = $role->permissions->pluck('name')->all();
        $permission_all = Permission::all()->groupBy('module_name');
        return view('admin.usergroup.permission_render',compact('role','permission_all','permissionNames'));
    }
    public function updateRole(Request $request){
        try{
            DB::beginTransaction();
            $role = Role::where('id',$request->idRole)->first();
            $checkRole = Role::where('name',$request->roleName)
                        ->where('id','!=',$request->idRole)
                        ->first();
            
            if($checkRole){
                return response()->json(['status' => 'error','message'=>'Role not available']);
            }

            $roleData['name'] = $request->roleName;
            $roleData['user_type'] = $request->user_type;
            $newInstance = $role->update($roleData);
            DB::table('role_has_permissions')->where('role_id',$request->idRole)->delete();
            $permissions = Permission::whereIn('id',$request->permissionArray)->get();
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            };
            
            DB::commit();
            return response()->json(['status'=>'success','message'=>'Role Updated Successfully']);
        }
        catch(\Exception $exception){
            DB::rollBack();
            return $exception->getMessage();
        }
    }

}
