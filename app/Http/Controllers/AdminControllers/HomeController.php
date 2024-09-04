<?php

namespace App\Http\Controllers\AdminControllers;

use App\Models\AddReport;
use App\Models\LabLocations;
use App\Models\LabProfileDetails;
use App\Models\LoginHistory;
use App\Models\OrderEntryTransactions;
use App\Models\OrderReturnAmount;
use App\Models\Patients;
use App\Models\ResultReports;
use App\Models\SystemSettings;
use App\Models\OrderEntry;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class HomeController extends CommonController
{
    public function index()
    {
        $user = $this->getUserData();
        return view('admin.home', compact('user'));
    }

    public function indexDashbaord(Request $request)
    {
        $user = $this->getUserData();
        $fromDate = ($request->has('fromDate')) ? $request->query('fromDate') : now()->toDateString();
        $toDate = ($request->has('toDate')) ? $request->query('toDate') : now()->toDateString();

        $noLoggedUsers = LoginHistory::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)->count();
        $noOfBills = OrderEntry::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)->count();
        $totalBilledAmount = OrderEntry::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)->sum('total_bill');
        $totalPaidAmount = OrderEntry::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)->sum('paid_amount');

        $totalDiscountAmount = 0;
        $allOrderIds = array();
        $allOrderAmount = array();

        $getTodayOrders = OrderEntry::whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate)
            ->get();

        foreach ($getTodayOrders as $order) {
            $orderIdsArray = explode(',', $order->order_ids);
            $orderAmountArray = explode(',', $order->order_amount);

            foreach ($orderIdsArray as $key => $id) {
                if (array_search($id, $allOrderIds, true) === false) {
                    $allOrderIds[] = $id;
                    $allOrderAmount[] = $orderAmountArray[$key];
                } else {
                    $index = array_search($id, $allOrderIds);
                    $allOrderAmount[$index] = $allOrderAmount[$index] + $orderAmountArray[$key];
                }
            }

            $totalDiscountAmount += OrderEntryController::getDiscountAmount($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
        }

        $totalPendingBalance = ($totalBilledAmount - $totalDiscountAmount) - $totalPaidAmount;
        $totalConsultationsBalance = 0;

        $upiPaidAmount = 0;
        $cashPaidAmount = 0;
        $cardPaidAmount = 0;
        $previousPaidAmount = 0;
        $previousPaidAmount = 0;
        $cancelRefundAmount = 0;

        $orderDetails = OrderEntry::get();
        foreach ($orderDetails as $orderKey => $order) {
            $allTransaction = OrderEntryTransactions::where('bill_no', $order->bill_no)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->get();

            $orderDate = $order->created_at->format('Y-m-d');

            if ($allTransaction) {
                foreach ($allTransaction as $transaction) {
                    if ($orderDate >= $fromDate && $orderDate <= $toDate) {
                        switch ($transaction->payment_method) {
                            case 'UPI':
                                $upiPaidAmount = $upiPaidAmount + $transaction->amount;
                                break;
                            case 'Cash':
                                $cashPaidAmount = $cashPaidAmount + $transaction->amount;
                                break;
                            case 'Card':
                                $cardPaidAmount = $cardPaidAmount + $transaction->amount;
                                break;
                        }
                    } else {
                        $previousPaidAmount += $transaction->amount;
                    }
                }
            }

            $cancelRefundAmount += OrderReturnAmount::where('bill_no', $order->bill_no)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->sum('amount');

            if ($order->status == "cancelled") {
                if ($orderDate >= $fromDate && $orderDate <= $toDate) {
                    $cancelRefundAmount += OrderEntryController::getFinalBalance($order->total_bill, $order->overall_dis, $order->is_dis_percentage);
                }
            }
        }

        $orderReports = AddReport::whereIn('report_id', $allOrderIds)->get();

        foreach ($allOrderIds as $id) {
            $orderReport = $orderReports->firstWhere('report_id', $id);
            $allOrderIndex = array_search($id, $allOrderIds);

            if ($orderReport) {
                $orderOrderType = $orderReport->order_order_type;

                if ($orderOrderType == 2) {
                    $totalConsultationsBalance += $allOrderAmount[$allOrderIndex];
                }
            }
        }

        return view(
            'admin.dashboard',
            compact(
                'user',
                'noLoggedUsers',
                'noOfBills',
                'totalBilledAmount',
                'totalPaidAmount',
                'totalDiscountAmount',
                'totalPendingBalance',
                'totalConsultationsBalance',
                'upiPaidAmount',
                'cashPaidAmount',
                'cardPaidAmount',
                'previousPaidAmount',
                'cancelRefundAmount',
                'fromDate',
                'toDate',
            )
        );
    }

    public static function getUserData()
    {
        $session = Session::get('user');
        $user = DB::table('users')
            ->select('*')
            ->selectSub(
                function ($query) {
                    $query->select('name')
                        ->from('admin_roles')
                        ->whereColumn('admin_roles.id', 'users.role');
                },
                'role'
            )->selectSub(
                function ($query) {
                    $query->select('access')
                        ->from('admin_roles')
                        ->whereColumn('admin_roles.id', 'users.role');
                },
                'access'
            )
            ->where('id', $session->id)
            ->first();

        $settings = SystemSettings::first();

        if (!is_null($user->lab_location) && !empty($user->lab_location)) {
            $labLocation = LabLocations::find($user->lab_location);

            if (!is_null($labLocation->location_bill_header) && !empty($labLocation->location_bill_header)) {
                $settings->bill_header = $labLocation->location_bill_header;
            }

            if (!is_null($labLocation->consulting_bill_header) && !empty($labLocation->consulting_bill_header)) {
                $settings->header_consulting_bill = $labLocation->consulting_bill_header;
            }
        }

        $user->settings = $settings;
        return $user;
    }

    public static function isUserLoggedIn()
    {
        return Session::has('user');
    }

    public static function getUserDataFromId($id)
    {
        $user = DB::table('users')
            ->select('*')
            ->selectSub(
                function ($query) {
                    $query->select('name')
                        ->from('admin_roles')
                        ->whereColumn('admin_roles.id', 'users.role');
                },
                'role'
            )
            ->where('id', $id)
            ->first();
        return $user;
    }

    public static function sendSMS($phoneNumber, $message, $templateId)
    {
        $userId = "mstarbiz";
        $password = "773190@Sad";
        $senderId = "MSTARD";
        $entityId = "1001621050000073627";

        $apiContext = "SendSingleApi";

        // if (str_contains($phoneNumber, ",")) {
        //     $apiContext = "SendMultipleApi";
        // }

        $userId = urlencode($userId);
        $password = urlencode($password);
        $senderId = urlencode($senderId);
        $entityId = urlencode($entityId);
        $phoneNumber = urlencode($phoneNumber);
        $message = urlencode($message);
        $templateId = urlencode($templateId);

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "http://nimbusit.biz/api/SmsApi/$apiContext?UserID=$userId&Password=$password&SenderID=$senderId&Phno=$phoneNumber&Msg=$message&EntityID=$entityId&TemplateID=$templateId",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        return;
    }

    public static function sendBulkSMS($phoneNumber, $message, $templateId)
    {
        $userId = "mstarbiz";
        $password = "773190@Sad";
        $senderId = "MSTARD";
        $entityId = "1001621050000073627";

        // $userId = urlencode($userId);
        // $password = urlencode($password);
        // $senderId = urlencode($senderId);
        // $entityId = urlencode($entityId);
        // $phoneNumber = urlencode($phoneNumber);
        // $message = urlencode($message);
        // $templateId = urlencode($templateId);

        $data = array(
            'UserID' => $userId,
            'Password' => $password,
            'SenderID' => $senderId,
            'Phno' => $phoneNumber,
            'Msg' => $message,
            'EntityID' => $entityId,
            'TemplateID' => $templateId,
            'FlashMsg' => 0
        );

        $url = 'http://nimbusit.biz/api/SmsApi/SendBulkApi';
        $postData = http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/x-www-form-urlencoded'
            )
        );

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function sendDispatchSMS($billNo, $orderNo)
    {
        $user = $this->getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $orderEntry->umr_number)->first();

        // $inputMessage = "Dear [PatientName](UMR: [UMRNumber]), few reports for your bill number [BillNumber] are ready. Visit [ReportURL] . From [LabName], Phone: [PhoneNo]. M STAR";
        $inputMessage = "Hi [PatientName](UMR: [UMRNumber]), Please check your report on below link [ReportURL] - MSTARD";
        $replacementArray = array(
            "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
            "[UMRNumber]" => $patientData->umr_number,
            "[BillNumber]" => $billNo,
            "[LabName]" => $user->settings['lab_name'],
            "[PhoneNo]" => $user->settings['phone_number'],
            "[ReportURL]" => url("viewbill/$billNo?orders=$orderNo")
        );

        $smsPhone = $patientData->phone;
        $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $inputMessage);
        $smsTempleteId = "1007165104169347549";
        HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);

        return redirect()->back();
    }

    public function sendAllDispatchSMS($billNo, $orderIds)
    {
        $user = $this->getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $orderEntry->umr_number)->first();
        // $allOrderIds = explode(',', $orderEntry->order_ids);
        // $orderIds = explode(',', $orderIds);
        // $index = 0;

        $inputMessage = "Hi [PatientName](UMR: [UMRNumber]), Please check your report on below link [ReportURL] - MSTARD";
        $replacementArray = array(
            "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
            "[UMRNumber]" => $patientData->umr_number,
            "[BillNumber]" => $billNo,
            "[LabName]" => $user->settings['lab_name'],
            "[PhoneNo]" => $user->settings['phone_number'],
            "[ReportURL]" => url("viewbill/$billNo?orders=" . $orderIds)
        );

        $smsPhone = $patientData->phone;
        $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $inputMessage);
        $smsTempleteId = "1007165104169347549";
        HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);

        // while ($index < count($allOrderIds)) {
        //     $orderNo = $allOrderIds[$index];

        //     if (str_contains($orderNo, $this->orderTypeProfile)) {
        //         $labProfileDetails = LabProfileDetails::where('profile_id', str_replace($this->orderTypeProfile, "", $orderNo))->get();

        //         foreach ($labProfileDetails as $labProfile) {
        //             $allOrderIds[] = "" . $labProfile->order_id;
        //         }
        //     }

        //     if (in_array($orderNo, $orderIds)) {
        //         $resultReports = ResultReports::where('bill_no', $billNo)->where('report_no', $orderNo)->first();

        //         if ($resultReports && $resultReports->status != 'Save') {
        //             $inputMessage = "Hi [PatientName](UMR: [UMRNumber]), Please check your report on below link [ReportURL] - MSTARD";
        //             $replacementArray = array(
        //                 "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
        //                 "[UMRNumber]" => $patientData->umr_number,
        //                 "[BillNumber]" => $billNo,
        //                 "[LabName]" => $user->settings['lab_name'],
        //                 "[PhoneNo]" => $user->settings['phone_number'],
        //                 "[ReportURL]" => url("viewbill/$billNo/$orderNo")
        //             );

        //             $smsPhone = $patientData->phone;
        //             $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $inputMessage);
        //             $smsTempleteId = "1007165104169347549";
        //             HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);
        //         }
        //     }

        //     $index++;
        // }

        return redirect()->back()->with(["actionSuccess" => true, "actionMessage" => "Selected reports sent to SMS successfully"]);
    }

    public static function sendSampleRejectSMS($billNo, $ordersNameTxt, $rejectReason)
    {
        $user = HomeController::getUserData();

        $orderEntry = OrderEntry::where('bill_no', $billNo)->first();
        $patientData = Patients::where('umr_number', $orderEntry->umr_number)->first();

        // $inputMessage = "Hello [PatientName](UMR: [UMRNumber]), This is to notify you that the sample of order ([OrderName]) with Bill Number : [BillNumber] was Rejected due to [RejectionReason]. Thank you. M Star Diagnostics";
        $inputMessage = "Dear [PatientName](UMR: [UMRNumber]) Your bill number is [BillNumber] and your orders are [OrderName] have been Rejected due to [RejectionReason] Please contact this number [ContactPhoneNumber] Thank you M Star Diagnostics";
        $replacementArray = array(
            "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
            "[UMRNumber]" => $patientData->umr_number,
            "[OrderName]" => $ordersNameTxt,
            "[BillNumber]" => $billNo,
            "[RejectionReason]" => $rejectReason,
            "[ContactPhoneNumber]" => $user->settings->phone_number
        );

        $smsPhone = $patientData->phone;
        $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $inputMessage);
        $smsTempleteId = "1007170230752039985";
        HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);

        return;
    }

    public function sendPaymentReminder($billNo)
    {
        $orderDetails = OrderEntry::where('bill_no', $billNo)
            ->select('*')
            ->selectRaw('(SELECT patients.patient_title_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_title_name')
            ->selectRaw('(SELECT patients.patient_name FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_name')
            ->selectRaw('(SELECT patients.age FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age')
            ->selectRaw('(SELECT patients.gender FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_gender')
            ->selectRaw('(SELECT patients.phone FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_phone')
            ->selectRaw('(SELECT patients.age_type FROM patients WHERE patients.umr_number = order_entry.umr_number) AS patient_age_type')
            ->selectRaw('(SELECT doctors.doc_name FROM doctors WHERE doctors.id = order_entry.doctor) AS doc_name')
            ->first();

        if ($orderDetails) {
            $leftBalance = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);

            if ($leftBalance != 0) {
                $message = "Dear *" . $orderDetails->patient_title_name . " " . $orderDetails->patient_name . "*,

You have a pending bill towards *M.Star Diagnostics* with bill number *" . $billNo . "*. Please clear your due of *₹" . $leftBalance . "* to access your lab test reports. Use PhonePe, GooglePay or any other UPI application to make your payment.

Payment account details:-

UPI ID :- *mstardiagnostics@ybl*
Phonepe/Googlepay :- *9603496176*


M.Star Diagnostics";

                $apiEndpoint = 'http://api.wtap.sms4power.com/wapp/api/send';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);

                $postData = array(
                    'apikey' => 'eb61e2275395438b88a4bb465eba19e0',
                    'msg' => $message,
                    'mobile' => $orderDetails->patient_phone
                );

                $queryString = http_build_query($postData);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    echo 'cURL error: ' . curl_error($ch);
                }
                curl_close($ch);

                return response()->json(['message' => 'Sent successfully', 'response' => $response]);
            }
        }

        return response()->json(['message' => 'Failed to send']);
    }
}
