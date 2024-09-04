<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\PrescriptionAttachments;
use App\Models\Prescriptions;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\IPBillingCategoryType;
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

class PrescriptionsController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $prescriptions = Prescriptions::get();

        return view('admin.Prescriptions.index', compact('user', 'prescriptions'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.Prescriptions.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $prescription = Prescriptions::find($id);
        $prescriptionAttachments = PrescriptionAttachments::where("prescription_id", $id)->get();

        return view('admin.Prescriptions.add', compact('user', 'prescription', 'prescriptionAttachments'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();
        $prescription = new Prescriptions();
        $prescription->created_by = $user->id;
        $prescription->name = $request->name;
        $prescription->age = $request->age;
        $prescription->content = $request->content;
        $prescription->patient_name = $request->patientName;
        $prescription->patient_phone = $request->patientPhone;

        if ($prescription->save()) {
            return redirect('prescriptions/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();
        $prescription = Prescriptions::find($id);
        $prescription->name = $request->name;
        $prescription->age = $request->age;
        $prescription->content = $request->content;
        $prescription->patient_name = $request->patientName;
        $prescription->patient_phone = $request->patientPhone;

        if ($prescription->save()) {
            return redirect('prescriptions/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $prescription = Prescriptions::find($id);
        $prescription->delete();
        return redirect()->back();
    }

    public function addAttachment($prescriptionId, Request $request)
    {
        $user = HomeController::getUserData();
        $attachment = $request->file('attachment');
        $randomName = $request->fileName;

        $extension = $attachment->getClientOriginalExtension();
        $newFileName = $randomName . '.' . $extension;

        $attachment->move(public_path('assets/uploads/prescriptions'), $newFileName);
        $attachment = 'assets/uploads/prescriptions/' . $newFileName;

        $prescriptionAttachment = new PrescriptionAttachments();
        $prescriptionAttachment->created_by = $user->id;
        $prescriptionAttachment->prescription_id = $prescriptionId;
        $prescriptionAttachment->file_name = $randomName;
        $prescriptionAttachment->file_path = $attachment;
        $prescriptionAttachment->save();

        return redirect()->back();
    }

    public function deleteAttachment($prescriptionId)
    {
        $user = HomeController::getUserData();
        $prescriptionAttachment = PrescriptionAttachments::find($prescriptionId);
        $prescriptionAttachment->delete();
        return redirect()->back();
    }
}
