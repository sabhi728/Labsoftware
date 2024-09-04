<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabLocations;
use App\Models\LabProfileDetails;
use App\Models\LabProfiles;
use App\Models\OrderReturnAmount;
use App\Models\ReferralCompany;
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

class ReportsController extends CommonController
{
    public function loginReportIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.Reports.LoginReport.index', compact('user'));
    }

    public function loginReportIndexData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $loginHistory = LoginHistory::whereDate('login_history.created_at', '>=', $fromDate)
            ->whereDate('login_history.created_at', '<=', $toDate)
            ->join('users', 'login_history.user_id', '=', 'users.id')
            ->select('login_history.*', 'users.first_name', 'users.last_name')
            ->orderBy('login_history.id', 'desc')
            ->get();

        return view('admin.Reports.LoginReport.data', compact('user', 'loginHistory', 'fromDate', 'toDate'));
    }

    public function billReportsIndex()
    {
        $user = HomeController::getUserData();

        $locations = LabLocations::get();
        $users = User::get();
        $paymentType = [
            'Cash' => 'Cash',
            'Card' => 'Card',
            'Cheque' => 'Cheque',
            'Paytm' => 'Paytm',
            'UPI' => 'UPI'
        ];
        $orderTypes = OrderType::get();

        return view('admin.Reports.BillReports.index', compact('user', 'locations', 'users', 'paymentType', 'orderTypes'));
    }

    public function billReportsIndexData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        if ($request->has('previous') || $request->has('return')) {
            $orderDetails = OrderEntry::select('order_entry.*')
                ->join('patients', 'order_entry.umr_number', '=', 'patients.umr_number')
                ->leftJoin('doctors', function ($join) {
                    $join->on('order_entry.doctor', '=', 'doctors.id')
                        ->whereNotNull('order_entry.doctor')
                        ->where('order_entry.doctor', '!=', '');
                })
                ->select(
                    'order_entry.*',
                    'patients.patient_title_name',
                    'patients.patient_name',
                    'patients.age',
                    'patients.age_type',
                    'patients.gender',
                    DB::raw('IFNULL(doctors.doc_name, "") AS doc_name')
                )
                ->orderBy('order_entry.id', 'desc')
                ->get();
        } else {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->has('discountBillsReport'), function ($query) use ($request) {
                    return $query->where('reason_for_discount', '!=', '');
                })
                ->when($request->user !== null && $request->user !== '', function ($query) use ($request) {
                    return $query->where('order_entry.created_by', '=', $request->user);
                })
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->discountReason !== null && $request->discountReason !== '', function ($query) use ($request) {
                    return $query->where('order_entry.reason_for_discount', '=', $request->discountReason);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->join('patients', 'order_entry.umr_number', '=', 'patients.umr_number')
                ->leftJoin('doctors', function ($join) {
                    $join->on('order_entry.doctor', '=', 'doctors.id')
                        ->whereNotNull('order_entry.doctor')
                        ->where('order_entry.doctor', '!=', '');
                })
                ->select(
                    'order_entry.*',
                    'patients.patient_title_name',
                    'patients.patient_name',
                    'patients.age',
                    'patients.age_type',
                    'patients.gender',
                    DB::raw('IFNULL(doctors.doc_name, "") AS doc_name')
                )
                ->orderBy('order_entry.id', 'desc')
                ->get();
        }

        $totalAmount = 0;
        $paidAmount = 0;
        $finalAmount = 0;
        $balanceAmount = 0;
        $dicountAmount = 0;
        $returnAmount = 0;

        $advanceAmount = 0;
        $cashPaid = 0;
        $cardPaid = 0;
        $chequePaid = 0;
        $paytmPaid = 0;
        $upiPaid = 0;

        $previousCashPaid = 0;
        $previousCardPaid = 0;
        $previousChequePaid = 0;
        $previousPaytmPaid = 0;
        $previousUpiPaid = 0;

        foreach ($orderDetails as $orderKey => $order) {
            $orderIdsArray = explode(',', $order->order_ids);
            $ordersNameTxt = "";
            $showTheOrder = false;
            $index = 0;

            while ($index < count($orderIdsArray)) {
                $orderNo = $orderIdsArray[$index];

                if (str_contains($orderNo, $this->orderTypeProfile)) {
                    $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $orderNo))->get();

                    foreach ($labProfileDetails as $labProfile) {
                        $orderIdsArray[] = "" . $labProfile->order_id;
                    }
                }

                $data = AddReport::find($orderNo);

                if ($data) {
                    $orderTypes = $request->input('orderType');

                    if ($orderTypes !== null && !empty($orderTypes)) {
                        if ($request->input('orderType')[0] == NULL) {
                            $showTheOrder = true;
                        } else {
                            if (in_array($data->order_order_type, $orderTypes)) {
                                $showTheOrder = true;
                            }
                        }
                    } else {
                        $showTheOrder = true;
                    }

                    if ($ordersNameTxt == "") {
                        $ordersNameTxt = $data->order_name;
                    } else {
                        $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                    }
                }

                $index++;
            }

            if ($showTheOrder) {
                if (($request->has('return') && $order->updated_at->toDateString() != $fromDate) || ($request->has('return') && $order->status != "cancelled")) {
                    unset($orderDetails[$orderKey]);
                    continue;
                } else {
                    if ($order->status != "cancelled") {
                        if ($request->has('return') && OrderReturnAmount::where('bill_no', $order->bill_no)->whereDate('created_at', '=', $fromDate)->sum('amount') == 0) {
                            unset($orderDetails[$orderKey]);
                            continue;
                        }
                    }
                }

                if ($request->has('previous')) {
                    $orderTransactions = OrderEntryTransactions::where('bill_no', $order->bill_no)->whereDate('created_at', '=', $fromDate)->first();
                    if ($orderTransactions) {
                        $checkTransactionCount = OrderEntryTransactions::where('bill_no', $order->bill_no)->count();

                        if ($checkTransactionCount <= 1) {
                            unset($orderDetails[$orderKey]);
                            continue;
                        }
                    } else {
                        unset($orderDetails[$orderKey]);
                        continue;
                    }
                }

                $totalAmount = $totalAmount + $order->total_bill;
                $paidAmount = $paidAmount + $order->paid_amount;
                $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->whereDate('created_at', '=', $this->todayDate)->get();
                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();

                if ($allTransaction) {
                    foreach ($allTransaction as $transaction) {
                        if (str_contains($order->created_at, $this->todayDate)) {
                            $advanceAmount = $advanceAmount + $transaction->amount;

                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $previousCashPaid = $previousCashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $previousCardPaid = $previousCardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $previousChequePaid = $previousChequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                // $firstTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->first();
                // if ($firstTransaction) {
                //     $advanceAmount = $advanceAmount + $firstTransaction->amount;
                //     switch ($firstTransaction->payment_method) {
                //         case 'Cash':
                //             $cashPaid = $cashPaid + $firstTransaction->amount;
                //             break;
                //         case 'Card':
                //             $cardPaid = $cardPaid + $firstTransaction->amount;
                //             break;
                //         case 'Cheque':
                //             $chequePaid = $chequePaid + $firstTransaction->amount;
                //             break;
                //         case 'Paytm':
                //             $paytmPaid = $paytmPaid + $firstTransaction->amount;
                //             break;
                //         case 'UPI':
                //             $upiPaid = $upiPaid + $firstTransaction->amount;
                //             break;
                //     }
                // }

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                // if ($allTransaction) {
                //     foreach ($allTransaction as $key => $transaction) {
                //         if ($key == 0)
                //             continue;
                //         switch ($transaction->payment_method) {
                //             case 'Cash':
                //                 $previousCashPaid = $previousCashPaid + $transaction->amount;
                //                 break;
                //             case 'Card':
                //                 $previousCardPaid = $previousCardPaid + $transaction->amount;
                //                 break;
                //             case 'Cheque':
                //                 $previousChequePaid = $previousChequePaid + $transaction->amount;
                //                 break;
                //             case 'Paytm':
                //                 $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                //                 break;
                //             case 'UPI':
                //                 $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                //                 break;
                //         }
                //     }
                // }

                $order['order_name_txt'] = $ordersNameTxt;
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);

                $order['cash_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cash')->sum('amount');
                $order['card_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Card')->sum('amount');
                $order['cheque_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cheque')->sum('amount');
                $order['paytm_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Paytm')->sum('amount');
                $order['upi_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'UPI')->sum('amount');
                $order['return_amount'] = OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
            } else {
                unset($orderDetails[$orderKey]);
            }
        }

        if ($request->has('getReport')) {
            return view('admin.Reports.BillReports.get_report', compact('user', 'orderDetails', 'fromDate', 'toDate', 'totalAmount', 'paidAmount', 'finalAmount', 'balanceAmount', 'dicountAmount', 'returnAmount'));
        } else if ($request->has('nonFinancial')) {
            return view('admin.Reports.BillReports.non_financial_report', compact('user', 'orderDetails', 'fromDate', 'toDate'));
        } else if ($request->has('financial')) {
            return view(
                'admin.Reports.BillReports.financial_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'advanceAmount',
                    'previousCashPaid',
                    'previousCardPaid',
                    'previousChequePaid',
                    'previousPaytmPaid',
                    'previousUpiPaid',
                    'returnAmount'
                )
            );
        } else if ($request->has('summary')) {
            return view(
                'admin.Reports.BillReports.summary_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'advanceAmount',
                    'previousCashPaid',
                    'previousCardPaid',
                    'previousChequePaid',
                    'previousPaytmPaid',
                    'previousUpiPaid',
                    'returnAmount'
                )
            );
        } else if ($request->has('discountBillsReport')) {
            return view('admin.Reports.BillReports.discount_report', compact('user', 'orderDetails', 'fromDate', 'toDate', 'totalAmount', 'paidAmount', 'finalAmount', 'balanceAmount', 'dicountAmount', 'returnAmount'));
        }
    }

    public function orderSummaryIndex()
    {
        $user = HomeController::getUserData();

        $locations = LabLocations::get();
        $orderDetails = AddReport::get();
        $orderTypes = OrderType::get();

        return view('admin.Reports.OrderSummaryReport.index', compact('user', 'locations', 'orderDetails', 'orderTypes'));
    }

    public function orderSummaryIndexData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        if ($request->has('orderSummary')) {
            $allOrderIds = array();
            $allOrderCount = array();
            $allOrderAmount = array();

            $totalNoOrders = 0;
            $totalAmount = 0;

            $orderEntry = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->get();

            foreach ($orderEntry as $item) {
                $orderIdsArray = explode(',', $item->order_ids);
                $orderAmountArray = explode(',', $item->order_amount);

                foreach ($orderIdsArray as $key => $id) {
                    if (array_search($id, $allOrderIds, true) === false) {
                        $allOrderIds[] = $id;
                        $allOrderCount[] = 1;
                        $allOrderAmount[] = $orderAmountArray[$key];
                    } else {
                        $index = array_search($id, $allOrderIds);
                        $allOrderCount[$index] = $allOrderCount[$index] + 1;
                        $allOrderAmount[$index] = $allOrderAmount[$index] + $orderAmountArray[$key];
                    }
                }
            }

            $orderTypes = $request->input('orderType');
            $dataArray = array();
            $orderReports = AddReport::whereIn('report_id', $allOrderIds)->get();

            foreach ($allOrderIds as $id) {
                $orderReport = $orderReports->firstWhere('report_id', $id);
                $allOrderIndex = array_search($id, $allOrderIds);

                if ($orderTypes !== null && is_array($orderTypes) && $orderTypes[0] !== null) {
                    if ($orderReport && in_array($orderReport->order_order_type, $orderTypes)) {
                        $dataArray[] = array(
                            'id' => $id,
                            'order_name' => $orderReport->order_name,
                            'no_of_orders' => $allOrderCount[$allOrderIndex],
                            'total_amount' => $allOrderAmount[$allOrderIndex]
                        );

                        $totalNoOrders = $totalNoOrders + $allOrderCount[$allOrderIndex];
                        $totalAmount = $totalAmount + $allOrderAmount[$allOrderIndex];
                    }
                } else {
                    if ($orderReport) {
                        $dataArray[] = array(
                            'id' => $id,
                            'order_name' => $orderReport->order_name,
                            'no_of_orders' => $allOrderCount[$allOrderIndex],
                            'total_amount' => $allOrderAmount[$allOrderIndex]
                        );

                        $totalNoOrders = $totalNoOrders + $allOrderCount[$allOrderIndex];
                        $totalAmount = $totalAmount + $allOrderAmount[$allOrderIndex];
                    }
                }
            }

            return view('admin.Reports.OrderSummaryReport.order_summary', compact('user', 'fromDate', 'toDate', 'dataArray', 'totalNoOrders', 'totalAmount'));
        } else if ($request->has('orderTypeSummary')) {
            $allOrderIds = array();
            $allOrderCount = array();
            $allOrderAmount = array();

            $totalNoOrders = 0;
            $totalAmount = 0;

            $orderEntry = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->get();

            foreach ($orderEntry as $item) {
                $orderIdsArray = explode(',', $item->order_ids);
                $orderAmountArray = explode(',', $item->order_amount);

                foreach ($orderIdsArray as $key => $id) {
                    if (array_search($id, $allOrderIds, true) === false) {
                        $allOrderIds[] = $id;
                        $allOrderCount[] = 1;
                        $allOrderAmount[] = $orderAmountArray[$key];
                    } else {
                        $index = array_search($id, $allOrderIds);
                        $allOrderCount[$index] = $allOrderCount[$index] + 1;
                        $allOrderAmount[$index] = $allOrderAmount[$index] + $orderAmountArray[$key];
                    }
                }
            }

            $orderTypes = $request->input('orderType');
            $dataArray = array();
            $orderReports = AddReport::whereIn('report_id', $allOrderIds)->get();

            $orderTypeIds = array();
            $orderTypeCount = array();
            $orderTypeAmount = array();

            foreach ($orderReports as $report) {
                $allOrderIndex = array_search($report->report_id, $allOrderIds);

                if ($orderTypes !== null && is_array($orderTypes) && $orderTypes[0] !== null) {
                    if ($report && in_array($report->order_order_type, $orderTypes)) {
                        if (array_search($report->order_order_type, $orderTypeIds, true) === false) {
                            $orderTypeIds[] = $report->order_order_type;
                            $orderTypeCount[] = $allOrderCount[$allOrderIndex];
                            $orderTypeAmount[] = $allOrderAmount[$allOrderIndex];
                        } else {
                            $index = array_search($report->order_order_type, $orderTypeIds);
                            $orderTypeCount[$index] = $orderTypeCount[$index] + $allOrderCount[$allOrderIndex];
                            $orderTypeAmount[$index] = $orderTypeAmount[$index] + $allOrderAmount[$allOrderIndex];
                        }
                    }
                } else {
                    if (array_search($report->order_order_type, $orderTypeIds, true) === false) {
                        $orderTypeIds[] = $report->order_order_type;
                        $orderTypeCount[] = $allOrderCount[$allOrderIndex];
                        $orderTypeAmount[] = $allOrderAmount[$allOrderIndex];
                    } else {
                        $index = array_search($report->order_order_type, $orderTypeIds);
                        $orderTypeCount[$index] = $orderTypeCount[$index] + $allOrderCount[$allOrderIndex];
                        $orderTypeAmount[$index] = $orderTypeAmount[$index] + $allOrderAmount[$allOrderIndex];
                    }
                }
            }

            $orderTypes = OrderType::whereIn('id', $orderTypeIds)->get();
            foreach ($orderTypes as $data) {
                $index = array_search($data->id, $orderTypeIds);

                $dataArray[] = array(
                    'id' => $data->id,
                    'order_name' => $data->name,
                    'no_of_orders' => $orderTypeCount[$index],
                    'total_amount' => $orderTypeAmount[$index]
                );

                $totalNoOrders = $totalNoOrders + $orderTypeCount[$index];
                $totalAmount = $totalAmount + $orderTypeAmount[$index];
            }

            return view('admin.Reports.OrderSummaryReport.order_summary', compact('user', 'fromDate', 'toDate', 'dataArray', 'totalNoOrders', 'totalAmount'));
        } else if ($request->has('orderTypeDetailed')) {
            $orderEntry = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->get();

            if (!empty($request->input('orderType'))) {
                $orderTypes = $request->input('orderType');
                $orderTypeIds = array();

                foreach ($orderEntry as $key => $item) {
                    $orderIdsArray = explode(',', $item->order_ids);
                    foreach ($orderIdsArray as $key => $id) {
                        if (array_search($id, $orderTypeIds, true) === false) {
                            $orderTypeIds[] = $id;
                        }
                    }
                }

                $orderReports = AddReport::where(function ($query) use ($orderTypeIds, $orderTypes) {
                    $query->whereIn('report_id', $orderTypeIds)
                        ->whereIn('order_order_type', $orderTypes);
                })->get();
                $orderReportIds = array();

                foreach ($orderReports as $item) {
                    if (array_search($item->report_id, $orderReportIds, true) === false) {
                        $orderReportIds[] = $item->report_id;
                    }
                }
            }
            if (!empty($orderReportIds)) {
                $allOrders = OrderEntry::select('*')
                    ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                    ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                    ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                    ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                    ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                    ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                    ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                    ->whereDate('order_entry.created_at', '>=', $fromDate)
                    ->whereDate('order_entry.created_at', '<=', $toDate)
                    ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                        return $query->where('referred_by_id', $request->location);
                    })
                    ->where(function ($query) use ($orderReportIds) {
                        foreach ($orderReportIds as $orderId) {
                            $query->orWhereRaw("FIND_IN_SET('$orderId', order_entry.order_ids) > 0");
                        }
                    })
                    ->get();

                $totalAmount = 0;
                foreach ($allOrders as $order) {
                    $orderIdsArray = explode(',', $order->order_ids);
                    $ordersNameTxt = "";

                    $addReport = AddReport::whereIn('report_id', $orderIdsArray)->get();
                    foreach ($addReport as $data) {
                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = $data->order_name;
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                        }
                    }

                    $totalAmount += $order->total_bill;
                    $order->order_name_txt = $ordersNameTxt;
                }
            } else {
                $totalAmount = 0;
                $allOrders = array();
            }

            return view('admin.Reports.OrderSummaryReport.order_type_details', compact('user', 'fromDate', 'toDate', 'totalAmount', 'allOrders'));
        } else if ($request->has('orderDetailed')) {
            $order = $request->input('order');
            if (empty($order))
                return "Select valid orders to view details overview";

            $orderEntry = OrderEntry::select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->where(function ($query) use ($order) {
                    foreach ($order as $orderId) {
                        $query->orWhereRaw("FIND_IN_SET('$orderId', order_entry.order_ids) > 0");
                    }
                })
                ->get();

            foreach ($orderEntry as $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                $addReport = AddReport::whereIn('report_id', $orderIdsArray)->get();
                foreach ($addReport as $data) {
                    if ($ordersNameTxt == "") {
                        $ordersNameTxt = $data->order_name;
                    } else {
                        $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                    }
                }

                $order->order_name_txt = $ordersNameTxt;
            }

            $totalOrders = count($orderEntry);
            return view('admin.Reports.OrderSummaryReport.order_details', compact('user', 'fromDate', 'toDate', 'totalOrders', 'orderEntry'));
        } else if ($request->has('externalOrders')) {
            $allOrderIds = array();
            $allOrderCount = array();
            $allOrderAmount = array();

            $totalNoOrders = 0;
            $totalAmount = 0;

            $orderEntry = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->get();

            foreach ($orderEntry as $item) {
                $orderIdsArray = explode(',', $item->order_ids);
                $orderAmountArray = explode(',', $item->order_amount);

                foreach ($orderIdsArray as $key => $id) {
                    if (array_search($id, $allOrderIds, true) === false) {
                        $allOrderIds[] = $id;
                        $allOrderCount[] = 1;
                        $allOrderAmount[] = $orderAmountArray[$key];
                    } else {
                        $index = array_search($id, $allOrderIds);
                        $allOrderCount[$index] = $allOrderCount[$index] + 1;
                        $allOrderAmount[$index] = $allOrderAmount[$index] + $orderAmountArray[$key];
                    }
                }
            }

            $dataArray = array();
            $orderReports = AddReport::whereIn('report_id', $allOrderIds)->get();

            foreach ($allOrderIds as $id) {
                $orderReport = $orderReports->firstWhere('report_id', $id);
                $allOrderIndex = array_search($id, $allOrderIds);

                if ($orderReport && $orderReport->order_order_type == 3) {
                    $dataArray[] = array(
                        'id' => $id,
                        'order_name' => $orderReport->order_name,
                        'no_of_orders' => $allOrderCount[$allOrderIndex],
                        'total_amount' => $allOrderAmount[$allOrderIndex]
                    );

                    $totalNoOrders = $totalNoOrders + $allOrderCount[$allOrderIndex];
                    $totalAmount = $totalAmount + $allOrderAmount[$allOrderIndex];
                }
            }

            return view('admin.Reports.OrderSummaryReport.external_summary', compact('user', 'fromDate', 'toDate', 'dataArray', 'totalNoOrders', 'totalAmount'));
        } else if ($request->has('externalTypeSummary')) {
            $allOrderIds = array();
            $allOrderCount = array();
            $allOrderAmount = array();

            $totalNoOrders = 0;
            $totalAmount = 0;

            $orderEntry = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->get();

            foreach ($orderEntry as $item) {
                $orderIdsArray = explode(',', $item->order_ids);
                $orderAmountArray = explode(',', $item->order_amount);

                foreach ($orderIdsArray as $key => $id) {
                    if (array_search($id, $allOrderIds, true) === false) {
                        $allOrderIds[] = $id;
                        $allOrderCount[] = 1;
                        $allOrderAmount[] = $orderAmountArray[$key];
                    } else {
                        $index = array_search($id, $allOrderIds);
                        $allOrderCount[$index] = $allOrderCount[$index] + 1;
                        $allOrderAmount[$index] = $allOrderAmount[$index] + $orderAmountArray[$key];
                    }
                }
            }

            $dataArray = array();
            $orderReports = AddReport::whereIn('report_id', $allOrderIds)->get();

            $orderTypeIds = array();
            $orderTypeCount = array();
            $orderTypeAmount = array();

            foreach ($orderReports as $report) {
                $allOrderIndex = array_search($report->report_id, $allOrderIds);

                if ($report && $report->order_order_type == 3) {
                    if (array_search($report->order_order_type, $orderTypeIds, true) === false) {
                        $orderTypeIds[] = $report->order_order_type;
                        $orderTypeCount[] = $allOrderCount[$allOrderIndex];
                        $orderTypeAmount[] = $allOrderAmount[$allOrderIndex];
                    } else {
                        $index = array_search($report->order_order_type, $orderTypeIds);
                        $orderTypeCount[$index] = $orderTypeCount[$index] + $allOrderCount[$allOrderIndex];
                        $orderTypeAmount[$index] = $orderTypeAmount[$index] + $allOrderAmount[$allOrderIndex];
                    }
                }
            }

            $orderTypes = OrderType::whereIn('id', $orderTypeIds)->get();
            foreach ($orderTypes as $data) {
                $index = array_search($data->id, $orderTypeIds);

                $dataArray[] = array(
                    'id' => $data->id,
                    'order_name' => $data->name,
                    'no_of_orders' => $orderTypeCount[$index],
                    'total_amount' => $orderTypeAmount[$index]
                );

                $totalNoOrders = $totalNoOrders + $orderTypeCount[$index];
                $totalAmount = $totalAmount + $orderTypeAmount[$index];
            }

            return view('admin.Reports.OrderSummaryReport.external_summary', compact('user', 'fromDate', 'toDate', 'dataArray', 'totalNoOrders', 'totalAmount'));
        }
    }

    public function shiftCollectionIndex()
    {
        $user = HomeController::getUserData();

        $locations = LabLocations::get();
        $users = User::get();

        return view('admin.Reports.ShiftCollection.index', compact('user', 'locations', 'users'));
    }

    public function shiftCollectionIndexData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        if ($request->has('shiftCollectionDetailed')) {
            $orderDetails = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('created_by', $request->input('user'))
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $totalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $balanceAmount = 0;
            $dicountAmount = 0;
            $returnAmount = 0;

            $advanceAmount = 0;
            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            $previousCashPaid = 0;
            $previousCardPaid = 0;
            $previousChequePaid = 0;
            $previousPaytmPaid = 0;
            $previousUpiPaid = 0;
            $previousDuesPaid = 0;

            $billCount = count($orderDetails);

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalAmount = $totalAmount + $order->total_bill;
                $paidAmount = $paidAmount + $order->paid_amount;
                $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->whereDate('created_at', '=', $this->todayDate)->get();
                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();

                if ($allTransaction) {
                    foreach ($allTransaction as $transaction) {
                        if (str_contains($order->created_at, $this->todayDate)) {
                            $advanceAmount = $advanceAmount + $transaction->amount;

                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $previousCashPaid = $previousCashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $previousCardPaid = $previousCardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $previousChequePaid = $previousChequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                // $firstTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->first();
                // if ($firstTransaction) {
                //     $advanceAmount = $advanceAmount + $firstTransaction->amount;
                //     switch ($firstTransaction->payment_method) {
                //         case 'Cash':
                //             $cashPaid = $cashPaid + $firstTransaction->amount;
                //             break;
                //         case 'Card':
                //             $cardPaid = $cardPaid + $firstTransaction->amount;
                //             break;
                //         case 'Cheque':
                //             $chequePaid = $chequePaid + $firstTransaction->amount;
                //             break;
                //         case 'Paytm':
                //             $paytmPaid = $paytmPaid + $firstTransaction->amount;
                //             break;
                //         case 'UPI':
                //             $upiPaid = $upiPaid + $firstTransaction->amount;
                //             break;
                //     }
                // }

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                // if ($allTransaction) {
                //     foreach ($allTransaction as $key => $transaction) {
                //         if ($key == 0)
                //             continue;
                //         switch ($transaction->payment_method) {
                //             case 'Cash':
                //                 $previousCashPaid = $previousCashPaid + $transaction->amount;
                //                 break;
                //             case 'Card':
                //                 $previousCardPaid = $previousCardPaid + $transaction->amount;
                //                 break;
                //             case 'Cheque':
                //                 $previousChequePaid = $previousChequePaid + $transaction->amount;
                //                 break;
                //             case 'Paytm':
                //                 $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                //                 break;
                //             case 'UPI':
                //                 $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                //                 break;
                //         }
                //     }
                // }

                $previousDuesPaid = $previousCashPaid + $previousCardPaid + $previousChequePaid + $previousPaytmPaid + $previousUpiPaid;

                $isPreviousDues = false;
                $checkPreviousDues = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if (count($checkPreviousDues) > 1) {
                    $isPreviousDues = true;
                }

                $order['order_name_txt'] = $ordersNameTxt;
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['cash_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cash')->sum('amount');
                $order['card_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Card')->sum('amount');
                $order['cheque_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cheque')->sum('amount');
                $order['paytm_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Paytm')->sum('amount');
                $order['upi_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'UPI')->sum('amount');
                $order['return_amount'] = OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
                $order['is_previous_dues'] = $isPreviousDues;
            }

            $username = User::find($request->input('user'))->first();
            $username = $username->first_name . ' ' . $username->last_name;

            return view(
                'admin.Reports.ShiftCollection.shift_collection_details',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'advanceAmount',
                    'previousCashPaid',
                    'previousCardPaid',
                    'previousChequePaid',
                    'previousPaytmPaid',
                    'previousUpiPaid',
                    'previousDuesPaid',
                    'billCount',
                    'username',
                    'returnAmount'
                )
            );
        } else if ($request->has('shiftCollection')) {
            $orderDetails = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('created_by', $request->input('user'))
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $allTransactionsData = array();

            $finalAmount = 0;
            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            $username = User::find($request->input('user'))->first();
            $username = $username->first_name . ' ' . $username->last_name;

            foreach ($orderDetails as $orderKey => $order) {
                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                $isPreviousDues = false;

                foreach ($allTransaction as $transaction) {
                    if ($transaction->amount != "0") {
                        $allTransactionsData[$transaction->payment_method][] = array(
                            'trans_id' => $transaction->id,
                            'req_no' => $order->bill_no,
                            'patient_name' => $order->patient_title_name . ' ' . $order->patient_name,
                            'user_name' => $username,
                            'return_amount' => '',
                            'txn_id' => $transaction->txn_id,
                            'mode' => $transaction->payment_method,
                            'amount' => $transaction->amount,
                            'is_previous_due' => $isPreviousDues,
                        );

                        switch ($transaction->payment_method) {
                            case 'Cash':
                                $cashPaid = $cashPaid + $transaction->amount;
                                break;
                            case 'Card':
                                $cardPaid = $cardPaid + $transaction->amount;
                                break;
                            case 'Cheque':
                                $chequePaid = $chequePaid + $transaction->amount;
                                break;
                            case 'Paytm':
                                $paytmPaid = $paytmPaid + $transaction->amount;
                                break;
                            case 'UPI':
                                $upiPaid = $upiPaid + $transaction->amount;
                                break;
                        }

                        if (!$isPreviousDues) {
                            $isPreviousDues = true;
                        }
                    }
                }
            }
            $finalAmount = $cashPaid + $cardPaid + $chequePaid + $paytmPaid + $upiPaid;

            return view('admin.Reports.ShiftCollection.shift_collection', compact('user', 'fromDate', 'toDate', 'username', 'cashPaid', 'cardPaid', 'chequePaid', 'paytmPaid', 'upiPaid', 'finalAmount', 'allTransactionsData'));
        } else if ($request->has('summaryReport')) {
            $orderDetails = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('created_by', $request->input('user'))
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $totalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $balanceAmount = 0;
            $dicountAmount = 0;
            $returnAmount = 0;

            $advanceAmount = 0;
            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            $previousCashPaid = 0;
            $previousCardPaid = 0;
            $previousChequePaid = 0;
            $previousPaytmPaid = 0;
            $previousUpiPaid = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalAmount = $totalAmount + $order->total_bill;
                $paidAmount = $paidAmount + $order->paid_amount;
                $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->whereDate('created_at', '=', $this->todayDate)->get();
                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();

                if ($allTransaction) {
                    foreach ($allTransaction as $transaction) {
                        if (str_contains($order->created_at, $this->todayDate)) {
                            $advanceAmount = $advanceAmount + $transaction->amount;

                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $previousCashPaid = $previousCashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $previousCardPaid = $previousCardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $previousChequePaid = $previousChequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                // $firstTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->first();
                // if ($firstTransaction) {
                //     $advanceAmount = $advanceAmount + $firstTransaction->amount;
                //     switch ($firstTransaction->payment_method) {
                //         case 'Cash':
                //             $cashPaid = $cashPaid + $firstTransaction->amount;
                //             break;
                //         case 'Card':
                //             $cardPaid = $cardPaid + $firstTransaction->amount;
                //             break;
                //         case 'Cheque':
                //             $chequePaid = $chequePaid + $firstTransaction->amount;
                //             break;
                //         case 'Paytm':
                //             $paytmPaid = $paytmPaid + $firstTransaction->amount;
                //             break;
                //         case 'UPI':
                //             $upiPaid = $upiPaid + $firstTransaction->amount;
                //             break;
                //     }
                // }

                // $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                // if ($allTransaction) {
                //     foreach ($allTransaction as $key => $transaction) {
                //         if ($key == 0)
                //             continue;
                //         switch ($transaction->payment_method) {
                //             case 'Cash':
                //                 $previousCashPaid = $previousCashPaid + $transaction->amount;
                //                 break;
                //             case 'Card':
                //                 $previousCardPaid = $previousCardPaid + $transaction->amount;
                //                 break;
                //             case 'Cheque':
                //                 $previousChequePaid = $previousChequePaid + $transaction->amount;
                //                 break;
                //             case 'Paytm':
                //                 $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                //                 break;
                //             case 'UPI':
                //                 $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                //                 break;
                //         }
                //     }
                // }

                $order['order_name_txt'] = $ordersNameTxt;
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['cash_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cash')->sum('amount');
                $order['card_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Card')->sum('amount');
                $order['cheque_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cheque')->sum('amount');
                $order['paytm_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Paytm')->sum('amount');
                $order['upi_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'UPI')->sum('amount');
            }

            $username = User::find($request->input('user'))->first();
            $username = $username->first_name . ' ' . $username->last_name;

            return view(
                'admin.Reports.ShiftCollection.summary_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'advanceAmount',
                    'previousCashPaid',
                    'previousCardPaid',
                    'previousChequePaid',
                    'previousPaytmPaid',
                    'previousUpiPaid',
                    'username',
                    'returnAmount'
                )
            );
        }
    }

    public function collectionIndex()
    {
        $user = HomeController::getUserData();

        $locations = LabLocations::get();
        $paymentType = [
            'Cash' => 'Cash',
            'Card' => 'Card',
            'Cheque' => 'Cheque',
            'Paytm' => 'Paytm',
            'UPI' => 'UPI'
        ];
        $users = User::get();
        $orderTypes = OrderType::get();
        $referralCompany = ReferralCompany::get();
        $doctors = Doctors::get();

        return view('admin.Reports.Collection.index', compact('user', 'locations', 'users', 'paymentType', 'orderTypes', 'doctors', 'referralCompany'));
    }

    public function collectionIndexData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        if ($request->has('collectionWithServices')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->user !== null && $request->user !== '', function ($query) use ($request) {
                    return $query->where('order_entry.created_by', '=', $request->user);
                })
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

            $totalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $balanceAmount = 0;
            $dicountAmount = 0;
            $returnAmount = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalAmount = $totalAmount + $order->total_bill;
                $paidAmount = $paidAmount + $order->paid_amount;
                $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        switch ($transaction->payment_method) {
                            case 'Cash':
                                $cashPaid = $cashPaid + $transaction->amount;
                                break;
                            case 'Card':
                                $cardPaid = $cardPaid + $transaction->amount;
                                break;
                            case 'Cheque':
                                $chequePaid = $chequePaid + $transaction->amount;
                                break;
                            case 'Paytm':
                                $paytmPaid = $paytmPaid + $transaction->amount;
                                break;
                            case 'UPI':
                                $upiPaid = $upiPaid + $transaction->amount;
                                break;
                        }
                    }
                }

                $order['order_name_txt'] = $ordersNameTxt;
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['cash_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cash')->sum('amount');
                $order['card_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Card')->sum('amount');
                $order['cheque_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cheque')->sum('amount');
                $order['paytm_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Paytm')->sum('amount');
                $order['upi_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'UPI')->sum('amount');
                $order['return_amount'] = OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
            }

            return view(
                'admin.Reports.Collection.collection_with_services',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'returnAmount'
                )
            );
        } else if ($request->has('collectionPerUser')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $billCount = 0;
            $grossAmount = 0;
            $paidAmount = 0;
            $discount = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;
            $previousDues = 0;

            $returnAmount = 0;
            $balanceAmount = 0;
            $cancelledBills = 0;

            $allData = array();
            $lastUser = "";

            foreach ($orderDetails as $orderKey => $order) {
                if (empty($lastUser))
                    $lastUser = $order->created_by;
                $user = User::where('id', $lastUser)->first();
                ($user) ? $username = $user->first_name . ' ' . $user->last_name : $username = "";

                if ($lastUser != $order->created_by) {
                    $allData[$lastUser][] = array(
                        'user_id' => $lastUser,
                        'username' => $username,
                        'bill_count' => $billCount,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'return_amount' => $returnAmount,
                        'balance_amount' => $balanceAmount,
                        'cancelled_bills' => $cancelledBills
                    );

                    $billCount = $grossAmount = $paidAmount = $discount = 0;
                    $cashPaid = $cardPaid = $chequePaid = $paytmPaid = $upiPaid = $previousDues = 0;
                    $returnAmount = $balanceAmount = $cancelledBills = 0;

                    $lastUser = $order->created_by;
                }

                $billCount++;
                $grossAmount += $order->total_bill;
                $paidAmount += $order->paid_amount;
                $discount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount += OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
                $balanceAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $cancelledBills += OrderEntry::where('created_by', $lastUser)->where('bill_no', $order->bill_no)->where('status', 'cancelled')->count();

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        if ($key != 0) {
                            $previousDues = $previousDues + $transaction->amount;
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                if ($orderKey == count($orderDetails) - 1) {
                    $allData[$order->created_by][] = array(
                        'user_id' => $order->created_by,
                        'username' => $username,
                        'bill_count' => $billCount,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'return_amount' => $returnAmount,
                        'balance_amount' => $balanceAmount,
                        'cancelled_bills' => $cancelledBills
                    );
                }
            }

            return view('admin.Reports.Collection.collection_per_user', compact('user', 'fromDate', 'toDate', 'allData'));
        } else if ($request->has('collectionPerDay')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->when($request->reffCompany !== null && $request->reffCompany !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->reffCompany);
                })
                ->when($request->reffDoctor !== null && $request->reffDoctor !== '', function ($query) use ($request) {
                    return $query->where('order_entry.doctor', '=', $request->reffDoctor);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $grossAmount = 0;
            $paidAmount = 0;
            $discount = 0;
            $balance = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;
            $previousDues = 0;

            $returnAmount = 0;
            $cancelledBills = 0;

            $allData = array();
            $lastDate = "";

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $showTheOrder = false;

                foreach ($orderIdsArray as $key => $id) {
                    $data = AddReport::find($id);
                    if ($data) {
                        $orderTypes = $request->input('orderType');
                        if ($orderTypes !== null && !empty($orderTypes)) {
                            if ($request->input('orderType')[0] == NULL) {
                                $showTheOrder = true;
                            } else {
                                if (in_array($data->order_order_type, $orderTypes)) {
                                    $showTheOrder = true;
                                }
                            }
                        } else {
                            $showTheOrder = true;
                        }
                    }
                }

                if (!$showTheOrder)
                    continue;

                if (empty($lastDate))
                    $lastDate = Str::substr($order->created_at, 0, 10);
                if ($lastDate != Str::substr($order->created_at, 0, 10)) {
                    $allData[$lastDate][] = array(
                        'date' => $lastDate,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'balance' => $balance,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'return_amount' => $returnAmount,
                        'cancelled_bills' => $cancelledBills
                    );

                    $balance = $grossAmount = $paidAmount = $discount = 0;
                    $cashPaid = $cardPaid = $chequePaid = $paytmPaid = $upiPaid = $previousDues = 0;
                    $returnAmount = $cancelledBills = 0;

                    $lastDate = Str::substr($order->created_at, 0, 10);
                }

                $grossAmount += $order->total_bill;
                $paidAmount += $order->paid_amount;
                $discount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balance += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount += OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
                $cancelledBills += OrderEntry::whereDate('created_at', $lastDate)->where('bill_no', $order->bill_no)->where('status', 'cancelled')->count();

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        if ($key != 0) {
                            $previousDues = $previousDues + $transaction->amount;
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                if ($orderKey == count($orderDetails) - 1) {
                    $allData[$lastDate][] = array(
                        'date' => $lastDate,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'balance' => $balance,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'return_amount' => $returnAmount,
                        'cancelled_bills' => $cancelledBills
                    );
                    $lastDate = "";
                }
            }

            if (!empty($lastDate)) {
                $allData[$lastDate][] = array(
                    'date' => $lastDate,
                    'gross_amount' => $grossAmount,
                    'paid_amount' => $paidAmount,
                    'discount' => $discount,
                    'balance' => $balance,
                    'cash_paid' => $cashPaid,
                    'card_paid' => $cardPaid,
                    'cheque_paid' => $chequePaid,
                    'paytm_paid' => $paytmPaid,
                    'upi_paid' => $upiPaid,
                    'previous_dues' => $previousDues,
                    'return_amount' => $returnAmount,
                    'cancelled_bills' => $cancelledBills
                );
                $lastDate = "";
            }

            return view('admin.Reports.Collection.collection_per_day', compact('user', 'fromDate', 'toDate', 'allData'));
        } else if ($request->has('collectionPerMonth')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $grossAmount = 0;
            $finalAmount = 0;
            $paidAmount = 0;
            $discount = 0;
            $balance = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;
            $previousDues = 0;

            $returnAmount = 0;

            $allData = array();
            $lastDate = "";

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $showTheOrder = false;

                foreach ($orderIdsArray as $key => $id) {
                    $data = AddReport::find($id);

                    if ($data) {
                        $orderTypes = $request->input('orderType');

                        if ($orderTypes !== null && !empty($orderTypes)) {
                            if ($request->input('orderType')[0] == NULL) {
                                $showTheOrder = true;
                            } else {
                                if (in_array($data->order_order_type, $orderTypes)) {
                                    $showTheOrder = true;
                                }
                            }
                        } else {
                            $showTheOrder = true;
                        }
                    }
                }

                if (!$showTheOrder)
                    continue;

                $date = Carbon::parse($order->created_at);
                $monthName = $date->format('F Y');

                if (empty($lastDate))
                    $lastDate = $monthName;
                if ($lastDate != $monthName) {
                    $allData[$lastDate][] = array(
                        'date' => $lastDate,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'balance' => $balance,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'final_amount' => $finalAmount,
                        'return_amount' => $returnAmount
                    );

                    $finalAmount = $balance = $grossAmount = $paidAmount = $discount = 0;
                    $cashPaid = $cardPaid = $chequePaid = $paytmPaid = $upiPaid = $previousDues = 0;
                    $returnAmount = 0;

                    $lastDate = $monthName;
                }

                $grossAmount += $order->total_bill;
                $paidAmount += $order->paid_amount;
                $discount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balance += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $finalAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount += OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        if ($key != 0) {
                            $previousDues = $previousDues + $transaction->amount;
                        } else {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                if ($orderKey == count($orderDetails) - 1) {
                    $allData[$lastDate][] = array(
                        'date' => $lastDate,
                        'gross_amount' => $grossAmount,
                        'paid_amount' => $paidAmount,
                        'discount' => $discount,
                        'balance' => $balance,
                        'cash_paid' => $cashPaid,
                        'card_paid' => $cardPaid,
                        'cheque_paid' => $chequePaid,
                        'paytm_paid' => $paytmPaid,
                        'upi_paid' => $upiPaid,
                        'previous_dues' => $previousDues,
                        'final_amount' => $finalAmount,
                        'return_amount' => $returnAmount
                    );
                    $lastDate = "";
                }
            }

            if (!empty($lastDate)) {
                $allData[$lastDate][] = array(
                    'date' => $lastDate,
                    'gross_amount' => $grossAmount,
                    'paid_amount' => $paidAmount,
                    'discount' => $discount,
                    'balance' => $balance,
                    'cash_paid' => $cashPaid,
                    'card_paid' => $cardPaid,
                    'cheque_paid' => $chequePaid,
                    'paytm_paid' => $paytmPaid,
                    'upi_paid' => $upiPaid,
                    'previous_dues' => $previousDues,
                    'return_amount' => $returnAmount
                );
                $lastDate = "";
            }

            return view('admin.Reports.Collection.collection_per_month', compact('user', 'fromDate', 'toDate', 'allData'));
        } else if ($request->has('monthlyShifCollection')) {
            $orderDetails = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->when($request->has('user') && !empty($request->user), function ($query) use ($request) {
                    return $query->where('created_by', $request->user);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $allTransactionsData = array();

            $finalAmount = 0;
            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                $isPreviousDues = false;

                $username = User::where('id', $order->created_by)->first();
                $username = ($username) ? $username->first_name . ' ' . $username->last_name : "";

                foreach ($allTransaction as $transaction) {
                    if ($transaction->amount != "0") {
                        $allTransactionsData[$transaction->payment_method][] = array(
                            'trans_id' => $transaction->id,
                            'req_no' => $order->bill_no,
                            'patient_name' => $order->patient_title_name . ' ' . $order->patient_name,
                            'user_name' => $username,
                            'return_amount' => '',
                            'txn_id' => $transaction->txn_id,
                            'mode' => $transaction->payment_method,
                            'amount' => $transaction->amount,
                            'is_previous_due' => $isPreviousDues,
                        );

                        switch ($transaction->payment_method) {
                            case 'Cash':
                                $cashPaid = $cashPaid + $transaction->amount;
                                break;
                            case 'Card':
                                $cardPaid = $cardPaid + $transaction->amount;
                                break;
                            case 'Cheque':
                                $chequePaid = $chequePaid + $transaction->amount;
                                break;
                            case 'Paytm':
                                $paytmPaid = $paytmPaid + $transaction->amount;
                                break;
                            case 'UPI':
                                $upiPaid = $upiPaid + $transaction->amount;
                                break;
                        }

                        if (!$isPreviousDues) {
                            $isPreviousDues = true;
                        }
                    }
                }
            }
            $finalAmount = $cashPaid + $cardPaid + $chequePaid + $paytmPaid + $upiPaid;

            if ($request->has('user') && !empty($request->user)) {
                $username = User::find($request->input('user'))->first();
                $username = ($username) ? $username->first_name . ' ' . $username->last_name : "All Users";
            } else {
                $username = "All Users";
            }

            return view('admin.Reports.ShiftCollection.shift_collection', compact('user', 'fromDate', 'toDate', 'username', 'cashPaid', 'cardPaid', 'chequePaid', 'paytmPaid', 'upiPaid', 'finalAmount', 'allTransactionsData'));
        } else if ($request->has('getDueBills')) {
            $orderDetails = OrderEntry::whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->has('location') && !empty($request->location), function ($query) use ($request) {
                    return $query->where('referred_by_id', $request->location);
                })
                ->when($request->paymentType !== null && $request->paymentType !== '', function ($query) use ($request) {
                    return $query->where(function ($subQuery) use ($request) {
                        $subQuery->whereExists(function ($existsQuery) use ($request) {
                            $existsQuery->select(DB::raw(1))
                                ->from('order_entry_transactions')
                                ->whereColumn('order_entry.bill_no', '=', 'order_entry_transactions.bill_no')
                                ->groupBy('order_entry_transactions.bill_no')
                                ->havingRaw('COUNT(DISTINCT order_entry_transactions.payment_method) = 1')
                                ->where('order_entry_transactions.payment_method', '=', $request->paymentType);
                        });
                    });
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            // still something wrong

            $totalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $balanceAmount = 0;
            $dicountAmount = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            foreach ($orderDetails as $orderKey => $order) {
                if (OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage) != 0) {
                    $totalAmount = $totalAmount + $order->total_bill;
                    $paidAmount = $paidAmount + $order->paid_amount;
                    $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                    $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                    $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);

                    $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                    if ($allTransaction) {
                        foreach ($allTransaction as $key => $transaction) {
                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $cashPaid = $cashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $cardPaid = $cardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $chequePaid = $chequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $paytmPaid = $paytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $upiPaid = $upiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }

                    $payMode = $allTransaction[0]->payment_method;

                    $order['net_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                    $order['balance_amount'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                    $order['discount_amount'] = OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                    $order['pay_mode'] = $payMode;
                } else {
                    unset($orderDetails[$orderKey]);
                }
            }

            return view(
                'admin.Reports.Collection.due_collections',
                compact(
                    'user',
                    'fromDate',
                    'toDate',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'orderDetails'
                )
            );
        } else if ($request->has('userWiseBillReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->user !== null && $request->user !== '', function ($query) use ($request) {
                    return $query->where('order_entry.created_by', '=', $request->user);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->get();

            $grossAmount = 0;
            $finalAmount = 0;
            $paidAmount = 0;
            $discount = 0;
            $balance = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            $returnAmount = 0;

            $allData = array();
            $lastUser = "";

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $localCashPaid = 0;
                $localCardPaid = 0;
                $localChequePaid = 0;
                $localPaytmPaid = 0;
                $localUpiPaid = 0;

                foreach ($orderIdsArray as $key => $id) {
                    $data = AddReport::find($id);
                    if ($data) {
                        $orderTypes = $request->input('orderType');
                        if ($orderTypes !== null && !empty($orderTypes)) {
                            if ($request->input('orderType')[0] == NULL) {
                                $showTheOrder = true;
                            } else {
                                if (in_array($data->order_order_type, $orderTypes)) {
                                    $showTheOrder = true;
                                }
                            }
                        } else {
                            $showTheOrder = true;
                        }

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = $data->order_name;
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                        }
                    }
                }

                if (!$showTheOrder)
                    continue;

                $userData = User::where('id', $order->created_by)->first();
                $username = ($userData) ? $userData->first_name . ' ' . $userData->last_name : "";
                $lastUser = $order->created_by . ' | ' . $username;

                $grossAmount += $order->total_bill;
                $paidAmount += $order->paid_amount;
                $discount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balance += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $finalAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount += OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();
                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        switch ($transaction->payment_method) {
                            case 'Cash':
                                $localCashPaid += $transaction->amount;
                                $cashPaid = $cashPaid + $transaction->amount;
                                break;
                            case 'Card':
                                $localCardPaid += $transaction->amount;
                                $cardPaid = $cardPaid + $transaction->amount;
                                break;
                            case 'Cheque':
                                $localChequePaid += $transaction->amount;
                                $chequePaid = $chequePaid + $transaction->amount;
                                break;
                            case 'Paytm':
                                $localPaytmPaid += $transaction->amount;
                                $paytmPaid = $paytmPaid + $transaction->amount;
                                break;
                            case 'UPI':
                                $localUpiPaid += $transaction->amount;
                                $upiPaid = $upiPaid + $transaction->amount;
                                break;
                        }
                    }
                }

                $order['dst'] = OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['cash'] = $localCashPaid;
                $order['card'] = $localCardPaid;
                $order['cheque'] = $localChequePaid;
                $order['paytm'] = $localPaytmPaid;
                $order['upi'] = $localUpiPaid;
                $order['return_amount'] = OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
                $order['ordersNameTxt'] = $ordersNameTxt;
                $order['bal'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);

                $allData[$lastUser][] = $order;
            }

            if ($request->has('user') && !empty($request->user)) {
                $username = User::find($request->input('user'))->first();
                $username = ($username) ? $username->first_name . ' ' . $username->last_name : "All Users";
            } else {
                $username = "All Users";
            }

            return view(
                'admin.Reports.Collection.user_wise_bills',
                compact(
                    'user',
                    'fromDate',
                    'toDate',
                    'allData',
                    'username',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'grossAmount',
                    'finalAmount',
                    'paidAmount',
                    'discount',
                    'balance',
                    'returnAmount'
                )
            );
        } else if ($request->has('collectionWithCancelledDates')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->when($request->user !== null && $request->user !== '', function ($query) use ($request) {
                    return $query->where('order_entry.created_by', '=', $request->user);
                })
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

            $totalAmount = 0;
            $paidAmount = 0;
            $finalAmount = 0;
            $balanceAmount = 0;
            $dicountAmount = 0;
            $returnAmount = 0;
            $cancelledAmount = 0;
            $prevAmountReceived = 0;

            $cashPaid = 0;
            $cardPaid = 0;
            $chequePaid = 0;
            $paytmPaid = 0;
            $upiPaid = 0;

            $previousCashPaid = 0;
            $previousCardPaid = 0;
            $previousChequePaid = 0;
            $previousPaytmPaid = 0;
            $previousUpiPaid = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalAmount = $totalAmount + $order->total_bill;
                $paidAmount = $paidAmount + $order->paid_amount;
                $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

                if ($order->status == "cancelled") {
                    $cancelledAmount += OrderEntryTransactions::where('bill_no', $order->bill_no)->sum('amount');
                }

                $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)->get();

                if ($allTransaction) {
                    foreach ($allTransaction as $key => $transaction) {
                        switch ($transaction->payment_method) {
                            case 'Cash':
                                $cashPaid = $cashPaid + $transaction->amount;
                                break;
                            case 'Card':
                                $cardPaid = $cardPaid + $transaction->amount;
                                break;
                            case 'Cheque':
                                $chequePaid = $chequePaid + $transaction->amount;
                                break;
                            case 'Paytm':
                                $paytmPaid = $paytmPaid + $transaction->amount;
                                break;
                            case 'UPI':
                                $upiPaid = $upiPaid + $transaction->amount;
                                break;
                        }

                        if (!str_contains($order->created_at, $this->todayDate) && $key > 0) {
                            $order['is_previous'] = true;

                            switch ($transaction->payment_method) {
                                case 'Cash':
                                    $previousCashPaid = $previousCashPaid + $transaction->amount;
                                    break;
                                case 'Card':
                                    $previousCardPaid = $previousCardPaid + $transaction->amount;
                                    break;
                                case 'Cheque':
                                    $previousChequePaid = $previousChequePaid + $transaction->amount;
                                    break;
                                case 'Paytm':
                                    $previousPaytmPaid = $previousPaytmPaid + $transaction->amount;
                                    break;
                                case 'UPI':
                                    $previousUpiPaid = $previousUpiPaid + $transaction->amount;
                                    break;
                            }
                        }
                    }
                }

                $order['order_name_txt'] = $ordersNameTxt;
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['discount_amount'] = $order->total_bill - OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['cash_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cash')->sum('amount');
                $order['card_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Card')->sum('amount');
                $order['cheque_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Cheque')->sum('amount');
                $order['paytm_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'Paytm')->sum('amount');
                $order['upi_paid'] = OrderEntryTransactions::where('bill_no', $order->bill_no)->where('payment_method', 'UPI')->sum('amount');
                $order['return_amount'] = OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');
            }

            $prevAmountReceived = $previousCashPaid + $previousCardPaid + $previousChequePaid + $previousPaytmPaid + $previousUpiPaid;

            return view(
                'admin.Reports.Collection.collection_with_cancelled_dates',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'totalAmount',
                    'paidAmount',
                    'finalAmount',
                    'balanceAmount',
                    'dicountAmount',
                    'cashPaid',
                    'cardPaid',
                    'chequePaid',
                    'paytmPaid',
                    'upiPaid',
                    'returnAmount',
                    'cancelledAmount',
                    'prevAmountReceived',
                    'previousCashPaid',
                    'previousCardPaid',
                    'previousChequePaid',
                    'previousPaytmPaid',
                    'previousUpiPaid'
                )
            );
        }
    }

    public function doctorReportsIndex()
    {
        $user = HomeController::getUserData();

        $locations = LabLocations::get();
        $doctors = Doctors::get();

        return view('admin.Reports.DoctorReports.index', compact('user', 'locations', 'doctors'));
    }

    public function doctorReportsData(Request $request)
    {
        $user = HomeController::getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        if ($request->has('getReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $amounToSales = 0;
            $finalAmount = 0;
            $dueAmount = 0;
            $amountToLab = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalPayableAmount = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $toDoctor = 0;
                $toLab = 0;

                if ($order->doc_percentage != 0) {
                    $toDoctor = $totalPayableAmount * ($order->doc_percentage / 100);
                    $toLab = $totalPayableAmount - $toDoctor;
                } else {
                    $toLab = $totalPayableAmount;
                }

                $finalAmount += $totalPayableAmount;
                $amounToSales += $toDoctor;
                $dueAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $amountToLab += $toLab;

                $order['order_name_txt'] = $ordersNameTxt;
                $order['due_amount'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['amount_to_sales'] = $toDoctor;
                $order['amount_to_lab'] = $toLab;
            }

            return view(
                'admin.Reports.DoctorReports.get_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'amounToSales',
                    'finalAmount',
                    'dueAmount',
                    'amountToLab'
                )
            );
        } else if ($request->has('getDetailReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('doctor', '!=', '')
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $amounToSales = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalPayableAmount = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $toDoctor = 0;

                if ($order->doc_percentage != 0) {
                    $toDoctor = $totalPayableAmount * ($order->doc_percentage / 100);
                }

                $amounToSales += $toDoctor;

                $order['order_name_txt'] = $ordersNameTxt;
                $order['due_amount'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['amount_to_sales'] = $toDoctor;
            }

            return view('admin.Reports.DoctorReports.get_detail_report', compact('user', 'orderDetails', 'fromDate', 'toDate', 'amounToSales'));
        } else if ($request->has('salesOnPaidReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('doctor', '!=', '')
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $finalAmount = 0;
            $amounToSales = 0;
            $amountToLab = 0;
            $totalDiscount = 0;
            $totalPaidAmount = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalPayableAmount = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $toDoctor = 0;
                $toLab = 0;

                if ($order->doc_percentage != 0) {
                    $toDoctor = $totalPayableAmount * ($order->doc_percentage / 100);
                    $toLab = $totalPayableAmount - $toDoctor;
                } else {
                    $toLab = $totalPayableAmount;
                }

                $finalAmount += $totalPayableAmount;
                $amounToSales += $toDoctor;
                $amountToLab += $toLab;
                $totalDiscount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $totalPaidAmount += $order->paid_amount;

                $order['order_name_txt'] = $ordersNameTxt;
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['discount'] = OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['amount_to_sales'] = $toDoctor;
                $order['amount_to_lab'] = $toLab;
            }

            return view(
                'admin.Reports.DoctorReports.sales_on_paid_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'amounToSales',
                    'finalAmount',
                    'totalDiscount',
                    'amountToLab',
                    'totalPaidAmount'
                )
            );
        } else if ($request->has('billByDoctorReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('doctor', '!=', '')
                ->where('status', '!=', 'cancelled')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $allData = array();
            $totalBilled = 0;
            $totalDiscount = 0;
            $totalNetAmount = 0;
            $totalPaidAmount = 0;
            $totalDueAmount = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalBilled += $order->total_bill;
                $totalDiscount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $totalNetAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $totalPaidAmount += $order->paid_amount;
                $totalDueAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);

                $order['order_name_txt'] = $ordersNameTxt;
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['discount'] = OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);

                $allData[$order->doctor . ' | ' . $order->doc_name][] = $order;
            }

            return view('admin.Reports.DoctorReports.bills_by_doctor_report', compact('user', 'allData', 'fromDate', 'toDate', 'totalBilled', 'totalDiscount', 'totalNetAmount', 'totalPaidAmount', 'totalDueAmount'));
        } else if ($request->has('nonFinancialBillsByDoctor')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $allData = array();

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $order['order_name_txt'] = $ordersNameTxt;
                $allData[$order->doctor . ' | ' . $order->doc_name][] = $order;
            }

            return view('admin.Reports.DoctorReports.non_financial_bills_by_doctor_report', compact('user', 'allData', 'fromDate', 'toDate'));
        } else if ($request->has('patientSourceReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $totalBilled = 0;
            $totalDiscount = 0;
            $totalNetAmount = 0;
            $totalPaidAmount = 0;
            $totalDueAmount = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalBilled += $order->total_bill;
                $totalDiscount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $totalNetAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $totalPaidAmount += $order->paid_amount;
                $totalDueAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);

                $order['order_name_txt'] = $ordersNameTxt;
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['discount'] = OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['balance'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
            }

            return view('admin.Reports.DoctorReports.patients_source_report', compact('user', 'orderDetails', 'fromDate', 'toDate', 'totalBilled', 'totalDiscount', 'totalNetAmount', 'totalPaidAmount', 'totalDueAmount'));
        } else if ($request->has('doctorReportWithoutPaidAndDue')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $amounToSales = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($labProfile) ? $labProfile->name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($labProfile) ? $labProfile->name : "Not available");
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($ordersNameTxt == "") {
                            $ordersNameTxt = ($data) ? $data->order_name : "Not available";
                        } else {
                            $ordersNameTxt = $ordersNameTxt . ", " . (($data) ? $data->order_name : "Not available");
                        }
                    }
                }

                $totalPayableAmount = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $toDoctor = 0;

                if ($order->doc_percentage != 0) {
                    $toDoctor = $totalPayableAmount * ($order->doc_percentage / 100);
                }

                $amounToSales += $toDoctor;

                $order['order_name_txt'] = $ordersNameTxt;
                $order['amount_to_sales'] = $toDoctor;
            }

            return view('admin.Reports.DoctorReports.doctor_report_without_paid_and_due', compact('user', 'orderDetails', 'fromDate', 'toDate', 'amounToSales'));
        } else if ($request->has('referralDoctorBills')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('status', '!=', 'cancelled')
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $orderAmount = 0;

            $allData = array();

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $orderAmountArray = explode(',', $order->order_amount);
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

                foreach ($orderIdsArray as $key => $id) {
                    if (str_contains($id, $this->orderTypeProfile)) {
                        $labProfile = LabProfiles::where('id', str_replace($this->orderTypeProfile, "", $id))->first();

                        if ($labProfile) {
                            $orderData = $order;
                            $orderData->order_name_txt = $labProfile->name;
                            $orderData->order_amount = $orderAmountArray[$key];
                            $orderData->order_test_code = '--';

                            $orderAmount += $orderAmountArray[$key];

                            $allData[] = clone $orderData;
                        }
                    } else {
                        $data = AddReport::find($id);

                        if ($data) {
                            $orderData = $order;
                            $orderData->order_name_txt = $data->order_name;
                            $orderData->order_amount = $orderAmountArray[$key];
                            $orderData->order_test_code = (empty($data->order_test_code)) ? '--' : $data->order_test_code;

                            $orderAmount += $orderAmountArray[$key];

                            $allData[] = clone $orderData;
                        }
                    }
                }
            }

            return view('admin.Reports.DoctorReports.referral_doctor_bills', compact('user', 'allData', 'fromDate', 'toDate', 'orderAmount'));
        } else if ($request->has('doctorDueReport')) {
            $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
                ->whereDate('order_entry.created_at', '<=', $toDate)
                ->where('doctor', '!=', '')
                ->when($request->location !== null && $request->location !== '', function ($query) use ($request) {
                    return $query->where('order_entry.referred_by_id', '=', $request->location);
                })
                ->select('*')
                ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
                ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
                ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
                ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
                ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
                ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
                ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
                ->selectRaw('(SELECT doctors.doc_percentage FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_percentage')
                ->get();

            $amounToSales = 0;
            $finalAmount = 0;
            $dueAmount = 0;
            $amountToLab = 0;

            foreach ($orderDetails as $orderKey => $order) {
                $orderIdsArray = explode(',', $order->order_ids);
                $ordersNameTxt = "";
                $showTheOrder = false;

                $doctors = $request->input('doctor');
                if ($doctors !== null && !empty($doctors)) {
                    if ($request->input('doctor')[0] == NULL) {
                        $showTheOrder = true;
                    } else {
                        if (in_array($order->doctor, $doctors)) {
                            $showTheOrder = true;
                        }
                    }
                } else {
                    $showTheOrder = true;
                }

                if (!$showTheOrder || OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage) == 0) {
                    unset($orderDetails[$orderKey]);
                    continue;
                }

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

                $totalPayableAmount = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $toDoctor = 0;
                $toLab = 0;

                if ($order->doc_percentage != 0) {
                    $toDoctor = $totalPayableAmount * ($order->doc_percentage / 100);
                    $toLab = $totalPayableAmount - $toDoctor;
                } else {
                    $toLab = $totalPayableAmount;
                }

                $finalAmount += $totalPayableAmount;
                $amounToSales += $toDoctor;
                $dueAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $amountToLab += $toLab;

                $order['order_name_txt'] = $ordersNameTxt;
                $order['due_amount'] = OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
                $order['final_amount'] = OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                $order['amount_to_sales'] = $toDoctor;
                $order['amount_to_lab'] = $toLab;
            }

            return view(
                'admin.Reports.DoctorReports.get_report',
                compact(
                    'user',
                    'orderDetails',
                    'fromDate',
                    'toDate',
                    'amounToSales',
                    'finalAmount',
                    'dueAmount',
                    'amountToLab'
                )
            );
        }
    }
}
