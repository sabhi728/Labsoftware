<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\ReferralCompany;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use App\Models\LabLocations;
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

class ReferralCompanyController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $referralCompanies = ReferralCompany::get();

        return view('admin.ReferralCompany.index', compact('user', 'referralCompanies'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.ReferralCompany.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $referralCompany = ReferralCompany::find($id);
        return view('admin.ReferralCompany.add', compact('user', 'referralCompany'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();

        $isReferralCompanyExist = ReferralCompany::where('name', $request->customerName)->first();

        if (!$isReferralCompanyExist) {
            $referralCompany = new ReferralCompany();
            $referralCompany->name = $request->customerName;
            $referralCompany->address = $request->address;
            $referralCompany->phone_number = $request->phoneNumber;
            $referralCompany->discount = $request->discount;
            $referralCompany->show_dashboard = $request->showDashboard ? "true" : "false";
            $referralCompany->show_bill_reports = $request->showBillReports ? "true" : "false";

            if (!empty($request->username) && !empty($request->password)) {
                $isUsernameExist = ReferralCompany::where('username', $request->username)->first();

                if ($isUsernameExist) {
                    return "Error: Entered username already used.";
                }

                $referralCompany->username = $request->username;
                $referralCompany->password = Hash::make($request->password);
            }

            if ($referralCompany->save()) {
                return redirect('referral_company/index');
            }

            return redirect()->back()->withInput();
        }

        return "Error: Referral company with this name already exist.";
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();

        $referralCompany = ReferralCompany::find($id);
        $referralCompany->name = $request->customerName;
        $referralCompany->address = $request->address;
        $referralCompany->phone_number = $request->phoneNumber;
        $referralCompany->discount = $request->discount;
        $referralCompany->show_dashboard = $request->showDashboard ? "true" : "false";
        $referralCompany->show_bill_reports = $request->showBillReports ? "true" : "false";

        if (!empty($request->username)) {
            $isUsernameExist = ReferralCompany::where('username', $request->username)->first();

            if ($isUsernameExist) {
                if ($isUsernameExist->id != $id) {
                    return "Error: Entered username already used.";
                }
            }

            $referralCompany->username = $request->username;
        }

        if (!empty($request->password)) {
            $referralCompany->password = Hash::make($request->password);
        }

        if ($referralCompany->save()) {
            return redirect('referral_company/index');
        }

        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $referralCompany = ReferralCompany::find($id);
        $referralCompany->delete();
        return redirect()->back();
    }
}
