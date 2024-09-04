<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LabLocations;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\AdminRoles;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\OrderEntryTransactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\AdminControllers\HomeController;
use App\Models\AdminMenuOptions;

class LabUserController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $users = User::select('*')
            ->selectSub(
                function ($query) {
                    $query->select('name')
                        ->from('admin_roles')
                        ->whereColumn('admin_roles.id', 'users.role');
                },
                'role'
            )
            ->selectSub(
                function ($query) {
                    $query->select('location_name')
                        ->from('lab_locations')
                        ->whereColumn('lab_locations.id', 'users.lab_location');
                },
                'location_name'
            )
            ->get();
        return view('admin.LabUser.index', compact('user', 'users'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        $labRoles = AdminRoles::get();
        $labLocations = LabLocations::get();
        $departments = Department::where('department_name', '!=', NULL)->get();

        return view('admin.LabUser.add', compact('user', 'labRoles', 'labLocations', 'departments'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $users = User::find($id);
        $labRoles = AdminRoles::get();
        $labLocations = LabLocations::get();
        $departments = Department::where('department_name', '!=', NULL)->get();

        return view('admin.LabUser.add', compact('user', 'users', 'labRoles', 'labLocations', 'departments'));
    }

    public function rolesIndex()
    {
        $user = HomeController::getUserData();
        $adminRoles = AdminRoles::get();
        return view('admin.LabUser.roles_index', compact('user', 'adminRoles'));
    }

    public function roleAddIndex()
    {
        $user = HomeController::getUserData();
        $adminMenuOptions = AdminMenuOptions::get();
        return view('admin.LabUser.roles_add', compact('user', 'adminMenuOptions'));
    }

    public function roleEditIndex($id)
    {
        $user = HomeController::getUserData();
        $adminMenuOptions = AdminMenuOptions::get();
        $labRoles = AdminRoles::find($id);
        $labRoleAccess = explode(",", $labRoles->access);
        return view('admin.LabUser.roles_add', compact('user', 'labRoles', 'adminMenuOptions', 'labRoleAccess'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();

        $user = new User();
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->lab_location = $request->labLocation;
        $user->department_access = $request->departmentAccess;
        $user->status = $request->status;

        if ($user->save()) {
            return redirect('labuser/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();

        $user = User::find($id);
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->username = $request->username;
        $user->email = $request->email;
        if ($request->password != "")
            $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->lab_location = $request->labLocation;
        $user->department_access = $request->departmentAccess;
        $user->status = $request->status;

        if ($user->save()) {
            return redirect('labuser/index');
        }
        return redirect()->back()->withInput();
    }

    public function updatePassword($id, Request $request)
    {
        $user = HomeController::getUserData();

        $user = User::find($id);
        $user->password = Hash::make($request->password);

        if ($user->save()) {
            return response()->json(['message' => "Password changed successfully"], 200);
        }

        return response()->json(['message' => "Password change failed"], 200);
    }

    public function roleAdd(Request $request)
    {
        $user = HomeController::getUserData();
        $roles = implode(",", $request->roleAccess);

        $adminRoles = new AdminRoles();
        $adminRoles->name = $request->roleName;
        $adminRoles->access = $roles;

        if ($adminRoles->save()) {
            return redirect('labuser/roles_index');
        }
        return redirect()->back()->withInput();
    }

    public function roleUpdate($id, Request $request)
    {
        $user = HomeController::getUserData();
        if ($request->roleAccess == "") {
            return redirect()->back()->withInput();
        }
        $roles = implode(",", $request->roleAccess);

        $adminRoles = AdminRoles::find($id);
        $adminRoles->name = $request->roleName;
        $adminRoles->access = $roles;

        if ($adminRoles->save()) {
            return redirect('labuser/roles_index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $user = User::find($id);
        $user->delete();
        return redirect()->back();
    }

    public function roleDelete($id)
    {
        $user = HomeController::getUserData();
        $role = AdminRoles::find($id);
        $role->delete();
        return redirect()->back();
    }
}
