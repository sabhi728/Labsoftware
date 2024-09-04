<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\DepartmentSignatures;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\Department;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\OrderEntryTransactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\AdminControllers\HomeController;

class DepartmentMaintenanceController extends Controller
{
    public function index() {
        $user = HomeController::getUserData();
        $departments = Department::where('department_name', '!=', 'No Department Signatures')->get();
        return view('admin.DepartmentMaintenance.index', compact('user', 'departments'));
    }

    public function noDepartmentIndex() {
        $user = HomeController::getUserData();
        $departId = 19;
        $department = Department::where('depart_id', $departId)->first();
        return view('admin.DepartmentMaintenance.none_department_signatures', compact('user', 'department', 'departId'));
    }

    public function addIndex() {
        $user = HomeController::getUserData();
        return view('admin.DepartmentMaintenance.add', compact('user'));
    }

    public function editIndex($id) {
        $user = HomeController::getUserData();
        $department = Department::find($id);
        return view('admin.DepartmentMaintenance.add', compact('user', 'department'));
    }

    public function add(Request $request) {
        $user = HomeController::getUserData();

        $uploadSignatureImagePath = null;
        $uploadLeftSignatureImagePath = null;

        if ($request->hasFile('signatureImage')) {
            $signatureImage = $request->file('signatureImage');
            $extension = $signatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $signatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        if ($request->hasFile('leftSignatureImage')) {
            $leftSignatureImage = $request->file('leftSignatureImage');
            $extension = $leftSignatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadLeftSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $leftSignatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        $uploadSignatureImageUrl = $uploadSignatureImagePath ? $uploadSignatureImagePath : null;
        $uploadLeftSignatureImageUrl = $uploadLeftSignatureImagePath ? $uploadLeftSignatureImagePath : null;

        $department = new Department();
        $department->department_name = $request->departmentName;
        $department->signature_label = $request->signatureLabel;
        $department->left_signature_label = $request->leftSignatureLabel;

        if ($request->has('printResultIndividualPage')) $department->print_results_in_individual_pages = "true";
        if ($uploadSignatureImageUrl != null) $department->signature_image = $uploadSignatureImageUrl;
        if ($uploadLeftSignatureImageUrl != null) $department->left_signature_image = $uploadLeftSignatureImageUrl;

        if ($department->save()) {
            return redirect('department/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request) {
        $user = HomeController::getUserData();

        $uploadSignatureImagePath = null;
        $uploadLeftSignatureImagePath = null;

        if ($request->hasFile('signatureImage')) {
            $signatureImage = $request->file('signatureImage');
            $extension = $signatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $signatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        if ($request->hasFile('leftSignatureImage')) {
            $leftSignatureImage = $request->file('leftSignatureImage');
            $extension = $leftSignatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadLeftSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $leftSignatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        $uploadSignatureImageUrl = $uploadSignatureImagePath ? $uploadSignatureImagePath : null;
        $uploadLeftSignatureImageUrl = $uploadLeftSignatureImagePath ? $uploadLeftSignatureImagePath : null;

        $department = Department::find($id);
        $department->department_name = $request->departmentName;
        $department->signature_label = $request->signatureLabel;
        $department->left_signature_label = $request->leftSignatureLabel;

        if ($request->has('printResultIndividualPage')) $department->print_results_in_individual_pages = "true";
        if ($uploadSignatureImageUrl != null) $department->signature_image = $uploadSignatureImageUrl;
        if ($uploadLeftSignatureImageUrl != null) $department->left_signature_image = $uploadLeftSignatureImageUrl;

        if ($department->save()) {
            return redirect('department/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id) {
        $user = HomeController::getUserData();
        $department = Department::find($id);
        $department->delete();
        return redirect()->back();
    }

    public function removeDepartmentSignatureImage($position, $id) {
        $user = HomeController::getUserData();

        $department = Department::find($id);
        if ($position == 'right') {
            $department->signature_image = NULL;
        } else {
            $department->left_signature_image = NULL;
        }

        $department->save();
        return redirect()->back();
    }

    public function signaturesListIndex($departmentId) {
        $user = HomeController::getUserData();
        $signaturesList = DepartmentSignatures::where('department_id', $departmentId)->get();
        return view('admin.DepartmentMaintenance.signatures_list_index', compact('user', 'signaturesList', 'departmentId'));
    }

    public function signatureAddIndex($departmentId) {
        $user = HomeController::getUserData();
        return view('admin.DepartmentMaintenance.signature_add', compact('user', 'departmentId'));
    }

    public function signatureAdd($departmentId, Request $request) {
        $user = HomeController::getUserData();

        $uploadSignatureImagePath = null;
        $uploadLeftSignatureImagePath = null;

        if ($request->hasFile('signatureImage')) {
            $signatureImage = $request->file('signatureImage');
            $extension = $signatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $signatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        if ($request->hasFile('leftSignatureImage')) {
            $leftSignatureImage = $request->file('leftSignatureImage');
            $extension = $leftSignatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadLeftSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $leftSignatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        $uploadSignatureImageUrl = $uploadSignatureImagePath ? $uploadSignatureImagePath : null;
        $uploadLeftSignatureImageUrl = $uploadLeftSignatureImagePath ? $uploadLeftSignatureImagePath : null;

        $departmentSignatures = new DepartmentSignatures();
        $departmentSignatures->department_id = $departmentId;
        $departmentSignatures->signature_name = $request->signatureName;
        $departmentSignatures->signature_label = $request->signatureLabel;
        $departmentSignatures->left_signature_label = $request->leftSignatureLabel;

        if ($request->has('status')) $departmentSignatures->status = "In Active";
        if ($uploadSignatureImageUrl != null) $departmentSignatures->signature_image = $uploadSignatureImageUrl;
        if ($uploadLeftSignatureImageUrl != null) $departmentSignatures->left_signature_image = $uploadLeftSignatureImageUrl;

        if ($departmentSignatures->save()) {
            return redirect('department/signatures_list_index/' . $departmentId);
        }
        return redirect()->back()->withInput();
    }

    public function signatureEditIndex($id) {
        $user = HomeController::getUserData();
        $signature = DepartmentSignatures::find($id);
        return view('admin.DepartmentMaintenance.signature_add', compact('user', 'signature'));
    }

    public function signatureUpdate($id, Request $request) {
        $user = HomeController::getUserData();

        $uploadSignatureImagePath = null;
        $uploadLeftSignatureImagePath = null;

        if ($request->hasFile('signatureImage')) {
            $signatureImage = $request->file('signatureImage');
            $extension = $signatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $signatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        if ($request->hasFile('leftSignatureImage')) {
            $leftSignatureImage = $request->file('leftSignatureImage');
            $extension = $leftSignatureImage->getClientOriginalExtension();
            $randomName = hash('sha256', time() . uniqid()) . '.' . $extension;
            $uploadLeftSignatureImagePath = 'uploads/department_signatures/' . $randomName;
            $leftSignatureImage->move(public_path('uploads/department_signatures'), $randomName);
        }

        $uploadSignatureImageUrl = $uploadSignatureImagePath ? $uploadSignatureImagePath : null;
        $uploadLeftSignatureImageUrl = $uploadLeftSignatureImagePath ? $uploadLeftSignatureImagePath : null;

        $departmentSignatures = DepartmentSignatures::find($id);
        $departmentSignatures->signature_name = $request->signatureName;
        $departmentSignatures->signature_label = $request->signatureLabel;
        $departmentSignatures->left_signature_label = $request->leftSignatureLabel;

        if ($request->has('status')) $departmentSignatures->status = "In Active";
        if ($uploadSignatureImageUrl != null) $departmentSignatures->signature_image = $uploadSignatureImageUrl;
        if ($uploadLeftSignatureImageUrl != null) $departmentSignatures->left_signature_image = $uploadLeftSignatureImageUrl;

        if ($departmentSignatures->save()) {
            return redirect('department/signatures_list_index/' . $departmentSignatures->department_id);
        }
        return redirect()->back()->withInput();
    }

    public function removeSignatureImage($position, $id) {
        $user = HomeController::getUserData();

        $departmentSignatures = DepartmentSignatures::find($id);
        if ($position == 'right') {
            $departmentSignatures->signature_image = NULL;
        } else {
            $departmentSignatures->left_signature_image = NULL;
        }

        $departmentSignatures->save();
        return redirect()->back();
    }
}
