<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
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

class IPCertificateController extends Controller
{
    public function index() {
        $user = HomeController::getUserData();
        $ipBillingCategories = IPBillingCategoryType::get();

        return view('admin.IPCertificate.index', compact('user', 'ipBillingCategories'));
    }

    public function addIndex() {
        $user = HomeController::getUserData();
        return view('admin.IPCertificate.add', compact('user'));
    }

    public function editIndex($id) {
        $user = HomeController::getUserData();
        $ipBilling = IPBillingCategoryType::find($id);
        return view('admin.IPCertificate.add', compact('user', 'ipBilling'));
    }

    public function add(Request $request) {
        $user = HomeController::getUserData();
        $ipBilling = new IPBillingCategoryType();
        $ipBilling->name = $request->certificateName;
        $ipBilling->content = $request->content;
        if ($ipBilling->save()) {
            return redirect('ip_certificate/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request) {
        $user = HomeController::getUserData();
        $ipBilling = IPBillingCategoryType::find($id);
        $ipBilling->name = $request->certificateName;
        $ipBilling->content = $request->content;
        if ($ipBilling->save()) {
            return redirect('ip_certificate/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id) {
        $user = HomeController::getUserData();
        $ipBilling = IPBillingCategoryType::find($id);
        $ipBilling->delete();
        return redirect()->back();
    }
}
