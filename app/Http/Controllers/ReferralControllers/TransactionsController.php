<?php

namespace App\Http\Controllers\ReferralControllers;

use App\Http\Controllers\AdminControllers\OrderBillsController;
use App\Http\Controllers\AdminControllers\OrderEntryController;
use App\Models\AddReport;
use App\Models\LabProfileDetails;
use App\Models\OrderEntry;
use App\Models\ResultReports;
use App\Models\SampleBarcodes;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionsController extends CommonController
{
    public function searchSubmittedSamples(Request $request)
    {
        $user = $this->getUserData();
        $todayDate = Carbon::now()->toDateString();

        $fromDate = isset($request->fromDate) ? $request->fromDate : $todayDate;
        $toDate = isset($request->toDate) ? $request->toDate : $todayDate;
        $searchType = isset($request->searchType) ? $request->searchType : '';
        $searchValue = isset($request->searchValue) ? $request->searchValue : '';

        if (!empty($fromDate) && !empty($toDate)) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('order_entry.referred_by_id', $user->id)
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->orderBy('order_entry.id', 'desc')
                ->get();

            foreach ($orderDetails as $orderKey => $order) {
                if ($searchValue != '') {
                    switch ($searchType) {
                        case 'InvName':
                            if (!str_contains(strtolower($this->getOrdersName($order->order_ids)), strtolower($searchValue))) {
                                unset($orderDetails[$orderKey]);
                                continue 2;
                            }
                            break;
                        case 'BillNo':
                            if (!str_contains(strtolower($order->bill_no), strtolower($searchValue))) {
                                unset($orderDetails[$orderKey]);
                                continue 2;
                            }
                            break;
                        case 'PatName':
                            if (!str_contains(strtolower($order->patient_name), strtolower($searchValue))) {
                                unset($orderDetails[$orderKey]);
                                continue 2;
                            }
                            break;
                    }
                }

                $orderIdsArray = explode(',', $order->order_ids);
                $index = 0;
                $items = [];

                while ($index < count($orderIdsArray)) {
                    $id = $orderIdsArray[$index];

                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                        foreach ($labProfileDetails as $labProfile) {
                            $orderIdsArray[] = "" . $labProfile->order_id;
                        }
                    }

                    if (!empty(OrderBillsController::getSampleTypeText($id))) {
                        $data = AddReport::find($id);

                        if ($data) {
                            $orderName = $data->order_name;
                            $barcodeNumber = '';
                            $sampleName = '';
                            $status = '';

                            $checkBarcode = SampleBarcodes::where('bill_no', $order->bill_no)
                                ->where('sample_type', OrderBillsController::getSampleTypeText($id))
                                ->first();

                            if ($checkBarcode) {
                                $barcodeNumber = $checkBarcode->barcode;
                                $sampleName = $checkBarcode->sample_type;
                                $status = ucfirst($checkBarcode->status);
                            }

                            $items[] = [
                                'order_name' => $orderName,
                                'barcode_number' => $barcodeNumber,
                                'sample_name' => $sampleName,
                                'status' => $status,
                            ];
                        }
                    }

                    $index++;
                }

                $order->items = $items;
                $order->formatted_created_at = Carbon::parse($order->created_at)->format('d-M-Y h:i A');
            }

            return view('referral.submitted_samples', compact('user', 'orderDetails', 'fromDate', 'toDate', 'searchType', 'searchValue'));
        }

        return view('referral.submitted_samples', compact('user', 'fromDate', 'toDate', 'searchType', 'searchValue'));
    }

    public function searchSampleStatus(Request $request)
    {
        $user = $this->getUserData();
        $deductionPercentage = $user->discount;
        $todayDate = Carbon::now()->toDateString();

        $fromDate = isset($request->fromDate) ? $request->fromDate : $todayDate;
        $toDate = isset($request->toDate) ? $request->toDate : $todayDate;
        $searchType = isset($request->searchType) ? $request->searchType : '';
        $searchValue = isset($request->searchValue) ? $request->searchValue : '';
        $status = isset($request->status) ? $request->status : '';

        $totalPatients = 0;
        $totalSampleReceived = 0;
        $totalReportsReady = 0;
        $totalPendingReports = 0;
        $totalDispatchReports = 0;

        if (!empty($fromDate) && !empty($toDate)) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('order_entry.referred_by_id', $user->id)
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->orderBy('order_entry.id', 'desc')
                ->get();

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $index = 0;
                $items = [];

                $totalPatients++;

                $currentPatientPendingReports = 0;
                $currentPatientDispatchReports = 0;

                while ($index < count($orderIdsArray)) {
                    $id = $orderIdsArray[$index];

                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                        foreach ($labProfileDetails as $labProfile) {
                            $orderIdsArray[] = "" . $labProfile->order_id;
                        }
                    }

                    $data = AddReport::select('*')
                        ->selectRaw('(SELECT department.department_name FROM department WHERE department.depart_id = add_report.order_department) AS order_department_name')
                        ->selectRaw('(SELECT order_type.name FROM order_type WHERE order_type.id = add_report.order_order_type) AS order_type_name')
                        ->where('report_id', $id)
                        ->first();

                    if ($data) {
                        $totalPendingReports++;
                        $currentPatientPendingReports++;

                        $orderName = $data->order_name;
                        $orderDepartmentName = $data->order_department_name;

                        $print = '';
                        $reportStatus = '';
                        $remark = '';
                        $bgColor = '';
                        $sampleDate = '';
                        $sampleBarcodeNo = '';

                        if ($searchValue != '') {
                            switch ($searchType) {
                                case 'InvName':
                                    if (!str_contains(strtolower($orderName), strtolower($searchValue))) {
                                        unset($orderDetails[$orderKey]);
                                        continue 3;
                                    }
                                    break;
                                case 'BillNo':
                                    if (!str_contains(strtolower($order->bill_no), strtolower($searchValue))) {
                                        unset($orderDetails[$orderKey]);
                                        continue 3;
                                    }
                                    break;
                                case 'PatName':
                                    if (!str_contains(strtolower($order->patient_name), strtolower($searchValue))) {
                                        unset($orderDetails[$orderKey]);
                                        continue 3;
                                    }
                                    break;
                            }
                        }

                        if ($order->status == "cancelled") {
                            $reportStatus = "Cancelled";
                            $remark = '';
                            $bgColor = '#ff4b4b';
                        } else if (empty(OrderBillsController::getSampleTypeText($id))) {
                            $reportStatus = "In-Process";
                            $remark = "";
                            $bgColor = '#00a038';

                            $checkReportAdded = ResultReports::where('bill_no', $order->bill_no)->where('report_no', $data->report_id)->first();

                            if ($checkReportAdded) {
                                $balanceAmount = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                                $balanceAmount = round($balanceAmount - ($balanceAmount * $deductionPercentage / 100));

                                switch ($checkReportAdded->status) {
                                    case 'Save':
                                        $reportStatus = "Processed";
                                        $remark = 'Pending for submitted';
                                        $bgColor = '#00a038';
                                        break;
                                    case 'Retest':
                                        $reportStatus = "Retest";
                                        $remark = '';
                                        $bgColor = '#ff4b4b';
                                        break;
                                    case 'Save And Complete':
                                    case 'Approved':
                                    case 'Dispatched':
                                    case 'Sent on WhatsApp':
                                        $reportStatus = "Pending for approval";
                                        $remark = '';
                                        $bgColor = '#247a00';

                                        if ($checkReportAdded->status != 'Save And Complete') {
                                            $totalReportsReady++;

                                            $totalDispatchReports++;
                                            $currentPatientDispatchReports++;

                                            $totalPendingReports--;
                                            $currentPatientPendingReports--;

                                            $reportStatus = "Completed";
                                            $remark = '';
                                            $bgColor = '#fa428b';

                                            if ($balanceAmount != 0) {
                                                // $remark = "Clear $balanceAmount due amount.";
                                                $remark = "Clear due amount.";
                                            } else {
                                                $print = url('referralpanel/viewbill/' . $order->bill_no . '/' . $id);
                                            }
                                        }

                                        // if ($checkReportAdded->is_printed == "true" || $checkReportAdded->status == 'Approved') {
                                        //     $reportStatus = "Completed";
                                        //     $remark = '';
                                        //     $bgColor = '#fa428b';

                                        //     if ($balanceAmount != 0) {
                                        //         $remark = "Clear $balanceAmount due amount.";
                                        //         $remark = "Clear due amount.";
                                        //     } else {
                                        //         $print = url('referralpanel/viewbill/' . $order->bill_no . '/' . $id);
                                        //     }
                                        // }

                                        if ($balanceAmount == 0) {
                                            if ($checkReportAdded->is_referral_printed == "true") {
                                                $reportStatus = "Printed";
                                                $remark = '';
                                                $bgColor = '#983ecf';
                                            }
                                        }
                                        break;
                                }
                            }
                        } else {
                            $reportStatus = "Registered";
                            $remark = '';
                            $bgColor = '#4e4e4e';

                            $checkBarcode = SampleBarcodes::where('bill_no', $order->bill_no)
                                ->where('sample_type', OrderBillsController::getSampleTypeText($id))
                                ->first();

                            if (!$checkBarcode) {
                                if (Carbon::parse($order->created_at)->diffInMinutes(Carbon::now()) > 30) {
                                    $reportStatus = "Pending";
                                    $remark = "SAMPLE NOT RECEVIED";
                                    $bgColor = '#915100';
                                }
                            } else {
                                if ($checkBarcode->status == 'collected') {
                                    $reportStatus = "Collected";
                                    $remark = '';
                                    $bgColor = '#008ac9';
                                } else if ($checkBarcode->status == 'rejected') {
                                    $reportStatus = "Rejected";
                                    $remark = $checkBarcode->reject_reason;
                                    $bgColor = '#ff4b4b';
                                } else if ($checkBarcode->status == 'received') {
                                    $totalSampleReceived++;

                                    $sampleDate = Carbon::createFromDate($checkBarcode->barcode_status_updated_on)->format('d-M-Y h:i A');
                                    $sampleBarcodeNo = $checkBarcode->barcode;

                                    $reportStatus = "Received";
                                    $remark = '';
                                    $bgColor = '#3377cf';

                                    $checkReportAdded = ResultReports::where('bill_no', $order->bill_no)->where('report_no', $data->report_id)->first();

                                    if (!$checkReportAdded) {
                                        if (Carbon::parse($checkBarcode->barcode_status_updated_on)->diffInMinutes(Carbon::now()) > 10) {
                                            $reportStatus = "In-Process";
                                            $remark = "";
                                            $bgColor = '#00a038';
                                        }
                                    } else {
                                        $balanceAmount = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                                        $balanceAmount = round($balanceAmount - ($balanceAmount * $deductionPercentage / 100));

                                        switch ($checkReportAdded->status) {
                                            case 'Save':
                                                $reportStatus = "Processed";
                                                $remark = 'Pending for submitted';
                                                $bgColor = '#00a038';
                                                break;
                                            case 'Retest':
                                                $reportStatus = "Retest";
                                                $remark = '';
                                                $bgColor = '#ff4b4b';
                                                break;
                                            case 'Save And Complete':
                                            case 'Approved':
                                            case 'Dispatched':
                                            case 'Sent on WhatsApp':
                                                $reportStatus = "Pending for approval";
                                                $remark = '';
                                                $bgColor = '#247a00';

                                                if ($checkReportAdded->status != 'Save And Complete') {
                                                    $totalReportsReady++;

                                                    $totalDispatchReports++;
                                                    $currentPatientDispatchReports++;

                                                    $totalPendingReports--;
                                                    $currentPatientPendingReports--;

                                                    $reportStatus = "Completed";
                                                    $remark = '';
                                                    $bgColor = '#fa428b';

                                                    if ($balanceAmount != 0) {
                                                        // $remark = "Clear $balanceAmount due amount.";
                                                        $remark = "Clear due amount.";
                                                    } else {
                                                        $print = url('referralpanel/viewbill/' . $order->bill_no . '/' . $id);
                                                    }
                                                }

                                                // if ($checkReportAdded->is_printed == "true" || $checkReportAdded->status == 'Approved') {
                                                //     $reportStatus = "Completed";
                                                //     $remark = '';
                                                //     $bgColor = '#fa428b';

                                                //     if ($balanceAmount != 0) {
                                                //         $remark = "Clear $balanceAmount due amount.";
                                                //         $remark = "Clear due amount.";
                                                //     } else {
                                                //         $print = url('referralpanel/viewbill/' . $order->bill_no . '/' . $id);
                                                //     }
                                                // }

                                                if ($balanceAmount == 0) {
                                                    if ($checkReportAdded->is_referral_printed == "true") {
                                                        $reportStatus = "Printed";
                                                        $remark = '';
                                                        $bgColor = '#983ecf';
                                                    }
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($status)) {
                            if ($status == $reportStatus) {
                                $items[] = array(
                                    'order_name' => $orderName,
                                    'print' => $print,
                                    'status' => $reportStatus,
                                    'remark' => $remark,
                                    'order_department_name' => $orderDepartmentName,
                                    'background_color' => $bgColor,
                                    'sample_date' => $sampleDate,
                                    'sample_barcode' => $sampleBarcodeNo,
                                );
                            }
                        } else {
                            $items[] = array(
                                'order_name' => $orderName,
                                'print' => $print,
                                'status' => $reportStatus,
                                'remark' => $remark,
                                'order_department_name' => $orderDepartmentName,
                                'background_color' => $bgColor,
                                'sample_date' => $sampleDate,
                                'sample_barcode' => $sampleBarcodeNo,
                            );
                        }
                    }

                    $index++;
                }

                $order->items = $items;
                $order->formatted_created_at = Carbon::parse($order->created_at)->format('d-M-Y h:i A');
                $order->currentPatientPendingReports = $currentPatientPendingReports;
                $order->currentPatientDispatchReports = $currentPatientDispatchReports;
            }

            return view(
                'referral.sample_status',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'searchType',
                    'searchValue',
                    'status',
                    'totalPatients',
                    'totalSampleReceived',
                    'totalReportsReady',
                    'totalPendingReports',
                    'totalDispatchReports',
                )
            );
        }

        return view(
            'referral.sample_status',
            compact(
                'user',
                'fromDate',
                'toDate',
                'searchType',
                'searchValue',
                'status',
                'totalPatients',
                'totalSampleReceived',
                'totalReportsReady',
                'totalPendingReports',
                'totalDispatchReports',
            )
        );
    }

    public function getOrdersName($orderIds)
    {
        $orderIdsArray = explode(',', $orderIds);
        $index = 0;
        $orderName = '';

        while ($index < count($orderIdsArray)) {
            $id = $orderIdsArray[$index];

            if (str_contains($id, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $orderIdsArray[] = "" . $labProfile->order_id;
                }
            }

            if (!empty(OrderBillsController::getSampleTypeText($id))) {
                $data = AddReport::find($id);

                if ($data) {
                    $orderName = empty($orderName) ? $data->order_name : ($orderName . ', ' . $data->order_name);
                }
            }

            $index++;
        }

        return $orderName;
    }
}
