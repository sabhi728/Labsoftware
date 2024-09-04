<?php

namespace App\Http\Controllers\ReferralControllers;

use App\Http\Controllers\AdminControllers\OrderBillsController;
use App\Http\Controllers\AdminControllers\OrderEntryController;
use App\Models\OrderEntry;
use App\Models\ResultReports;
use App\Models\SystemSettings;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use stdClass;

class HomeController extends CommonController
{
    public function index(Request $request)
    {
        $user = $this->getUserData();

        if ($user->show_dashboard == "false") {
            return redirect('referralpanel/orderentry/index');
        }

        $fromDate = ($request->has('fromDate')) ? $request->query('fromDate') : now()->toDateString();
        $toDate = ($request->has('toDate')) ? $request->query('toDate') : now()->toDateString();

        $orderDetails = OrderEntry::where('status', '!=', 'cancelled')
            ->where('referred_by_id', $user->id)
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get();

        $deductionPercentage = $user->discount;
        $totalAmount = 0;
        $paidAmount = 0;
        $balanceAmount = 0;

        foreach ($orderDetails as $orderKey => $order) {
            $totalAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
            $paidAmount += $order->paid_amount;
            $balanceAmount += OrderEntryController::getLeftBalance($order->total_bill, $order->paid_amount, $order->overall_dis, $order->is_dis_percentage);
        }

        $totalAmount = round($totalAmount - ($totalAmount * $deductionPercentage / 100));
        $paidAmount = round($paidAmount - ($paidAmount * $deductionPercentage / 100));
        $balanceAmount = round($balanceAmount - ($balanceAmount * $deductionPercentage / 100));

        return view(
            'referral.dashboard',
            compact(
                'user',
                'totalAmount',
                'paidAmount',
                'balanceAmount',
                'fromDate',
                'toDate',
            )
        );
    }

    public function indexContactUs()
    {
        $user = $this->getUserData();

        return view('referral.contact', compact('user'));
    }

    public function userViewResult($billNo, $orderNo, $withHeader = false)
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
            $orderBillsController = new OrderBillsController();
            $orderDetails = $orderBillsController->getResultData($billNo, $orderNo);

            $qrCodeUrl = url('/') . '/viewbill/' . $orderDetails->bill_no . '?orders=' . $orderNo;

            // $updateResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
            // $updateResultReport->is_referral_printed = "true";
            // $updateResultReport->save();

            $html = view('admin.OrderBills.user_result_preview', compact('user', 'orderDetails', 'orderNo', 'withHeader', 'qrCodeUrl'))->render();

            $pdf = SnappyPdf::loadHTML($html)
                ->setOption('zoom', 1.25)
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOption('margin-top', '0mm')
                ->setOption('margin-bottom', '0mm')
                ->setOption('margin-left', '0mm')
                ->setOption('margin-right', '0mm')
                ->setOption('dpi', 200);

            return $pdf->stream('bill_' . $billNo . '.pdf');
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function markResultAsPrinted($billNo, $orderNo)
    {
        $orderDetails = OrderEntry::select('*')
            ->where('bill_no', $billNo)
            ->first();

        $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

        if ($leftBalance != "0") {
            return response()->json(["error" => "balance not clear"]);
        }

        $updateResultReport = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();
        $updateResultReport->is_referral_printed = "true";
        $updateResultReport->save();

        return response()->json(["success" => "success"]);
    }
}
