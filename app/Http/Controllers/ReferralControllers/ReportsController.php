<?php

namespace App\Http\Controllers\ReferralControllers;

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

    public function billReportsIndex()
    {
        $user = $this->getUserData();

        if ($user->show_bill_reports == "false") {
            return redirect('referralpanel/orderentry/index');
        }

        $fromDate = Carbon::now()->toDateString();
        $toDate = Carbon::now()->toDateString();

        return view('referral.Reports.BillReports.index', compact('user', 'fromDate', 'toDate'));
    }

    public function billReportsIndexData(Request $request)
    {
        $user = $this->getUserData();

        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $referredById = $user->id;

        $orderDetails = OrderEntry::whereDate('order_entry.created_at', '>=', $fromDate)
            ->whereDate('order_entry.created_at', '<=', $toDate)
            ->where('order_entry.status', '!=', 'cancelled')
            ->where('order_entry.referred_by_id', $referredById)
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
                    if ($ordersNameTxt == "") {
                        $ordersNameTxt = $data->order_name;
                    } else {
                        $ordersNameTxt = $ordersNameTxt . ", " . $data->order_name;
                    }
                }

                $index++;
            }

            $totalAmount = $totalAmount + $order->total_bill;
            $paidAmount = $paidAmount + $order->paid_amount;
            $finalAmount = $finalAmount + OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
            $balanceAmount = $balanceAmount + OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
            $dicountAmount = $dicountAmount + OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
            $returnAmount = $returnAmount + OrderReturnAmount::where('bill_no', $order->bill_no)->sum('amount');

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

        return view('referral.Reports.BillReports.get_report', compact('user', 'orderDetails', 'fromDate', 'toDate', 'totalAmount', 'paidAmount', 'finalAmount', 'balanceAmount', 'dicountAmount', 'returnAmount'));
    }
}
