<?php

namespace App\Http\Controllers\ReferralControllers;

use App\Http\Controllers\AdminControllers\OrderBillsController;
use App\Models\LabProfiles;
use App\Models\ReferralCompany;
use Carbon\Carbon;

use App\Models\OrderType;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\OrderEntryTransactions;
use App\Models\Doctors;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Controllers\AdminControllers\HomeController;

class OrderEntryController extends CommonController
{
    public function index()
    {
        $user = $this->getUserData();
        $isDuplicateOrdersAllowed = true;

        if ($user->settings->unique_order_entry == "true") {
            $isDuplicateOrdersAllowed = false;
        }

        return view('referral.OrderEntry.oe_index', compact('user', 'isDuplicateOrdersAllowed'));
    }

    public function indexSearch($search)
    {
        $user = $this->getUserData();
        $patientDetails = Patients::where('umr_number', $search)->first();

        $isDuplicateOrdersAllowed = true;

        if ($user->settings->unique_order_entry == "true") {
            $isDuplicateOrdersAllowed = false;
        }

        return view('referral.OrderEntry.oe_index', compact('user', 'patientDetails', 'isDuplicateOrdersAllowed'));
    }

    public function addPatientIndex()
    {
        $user = $this->getUserData();
        $isPatientPhoneRequired = true;

        if ($user->settings->patient_phone_not_required == "true") {
            $isPatientPhoneRequired = false;
        }

        return view('referral.OrderEntry.oe_add_patient', compact('user', 'isPatientPhoneRequired'));
    }

    public function searchPatient($search)
    {
        $results = Patients::where(function ($query) use ($search) {
            $query->where('umr_number', 'LIKE', '%' . $search . '%')
                ->orWhere('phone', 'LIKE', '%' . $search . '%')
                ->orWhere('patient_title_name', 'LIKE', '%' . $search . '%')
                ->orWhere('patient_name', 'LIKE', '%' . $search . '%')
                ->orWhere('age', 'LIKE', '%' . $search . '%')
                ->orWhere('address', 'LIKE', '%' . $search . '%')
                ->orWhere('gender', 'LIKE', '%' . $search . '%')
            ;
        })->get();
        return $results;
    }

    public function indexBillDetails($billNo)
    {
        $user = $this->getUserData();
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

        $orderIdsArray = explode(',', $orderDetails->order_ids);
        $orderAmountArray = explode(',', $orderDetails->order_amount);

        $orderData = [];
        $isOrderOnlyConsulting = true;

        foreach ($orderIdsArray as $key => $id) {
            if (str_contains($id, $this->orderTypeProfile)) {
                $data = LabProfiles::find(str_replace($this->orderTypeProfile, "", $id));

                if ($data) {
                    $isOrderOnlyConsulting = false;

                    $orderData[] = array(
                        "order_name" => $data->name,
                        "custom_order_amount" => $orderAmountArray[$key],
                        "sample_type" => OrderBillsController::getSampleTypeText($id),
                    );
                }
            } else {
                $data = AddReport::find($id);

                if ($data) {
                    $orderType = OrderType::find($data->order_order_type);

                    if ($orderType) {
                        if ($orderType->name != "Consulting") {
                            $isOrderOnlyConsulting = false;
                        }
                    }

                    $orderData[] = array(
                        "order_name" => $data->order_name,
                        "custom_order_amount" => $orderAmountArray[$key],
                        "sample_type" => OrderBillsController::getSampleTypeText($id),
                    );
                }
            }
        }

        $orderDetails['orderData'] = $orderData;
        $orderDetails['balance'] = OrderEntryController::getLeftBalance($orderDetails->total_bill, $orderDetails->paid_amount, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);
        $orderDetails['discount'] = OrderEntryController::getDiscountAmount($orderDetails->total_bill, $orderDetails->overall_dis, $orderDetails->is_dis_percentage);
        $createdAtFormatted = Carbon::parse($orderDetails->created_at)->format('d-M-Y h:i A');

        $payMode = "";

        $orderEntryTransactions = OrderEntryTransactions::where('bill_no', $billNo)->get();

        if ($orderEntryTransactions) {
            foreach ($orderEntryTransactions as $transaction) {
                if ($transaction->amount != "0") {
                    if (empty($payMode)) {
                        $payMode = $transaction->payment_method;
                    } else {
                        if (!str_contains($payMode, $transaction->payment_method)) {
                            $payMode .= ', ' . $transaction->payment_method;
                        }
                    }
                }
            }
        }

        return view('referral.OrderEntry.oe_bill_details', compact('user', 'orderDetails', 'isOrderOnlyConsulting', 'createdAtFormatted', 'payMode'));
    }

    public static function getLeftBalance($totalBill, $paidAmount, $discountAmount, $isDiscountPercentage)
    {
        $discount = 0;
        if ($isDiscountPercentage == "true") {
            $percentage = $discountAmount;
            $discount = $totalBill * ($percentage / 100);
        } else {
            $discount = $discountAmount;
        }
        return ($totalBill - $discount) - $paidAmount;
    }

    public static function getFinalBalance($totalBill, $discountAmount, $isDiscountPercentage)
    {
        $discount = 0;
        if ($isDiscountPercentage == "true") {
            $percentage = $discountAmount;
            $discount = $totalBill * ($percentage / 100);
        } else {
            $discount = $discountAmount;
        }
        return ($totalBill - $discount);
    }

    public static function getDiscountAmount($totalBill, $discountAmount, $isDiscountPercentage)
    {
        $discount = 0;
        if ($isDiscountPercentage == "true") {
            $percentage = $discountAmount;
            $discount = $totalBill * ($percentage / 100);
        } else {
            $discount = $discountAmount;
        }
        return $discount;
    }

    public function addPatient(Request $request)
    {
        $user = $this->getUserData();
        $umrNumber = rand(10000000, 99999999);
        $attachment = "";

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $randomName = Str::random(20);

            $extension = $attachment->getClientOriginalExtension();
            $newFileName = $randomName . '.' . $extension;

            $attachment->move(public_path('assets/uploads/patient'), $newFileName);
            $attachment = 'assets/uploads/patient/' . $newFileName;
        } else {
            $attachment = null;
        }

        $patient = new Patients();
        $patient->created_by = $user->id;
        $patient->umr_number = $umrNumber;
        $patient->patient_title_name = $request->input('patientTitlename');
        $patient->patient_name = $request->input('patientName');
        $patient->age = $request->input('age');
        $patient->age_type = (empty($request->additionalAgeInput)) ? $request->input('ageType') : $request->input('ageType') . ' ' . $request->additionalAgeInput . ' ' . $request->input('additionalAgeType');
        $patient->gender = $request->input('gender');
        $patient->address = $request->input('address');
        $patient->phone = $request->input('phone');
        $patient->email = $request->input('email');
        $patient->area = $request->input('area');
        $patient->city = $request->input('city');
        $patient->district = $request->input('district');
        $patient->state = $request->input('state');
        $patient->country = $request->input('country');
        $patient->attachment = $attachment;
        $patient->clinicalHistory = $request->input('clinicalHistory');

        if ($patient->save()) {
            if ($request->has('search')) {
                return redirect('referralpanel/orderentry/index/' . $umrNumber);
            }
            return redirect('referralpanel/orderentry/index');
        }
        return redirect()->back()->withInput();
    }

    public function addOrderEntry(Request $request)
    {
        $user = $this->getUserData();

        $lastBillNo = OrderEntry::orderBy('id', 'desc')->limit(1)->value('bill_no');

        if ($lastBillNo) {
            $newBillNo = str_replace("M", "", $lastBillNo) + 1;

            if ($newBillNo < 10) {
                $newBillNo = "M00" . $newBillNo;
            } else if ($newBillNo < 100) {
                $newBillNo = "M0" . $newBillNo;
            } else {
                $newBillNo = "M" . $newBillNo;
            }
        } else {
            $newBillNo = "M001";
        }

        $selectedDoctorId = "";

        if (empty($request->selectedDoctorId)) {
            if (!empty($request->selectedDoctorName)) {
                $isDoctorExist = Doctors::where('doc_name', $request->selectedDoctorName)->first();

                if (!$isDoctorExist) {
                    $doctor = new Doctors();
                    $doctor->doc_name = $request->selectedDoctorName;
                    $doctor->doc_type = "Both";
                    $doctor->doc_percentage = "0";
                    $doctor->save();

                    $selectedDoctorId = $doctor->id;
                } else {
                    $selectedDoctorId = $isDoctorExist->id;
                }
            }
        } else {
            $selectedDoctorId = $request->selectedDoctorId;
        }

        $orderEntry = new OrderEntry();
        $orderEntry->bill_no = $newBillNo;
        $orderEntry->created_by = $user->id;
        $orderEntry->umr_number = $request->umrInput;
        $orderEntry->order_ids = $request->selectedOrderCommaSeparatedValues;
        $orderEntry->order_amount = $request->selectedOrderAmountsCommaSeparatedValues;
        $orderEntry->order_date = $request->selectedOrderDate;
        $orderEntry->doctor = $selectedDoctorId;
        $orderEntry->referred_by = $user->name;
        $orderEntry->referred_by_id = $user->id;
        $orderEntry->total_bill = $request->totalBill;
        $orderEntry->paid_amount = $request->paidAmount == "" ? "0" : $request->paidAmount;
        // $orderEntry->balance = $request->balanceLeft;

        if ($request->overallDiscount != "" && $request->overallDiscount != "0" && $request->reasonForDiscount != "") {
            $orderEntry->overall_dis = $request->overallDiscount;
            $orderEntry->is_dis_percentage = $request->isDisPercentage;
            $orderEntry->reason_for_discount = $request->reasonForDiscount;
        }

        if ($orderEntry->save()) {
            $orderEntryTransaction = new OrderEntryTransactions();
            $orderEntryTransaction->created_by = $user->id;
            $orderEntryTransaction->bill_no = $newBillNo;
            $orderEntryTransaction->amount = $request->paidAmount == "" ? "0" : $request->paidAmount;
            $orderEntryTransaction->payment_method = $request->paymentMethodSelect;
            $orderEntryTransaction->txn_id = $request->paymentNumber;
            $orderEntryTransaction->save();

            if ($user->settings->no_sms_after_billing == "false") {
                $patientData = Patients::where('umr_number', $request->umrInput)->first();
                $orderIdsArray = explode(',', $request->selectedOrderCommaSeparatedValues);
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
                    break;
                }

                $replacementArray = array(
                    "[PatientName]" => $patientData->patient_title_name . $patientData->patient_name,
                    "[UMRNumber]" => $patientData->umr_number,
                    "[BillNumber]" => $newBillNo,
                    "[Orders]" => $ordersNameTxt,
                    "[LabName]" => $user->settings['lab_name'],
                    "[PhoneNo]" => $user->settings['phone_number']
                );

                $smsPhone = $patientData->phone;
                $smsMessage = str_replace(array_keys($replacementArray), array_values($replacementArray), $user->settings['billing_message_format']);
                $smsTempleteId = "1007166910940701977";
                HomeController::sendSMS($smsPhone, $smsMessage, $smsTempleteId);
            }

            return redirect('referralpanel/orderentry/bill_details/' . $newBillNo);
        }

        return redirect()->back()->withInput();
    }
}
