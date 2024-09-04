<?php

namespace App\Http\Controllers\AdminControllers;

use App\Models\DepartmentSignatures;
use App\Models\LabProfileDetails;
use App\Models\LabProfiles;
use App\Models\OrderDetailValues;
use App\Models\OrderReturnAmount;
use App\Models\ReferralCompany;
use App\Models\SystemSettings;
use App\Models\UpdateOrderValuesFromMachine;
use Barryvdh\Snappy\Facades\SnappyPdf;
use DateTime;
use Exception;

use App\Models\Department;
use App\Models\SampleType;
use App\Models\OrderType;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\ResultReports;
use App\Models\ResultAttachments;
use App\Models\ResultReportsItems;
use App\Models\OrderEntryTransactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\AdminControllers\HomeController;
use App\Models\OrderDetails;
use App\Models\SampleBarcodes;
use Carbon\Carbon;
use stdClass;

class OrderBillsController extends CommonController
{
    public function inProcessBillsIndex(Request $request)
    {
        $user = HomeController::getUserData();
        $searchValue = "";

        if (!is_null($request) && $request->has('search')) {
            $searchValue = $request->query('search');
        }

        // $orderDetails = OrderEntry::select('*')
        //     ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
        //     ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
        //     ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
        //     ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
        //     ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
        //     ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
        //     ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
        //     // ->where('status', 'process')
        //     ->where('status', '!=', 'cancelled');
        // ->orderBy('order_entry.id', 'desc')
        // ->paginate(10);

        $orderDetails = OrderEntry::select(
            'order_entry.*',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age as patient_age',
            'patients.gender as patient_gender',
            'patients.phone as patient_phone',
            'patients.age_type as patient_age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                FROM add_report
                WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->where('order_entry.status', '!=', 'cancelled');

        if (!empty($searchValue)) {
            $orderDetails->where(function ($query) use ($searchValue) {
                $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_title_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.gender', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('doctors.doc_name', 'like', '%' . $searchValue . '%')
                    ->orWhere(DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                        FROM add_report
                        WHERE FIND_IN_SET(report_id, order_entry.order_ids))"), 'like', '%' . $searchValue . '%');
            });
        }

        $orderDetails = $orderDetails->orderBy('order_entry.id', 'desc')
            ->paginate(50);

        foreach ($orderDetails as $order) {
            $orderIdsArray = explode(',', $order->order_ids);
            $ordersNameTxt = "";
            $index = 0;

            $reportColor = "black";

            $pendingReports = 0;
            $saveReports = 0;
            $saveAndCompleteReports = 0;
            $deliveredReports = 0;
            $sentOnWhatsAppReports = 0;

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
                    if (empty(OrderBillsController::getSampleTypeText($id))) {
                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = $data->order_name;
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                        }
                    } else {
                        $checkBarcode = SampleBarcodes::where('bill_no', $order->bill_no)
                            ->where('sample_type', OrderBillsController::getSampleTypeText($id))
                            ->where('status', 'received')
                            ->first();

                        if ($checkBarcode) {
                            if ($ordersNameTxt == "") {
                                $ordersNameTxt = $data->order_name;
                            } else {
                                $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                            }
                        }
                    }

                    $checkReportAdded = ResultReports::where('bill_no', $order->bill_no)->where('report_no', $id)->first();

                    if ($checkReportAdded) {
                        switch ($checkReportAdded->status) {
                            case $this->reportStatusNotFound:
                                $pendingReports++;
                                break;
                            case $this->reportStatusSave:
                            case $this->reportStatusRetest:
                                $saveReports++;
                                break;
                            case $this->reportStatusSaveAndComplete:
                                $saveAndCompleteReports++;
                                break;
                            case $this->reportStatusApproved:
                            case $this->reportStatusDispatched:
                                $deliveredReports++;
                                break;
                            case $this->reportStatusSentOnWhatsApp:
                                $sentOnWhatsAppReports++;
                                break;
                        }
                    } else {
                        $pendingReports++;
                    }
                }

                $index++;
            }

            if ($pendingReports != 0) {
                $reportColor = "black";
            } else if ($saveReports != 0) {
                $reportColor = "red";
            } else if ($saveAndCompleteReports != 0) {
                $reportColor = "green";
            } else if ($deliveredReports != 0) {
                $reportColor = "purple";
            } else if ($sentOnWhatsAppReports != 0) {
                $reportColor = "#008080";
            } else {
                $reportColor = "black";
            }

            $order['order_name_txt'] = $ordersNameTxt;
            $order['report_color'] = $reportColor;
        }

        return view('admin.OrderBills.in_process_bills', compact('user', 'orderDetails', 'searchValue'));
    }

    public function approveReports(Request $request)
    {
        $user = HomeController::getUserData();
        $searchValue = "";

        if (!is_null($request) && $request->has('search')) {
            $searchValue = $request->query('search');
        }

        $orderDetails = ResultReports::select(
            'result_reports.*',
            'add_report.order_name',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age as patient_age',
            'patients.gender as patient_gender',
            'patients.phone as patient_phone',
            'patients.age_type as patient_age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                FROM add_report
                WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('order_entry', function ($join) {
                $join->on(DB::raw('order_entry.bill_no COLLATE utf8mb4_unicode_ci'), '=', DB::raw('result_reports.bill_no COLLATE utf8mb4_unicode_ci'));
            })
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->leftJoin('add_report', 'add_report.report_id', '=', 'result_reports.report_no')
            ->where('add_report.has_components', 'true')
            ->where('result_reports.status', $this->reportStatusSaveAndComplete);

        if (!empty($searchValue)) {
            $orderDetails->where(function ($query) use ($searchValue) {
                $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_title_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.gender', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('doctors.doc_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('add_report.order_name', 'like', '%' . $searchValue . '%')
                    ->orWhere(DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                        FROM add_report
                        WHERE FIND_IN_SET(report_id, order_entry.order_ids))"), 'like', '%' . $searchValue . '%');
            });
        }

        $orderDetails = $orderDetails->orderBy('result_reports.id', 'desc')
            ->paginate(50);

        $orderDetailsGrouped = $orderDetails->getCollection()->groupBy('bill_no');
        $orderDetails->setCollection($orderDetailsGrouped);

        return view('admin.OrderBills.approve_orders', compact('user', 'orderDetails', 'searchValue'));
    }

    public function completedBillsIndex(Request $request)
    {
        $user = HomeController::getUserData();
        $searchValue = "";

        if (!is_null($request) && $request->has('search')) {
            $searchValue = $request->query('search');
        }

        // $orderDetails = OrderEntry::select('*')
        //     ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
        //     ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
        //     ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
        //     ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
        //     ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
        //     ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
        //     ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
        //     ->where('status', 'completed')
        //     ->orWhere('status', 'process');
        // ->orderBy('order_entry.id', 'desc')
        // ->get();

        $orderDetails = OrderEntry::select(
            'order_entry.*',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age as patient_age',
            'patients.gender as patient_gender',
            'patients.phone as patient_phone',
            'patients.age_type as patient_age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                FROM add_report
                WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->whereIn('status', ['completed', 'process']);

        if (!empty($searchValue)) {
            // $orderDetails->where(function ($query) use ($searchValue) {
            //     $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
            //         ->orWhereRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) LIKE ?', ['%' . $searchValue . '%']);
            // });

            $orderDetails->where(function ($query) use ($searchValue) {
                $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_title_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.gender', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('doctors.doc_name', 'like', '%' . $searchValue . '%')
                    ->orWhere(DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                        FROM add_report
                        WHERE FIND_IN_SET(report_id, order_entry.order_ids))"), 'like', '%' . $searchValue . '%');
            });
        }

        $orderDetails = $orderDetails->orderBy('order_entry.id', 'desc')
            ->paginate(50);

        foreach ($orderDetails as $order) {
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

                $index++;
                $data = AddReport::find($id);

                if ($data) {
                    $checkReportAdded = ResultReports::where('bill_no', $order->bill_no)->where('report_no', $data->report_id)->first();

                    if ($checkReportAdded && $this->isReportCompleted($checkReportAdded->status)) {
                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = $data->order_name;
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                        }
                    }
                }
            }

            $order['order_name_txt'] = $ordersNameTxt;
            $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
        }

        return view('admin.OrderBills.completed_bills', compact('user', 'orderDetails', 'searchValue'));
    }

    public function previousBillsIndex(Request $request)
    {
        $user = HomeController::getUserData();
        $searchValue = "";

        if (!is_null($request) && $request->has('search')) {
            $searchValue = $request->query('search');
        }

        // $orderDetails = OrderEntry::select('*')
        //     ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
        //     ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
        //     ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
        //     ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
        //     ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
        //     ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
        //     ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
        //     ->where('status', '!=', 'cancelled');
        // ->orderBy('order_entry.id', 'desc')
        // ->get();

        $orderDetails = OrderEntry::select(
            'order_entry.*',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age',
            'patients.gender',
            'patients.phone',
            'patients.age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                FROM add_report
                WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->where('order_entry.status', '!=', 'cancelled');

        if (!empty($searchValue)) {
            // $orderDetails->where(function ($query) use ($searchValue) {
            //     $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
            //         ->orWhereRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) LIKE ?', ['%' . $searchValue . '%']);
            // });

            $orderDetails->where(function ($query) use ($searchValue) {
                $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_title_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.gender', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('doctors.doc_name', 'like', '%' . $searchValue . '%')
                    ->orWhere(DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                        FROM add_report
                        WHERE FIND_IN_SET(report_id, order_entry.order_ids))"), 'like', '%' . $searchValue . '%');
            });
        }

        $orderDetails = $orderDetails->orderBy('order_entry.id', 'desc')
            ->paginate(50);

        foreach ($orderDetails as $order) {
            $orderIdsArray = explode(',', $order->order_ids);
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

            $order['order_name_txt'] = $ordersNameTxt;
            $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
        }

        return view('admin.OrderBills.previous_bills', compact('user', 'orderDetails', 'searchValue'));
    }

    public function cancelledBillsIndex(Request $request)
    {
        $user = HomeController::getUserData();
        $searchValue = "";

        if (!is_null($request) && $request->has('search')) {
            $searchValue = $request->query('search');
        }

        // $orderDetails = OrderEntry::select('*')
        //     ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
        //     ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
        //     ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
        //     ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
        //     ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
        //     ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
        //     ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
        //     ->where('status', 'cancelled');
        // ->orderBy('order_entry.id', 'desc')
        // ->get();

        $orderDetails = OrderEntry::select(
            'order_entry.*',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age',
            'patients.gender',
            'patients.phone',
            'patients.age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                FROM add_report
                WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->where('order_entry.status', 'cancelled');

        if (!empty($searchValue)) {
            // $orderDetails->where(function ($query) use ($searchValue) {
            //     $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
            //         ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
            //         ->orWhereRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) LIKE ?', ['%' . $searchValue . '%'])
            //         ->orWhereRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) LIKE ?', ['%' . $searchValue . '%']);
            // });

            $orderDetails->where(function ($query) use ($searchValue) {
                $query->where('order_entry.umr_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.bill_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.order_date', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.referred_by', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.status', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.reason_for_discount', 'like', '%' . $searchValue . '%')
                    ->orWhere('order_entry.additional_info', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_title_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.patient_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.gender', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.phone', 'like', '%' . $searchValue . '%')
                    ->orWhere('patients.age_type', 'like', '%' . $searchValue . '%')
                    ->orWhere('doctors.doc_name', 'like', '%' . $searchValue . '%')
                    ->orWhere(DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                        FROM add_report
                        WHERE FIND_IN_SET(report_id, order_entry.order_ids))"), 'like', '%' . $searchValue . '%');
            });
        }

        $orderDetails = $orderDetails->orderBy('order_entry.id', 'desc')
            ->paginate(50);

        foreach ($orderDetails as $order) {
            $orderIdsArray = explode(',', $order->order_ids);
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

            $order['order_name_txt'] = $ordersNameTxt;
            $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
        }

        return view('admin.OrderBills.cancelled_bills', compact('user', 'orderDetails', 'searchValue'));
    }

    public function billDetailsIndex($billNo)
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
            ->where('bill_no', $billNo)
            ->first();

        $enableDispatchButton = true;
        $orderIdsArray = explode(',', $orderDetails->order_ids);
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

            $index++;

            $data = AddReport::find($id);

            if ($data) {
                $orderSampleTypeIdsArray = explode(', ', $data->order_sample_type);
                $sampleTypeName = OrderBillsController::getSampleTypeText($id);

                if (empty($sampleTypeName)) {
                    $orderData[] = $data;

                    if (!empty($sampleTypeName)) {
                        $barcodeData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleTypeName)->first();
                    } else {
                        $barcodeData = (object) [
                            'created_at' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +5 minutes')),
                            'barcode_status_updated_on' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +10 minutes')),
                        ];
                    }

                    if (isset($barcodeData->created_at)) {
                        $inputDate = $barcodeData->created_at;
                        $carbonDate = Carbon::parse($inputDate);
                        $formattedDate = $carbonDate->format('d-M-Y h:i A');
                    } else {
                        $formattedDate = "Not found";
                    }

                    $showWhatsAppButton = false;
                    $reportColor = "orange";
                    $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                    if ($checkReportAdded) {
                        switch ($checkReportAdded->status) {
                            case $this->reportStatusNotFound:
                                break;
                            case $this->reportStatusSave:
                            case $this->reportStatusRetest:
                                $reportColor = "red";
                                break;
                            case $this->reportStatusSaveAndComplete:
                                $reportColor = "green";
                                break;
                            case $this->reportStatusDispatched:
                            case $this->reportStatusApproved:
                                $reportColor = "purple";
                                $showWhatsAppButton = true;
                                break;
                            case $this->reportStatusSentOnWhatsApp:
                                $reportColor = "#008080";
                                $showWhatsAppButton = true;
                                break;
                        }

                        $orderData[count($orderData) - 1]->reporting_date = $checkReportAdded->created_at;
                    } else {
                        // $enableDispatchButton = false;
                    }

                    $orderData[count($orderData) - 1]->sample_taked_on = $formattedDate;
                    $orderData[count($orderData) - 1]->sample_type = $sampleTypeName;
                    $orderData[count($orderData) - 1]->report_color = $reportColor;
                    $orderData[count($orderData) - 1]->show_whatsapp_button = $showWhatsAppButton;
                } else {
                    $barcodeData = SampleBarcodes::where('bill_no', $billNo)
                        ->where('sample_type', $sampleTypeName)
                        ->where('status', 'received')
                        ->first();

                    if (!$barcodeData)
                        continue;

                    $orderData[] = $data;

                    if (!empty($sampleTypeName)) {
                        $barcodeData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleTypeName)->first();
                    } else {
                        $barcodeData = (object) [
                            'created_at' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +5 minutes')),
                            'barcode_status_updated_on' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +10 minutes')),
                        ];
                    }

                    if (isset($barcodeData->created_at)) {
                        $inputDate = $barcodeData->created_at;
                        $carbonDate = Carbon::parse($inputDate);
                        $formattedDate = $carbonDate->format('d-M-Y h:i A');
                    } else {
                        $formattedDate = "Not found";
                    }

                    $showWhatsAppButton = false;
                    $reportColor = "orange";
                    $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                    if ($checkReportAdded) {
                        switch ($checkReportAdded->status) {
                            case $this->reportStatusNotFound:
                                break;
                            case $this->reportStatusSave:
                            case $this->reportStatusRetest:
                                $reportColor = "red";
                                break;
                            case $this->reportStatusSaveAndComplete:
                                $reportColor = "green";
                                break;
                            case $this->reportStatusDispatched:
                            case $this->reportStatusApproved:
                                $reportColor = "purple";
                                $showWhatsAppButton = true;
                                break;
                            case $this->reportStatusSentOnWhatsApp:
                                $reportColor = "#008080";
                                $showWhatsAppButton = true;
                                break;
                        }

                        $orderData[count($orderData) - 1]->reporting_date = $checkReportAdded->created_at;
                    } else {
                        // $enableDispatchButton = false;
                    }

                    $orderData[count($orderData) - 1]->sample_taked_on = $formattedDate;
                    $orderData[count($orderData) - 1]->sample_type = $sampleTypeName;
                    $orderData[count($orderData) - 1]->report_color = $reportColor;
                    $orderData[count($orderData) - 1]->show_whatsapp_button = $showWhatsAppButton;
                }
            }
        }

        $orderDetails['orderData'] = $orderData;
        $orderDetails['enableDispatchButton'] = $enableDispatchButton;

        $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        return view('admin.OrderBills.bill_details', compact('user', 'orderDetails', 'leftBalance'));
    }

    public function approveReportDetailsIndex($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = $this->getResultData($billNo, $orderNo);
        return view('admin.OrderBills.approve_order_details', compact('user', 'orderDetails', 'billNo', 'orderNo'));
    }

    public function completedBillDetailsIndex($billNo)
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
            ->where('bill_no', $billNo)
            ->first();

        $enableDispatchButton = true;
        $orderIdsArray = explode(',', $orderDetails->order_ids);
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

            $index++;
            $data = AddReport::find($id);

            if ($data) {
                $orderSampleTypeIdsArray = explode(', ', $data->order_sample_type);
                $sampleTypeName = OrderBillsController::getSampleTypeText($id);

                if (!empty($sampleTypeName)) {
                    $barcodeData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleTypeName)->first();
                } else {
                    $barcodeData = (object) [
                        'created_at' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +5 minutes')),
                        'barcode_status_updated_on' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +10 minutes')),
                    ];
                }

                if (isset($barcodeData->created_at)) {
                    $inputDate = $barcodeData->created_at;
                    $carbonDate = Carbon::parse($inputDate);
                    $formattedDate = $carbonDate->format('d-M-Y h:i A');
                } else {
                    $formattedDate = "Not found";
                }

                $reportColor = "red";
                $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                if ($checkReportAdded && $this->isReportCompleted($checkReportAdded->status)) {
                    $orderData[] = $data;
                    $orderData[count($orderData) - 1]->sample_taked_on = $formattedDate;
                    $orderData[count($orderData) - 1]->sample_type = $sampleTypeName;
                    $orderData[count($orderData) - 1]->report_color = $reportColor;
                    $orderData[count($orderData) - 1]->printable_content = OrderBillsController::getPrintContent($billNo, $data->report_id);
                    $orderData[count($orderData) - 1]->report_status = $checkReportAdded->status;
                } else {
                    $enableDispatchButton = false;
                }
            }
        }
        $attachmentsList = ResultAttachments::where('bill_no', $billNo)->get();

        $orderDetails['attachmentsList'] = $attachmentsList;
        $orderDetails['orderData'] = $orderData;
        $orderDetails['enableDispatchButton'] = $enableDispatchButton;
        $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        return view('admin.OrderBills.completed_bill_details', compact('user', 'orderDetails', 'leftBalance'));
    }

    public function previousBillDetailsIndex($billNo)
    {
        $user = HomeController::getUserData();
        $isDuplicateOrdersAllowed = true;

        if ($user->settings->unique_order_entry == "true") {
            $isDuplicateOrdersAllowed = false;
        }

        $orderDetails = OrderEntry::select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
            ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
            ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
            ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
            ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
            ->where('bill_no', $billNo)
            ->first();

        $sampleBarcodesData = SampleBarcodes::where('bill_no', $billNo)->get();
        $isBillEditable = "true";

        if ($orderDetails->status == "cancelled") {
            $isBillEditable = "false";
        }

        $checkResultReports = ResultReports::where('bill_no', $billNo)->first();

        if ($checkResultReports) {
            $isBillEditable = "false";
        }

        $orderIdsArray = explode(',', $orderDetails->order_ids);
        $orderAmountArray = explode(',', $orderDetails->order_amount);
        $orderData = [];

        foreach ($orderIdsArray as $key => $id) {
            if (str_contains($id, $this->orderTypeProfile)) {
                $profileId = str_replace($this->orderTypeProfile, "", $id);
                $data = LabProfiles::find($profileId);
            } else {
                $data = AddReport::find($id);
            }

            if ($data) {
                $orderType = OrderType::where('id', $data->order_order_type)->first();

                $orderData[] = array(
                    "order_name" => isset($data->order_name) ? $data->order_name : $data->name,
                    "order_type" => ($orderType) ? $orderType->name : "",
                    "order_amount" => $orderAmountArray[$key],
                    "report_id" => $id
                );

                $orderReturnAmount = OrderReturnAmount::where('bill_no', $billNo)->where('order_no', $id)->first();

                if ($orderReturnAmount) {
                    $orderData[count($orderData) - 1]['return_type'] = ucfirst($orderReturnAmount->type);
                    $orderData[count($orderData) - 1]['return_amount'] = $orderReturnAmount->amount;
                    $orderData[count($orderData) - 1]['return_note'] = $orderReturnAmount->note;
                }
            }
        }

        $orderDetails['order_data'] = $orderData;
        $orderDetails['isBillEditable'] = $isBillEditable;

        $orderEntryTransactions = OrderEntryTransactions::where('bill_no', $billNo)->first();
        $orderDetails['transaction_details'] = $orderEntryTransactions;
        $orderDetails['balance'] = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        return view('admin.OrderBills.previous_bill_details', compact('user', 'orderDetails', 'isDuplicateOrdersAllowed'));
    }

    public function resultEntryIndex($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderBillsController::getResultEntryData($billNo, $orderNo);
        return view('admin.OrderBills.result_entry', compact('user', 'orderDetails', 'orderNo'));
    }

    public function editResultEntryIndex($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderBillsController::getResultEntryData($billNo, $orderNo);
        return view('admin.OrderBills.edit_result_entry', compact('user', 'orderDetails', 'orderNo'));
    }

    public function getResultEntryData($billNo, $orderNo)
    {
        $orderDetails = OrderEntry::select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
            ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
            ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
            ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
            ->selectRaw('(SELECT patients.address FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_address')
            ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
            ->selectRaw('(SELECT users.first_name FROM users WHERE users.id = order_entry.created_by) AS user_first_name')
            ->selectRaw('(SELECT users.last_name FROM users WHERE users.id = order_entry.created_by) AS user_last_name')
            ->where('bill_no', $billNo)
            ->first();

        $orderData = AddReport::select('*')
            ->selectRaw('(SELECT department.department_name FROM department WHERE department.depart_id = add_report.order_department) AS order_department_name')
            ->selectRaw('(SELECT order_type.name FROM order_type WHERE order_type.id = add_report.order_order_type) AS order_type_name')
            ->where('report_id', $orderNo)
            ->first();

        $orderDataList = [];
        $checkResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();

        if ($checkResultReport) {
            $orderDataList['report_id'] = $checkResultReport['report_no'];
            $orderDataList['order_method'] = $checkResultReport['method'];
            $orderDataList['order_notes'] = $checkResultReport['notes'];
            $orderDataList['order_result_notes_1'] = '';
            $orderDataList['order_result_notes_2'] = '';
            $orderDataList['order_result_notes_3'] = '';
            $orderDataList['order_advice'] = $checkResultReport['advice'];
            $orderDataList['order_department_name'] = $orderData['order_department_name'];
            $orderDataList['order_type_name'] = $orderData['order_type_name'];
            $orderDataList['order_name'] = $orderData['order_name'];
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['result_page_1'] = $checkResultReport['result_page_1'];
            $orderDataList['result_page_2'] = $checkResultReport['result_page_2'];
            $orderDataList['result_page_3'] = $checkResultReport['result_page_3'];
            $orderDataList['signature'] = $checkResultReport['signature'];
            $orderDataList['status'] = $checkResultReport['status'];
        } else {
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['order_method'] = $orderData['order_method'];
            $orderDataList['order_notes'] = $orderData['order_result_notes_1'] . $orderData['order_result_notes_2'] . $orderData['order_result_notes_3'];
            $orderDataList['order_result_notes_1'] = $orderData['order_result_notes_1'];
            $orderDataList['order_result_notes_2'] = $orderData['order_result_notes_2'];
            $orderDataList['order_result_notes_3'] = $orderData['order_result_notes_3'];
            $orderDataList['order_advice'] = $orderData['order_advice'];
            $orderDataList['order_department_name'] = $orderData['order_department_name'];
            $orderDataList['order_type_name'] = $orderData['order_type_name'];
            $orderDataList['order_name'] = $orderData['order_name'];
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['result_page_1'] = '';
            $orderDataList['result_page_2'] = '';
            $orderDataList['result_page_3'] = '';
            $orderDataList['signature'] = '';
            $orderDataList['status'] = '';
        }

        $componentsDataList = array();

        if ($checkResultReport) {
            $getComponentsData = ResultReportsItems::select('*')
                ->where('result_reports_id', $checkResultReport->id)
                ->selectRaw('(SELECT order_details.component_name FROM order_details WHERE order_details.id = result_reports_items.component_id) AS component_name')
                ->selectRaw('(SELECT order_details.from_range FROM order_details WHERE order_details.id = result_reports_items.component_id) AS from_range')
                ->selectRaw('(SELECT order_details.to_range FROM order_details WHERE order_details.id = result_reports_items.component_id) AS to_range')
                ->orderBy('position', 'asc')
                ->get();

            foreach ($getComponentsData as $component) {
                $componentsDataList[] = array(
                    'component_name' => $component['component_name'],
                    'from_range' => $component['from_range'],
                    'to_range' => $component['to_range'],
                    'id' => $component['component_id'],
                    'order_details_range' => $component['results_range'],
                    'units' => $component['units'],
                    'method' => $component['method'],
                    'results' => $component['results'],
                    'calculations' => $component['calculations'],
                    'position' => $component['position'],
                    'abnormal' => $component['abnormal']
                );
            }
        } else {
            $templeteData = DB::table('order_templates')
                ->where('report_id', $orderNo)
                ->where('template_gender', $orderDetails->patient_gender)
                ->where('status', 'Active')
                ->get();

            $templeteId = null;
            if ($templeteData) {
                $patientAgeType = explode(' ', $orderDetails->patient_age_type)[0];
                $patientAgeInDays = $this->convertAgeToDays($orderDetails->patient_age, $patientAgeType);

                foreach ($templeteData as $key => $value) {
                    $templateFromAgeInDays = $this->convertAgeToDays($value->template_from_age, $value->template_from_age_type);
                    $templateToAgeInDays = $this->convertAgeToDays($value->template_to_age, $value->template_to_age_type);

                    if (
                        $templateFromAgeInDays <= $patientAgeInDays &&
                        $patientAgeInDays <= $templateToAgeInDays
                    ) {
                        $templeteId = $value->id;
                        break;
                    }
                }
            }

            $componentsData = OrderDetails::select('*')
                ->where('report_id', $orderNo)
                ->where(function ($query) use ($templeteId) {
                    if ($templeteId === null) {
                        $query->whereNull('template_id');
                    } else {
                        $query->where('template_id', $templeteId);
                    }
                })
                ->orderBy('position', 'asc')
                ->get();

            foreach ($componentsData as $component) {
                $componentsDataList[] = array(
                    'component_name' => $component['component_name'],
                    'from_range' => $component['from_range'],
                    'to_range' => $component['to_range'],
                    'id' => $component['id'],
                    'order_details_range' => $component['order_details_range'],
                    'units' => $component['units'],
                    'method' => $component['method'],
                    'results' => '',
                    'calculations' => $component['calculations'],
                    'position' => $component['position'],
                    'abnormal' => 'off'
                );
            }
        }

        $isPrinted = false;
        $reportStatus = "";
        $checkResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();

        if ($checkResultReport) {
            $reportStatus = $checkResultReport->status;
            $isPrinted = $checkResultReport->is_printed;
        } else {
            $orderSampleTypeIdsArray = explode(', ', $orderData->order_sample_type);
            $sampleTypeName = OrderBillsController::getSampleTypeText($orderNo);

            $sampleBarcodesData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleTypeName)->first();
            if ($sampleBarcodesData) {
                if ($sampleBarcodesData->status == "received") {
                    $reportStatus = $this->reportSampleReceived;
                } else {
                    $reportStatus = $this->reportSampleCollected;
                }
            } else {
                $reportStatus = "Not found";
            }
        }

        if (!empty($orderData->order_department)) {
            $signaturesList = DepartmentSignatures::where('department_id', $orderData->order_department)->where('status', 'Active')->get();
        } else {
            $signaturesList = DepartmentSignatures::where('department_id', 19)->where('status', 'Active')->get();
        }

        $orderDetails['bill_formatted_date'] = $this->formatDate($orderDetails->created_at);

        $orderDetails['signaturesList'] = $signaturesList;
        $orderDetails['orderData'] = $orderDataList;
        $orderDetails['componentsData'] = $componentsDataList;
        $orderDetails['reportStatus'] = $reportStatus;
        $orderDetails['isPrinted'] = $isPrinted;

        $showPrintBtn = false;
        $disableForm = false;

        switch ($reportStatus) {
            case $this->reportStatusSaveAndComplete:
                $disableForm = true;

                if (empty($componentsDataList)) {
                    $showPrintBtn = true;
                }
                break;
            case $this->reportStatusApproved:
            case $this->reportStatusDispatched:
            case $this->reportStatusSentOnWhatsApp:
                $showPrintBtn = true;
                $disableForm = true;
                break;
        }

        $orderDetails['showSaveBtn'] = !OrderBillsController::isReportCompleted($reportStatus);
        $orderDetails['showPrintBtn'] = $showPrintBtn;
        $orderDetails['disableForm'] = $disableForm;

        return $orderDetails;
    }

    public function saveResult(Request $request)
    {
        // return $request;
        $user = HomeController::getUserData();

        $checkResultReports = ResultReports::where('bill_no', $request->billNo)->where('report_no', $request->reportNo)->first();
        if ($checkResultReports) {
            $checkResultReports->method = $request->resultMethod;
            $checkResultReports->notes = $request->resultNotes;
            $checkResultReports->advice = $request->resultAdvice;
            $checkResultReports->status = $request->status;
            $checkResultReports->result_page_1 = $request->resultNotesPage1;
            $checkResultReports->result_page_2 = $request->resultNotesPage2;
            $checkResultReports->result_page_3 = $request->resultNotesPage3;
            $checkResultReports->signature = $request->signature;
            $checkResultReports->save();
        } else {
            $checkResultReports = new ResultReports();
            $checkResultReports->created_by = $user->id;
            $checkResultReports->bill_no = $request->billNo;
            $checkResultReports->report_no = $request->reportNo;
            $checkResultReports->method = $request->resultMethod;
            $checkResultReports->notes = $request->resultNotes;
            $checkResultReports->advice = $request->resultAdvice;
            $checkResultReports->status = $request->status;
            $checkResultReports->result_page_1 = $request->resultNotesPage1;
            $checkResultReports->result_page_2 = $request->resultNotesPage2;
            $checkResultReports->result_page_3 = $request->resultNotesPage3;
            $checkResultReports->signature = $request->signature;
            $checkResultReports->save();
        }

        if (empty($request->resultNotesPage1) && empty($request->resultNotesPage2)) {
            if ($request->componentId != null) {
                $checkResultReportsItems = ResultReportsItems::where('result_reports_id', $checkResultReports->id)->orderBy('position', 'asc')->get();

                if (!$checkResultReportsItems->isEmpty()) {
                    foreach ($request->componentId as $index => $componentId) {
                        $abnormalId = $request->componentId[$index] . "_abnormal";
                        $resultReportsItems = ResultReportsItems::where('result_reports_id', $checkResultReports->id)->where('component_id', $request->componentId[$index])->first();
                        $resultReportsItems->results = $request->results[$index];
                        $resultReportsItems->abnormal = isset($request->$abnormalId) ? "on" : "off";
                        $resultReportsItems->results_range = $request->range[$index];
                        $resultReportsItems->units = $request->units[$index];
                        $resultReportsItems->method = $request->method[$index];
                        $resultReportsItems->save();
                    }
                } else {
                    foreach ($request->componentId as $index => $componentId) {
                        $abnormalId = $request->componentId[$index] . "_abnormal";
                        $resultReportsItems = new ResultReportsItems();
                        $resultReportsItems->created_by = $user->id;
                        $resultReportsItems->result_reports_id = $checkResultReports->id;
                        $resultReportsItems->component_id = $request->componentId[$index];
                        $resultReportsItems->results = $request->results[$index];
                        $resultReportsItems->abnormal = isset($request->$abnormalId) ? "on" : "off";
                        $resultReportsItems->results_range = $request->range[$index];
                        $resultReportsItems->units = $request->units[$index];
                        $resultReportsItems->method = $request->method[$index];
                        $resultReportsItems->position = $request->position[$index];
                        $resultReportsItems->save();
                    }
                }
            }
        }

        if ($request->status == $this->reportStatusSaveAndComplete) {
            return $this->nextResultEntry($request->billNo, $request->reportNo);
            // if ($checkResultReports) {
            //     $isPrinted = $checkResultReports->is_printed;
            // } else {
            //     $isPrinted = false;
            // }

            // return redirect()->back()->with(['showPreview' => true, 'isPrinted' => $isPrinted]);
        } else {
            return redirect()->back();
        }
    }

    public function nextResultEntry($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();

        $indexTo = -1;
        $orderIdsArray = explode(',', $orderDetails->order_ids);
        $index = 0;

        while ($index < count($orderIdsArray)) {
            $id = $orderIdsArray[$index];

            if (str_contains($id, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $orderIdsArray[] = "" . $labProfile->order_id;
                }
            }

            if ($id == $orderNo) {
                if (isset($orderIdsArray[$index + 1])) {
                    $indexTo = $orderIdsArray[$index + 1];

                    if ($indexTo != -1) {
                        $data = AddReport::find($indexTo);

                        if ($data) {
                            if (!empty(OrderBillsController::getSampleTypeText($indexTo))) {
                                $checkBarcode = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', OrderBillsController::getSampleTypeText($indexTo))->first();
                                if (!$checkBarcode)
                                    $indexTo = -1;
                            }
                        } else {
                            $indexTo = -1;
                        }
                    }

                    break;
                }
            }

            $index++;
        }

        if ($indexTo == -1) {
            return redirect('orderbills/in_process_bills');
        } else {
            return redirect('orderbills/result_entry/' . $billNo . '/' . $indexTo);
        }
    }

    public function printResult($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        ResultReports::where('bill_no', $billNo)
            ->where('report_no', $orderNo)
            ->update([
                'is_printed' => 'true',
                'status' => $this->reportStatusApproved
            ]);

        $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);
        $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        return view('admin.OrderBills.print_result', compact('user', 'orderDetails', 'billNo', 'orderNo', 'leftBalance'));
    }

    public function changeResult($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();

        $indexTo = -1;
        $orderIdsArray = explode(',', $orderDetails->order_ids);
        $index = 0;

        while ($index < count($orderIdsArray)) {
            $id = $orderIdsArray[$index];

            if (str_contains($id, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $orderIdsArray[] = "" . $labProfile->order_id;
                }
            }

            if ($id == $orderNo) {
                if (isset($orderIdsArray[$index + 1])) {
                    $indexTo = $orderIdsArray[$index + 1];

                    if ($indexTo != -1) {
                        $data = AddReport::find($indexTo);

                        if ($data) {
                            if (!empty(OrderBillsController::getSampleTypeText($indexTo))) {
                                $checkBarcode = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', OrderBillsController::getSampleTypeText($indexTo))->first();
                                if (!$checkBarcode)
                                    $indexTo = -1;
                            }
                        } else {
                            $indexTo = -1;
                        }
                    }

                    break;
                }
            }

            $index++;
        }

        if ($indexTo == -1) {
            return redirect('orderbills/result_dispatch/' . $billNo);
        } else {
            return redirect('orderbills/result_entry/' . $billNo . '/' . $indexTo);
        }
    }

    public function previewResult($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);
        return view('admin.OrderBills.result_preview', compact('user', 'orderDetails', 'orderNo'));
    }

    public function updateReportStatus($billNo, $orderNo, $status)
    {
        $user = HomeController::getUserData();
        ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->update(['status' => $status]);
        return $this->nextReportForApproval($billNo, $orderNo);
    }

    public function nextReportForApproval($billNo, $orderNo)
    {
        $currentReport = ResultReports::select('result_reports.id')
            ->where('bill_no', $billNo)
            ->where('report_no', $orderNo)
            ->where('status', $this->reportStatusSaveAndComplete)
            ->first();

        if (!$currentReport) {
            return redirect('orderbills/approve_reports');
        }

        $nextReport = ResultReports::select(
            'result_reports.*',
            'add_report.order_name',
            'patients.patient_title_name',
            'patients.patient_name',
            'patients.age as patient_age',
            'patients.gender as patient_gender',
            'patients.phone as patient_phone',
            'patients.age_type as patient_age_type',
            'doctors.doc_name',
            DB::raw("(SELECT GROUP_CONCAT(order_name SEPARATOR ', ')
                      FROM add_report
                      WHERE FIND_IN_SET(report_id, order_entry.order_ids)) as order_names")
        )
            ->leftJoin('order_entry', function ($join) {
                $join->on(DB::raw('order_entry.bill_no COLLATE utf8mb4_unicode_ci'), '=', DB::raw('result_reports.bill_no COLLATE utf8mb4_unicode_ci'));
            })
            ->leftJoin('patients', 'patients.umr_number', '=', 'order_entry.umr_number')
            ->leftJoin('doctors', 'doctors.id', '=', 'order_entry.doctor')
            ->leftJoin('add_report', 'add_report.report_id', '=', 'result_reports.report_no')
            ->where('add_report.has_components', 'true')
            ->where('result_reports.status', $this->reportStatusSaveAndComplete)
            ->where('result_reports.id', '>', $currentReport->id)
            ->orderBy('result_reports.id', 'asc')
            ->first();

        if ($nextReport) {
            return redirect('orderbills/approve_report_details/' . $nextReport->bill_no . '/' . $nextReport->report_no);
        } else {
            return redirect('orderbills/approve_reports');
        }
    }

    public function userViewResult($billNo, Request $request /* $orderNo = "" */)
    {
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();

        $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        if ($leftBalance != "0") {
            return response()->json(["error" => "balance not clear"]);
        }

        $user = new stdClass();
        $user->settings = SystemSettings::first();

        try {
            $orderIds = $request->query('orders');
            $qrCodeUrl = url('/') . '/viewbill/' . $orderDetails->bill_no . '?orders=' . $orderIds;

            // if (!empty($orderNo)) {
            //     $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);
            //     $html = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'qrCodeUrl'))->render();

            //     $pdf = SnappyPdf::loadHTML($html)
            //         ->setOption('zoom', 1.25)
            //         ->setPaper('a4')
            //         ->setOrientation('portrait')
            //         ->setOption('margin-top', '0mm')
            //         ->setOption('margin-bottom', '0mm')
            //         ->setOption('margin-left', '0mm')
            //         ->setOption('margin-right', '0mm')
            //         ->setOption('dpi', 200);

            //     return $pdf->stream($this->getReportPdfName($billNo));
            // }

            if (empty($orderIds)) {
                $orderIdsArray = explode(',', $orderDetails->order_ids);
            } else {
                $orderIdsArray = explode(',', $orderIds);
            }

            $index = 0;
            $pages = [];

            while ($index < count($orderIdsArray)) {
                $id = $orderIdsArray[$index];

                if (str_contains($id, $this->orderTypeProfile)) {
                    $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                    foreach ($labProfileDetails as $labProfile) {
                        $orderIdsArray[] = "" . $labProfile->order_id;
                    }
                }

                $index++;
                $data = AddReport::find($id);

                if ($data) {
                    $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                    if ($checkReportAdded && $this->isReportCompleted($checkReportAdded->status)) {
                        $orderNo = $data->report_id;
                        $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);

                        $pageContent = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'qrCodeUrl'))->render();

                        $pages[] = $pageContent;
                    }
                }
            }

            $pdf = SnappyPdf::loadView('admin.OrderBills.multipages_pdf', ['pages' => $pages])
                ->setOption('zoom', 1.25)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-top', '0mm')
                ->setOption('margin-bottom', '0mm')
                ->setOption('margin-left', '0mm')
                ->setOption('margin-right', '0mm')
                ->setOption('dpi', 200);

            return $pdf->stream($this->getReportPdfName($billNo));
        } catch (Exception $e) {
            return $e;
        }
    }

    public function getResultData($billNo, $orderNo)
    {
        $orderDetails = OrderEntry::select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
            ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
            ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
            ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
            ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
            ->where('bill_no', $billNo)
            ->first();

        if (!empty(OrderBillsController::getSampleTypeText($orderNo))) {
            $sampleBarcodesData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', OrderBillsController::getSampleTypeText($orderNo))->first();
        } else {
            $sampleBarcodesData = (object) [
                'created_at' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +5 minutes')),
                'barcode_status_updated_on' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +10 minutes')),
            ];
        }

        $resultReportsData = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
        $addReportData = AddReport::where('report_id', $orderNo)->first();
        if ($resultReportsData) {
            if (is_null($resultReportsData->signature) || empty($resultReportsData->signature)) {
                if (is_null($addReportData->order_department) || empty($addReportData->order_department)) {
                    $signatureData = Department::where('depart_id', 19)->first();
                } else {
                    $signatureData = Department::where('depart_id', $addReportData->order_department)->first();
                }
            } else {
                $signatureData = DepartmentSignatures::where('id', $resultReportsData->signature)->first();
            }

            if ($signatureData) {
                $orderDetails['signature_label'] = $signatureData->signature_label;
                $orderDetails['signature_image'] = $signatureData->signature_image;
                $orderDetails['left_signature_label'] = $signatureData->left_signature_label;
                $orderDetails['left_signature_image'] = $signatureData->left_signature_image;
            }
        }

        $orderDetails['bill_formatted_date'] = OrderBillsController::formatDate($orderDetails->created_at);
        $orderDetails['sample_type'] = OrderBillsController::getSampleTypeText($orderNo);
        $orderDetails['order_name'] = $addReportData->order_name;
        $orderDetails['order_display_name'] = $addReportData->order_display_name;

        $departmentName = Department::where('depart_id', $addReportData->order_department)->first();
        $orderDetails['order_department_name'] = ($departmentName) ? $departmentName->department_name : "";

        $orderDetails['report_id'] = $addReportData->report_id;

        if (!empty($sampleBarcodesData->created_at)) {
            $orderDetails['sample_collection_date'] = OrderBillsController::formatDate($sampleBarcodesData->created_at);
        }
        if (!empty($sampleBarcodesData->barcode_status_updated_on)) {
            $orderDetails['sample_received_date'] = OrderBillsController::formatDate($sampleBarcodesData->barcode_status_updated_on);
        }

        $componentsDataList = array();
        if ($resultReportsData && OrderBillsController::isReportCompleted($resultReportsData->status)) {
            $orderDetails['result_page_1'] = $resultReportsData->result_page_1;
            $orderDetails['result_page_2'] = $resultReportsData->result_page_2;
            $orderDetails['result_page_3'] = $resultReportsData->result_page_3;

            $orderDetails['reporting_date'] = OrderBillsController::formatDate($resultReportsData->created_at);
            $typedBy = HomeController::getUserDataFromId($resultReportsData->created_by);
            $orderDetails['typed_by'] = $typedBy->first_name . ' ' . $typedBy->last_name;

            if (!empty($resultReportsData->method))
                $orderDetails['reporting_method'] = $resultReportsData->method;
            if (!empty($resultReportsData->notes))
                $orderDetails['reporting_notes'] = $resultReportsData->notes;
            if (!empty($resultReportsData->advice))
                $orderDetails['reporting_advice'] = $resultReportsData->advice;

            $resultReportItems = ResultReportsItems::select('*')
                ->where('result_reports_id', $resultReportsData->id)
                ->selectRaw('(SELECT order_details.component_name FROM order_details WHERE order_details.id = result_reports_items.component_id) AS component_name')
                ->selectRaw('(SELECT order_details.sub_heading FROM order_details WHERE order_details.id = result_reports_items.component_id) AS sub_heading')
                ->orderBy('position', 'asc')
                ->get();

            foreach ($resultReportItems as $reportItem) {
                $componentsDataList[] = array(
                    'sub_heading' => $reportItem['sub_heading'],
                    'component_name' => $reportItem['component_name'],
                    'id' => $reportItem['component_id'],
                    'order_details_range' => $reportItem['results_range'],
                    'units' => $reportItem['units'],
                    'method' => $reportItem['method'],
                    'results' => $reportItem['results'],
                    'abnormal' => $reportItem['abnormal']
                );
            }
        }
        $orderDetails['report_items_data'] = $componentsDataList;
        return $orderDetails;
    }

    public function formatDate($date)
    {
        $inputDate = $date;
        $carbonDate = Carbon::parse($inputDate);
        return $carbonDate->format('d-M-Y h:i A');
    }

    public static function getSampleTypeText($id)
    {
        $data = AddReport::find($id);
        $sampleTypeName = "";
        if ($data) {
            $orderSampleTypeIdsArray = explode(', ', $data->order_sample_type);
            $invisibleId = "<!-- $id -->";

            if (count($orderSampleTypeIdsArray) == 1) {
                $sampleType = SampleType::find($orderSampleTypeIdsArray[0]);
                if ($sampleType) {
                    if (OrderBillsController::isSampleNeededToBeCollectedSeparately($sampleType['name'])) {
                        $sampleTypeName = $sampleType['name'] . " " . $invisibleId;
                    } else {
                        $sampleTypeName = $sampleType['name'];
                    }
                }
            } else {
                foreach ($orderSampleTypeIdsArray as $key => $sampleId) {
                    $sampleType = SampleType::find($sampleId);
                    if (empty($sampleTypeName)) {
                        if (OrderBillsController::isSampleNeededToBeCollectedSeparately($sampleType['name'])) {
                            $sampleTypeName = $sampleType['name'] . " " . $invisibleId;
                        } else {
                            $sampleTypeName = $sampleType['name'];
                        }
                    } else {
                        if (OrderBillsController::isSampleNeededToBeCollectedSeparately($sampleType['name'])) {
                            $sampleTypeName = $sampleTypeName . ", " . $sampleType['name'] . " " . $invisibleId;
                        } else {
                            $sampleTypeName = $sampleTypeName . ", " . $sampleType['name'];
                        }
                    }
                }
            }

            // if (count($orderSampleTypeIdsArray) == 1) {
            //     $sampleType = SampleType::find($orderSampleTypeIdsArray[0]);
            //     if ($sampleType) {
            //         $sampleTypeName = $sampleType['name'];
            //     }
            // } else {
            //     foreach ($orderSampleTypeIdsArray as $key => $sampleId) {
            //         $sampleType = SampleType::find($sampleId);
            //         if ($sampleType) {
            //             if (empty($sampleTypeName)) {
            //                 $sampleTypeName = $sampleType['name'];
            //             } else {
            //                 $sampleTypeName = $sampleTypeName . ", " . $sampleType['name'];
            //             }
            //         }
            //     }
            // }
        }

        return $sampleTypeName;
    }

    public static function isSampleNeededToBeCollectedSeparately($sample)
    {
        if ($sample == 'Fluoride Plasma' || $sample == 'fluoride plasma')
            return true;
        return false;
    }

    public function updatePatient(Request $request)
    {
        $user = HomeController::getUserData();
        $umrNumber = rand(10000000, 99999999);

        // $patient = Patients::where('umr_number', $request->umr_number)->first();
        $patient = new Patients();
        $patient->created_by = $user->id;
        $patient->umr_number = $umrNumber;
        $patient->patient_title_name = $request->input('patientTitlename');
        $patient->patient_name = $request->input('patientName');
        $patient->age = $request->input('age');
        $patient->age_type = (empty($request->additionalAgeInput)) ? $request->input('ageType') : $request->input('ageType') . ' ' . $request->additionalAgeInput . ' ' . $request->additionalAgeType;
        $patient->gender = $request->input('gender');
        $patient->phone = $request->input('phone');
        $patient->save();

        $selectedDoctorId = "";
        $selectedReferredById = "";
        $selectedReferredByName = "";

        if (!empty($request->referred_by_id) && !empty($request->referredByInput)) {
            $isReferralCompanyExist = ReferralCompany::where('id', $request->referred_by_id)->first();

            if ($isReferralCompanyExist) {
                $selectedReferredById = $isReferralCompanyExist->id;
                $selectedReferredByName = $isReferralCompanyExist->name;
            }
        }

        if (!empty($request->doctor) && !empty($request->doctorInput)) {
            $selectedDoctorId = $request->doctor;
        }

        $orderEntry = OrderEntry::where('bill_no', $request->bill_no)->first();
        $orderEntry->umr_number = $umrNumber;
        $orderEntry->doctor = $selectedDoctorId;
        $orderEntry->referred_by = $selectedReferredByName;
        $orderEntry->referred_by_id = $selectedReferredById;
        $orderEntry->save();

        return redirect($request->redirect_url);
    }

    public function resultAttachmentsIndex($billNo, $orderNo)
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
            ->where('bill_no', $billNo)
            ->first();

        $orderData = AddReport::select('*')
            ->selectRaw('(SELECT department.department_name FROM department WHERE department.depart_id = add_report.order_department) AS order_department_name')
            ->where('report_id', $orderNo)
            ->first();

        $orderDataList = [];
        $checkResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
        if ($checkResultReport) {
            $orderDataList['report_id'] = $checkResultReport['report_no'];
            $orderDataList['order_method'] = $checkResultReport['method'];
            $orderDataList['order_notes'] = $checkResultReport['notes'];
            $orderDataList['order_result_notes_1'] = '';
            $orderDataList['order_result_notes_2'] = '';
            $orderDataList['order_result_notes_3'] = '';
            $orderDataList['order_advice'] = $checkResultReport['advice'];
            $orderDataList['order_department_name'] = $orderData['order_department_name'];
            $orderDataList['order_name'] = $orderData['order_name'];
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['result_page_1'] = $checkResultReport['result_page_1'];
            $orderDataList['result_page_2'] = $checkResultReport['result_page_2'];
            $orderDataList['result_page_3'] = $checkResultReport['result_page_3'];
            $orderDataList['signature'] = $checkResultReport['signature'];
        } else {
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['order_method'] = $orderData['order_method'];
            $orderDataList['order_notes'] = $orderData['order_result_notes_1'] . $orderData['order_result_notes_2'] . $orderData['order_result_notes_3'];
            $orderDataList['order_result_notes_1'] = $orderData['order_result_notes_1'];
            $orderDataList['order_result_notes_2'] = $orderData['order_result_notes_2'];
            $orderDataList['order_result_notes_3'] = $orderData['order_result_notes_3'];
            $orderDataList['order_advice'] = $orderData['order_advice'];
            $orderDataList['order_department_name'] = $orderData['order_department_name'];
            $orderDataList['order_name'] = $orderData['order_name'];
            $orderDataList['report_id'] = $orderData['report_id'];
            $orderDataList['result_page_1'] = '';
            $orderDataList['result_page_2'] = '';
            $orderDataList['result_page_3'] = '';
            $orderDataList['signature'] = '';
        }

        $reportStatus = "";
        $checkResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
        if ($checkResultReport) {
            $reportStatus = $checkResultReport->status;
        } else {
            $orderSampleTypeIdsArray = explode(', ', $orderData->order_sample_type);
            $sampleTypeName = OrderBillsController::getSampleTypeText($orderNo);

            $sampleBarcodesData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', $sampleTypeName)->first();
            if ($sampleBarcodesData) {
                if ($sampleBarcodesData->status == "received") {
                    $reportStatus = $this->reportSampleReceived;
                } else {
                    $reportStatus = $this->reportSampleCollected;
                }
            } else {
                $reportStatus = "Not found";
            }
        }

        $attachmentsList = ResultAttachments::where('bill_no', $billNo)->where('report_id', $orderNo)->get();

        $orderDetails['orderData'] = $orderDataList;
        $orderDetails['attachmentsList'] = $attachmentsList;
        $orderDetails['reportStatus'] = $reportStatus;
        if (OrderBillsController::isReportCompleted($reportStatus)) {
            $orderDetails['showSaveBtn'] = false;
        } else {
            $orderDetails['showSaveBtn'] = true;
        }

        return view('admin.OrderBills.result_attachments', compact('user', 'orderDetails'));
    }

    public function addAttachment(Request $request)
    {
        $user = HomeController::getUserData();
        $attachment = $request->file('attachment');
        $randomName = $request->fileName;

        $extension = $attachment->getClientOriginalExtension();
        $newFileName = $randomName . '.' . $extension;

        $attachment->move(public_path('assets/uploads/reports'), $newFileName);
        $attachment = 'assets/uploads/reports/' . $newFileName;

        $resultAttachments = new ResultAttachments();
        $resultAttachments->created_by = $user->id;
        $resultAttachments->bill_no = $request->bill_no;
        $resultAttachments->report_id = $request->report_id;
        $resultAttachments->file_name = $randomName;
        $resultAttachments->file_path = $attachment;
        $resultAttachments->save();

        return redirect()->back();
    }

    public function deleteAttachment($id)
    {
        $user = HomeController::getUserData();
        $resultAttachments = ResultAttachments::find($id);
        $resultAttachments->delete();
        return redirect()->back();
    }

    public function resultDispatchIndex($billNo)
    {
        $user = HomeController::getUserData();
        $resultReports = ResultReports::select('*')
            ->where('bill_no', $billNo)
            ->get();
        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();

        foreach ($resultReports as $key => $report) {
            $data = AddReport::find($report->report_no);

            if ($data) {
                if (!$this->isReportCompleted($report->status) || $report->status == $this->reportStatusSaveAndComplete) {
                    unset($resultReports[$key]);
                    continue;
                }

                $orderSampleTypeIdsArray = explode(', ', $data->order_sample_type);
                $sampleTypeName = OrderBillsController::getSampleTypeText($report->report_no);

                $report->order_name = $data->order_name;
                $report->report_id = $data->report_id;
                $report->order_date = OrderBillsController::formatDate($orderEntry->created_at);
                $report->sample_type = $sampleTypeName;
                $report['printable_content'] = OrderBillsController::getPrintContent($report->bill_no, $report->report_no);
            } else {
                unset($resultReports[$key]);
            }
        }

        $leftBalance = OrderEntryController::getLeftBalance($orderEntry->total_bill, $orderEntry->paid_amount, $orderEntry->overall_dis, $orderEntry->is_dis_percentage);

        return view('admin.OrderBills.result_dispatch', compact('user', 'resultReports', 'billNo', 'leftBalance'));
    }

    public function resultDispatched($billNo, $reportId)
    {
        $user = HomeController::getUserData();
        $resultReports = ResultReports::find($reportId);
        $resultReports->status = $this->reportStatusDispatched;
        $resultReports->save();

        $changeBillStatus = "";
        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();

        $orderIdsArray = explode(",", $orderEntry->order_ids);
        $resultReports = ResultReports::where('bill_no', $billNo)->get();

        if (count($orderIdsArray) == count($resultReports)) {
            foreach ($resultReports as $report) {
                if ($report->status == $this->reportStatusDispatched) {
                    $changeBillStatus = $billNo;
                } else {
                    $changeBillStatus = "";
                    break;
                }
            }
        } else {
            $changeBillStatus = "";
        }

        if ($changeBillStatus != "") {
            $orderEntry = OrderEntry::where('bill_no', $changeBillStatus)->first();
            $orderEntry->status = "completed";
            $orderEntry->save();
            return OrderBillsController::inProcessBillsIndex(new Request());
        }

        return redirect()->back();
    }

    public function resultAllDispatched($billNo, $orderIds)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $allOrderIds = explode(',', $orderEntry->order_ids);
        $orderIds = explode(',', $orderIds);
        $index = 0;

        while ($index < count($allOrderIds)) {
            $orderNo = $allOrderIds[$index];

            if (str_contains($orderNo, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $orderNo))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $allOrderIds[] = "" . $labProfile->order_id;
                }
            }

            if (in_array($orderNo, $orderIds)) {
                $resultReports = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();

                if ($resultReports && $resultReports->status != $this->reportStatusSave) {
                    $resultReports->status = $this->reportStatusDispatched;
                    $resultReports->save();
                }
            }

            $index++;
        }

        $changeBillStatus = "";
        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();

        $orderIdsArray = explode(",", $orderEntry->order_ids);
        $resultReports = ResultReports::where('bill_no', $billNo)->get();

        if (count($orderIdsArray) == count($resultReports)) {
            foreach ($resultReports as $report) {
                if ($report->status == $this->reportStatusDispatched) {
                    $changeBillStatus = $billNo;
                } else {
                    $changeBillStatus = "";
                    break;
                }
            }
        } else {
            $changeBillStatus = "";
        }

        if ($changeBillStatus != "") {
            $orderEntry = OrderEntry::where('bill_no', $changeBillStatus)->first();
            $orderEntry->status = "completed";
            $orderEntry->save();
            return OrderBillsController::inProcessBillsIndex(new Request());
        }

        return redirect()->back()->with(["actionSuccess" => true, "actionMessage" => "Selected reports has been dispatched"]);
    }

    public function dispatchPageGoBack($billNo)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        if ($orderEntry->status == "completed") {
            return OrderBillsController::inProcessBillsIndex(new Request());
        }

        return redirect()->back()->with(["actionSuccess" => true, "actionMessage" => "go_back"]);
    }

    public function resultAllDispatchedCompletedBills($billNo)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $allOrderIds = explode(',', $orderEntry->order_ids);

        foreach ($allOrderIds as $key => $orderNo) {
            $resultReports = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
            if ($resultReports && $resultReports->status != 'Save') {
                $resultReports->status = $this->reportStatusDispatched;
                $resultReports->save();
            }
        }

        return redirect()->back()->with(["actionSuccess" => true, "actionMessage" => "All reports has been dispatched"]);
    }

    public function getPrintContent($billNo, $orderNo)
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
            ->where('bill_no', $billNo)
            ->first();

        if (!empty(OrderBillsController::getSampleTypeText($orderNo))) {
            $sampleBarcodesData = SampleBarcodes::where('bill_no', $billNo)->where('sample_type', OrderBillsController::getSampleTypeText($orderNo))->first();
        } else {
            $sampleBarcodesData = (object) [
                'created_at' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +5 minutes')),
                'barcode_status_updated_on' => date('Y-m-d H:i:s', strtotime($orderDetails->created_at . ' +10 minutes')),
            ];
        }

        $resultReportsData = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
        $addReportData = AddReport::where('report_id', $orderNo)->first();

        if ($resultReportsData) {
            if (is_null($resultReportsData->signature) || empty($resultReportsData->signature)) {
                if (is_null($addReportData->order_department) || empty($addReportData->order_department)) {
                    $signatureData = Department::where('depart_id', 19)->first();
                } else {
                    $signatureData = Department::where('depart_id', $addReportData->order_department)->first();
                }
            } else {
                $signatureData = DepartmentSignatures::where('id', $resultReportsData->signature)->first();
            }

            if ($signatureData) {
                $orderDetails['signature_label'] = $signatureData->signature_label;
                $orderDetails['signature_image'] = $signatureData->signature_image;
                $orderDetails['left_signature_label'] = $signatureData->left_signature_label;
                $orderDetails['left_signature_image'] = $signatureData->left_signature_image;
            }
        }

        $orderDetails['bill_formatted_date'] = OrderBillsController::formatDate($orderDetails->created_at);
        $orderDetails['sample_type'] = OrderBillsController::getSampleTypeText($orderNo);
        $orderDetails['order_name'] = $addReportData->order_name;
        $orderDetails['order_display_name'] = $addReportData->order_display_name;

        $departmentName = Department::where('depart_id', $addReportData->order_department)->first();
        $orderDetails['order_department_name'] = ($departmentName) ? $departmentName->department_name : "";

        $orderDetails['report_id'] = $addReportData->report_id;

        if (!empty($sampleBarcodesData->created_at)) {
            $orderDetails['sample_collection_date'] = OrderBillsController::formatDate($sampleBarcodesData->created_at);
        }
        if (!empty($sampleBarcodesData->barcode_status_updated_on)) {
            $orderDetails['sample_received_date'] = OrderBillsController::formatDate($sampleBarcodesData->barcode_status_updated_on);
        }

        $componentsDataList = array();
        if ($resultReportsData && OrderBillsController::isReportCompleted($resultReportsData->status)) {
            $orderDetails['result_page_1'] = $resultReportsData->result_page_1;
            $orderDetails['result_page_2'] = $resultReportsData->result_page_2;
            $orderDetails['result_page_3'] = $resultReportsData->result_page_3;

            $orderDetails['reporting_date'] = OrderBillsController::formatDate($resultReportsData->created_at);
            $typedBy = HomeController::getUserDataFromId($resultReportsData->created_by);
            $orderDetails['typed_by'] = $typedBy->first_name . ' ' . $typedBy->last_name;

            if (!empty($resultReportsData->method))
                $orderDetails['reporting_method'] = $resultReportsData->method;
            if (!empty($resultReportsData->notes))
                $orderDetails['reporting_notes'] = $resultReportsData->notes;
            if (!empty($resultReportsData->advice))
                $orderDetails['reporting_advice'] = $resultReportsData->advice;

            $resultReportItems = ResultReportsItems::select('*')
                ->where('result_reports_id', $resultReportsData->id)
                ->selectRaw('(SELECT order_details.component_name FROM order_details WHERE order_details.id = result_reports_items.component_id) AS component_name')
                ->selectRaw('(SELECT order_details.sub_heading FROM order_details WHERE order_details.id = result_reports_items.component_id) AS sub_heading')
                ->orderBy('position', 'asc')
                ->get();

            foreach ($resultReportItems as $reportItem) {
                $componentsDataList[] = array(
                    'sub_heading' => $reportItem['sub_heading'],
                    'component_name' => $reportItem['component_name'],
                    'id' => $reportItem['component_id'],
                    'order_details_range' => $reportItem['results_range'],
                    'units' => $reportItem['units'],
                    'method' => $reportItem['method'],
                    'results' => $reportItem['results'],
                    'abnormal' => $reportItem['abnormal']
                );
            }
        }
        $orderDetails['report_items_data'] = $componentsDataList;
        return $orderDetails;
    }

    public function getOrderEntryData($billNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();
        $orderDetails['balance'] = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);
        return $orderDetails;
    }

    public function getOrderEntryTransactionsData($billNo)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderEntryTransactions::select('*')
            ->where('bill_no', $billNo)
            ->selectRaw("(SELECT CONCAT(users.first_name, ' ', users.last_name) FROM users WHERE users.id = order_entry_transactions.created_by) AS full_name")
            ->get();
        return $orderDetails;
    }

    public function saveBillPayment(Request $request)
    {
        $user = HomeController::getUserData();
        $amountPaidNow = str_replace(" ", "", $request->amountPaidNow);

        $orderEntry = OrderEntry::where('bill_no', $request->billNo)->first();
        $orderEntry->overall_dis = $request->overallDis;
        $orderEntry->is_dis_percentage = $request->isDisPercentage;
        $orderEntry->reason_for_discount = $request->reasonForDiscount;

        if ($amountPaidNow != "" && $amountPaidNow != "0") {
            $orderEntry->paid_amount = ($orderEntry->paid_amount + $amountPaidNow);

            $orderEntryTransactions = new OrderEntryTransactions();
            $orderEntryTransactions->created_by = $user->id;
            $orderEntryTransactions->bill_no = $request->billNo;
            $orderEntryTransactions->amount = $amountPaidNow;
            $orderEntryTransactions->payment_method = $request->paymentMethod;
            $orderEntryTransactions->txn_id = $request->paymentNumber;
            $orderEntryTransactions->save();
        }

        $orderEntry->save();

        return redirect()->back();
    }

    public function getReportDates($billNo, $reportId)
    {
        $user = HomeController::getUserData();
        $outputData = array();

        $orderDetails = OrderEntry::where('bill_no', $billNo)->first();

        if ($orderDetails) {
            $dateTime = new DateTime($orderDetails->created_at);
            $date = $dateTime->format("Y-m-d");
            $time = $dateTime->format("H:i");

            $outputData['bill_date'] = $date;
            $outputData['bill_time'] = $time;
            $outputData['bill_no'] = $billNo;
        }

        $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $reportId)->first();

        if ($checkReportAdded) {
            $dateTime = new DateTime($checkReportAdded->created_at);
            $date = $dateTime->format("Y-m-d");
            $time = $dateTime->format("H:i");

            $outputData['report_date'] = $date;
            $outputData['report_time'] = $time;
            $outputData['report_no'] = $reportId;
        }

        return $outputData;
    }

    public function updateReportDates(Request $request)
    {
        $user = HomeController::getUserData();

        $orderDetails = OrderEntry::where('bill_no', $request->updateBillNo)->first();
        $newCreatedAt = date('Y-m-d H:i:s', strtotime($request->updateBillDate . " " . $request->updateBillTime));
        $newCreatedAtPlus5Minutes = date('Y-m-d H:i:s', strtotime($newCreatedAt . " +5 minutes"));
        $newCreatedAtPlus10Minutes = date('Y-m-d H:i:s', strtotime($newCreatedAt . " +10 minutes"));

        if ($orderDetails && $orderDetails->created_at != $newCreatedAt) {
            $orderDetails->created_at = $newCreatedAt;
            $orderDetails->save();

            $updateSampleBarcodeDates = SampleBarcodes::where('bill_no', $request->updateBillNo)->get();

            foreach ($updateSampleBarcodeDates as $barcode) {
                $barcode->created_at = $newCreatedAtPlus5Minutes;

                if ($barcode->barcode_status_updated_on != NULL) {
                    $barcode->barcode_status_updated_on = $newCreatedAtPlus10Minutes;
                }

                $barcode->save();
            }
        }

        if (!is_null($request->updateReportingNo) && !empty($request->updateReportingNo)) {
            $checkReport = ResultReports::where('bill_no', $request->updateBillNo)->where('report_no', $request->updateReportingNo)->first();

            if ($checkReport) {
                $checkReport->created_at = date('Y-m-d H:i:s', strtotime($request->updateReportingDate . " " . $request->updateReportingTime));
                $checkReport->save();
            }
        }

        return redirect()->back();
    }

    public function sendBillWhatsapp(Request $request)
    {
        $pdfFile = $request->file('pdf');
        $filename = $pdfFile->getClientOriginalName();

        $pdfFile->move(public_path('assets/uploads/reports'), $filename);
        $fileUrl = url('assets/uploads/reports/' . $filename);

        $apiEndpoint = 'http://api.wtap.sms4power.com/wapp/api/send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $postData = array(
            'apikey' => 'eb61e2275395438b88a4bb465eba19e0',
            'msg' => '',
            'mobile' => $request->phone,
            'pdf' => $fileUrl
        );

        $queryString = http_build_query($postData);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }
        curl_close($ch);

        return response()->json(['success' => 'File uploaded successfully', 'fileUrl' => $fileUrl, 'response' => $response]);
    }

    public function sendReportWhatsapp(Request $request)
    {
        $phone = $request->phone;
        $billNo = $request->bill_no;
        $orderNo = $request->order_no;
        // $orderIds = $request->query('orders');

        if (empty($phone) || empty($billNo)) {
            return "Something went wrong";
        }

        $user = new stdClass();
        $user->settings = SystemSettings::first();

        if (!empty($orderNo)) {
            $qrCodeUrl = url('/') . '/viewbill/' . $billNo . '?orders=' . $orderNo;
            $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);

            $html = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'qrCodeUrl'))->render();
            $pdf = SnappyPdf::loadHTML($html)
                ->setOption('zoom', 1.25)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-top', '0mm')
                ->setOption('margin-bottom', '0mm')
                ->setOption('margin-left', '0mm')
                ->setOption('margin-right', '0mm')
                ->setOption('dpi', 200);

            $filename = $this->getReportPdfName($billNo);
            $pdf->save(public_path('assets/uploads/reports/' . $filename), true);
            $fileUrl = url('assets/uploads/reports/' . $filename);

            ResultReports::where('bill_no', $billNo)
                ->where('report_no', $orderNo)
                ->update([
                    'status' => $this->reportStatusSentOnWhatsApp
                ]);

            $this->finalReportSendOnWhatsapp($fileUrl, $phone);
        } else {
            $orderDetails = OrderEntry::select('*')
                ->where('bill_no', $billNo)
                ->first();

            $qrCodeUrl = url('/') . '/viewbill/' . $billNo . '?orders=' . $orderDetails->order_ids;
            $orderIdsArray = explode(',', $orderDetails->order_ids);

            $index = 0;
            $pages = [];

            while ($index < count($orderIdsArray)) {
                $id = $orderIdsArray[$index];

                if (str_contains($id, $this->orderTypeProfile)) {
                    $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                    foreach ($labProfileDetails as $labProfile) {
                        $orderIdsArray[] = "" . $labProfile->order_id;
                    }
                }

                $index++;
                $data = AddReport::find($id);

                if ($data) {
                    $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                    if ($checkReportAdded && $this->isReportCompleted($checkReportAdded->status)) {
                        $orderNo = $data->report_id;
                        $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);

                        ResultReports::where('bill_no', $billNo)
                            ->where('report_no', $orderNo)
                            ->update([
                                'status' => $this->reportStatusSentOnWhatsApp
                            ]);

                        $pageContent = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'qrCodeUrl'))->render();
                        $pages[] = $pageContent;
                    }
                }
            }

            $pdf = SnappyPdf::loadView('admin.OrderBills.multipages_pdf', ['pages' => $pages])
                ->setOption('zoom', 1.25)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-top', '0mm')
                ->setOption('margin-bottom', '0mm')
                ->setOption('margin-left', '0mm')
                ->setOption('margin-right', '0mm')
                ->setOption('dpi', 200);

            $pdfName = $this->getReportPdfName($billNo);

            $pdf->save(public_path('assets/uploads/reports/' . $pdfName), true);

            $mergedFileUrl = url('assets/uploads/reports/' . $pdfName);

            $this->finalReportSendOnWhatsapp($mergedFileUrl, $phone);
        }

        return response()->json(['success' => 'Sent successfully']);
    }

    public function sendSelectedReportWhatsapp(Request $request)
    {
        $phone = $request->phone;
        $billNo = $request->bill_no;
        $orderIds = $request->order_no;

        if (empty($phone) || empty($billNo) || empty($orderIds)) {
            return "Something went wrong";
        }

        $user = new stdClass();
        $user->settings = SystemSettings::first();

        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();

        $qrCodeUrl = url('/') . '/viewbill/' . $orderDetails->bill_no . '?orders=' . $orderIds;

        $orderIdsArray = explode(',', $orderIds);
        $index = 0;

        $pages = [];

        while ($index < count($orderIdsArray)) {
            $id = $orderIdsArray[$index];

            if (str_contains($id, $this->orderTypeProfile)) {
                $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $id))->get();

                foreach ($labProfileDetails as $labProfile) {
                    $orderIdsArray[] = "" . $labProfile->order_id;
                }
            }

            $index++;
            $data = AddReport::find($id);

            if ($data) {
                $checkReportAdded = ResultReports::where('bill_no', $billNo)->where('report_no', $data->report_id)->first();

                if ($checkReportAdded && $this->isReportCompleted($checkReportAdded->status)) {
                    $orderNo = $data->report_id;
                    $orderDetails = OrderBillsController::getResultData($billNo, $orderNo);

                    ResultReports::where('bill_no', $billNo)
                        ->where('report_no', $orderNo)
                        ->update([
                            'status' => $this->reportStatusSentOnWhatsApp
                        ]);

                    $pageContent = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'qrCodeUrl'))->render();
                    $pages[] = $pageContent;
                }
            }
        }

        $pdf = SnappyPdf::loadView('admin.OrderBills.multipages_pdf', ['pages' => $pages])
            ->setOption('zoom', 1.25)
            ->setPaper('a4')
            ->setOrientation('portrait')
            ->setOption('margin-top', '0mm')
            ->setOption('margin-bottom', '0mm')
            ->setOption('margin-left', '0mm')
            ->setOption('margin-right', '0mm')
            ->setOption('dpi', 200);

        $pdfName = $this->getReportPdfName($billNo);

        $pdf->save(public_path('assets/uploads/reports/' . $pdfName), true);

        $mergedFileUrl = url('assets/uploads/reports/' . $pdfName);

        $this->finalReportSendOnWhatsapp($mergedFileUrl, $phone);

        return response()->json(['success' => 'Sent successfully']);
    }

    public function finalReportSendOnWhatsapp($fileUrl, $phone)
    {
        $apiEndpoint = 'http://api.wtap.sms4power.com/wapp/api/send';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $postData = array(
            'apikey' => 'eb61e2275395438b88a4bb465eba19e0',
            'msg' => '',
            'mobile' => $phone,
            'pdf' => $fileUrl
        );

        $queryString = http_build_query($postData);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);
    }

    public function getReportPdfName($billNo)
    {
        $orderDetails = OrderEntry::select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->where('bill_no', $billNo)
            ->first();

        $patientName = strtolower($orderDetails->patient_title_name . '_' . $orderDetails->patient_name);
        $patientName = preg_replace('/[^a-z0-9]+/', '_', $patientName);

        return $patientName . '-(' . $billNo . ')-' . uniqid() . '.pdf';
    }

    public function returnOrderAmount(Request $request)
    {
        $user = HomeController::getUserData();
        // $orderEntry = OrderEntry::where('bill_no', $request->returnBillNo)->first();

        // $orderIdsArray = explode(',', $orderEntry->order_ids);
        // $orderAmountArray = explode(',', $orderEntry->order_amount);

        // foreach ($orderIdsArray as $index => $orderId) {
        //     if ($orderId == $request->returnOrderNo) {
        //         unset($orderIdsArray[$index]);
        //         unset($orderAmountArray[$index]);
        //         break;
        //     }
        // }

        // $newOrderIds = implode(',', $orderIdsArray);
        // $newOrderAmount = implode(',', $orderAmountArray);

        // $orderEntry->order_ids = $newOrderIds;
        // $orderEntry->order_amount = $newOrderAmount;
        // $orderEntry->paid_amount = $orderEntry->paid_amount - $request->returnAmount;
        // $orderEntry->save();

        $orderReturnAmount = OrderReturnAmount::where('bill_no', $request->returnBillNo)->where('order_no', $request->returnOrderNo)->first();
        if (!$orderReturnAmount)
            $orderReturnAmount = new OrderReturnAmount();

        $orderReturnAmount->created_by = $user->id;
        $orderReturnAmount->bill_no = $request->returnBillNo;
        $orderReturnAmount->order_no = $request->returnOrderNo;
        $orderReturnAmount->type = $request->returnRadioSelection;
        $orderReturnAmount->amount = $request->returnAmount;
        $orderReturnAmount->note = $request->returnNotes;
        $orderReturnAmount->save();

        return redirect()->back();
    }

    public function hardRefreshResultReports($billNo)
    {
        $user = HomeController::getUserData();
        $resultReports = ResultReports::where('bill_no', $billNo)->get();
        foreach ($resultReports as $key => $result) {
            ResultReportsItems::where('result_reports_id', $result->id)->delete();
            $result->delete();
        }
        return redirect()->back();
    }

    public function hardRefreshResultReport($billNo, $orderNo)
    {
        $user = HomeController::getUserData();
        $resultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();

        if ($resultReport) {
            ResultReportsItems::where('result_reports_id', $resultReport->id)->delete();
            $resultReport->delete();
        }

        return redirect()->back();
    }

    public function searchOrderDetailValues($componentId, $search = "")
    {
        if (empty($search)) {
            return OrderDetailValues::where('order_detail_id', $componentId)->get();
        }

        return OrderDetailValues::where('order_detail_id', $componentId)->where('value', 'LIKE', '%' . $search . '%')->get();
    }

    public function updateOrderValuesFromMachine(Request $request)
    {
        $updateData = new UpdateOrderValuesFromMachine();
        $updateData->method = $request->getMethod();
        $updateData->response = json_encode($request->all());
        $updateData->save();

        if (
            isset($request->all()['BillNumber'])
            && isset($request->all()['MachineCode'])
            && isset($request->all()['Result'])
        ) {
            $billNumber = $request->all()['BillNumber'];
            $machineCode = $request->all()['MachineCode'];
            $resultValue = $request->all()['Result'];

            if (!empty($billNumber) && !empty($machineCode) && !empty($resultValue)) {
                $orderDetails = OrderEntry::select('*')
                    ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                    ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                    ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                    ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                    ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                    ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                    ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                    ->where('bill_no', $billNumber)
                    ->first();

                $orderIdsArray = explode(',', $orderDetails->order_ids);
                $index = 0;

                while ($index < count($orderIdsArray)) {
                    $orderNo = $orderIdsArray[$index];

                    if (str_contains($orderNo, $this->orderTypeProfile)) {
                        $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $orderNo))->get();

                        foreach ($labProfileDetails as $labProfile) {
                            $orderIdsArray[] = "" . $labProfile->order_id;
                        }
                    }

                    $data = AddReport::where('has_components', 'true')
                        ->where('report_id', $orderNo)
                        ->first();

                    if ($data) {
                        $sampleName = OrderBillsController::getSampleTypeText($orderNo);

                        if (!empty($sampleName)) {
                            $checkBarcode = SampleBarcodes::where('bill_no', $billNumber)
                                ->where('sample_type', $sampleName)
                                ->where('status', 'received')
                                ->first();

                            if ($checkBarcode) {
                                $checkResultReport = ResultReports::where('bill_no', $billNumber)
                                    ->where('report_no', $orderNo)
                                    ->first();

                                if ($checkResultReport) {
                                    $getComponentsData = ResultReportsItems::select('*')
                                        ->where('result_reports_id', $checkResultReport->id)
                                        ->selectRaw('(SELECT order_details.component_name FROM order_details WHERE order_details.id = result_reports_items.component_id) AS component_name')
                                        ->selectRaw('(SELECT order_details.from_range FROM order_details WHERE order_details.id = result_reports_items.component_id) AS from_range')
                                        ->selectRaw('(SELECT order_details.to_range FROM order_details WHERE order_details.id = result_reports_items.component_id) AS to_range')
                                        ->selectRaw('(SELECT order_details.machine_code FROM order_details WHERE order_details.id = result_reports_items.component_id) AS machine_code')
                                        ->orderBy('position', 'asc')
                                        ->get();

                                    foreach ($getComponentsData as $component) {
                                        if ($component['machine_code'] == $machineCode) {
                                            $newResultValue = $this->cleanNumericString((string) $resultValue);
                                            $fromRange = $this->cleanNumericString((string) $component['from_range']);
                                            $toRange = $this->cleanNumericString((string) $component['to_range']);

                                            $isAbnormal = false;

                                            if (!empty($fromRange) && !empty($toRange)) {
                                                $isAbnormal = ($this->compareInt($newResultValue, $fromRange) == -1) || ($this->compareInt($newResultValue, $toRange) == 1);
                                            }

                                            $resultReportUpdate = ResultReportsItems::where('id', $component['id'])->first();
                                            $resultReportUpdate->results = $resultValue;
                                            $resultReportUpdate->abnormal = $isAbnormal ? 'on' : 'off';
                                            $resultReportUpdate->save();

                                            $checkResultReport->status = $this->reportStatusSave;
                                            $checkResultReport->save();
                                        }
                                    }
                                } else {
                                    $createResultReports = new ResultReports();
                                    $createResultReports->created_by = 1;
                                    $createResultReports->bill_no = $billNumber;
                                    $createResultReports->report_no = $orderNo;
                                    $createResultReports->method = $data->order_method;
                                    $createResultReports->notes = $data->order_result_notes_1 . $data->order_result_notes_2 . $data->order_result_notes_3;
                                    $createResultReports->advice = $data->order_advice;
                                    $createResultReports->status = $this->reportStatusNotFound;
                                    $createResultReports->result_page_1 = $data->order_result_notes_1;
                                    $createResultReports->result_page_2 = $data->order_result_notes_2;
                                    $createResultReports->result_page_3 = $data->order_result_notes_3;
                                    $createResultReports->save();

                                    $templeteData = DB::table('order_templates')
                                        ->where('report_id', $orderNo)
                                        ->where('template_gender', $orderDetails->patient_gender)
                                        ->where('status', 'Active')
                                        ->get();

                                    $templeteId = null;
                                    if ($templeteData) {
                                        $patientAgeType = explode(' ', $orderDetails->patient_age_type)[0];
                                        $patientAgeInDays = $this->convertAgeToDays($orderDetails->patient_age, $patientAgeType);

                                        foreach ($templeteData as $key => $value) {
                                            $templateFromAgeInDays = $this->convertAgeToDays($value->template_from_age, $value->template_from_age_type);
                                            $templateToAgeInDays = $this->convertAgeToDays($value->template_to_age, $value->template_to_age_type);

                                            if (
                                                $templateFromAgeInDays <= $patientAgeInDays &&
                                                $patientAgeInDays <= $templateToAgeInDays
                                            ) {
                                                $templeteId = $value->id;
                                                break;
                                            }
                                        }
                                    }

                                    $componentsData = OrderDetails::select('*')
                                        ->where('report_id', $orderNo)
                                        ->where(function ($query) use ($templeteId) {
                                            if ($templeteId === null) {
                                                $query->whereNull('template_id');
                                            } else {
                                                $query->where('template_id', $templeteId);
                                            }
                                        })
                                        ->orderBy('position', 'asc')
                                        ->get();

                                    foreach ($componentsData as $component) {
                                        $resultReportsItems = new ResultReportsItems();
                                        $resultReportsItems->created_by = 1;
                                        $resultReportsItems->result_reports_id = $createResultReports->id;
                                        $resultReportsItems->component_id = $component['id'];

                                        if ($component['machine_code'] == $machineCode) {
                                            $newResultValue = $this->cleanNumericString((string) $resultValue);
                                            $fromRange = $this->cleanNumericString((string) $component['from_range']);
                                            $toRange = $this->cleanNumericString((string) $component['to_range']);

                                            $newResultValue = $this->cleanNumericString((string) $resultValue);
                                            $fromRange = $this->cleanNumericString((string) $component['from_range']);
                                            $toRange = $this->cleanNumericString((string) $component['to_range']);

                                            $isAbnormal = false;

                                            if (!empty($fromRange) && !empty($toRange)) {
                                                $isAbnormal = ($this->compareInt($newResultValue, $fromRange) == -1) || ($this->compareInt($newResultValue, $toRange) == 1);
                                            }

                                            $resultReportsItems->results = $resultValue;
                                            $resultReportsItems->abnormal = $isAbnormal ? "on" : "off";

                                            $updateResultReport = ResultReports::where('bill_no', $billNumber)
                                                ->where('report_no', $orderNo)
                                                ->first();

                                            $updateResultReport->status = $this->reportStatusSave;
                                            $updateResultReport->save();
                                        } else {
                                            $resultReportsItems->results = '';
                                            $resultReportsItems->abnormal = 'off';
                                        }

                                        $resultReportsItems->results_range = $component['order_details_range'];
                                        $resultReportsItems->units = $component['units'];
                                        $resultReportsItems->method = $component['method'];
                                        $resultReportsItems->position = $component['position'];
                                        $resultReportsItems->save();
                                    }
                                }
                            }
                        }
                    }

                    $index++;
                }

                return "success";
            }
        }

        return "failed";
    }

    private function cleanNumericString($str)
    {
        return preg_replace('/[^0-9\.]/', '', $str);
    }

    private function compareInt($value1, $value2)
    {
        return bccomp($value1, $value2);
    }

    private function convertAgeToDays($age, $ageType)
    {
        if ($ageType == 'Years') {
            return $age * 365;
        } elseif ($ageType == 'Months') {
            return $age * 30;
        } elseif ($ageType == 'Days') {
            return $age;
        }

        return 0;
    }
}
