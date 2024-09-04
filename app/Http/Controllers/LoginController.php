<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminControllers\HomeController;
use App\Http\Controllers\AdminControllers\OrderBillsController;
use App\Models\LoginHistory;
use App\Models\AdminMenuOptions;

use App\Http\Controllers\Controller;
use App\Models\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function routeAdd()
    {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = implode('|', $route->methods());

            // Check if the route with the same URI and method exists in the table
            $existingRoute = AdminMenuOptions::where('url', $uri)->first();

            if (!$existingRoute) {
                $adminMenuOptions = new AdminMenuOptions();
                $adminMenuOptions->name = str_replace('/', '_', $uri);
                $adminMenuOptions->url = $uri;
                $adminMenuOptions->is_visible = 'false';
                $adminMenuOptions->save();
            }
        }

        $this->info('Routes inserted into the database.');
    }

    public function loginUser(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($validatedData)) {
            $user = Auth::user();
            if ($user->status == "active") {
                $loginHistory = new LoginHistory();
                $loginHistory->user_id = Auth::user()->id;
                $loginHistory->ip_address = HttpRequest::ip();
                if (!empty($request->latitude)) {
                    $loginHistory->coordinates = $request->latitude . ',' . $request->longitude;
                }
                $loginHistory->save();

                Session::put('user', $user);
                Session::put('loginHistory', $loginHistory);
                return redirect('/home');
            } else {
                $this->logout();
                return redirect()->back()->withErrors("This account is blocked")->withInput();
            }
        } else {
            return redirect()->back()->withErrors("Invalid email or password")->withInput();
        }
    }

    public function logout()
    {
        $session = Session::get('loginHistory');

        $currentDateTime = Carbon::now();
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

        $loginHistory = LoginHistory::find($session->id);
        $loginHistory->logout_time = $currentDateTime;
        $loginHistory->save();

        Auth::logout();
        Session::flush();
        return redirect('/');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if ($request->oldPassword == "") {
            return json_encode(["status" => "failed", "message" => "Enter old password."]);
        } else if ($request->newPassword == "") {
            return json_encode(["status" => "failed", "message" => "Enter new password."]);
        } else if ($request->newPassword == $request->oldPassword) {
            return json_encode(["status" => "failed", "message" => "Old password and new password cannot be same."]);
        } else if ($request->newPassword != $request->confirmPassword) {
            return json_encode(["status" => "failed", "message" => "New Password and Confirm Password didn\'t match."]);
        }

        if (Hash::check($request->oldPassword, $user->password)) {
            $newPassword = Hash::make($request->input('newPassword'));
            $user->update([
                'password' => $newPassword,
            ]);
            return json_encode(["status" => "success", "message" => "Password changed successfully"]);
        } else {
            return json_encode(["status" => "failed", "message" => "Old Password didn't match"]);
        }
    }

    public function editProfile()
    {
        $user = HomeController::getUserData();

        if ($user->role == "Admin") {
            $systemSettings = SystemSettings::first();
            return view('edit_profile', compact('user', 'systemSettings'));
        } else {
            return redirect("/");
        }
    }

    public function updateProfile(Request $request)
    {
        $user = HomeController::getUserData();

        $uploadResultHeaderPath = null;
        $uploadBillHeaderPath = null;
        $uploadConsultingBillHeaderPath = null;
        $uploadIPBillHeaderPath = null;
        $uploadReportBackgroundPath = null;
        $uploadBillStampPath = null;
        $uploadBillSignaturePath = null;
        $uploadPrescriptionBackgroundImagePath = null;
        $uploadReferralPanelIconPath = null;

        if ($request->hasFile('uploadResultHeader')) {
            $uploadResultHeader = $request->file('uploadResultHeader');
            $uploadResultHeaderPath = 'uploads/result_header.' . $uploadResultHeader->getClientOriginalExtension();
            $uploadResultHeader->move(public_path('uploads'), 'result_header.' . $uploadResultHeader->getClientOriginalExtension());
        }

        if ($request->hasFile('uploadBillHeader')) {
            $uploadBillHeader = $request->file('uploadBillHeader');
            $uploadBillHeaderPath = 'uploads/bill_header.' . $uploadBillHeader->getClientOriginalExtension();
            $uploadBillHeader->move(public_path('uploads'), 'bill_header.' . $uploadBillHeader->getClientOriginalExtension());
        }

        if ($request->hasFile('uploadConsultingBillHeader')) {
            $uploadConsultingBillHeader = $request->file('uploadConsultingBillHeader');
            $uploadConsultingBillHeaderPath = 'uploads/consulting_bill_header.' . $uploadConsultingBillHeader->getClientOriginalExtension();
            $uploadConsultingBillHeader->move(public_path('uploads'), 'consulting_bill_header.' . $uploadConsultingBillHeader->getClientOriginalExtension());
        }

        if ($request->hasFile('uploadIPBillHeader')) {
            $uploadIPBillHeader = $request->file('uploadIPBillHeader');
            $uploadIPBillHeaderPath = 'uploads/ip_bill_header.' . $uploadIPBillHeader->getClientOriginalExtension();
            $uploadIPBillHeader->move(public_path('uploads'), 'ip_bill_header.' . $uploadIPBillHeader->getClientOriginalExtension());
        }

        if ($request->hasFile('reportBackground')) {
            $reportBackground = $request->file('reportBackground');
            $uploadReportBackgroundPath = 'uploads/report_background.' . $reportBackground->getClientOriginalExtension();
            $reportBackground->move(public_path('uploads'), 'report_background.' . $reportBackground->getClientOriginalExtension());
        }

        if ($request->hasFile('billStamp')) {
            $billStamp = $request->file('billStamp');
            $uploadBillStampPath = 'uploads/bill_stamp.' . $billStamp->getClientOriginalExtension();
            $billStamp->move(public_path('uploads'), 'bill_stamp.' . $billStamp->getClientOriginalExtension());
        }

        if ($request->hasFile('billSignature')) {
            $billSignature = $request->file('billSignature');
            $uploadBillSignaturePath = 'uploads/bill_signature.' . $billSignature->getClientOriginalExtension();
            $billSignature->move(public_path('uploads'), 'bill_signature.' . $billSignature->getClientOriginalExtension());
        }

        if ($request->hasFile('prescriptionBackgroundImage')) {
            $prescriptionBackgroundImage = $request->file('prescriptionBackgroundImage');
            $uploadPrescriptionBackgroundImagePath = 'uploads/prescription_background_image.' . $prescriptionBackgroundImage->getClientOriginalExtension();
            $prescriptionBackgroundImage->move(public_path('uploads'), 'prescription_background_image.' . $prescriptionBackgroundImage->getClientOriginalExtension());
        }

        if ($request->hasFile('referralPanelIcon')) {
            $referralPanelIcon = $request->file('referralPanelIcon');
            $uploadReferralPanelIconPath = 'uploads/referral_panel_icon.' . $referralPanelIcon->getClientOriginalExtension();
            $referralPanelIcon->move(public_path('uploads'), 'referral_panel_icon.' . $referralPanelIcon->getClientOriginalExtension());
        }

        $uploadResultHeaderUrl = $uploadResultHeaderPath ? $uploadResultHeaderPath : null;
        $uploadBillHeaderUrl = $uploadBillHeaderPath ? $uploadBillHeaderPath : null;
        $uploadConsultingBillHeaderUrl = $uploadConsultingBillHeaderPath ? $uploadConsultingBillHeaderPath : null;
        $uploadIPBillHeaderUrl = $uploadIPBillHeaderPath ? $uploadIPBillHeaderPath : null;
        $uploadReportBackgroundUrl = $uploadReportBackgroundPath ? $uploadReportBackgroundPath : null;
        $uploadBillStampUrl = $uploadBillStampPath ? $uploadBillStampPath : null;
        $uploadBillSignatureUrl = $uploadBillSignaturePath ? $uploadBillSignaturePath : null;
        $uploadPrescriptionBackgroundImageUrl = $uploadPrescriptionBackgroundImagePath ? $uploadPrescriptionBackgroundImagePath : null;
        $uploadReferralPanelIconeUrl = $uploadReferralPanelIconPath ? $uploadReferralPanelIconPath : null;

        $systemSettings = SystemSettings::first();
        $systemSettings->lab_name = $request->labName;
        $systemSettings->phone_number = $request->phoneNumber;
        $systemSettings->email_address = $request->emailAddress;
        $systemSettings->phone_number_2 = $request->phoneNumber2;
        $systemSettings->address = $request->address;
        $systemSettings->location = $request->location;
        $systemSettings->bill_footer = $request->billFooter;
        $systemSettings->lab_name_on_bill = $request->labNameOnBill;
        $systemSettings->barcode_machince_code = $request->pnrFileText;
        $systemSettings->lab_address_for_invoice = $request->labAddressForInvoice;

        if ($uploadResultHeaderUrl != null)
            $systemSettings->result_header = $uploadResultHeaderUrl;
        if ($uploadBillHeaderUrl != null)
            $systemSettings->bill_header = $uploadBillHeaderUrl;
        if ($uploadConsultingBillHeaderUrl != null)
            $systemSettings->header_consulting_bill = $uploadConsultingBillHeaderUrl;
        if ($uploadIPBillHeaderUrl != null)
            $systemSettings->header_ip_billing = $uploadIPBillHeaderUrl;

        $systemSettings->billing_message_format = $request->billingMessageFormat;
        $systemSettings->no_print_balance_reports = isset($request->no_print_balance_reports) ? "true" : "false";
        $systemSettings->unique_order_entry = isset($request->unique_order_entry) ? "true" : "false";
        $systemSettings->no_sms_after_billing = isset($request->no_sms_after_billing) ? "true" : "false";
        $systemSettings->sample_time_in_reports = isset($request->sample_time_in_reports) ? "true" : "false";
        $systemSettings->patient_phone_not_required = isset($request->patient_phone_not_required) ? "true" : "false";

        if ($uploadReportBackgroundUrl != null)
            $systemSettings->report_background = $uploadReportBackgroundUrl;
        if ($uploadBillStampUrl != null)
            $systemSettings->bill_stamp = $uploadBillStampUrl;
        if ($uploadBillSignatureUrl != null)
            $systemSettings->bill_signature = $uploadBillSignatureUrl;
        if ($uploadPrescriptionBackgroundImageUrl != null)
            $systemSettings->prescription_background_image = $uploadPrescriptionBackgroundImageUrl;
        if ($uploadReferralPanelIconeUrl != null)
            $systemSettings->referral_panel_icon = $uploadReferralPanelIconeUrl;

        $systemSettings->save();

        return redirect()->back();
    }

    public function removeHeaderFile($which)
    {
        $user = HomeController::getUserData();

        if ($user->role == "Admin") {
            $systemSettings = SystemSettings::first();

            if ($which == "result") {
                $systemSettings->result_header = NULL;
            } else if ($which == "bill") {
                $systemSettings->bill_header = NULL;
            } else if ($which == "consulting") {
                $systemSettings->header_consulting_bill = NULL;
            } else if ($which == "ip") {
                $systemSettings->header_ip_billing = NULL;
            } else if ($which == "report") {
                $systemSettings->report_background = NULL;
            } else if ($which == "bill_stamp") {
                $systemSettings->bill_stamp = NULL;
            } else if ($which == "bill_signature") {
                $systemSettings->bill_signature = NULL;
            } else if ($which == "prescription_background_image") {
                $systemSettings->prescription_background_image = NULL;
            } else if ($which == "referral_panel_icon") {
                $systemSettings->referral_panel_icon = NULL;
            }

            $systemSettings->save();
        }

        return redirect()->back();
    }
}
