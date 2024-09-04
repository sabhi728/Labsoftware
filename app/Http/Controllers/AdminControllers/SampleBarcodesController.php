<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabProfileDetails;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\Department;
use App\Models\SampleType;
use App\Models\Patients;
use App\Models\IPBillingCategoryType;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\SampleBarcodes;
use App\Models\SystemSettings;
use App\Models\OrderTemplates;

use App\Models\ServiceGroups;
use App\Models\ServiceGroupOrders;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use App\Http\Controllers\AdminControllers\HomeController;
use App\Models\OrderEntry;

class SampleBarcodesController extends CommonController
{
    public function sampleCollectionIndex()
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderEntry::select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
            ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
            ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
            ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
            ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
            ->where('status', 'process')
            ->orderBy('order_entry.id', 'desc')
            ->paginate(10);

        foreach ($orderDetails as $orderKey => $order) {
            $showThisBill = false;
            $color = "black";
            $doesItHaveSamples = false;

            $orderIdsArray = explode(',', $order->order_ids);
            $ordersNameTxt = "";

            $index = 0;

            while ($index < count($orderIdsArray)) {
                $id = $orderIdsArray[$index];

                if (str_contains($id, $this->orderTypeProfile)) {
                    $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                    foreach ($labProfileDetails as $labProfile) {
                        $orderIdsArray[] = "" . $labProfile->order_id;
                    }
                }

                if (!empty(OrderBillsController::getSampleTypeText($id))) {
                    $doesItHaveSamples = true;

                    $checkBarcode = SampleBarcodes::where('bill_no', $order->bill_no)->where('sample_type', OrderBillsController::getSampleTypeText($id))->first();
                    if (!$checkBarcode || $checkBarcode->status == 'rejected') {
                        $showThisBill = true;
                    }

                    if ($checkBarcode && $checkBarcode->status == 'rejected') {
                        $color = "red";
                    }

                    if (!$checkBarcode || $checkBarcode->status == 'rejected') {
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

                $index++;
            }

            $order['order_name_txt'] = $ordersNameTxt;
            $order['color'] = $color;

            if (!$showThisBill) {
                unset($orderDetails[$orderKey]);
            } else {
                if (!$doesItHaveSamples) {
                    unset($orderDetails[$orderKey]);
                }
            }
        }

        return view('admin.SampleBarcodes.sample_collection', compact('user', 'orderDetails'));
    }

    public function sampleReceivalIndex()
    {
        $user = HomeController::getUserData();
        $sampleBarcodes = SampleBarcodes::select('*')
            ->where('status', 'collected')
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.SampleBarcodes.sample_receival', compact('user', 'sampleBarcodes'));
    }

    public function searchOrderWithBarcode($barcode)
    {
        $checkBarcode = SampleBarcodes::where('barcode', $barcode)->first();
        if (!$checkBarcode) {
            $returnValue = array(
                "status" => "failed",
                "message" => "Order not found"
            );
            return json_encode($returnValue);
        }

        $returnValue = array(
            "status" => "success",
            "message" => "Order found",
            "bill_no" => $checkBarcode->bill_no,
            "barcode_status" => $checkBarcode->status,
            "barcode" => $barcode
        );

        $orderEntry = OrderEntry::where('bill_no', $checkBarcode->bill_no)->first();
        $patientDetails = Patients::where('umr_number', $orderEntry->umr_number)->first();

        $returnValue['patient_name'] = $patientDetails->patient_title_name . ' ' . $patientDetails->patient_name;
        $returnValue['phone_number'] = $patientDetails->phone;
        $returnValue['gender'] = $patientDetails->gender;
        $returnValue['age'] = $patientDetails->age . ' ' . $patientDetails->age_type;
        $returnValue['umr'] = $patientDetails->umr_number;
        $returnValue['sample_type'] = $checkBarcode->sample_type;

        $orderIdsArray = explode(',', $checkBarcode->order_ids);
        $orderData = [];
        foreach ($orderIdsArray as $key => $id) {
            $data = AddReport::find($id);
            if ($data) {
                $orderData[] = $data;
            }
        }
        $returnValue['orderData'] = $orderData;

        return json_encode($returnValue);
    }

    public function updateSampleBarcodeStatus($barcode, $status, $rejectReason = "")
    {
        $checkBarcode = SampleBarcodes::where('barcode', $barcode)->first();
        $user = HomeController::getUserData();

        if ($checkBarcode) {
            $checkBarcode->status = $status;
            $checkBarcode->barcode_status_updated_on = date('Y-m-d H:i:s');
            $checkBarcode->barcode_status_updated_by = $user->id;
            $checkBarcode->reject_reason = $rejectReason;
            $checkBarcode->save();

            if ($rejectReason != "") {
                $orderIdsArray = explode(',', $checkBarcode->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    $data = AddReport::find($id);
                    if ($data) {
                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = $data->order_name;
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                        }
                    }
                }

                HomeController::sendSampleRejectSMS($checkBarcode->bill_no, $ordersNameTxt, $rejectReason);
            }

            return "success";
        }

        return "failed";
    }

    public function sampleCollectionDetailsIndex(Request $request)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $request->bill_no)->first();
        if (!$orderEntry) {
            $searchTerm = $request->bill_no;
            $checkOnUserData = Patients::where(function ($query) use ($searchTerm) {
                $query->where('umr_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('patient_title_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('patient_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            })->first();

            if (!$checkOnUserData) {
                return redirect()->back()->withInput();
            } else {
                $orderEntry = OrderEntry::where('umr_number', $checkOnUserData->umr_number)->orderBy('id', 'desc')->first();
                if (!$orderEntry) {
                    return redirect()->back()->withInput();
                }
            }
        }

        $patientDetails = Patients::where('umr_number', $orderEntry->umr_number)->first();
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $orderEntry->bill_no)
            ->first();

        $orderIdsArray = explode(',', $orderDetails->order_ids);
        $orderAmountArray = explode(',', $orderDetails->order_amount);

        $orderData = [];
        $index = 0;

        while ($index < count($orderIdsArray)) {
            $id = $orderIdsArray[$index];

            if (str_contains($id, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $orderIdsArray[] = "" . $labProfile->order_id;
                }
            }

            $data = AddReport::find($id);

            if ($data) {
                $orderSampleTypeIdsArray = explode(', ', $data->order_sample_type);
                $sampleTypeName = OrderBillsController::getSampleTypeText($id);

                if (!empty($sampleTypeName)) {
                    $checkSampleBarcode = SampleBarcodes::where('bill_no', $request->bill_no)->where('sample_type', $sampleTypeName)->first();

                    // if (!$checkSampleBarcode || $checkSampleBarcode->status == 'rejected') {
                    $barcode_number = ($checkSampleBarcode) ? $checkSampleBarcode->barcode : "";
                    $reject_reason = ($checkSampleBarcode) ? $checkSampleBarcode->reject_reason : "";

                    $color = "black";
                    if ($checkSampleBarcode && $checkSampleBarcode->status == 'rejected')
                        $color = "red";

                    $orderData[] = $data;
                    // $orderData[count($orderData) - 1]->custom_order_amount = $orderAmountArray[$index];
                    $orderData[count($orderData) - 1]->sample_type = $sampleTypeName;
                    $orderData[count($orderData) - 1]->barcode_number = $barcode_number;
                    $orderData[count($orderData) - 1]->reject_reason = $reject_reason;
                    $orderData[count($orderData) - 1]->color = $color;
                    // }
                }
            }

            $index++;
        }

        $orderDetails['orderData'] = $orderData;

        return view('admin.SampleBarcodes.sample_collection_details', compact('user', 'patientDetails', 'orderEntry', 'orderDetails'));
    }

    public function sampleCollectionSearch($search)
    {
        $user = HomeController::getUserData();
        $orderEntry = OrderEntry::with('patients')
            ->where(function ($query) use ($search) {
                $query->where('bill_no', 'LIKE', '%' . $search . '%')
                    ->orWhere('umr_number', 'LIKE', '%' . $search . '%');
            })
            ->orWhereHas('patients', function ($innerQuery) use ($search) {
                $innerQuery->where('patient_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            })
            ->get();

        return $orderEntry;
    }

    public function generateSampleBarcode($billNo, $sampleType, $orderIds, $barcode)
    {
        $checkSampleBarcode = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleType)->first();
        $user = HomeController::getUserData();

        if ($checkSampleBarcode) {
            $barcodeValue = $checkSampleBarcode->barcode;
        } else {
            $barcodeValue = rand(100000000000, 999999999999);
            if ($barcode != "0") {
                $checkBarcodeExist = SampleBarcodes::where('barcode', $barcode)->first();
                if ($checkBarcodeExist) {
                    return "Barcode already exist";
                } else {
                    $barcodeValue = $barcode;
                }
            }

            $sampleBarcode = new SampleBarcodes();
            $sampleBarcode->bill_no = $billNo;
            $sampleBarcode->created_by = $user->id;
            $sampleBarcode->barcode = $barcodeValue;
            $sampleBarcode->order_ids = $orderIds;
            $sampleBarcode->sample_type = $sampleType;
            $sampleBarcode->save();
        }

        $billData = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $billData->umr_number)->first();

        $barcodePrintingValues = $user->settings->barcode_machince_code;
        $barcodePrintingValues = str_replace('$PATIENT_NAME', '' . $patientData->patient_title_name . $patientData->patient_name . ' ' . $patientData->age . ' ' . $patientData->age_type, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$BARCODE', '' . $barcodeValue, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$PATIENT_GENDER', 'SEX ' . $patientData->gender, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$BILL_NO', '' . $billNo, $barcodePrintingValues);

        return $barcodePrintingValues;
    }

    public function regenerateSampleBarcode($billNo, $sampleType, $reason = "")
    {
        $user = HomeController::getUserData();

        $checkSampleBarcode = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleType)->first();
        $checkSampleBarcode->reprint_reason = $reason;
        $checkSampleBarcode->status = 'collected';
        $checkSampleBarcode->save();

        $billData = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $billData->umr_number)->first();

        $barcodePrintingValues = $user->settings->barcode_machince_code;
        $barcodePrintingValues = str_replace('$PATIENT_NAME', '' . $patientData->patient_title_name . $patientData->patient_name . ' ' . $patientData->age . ' ' . $patientData->age_type, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$BARCODE', '' . $checkSampleBarcode->barcode, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$PATIENT_GENDER', 'SEX ' . $patientData->gender, $barcodePrintingValues);
        $barcodePrintingValues = str_replace('$BILL_NO', 'ID: ' . $billNo, $barcodePrintingValues);

        return $barcodePrintingValues;
    }
}
