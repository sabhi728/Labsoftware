<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\Doctors;
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

class DoctorsController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $doctors = Doctors::get();
        return view('admin.Doctors.index', compact('user', 'doctors'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.Doctors.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $doctor = Doctors::find($id);
        return view('admin.Doctors.add', compact('user', 'doctor'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();

        $isDoctorExist = Doctors::where('doc_name', $request->doctorName)->first();

        if (!$isDoctorExist) {
            $doctor = new Doctors();
            $doctor->doc_name = $request->doctorName;
            $doctor->doc_type = $request->doctorType;
            $doctor->doc_percentage = ($request->doctorPercentage == null) ? "0" : $request->doctorPercentage;
            $doctor->doc_address = $request->doctorAddress;
            $doctor->doc_phone_num = $request->doctorPhoneNumber;
            $doctor->doc_email = $request->doctorEmail;
            $doctor->doc_category = $request->doctorCategory;

            if ($doctor->save()) {
                return redirect('doctors/index');
            }
            return redirect()->back()->withInput();
        }

        return "Error: Doctor with this name already exist.";
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();
        $doctor = Doctors::find($id);
        $doctor->doc_name = $request->doctorName;
        $doctor->doc_type = $request->doctorType;
        $doctor->doc_percentage = ($request->doctorPercentage == null) ? "0" : $request->doctorPercentage;
        $doctor->doc_address = $request->doctorAddress;
        $doctor->doc_phone_num = $request->doctorPhoneNumber;
        $doctor->doc_email = $request->doctorEmail;
        $doctor->doc_category = $request->doctorCategory;

        if ($doctor->save()) {
            return redirect('doctors/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $doctor = Doctors::find($id);
        $doctor->delete();
        return redirect()->back();
    }
}
