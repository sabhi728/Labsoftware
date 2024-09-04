<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabProfileDetails;
use App\Models\LabProfiles;
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

class LabProfileController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $labProfiles = LabProfiles::get();
        return view('admin.LabProfile.index', compact('user', 'labProfiles'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.LabProfile.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $labProfile = LabProfiles::find($id);
        return view('admin.LabProfile.add', compact('user', 'labProfile'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();

        $labProfile = new LabProfiles();
        $labProfile->name = $request->profileName;
        $labProfile->amount = $request->profileAmount;
        $labProfile->status = $request->status;

        if ($labProfile->save()) {
            return redirect('lab_profile/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();

        $labProfile = LabProfiles::find($id);
        $labProfile->name = $request->profileName;
        $labProfile->amount = $request->profileAmount;
        $labProfile->status = $request->status;

        if ($labProfile->save()) {
            return redirect('lab_profile/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();

        LabProfileDetails::where('profile_id', $id)->delete();
        LabProfiles::find($id)->delete();

        return redirect()->back();
    }

    public function profileDetailsIndex($id)
    {
        $user = HomeController::getUserData();
        $labProfile = LabProfiles::find($id);
        $profileDetails = LabProfileDetails::where('profile_id', $id)
            ->select('*')
            ->selectRaw('(SELECT add_report.order_name FROM add_report WHERE add_report.report_id = lab_profile_details.order_id) AS order_name')
            ->get();

        return view('admin.LabProfile.profile_details', compact('user', 'labProfile', 'profileDetails'));
    }

    public function addProfileDetails(Request $request)
    {
        $user = HomeController::getUserData();

        $labProfileDetails = new LabProfileDetails();
        $labProfileDetails->profile_id = $request->profileId;
        $labProfileDetails->order_id = $request->orderId;

        $labProfileDetails->save();
        return redirect()->back();
    }

    public function deleteProfileDetail($id)
    {
        $user = HomeController::getUserData();
        LabProfileDetails::find($id)->delete();
        return redirect()->back();
    }

    public function smsIndex()
    {
        $user = HomeController::getUserData();
        $labProfiles = LabProfiles::get();
        $patientDetails = DB::table('patients')->select(DB::raw('MIN(id) as id, phone, MIN(patient_title_name) as patient_title_name, MIN(patient_name) as patient_name'))->groupBy('phone')->get();
        $allNumbers = "";

        foreach ($patientDetails as $patient) {
            if ($allNumbers == "") {
                $allNumbers = $patient->phone;
            } else {
                $allNumbers = $allNumbers . "," . $patient->phone;
            }
        }

        $profileString = "";
        foreach ($labProfiles as $labProfile) {
            if ($profileString == "") {
                $profileString .= $labProfile->name . '-' . $labProfile->amount;
            } else {
                $profileString .= ', ' . $labProfile->name . '-' . $labProfile->amount;
            }
        }

        $message = "Dear customer, Greatings from " . $user->settings['lab_name'] . ", we are offering " . $profileString . " tests this month. Phone " . $user->settings['phone_number'] . ". M Star Diagnostics";
        return view('admin.LabProfile.sms', compact('user', 'message', 'patientDetails', 'allNumbers'));
    }

    public function sendOfferMessage($phoneNumber, $message)
    {
        $user = HomeController::getUserData();

        $smsTempleteId = "1007170227613005701";
        HomeController::sendBulkSMS($phoneNumber, $message, $smsTempleteId);

        return redirect()->back();
    }

    public function letterhad()
    {
        $user = HomeController::getUserData();
        $labProfiles = LabProfiles::get();
        return view('admin.LabProfile.letterhad', compact('user', 'labProfiles'));
    }
}
