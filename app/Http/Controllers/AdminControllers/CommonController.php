<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabLocations;
use App\Models\LabProfiles;
use App\Models\OrderReturnAmount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\Doctors;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\OrderEntryTransactions;
use App\Models\LoginHistory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\AdminControllers\HomeController;

class CommonController extends Controller
{
    public $todayDate;

    public $orderTypeProfile = "(profile)";

    public $reportStatusNotFound = "Not found";
    public $reportSampleReceived = "Sample Received";
    public $reportSampleCollected = "Sample Collected";
    public $reportStatusSave = "Save";
    public $reportStatusSaveAndComplete = "Save And Complete";
    public $reportStatusApproved = "Approved";
    public $reportStatusRetest = "Retest";
    public $reportStatusDispatched = "Dispatched";
    public $reportStatusSentOnWhatsApp = "Sent on WhatsApp";

    public function __construct()
    {
        $this->todayDate = now()->toDateString();
    }

    public function isReportCompleted($reportStatus)
    {
        return $reportStatus == $this->reportStatusDispatched
            || $reportStatus == $this->reportStatusApproved
            || $reportStatus == $this->reportStatusSaveAndComplete
            || $reportStatus == $this->reportStatusSentOnWhatsApp;
    }

    public function sendUpdateSMS($billNo)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $orderEntry->umr_number)->first();

        $orderIdsArray = explode(',', $orderEntry->order_ids);
        $ordersNameTxt = "";

        foreach ($orderIdsArray as $key => $id) {
            if (str_contains($id, $this->orderTypeProfile)) {
                $profileId = str_replace($this->orderTypeProfile, "", $id);
                $labProfile = LabProfiles::find($profileId);

                if ($labProfile) {
                    if ($ordersNameTxt == "") {
                        $ordersNameTxt = $labProfile->name;
                    } else {
                        $ordersNameTxt = $ordersNameTxt . ", " . $labProfile->name;
                    }
                }
            } else {
                $data = AddReport::find($id);

                if ($data) {
                    if ($ordersNameTxt == "") {
                        $ordersNameTxt = $data->order_name;
                    } else {
                        $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                    }
                }
            }
        }

        $inputMessage = "Dear [PatientName](UMR: [UMRNumber]), your bill [BillNumber] is updated. Your current orders are [Orders]. From [LabName], Phone: [PhoneNo].M STAR";
        $replacementArray = array(
            "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
            "[UMRNumber]" => $patientData->umr_number,
            "[BillNumber]" => $billNo,
            "[Orders]" => $ordersNameTxt,
            "[LabName]" => $user->settings['lab_name'],
            "[PhoneNo]" => $user->settings['phone_number']
        );

        $smsPhone = $patientData->phone;
        $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $inputMessage);
        $smsTempleteId = "1007166910928858162";
        HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);

        return redirect()->back();
    }

}
