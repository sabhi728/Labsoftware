<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabProfiles;
use App\Models\LocationOrderRates;
use App\Models\LocationProfileRates;
use App\Models\ReferralCompany;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\Department;
use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\IPBillingCategoryType;
use App\Models\LabLocations;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\OrderDetails;
use App\Models\OrderDetailValues;
use App\Models\OrderTemplates;
use App\Models\Doctors;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\AdminControllers\HomeController;

class OrderMaintenance extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $reports = AddReport::with('orderType')->get();

        return view('admin.order_maintenance', compact('user', 'reports'));
    }

    public function addOrderIndex()
    {
        $user = HomeController::getUserData();
        $departments = Department::get();
        $sampleTypes = SampleType::get();
        $orderTypes = OrderType::get();
        $ipBillingCategoryTypes = IPBillingCategoryType::get();
        $reportFormats = ReportFormat::get();

        return view('admin.add_order', compact('user', 'departments', 'sampleTypes', 'orderTypes', 'ipBillingCategoryTypes', 'reportFormats'));
    }

    public function updateOrderIndex($id)
    {
        $user = HomeController::getUserData();
        $departments = Department::get();
        $sampleTypes = SampleType::get();
        $orderTypes = OrderType::get();
        $ipBillingCategoryTypes = IPBillingCategoryType::get();
        $reportFormats = ReportFormat::get();

        $orderData = AddReport::where('report_id', $id)->first();
        return view('admin.add_order', compact('user', 'departments', 'sampleTypes', 'orderTypes', 'ipBillingCategoryTypes', 'reportFormats', 'orderData'));
    }

    public function orderDetailsIndex($orderId)
    {
        $user = HomeController::getUserData();
        $reportDetails = AddReport::where('report_id', $orderId)->first();
        $orderDetails = OrderDetails::where('report_id', $orderId)->where('template_id', NULL)->orderBy('position', 'asc')->get();

        return view('admin.order_details', compact('user', 'reportDetails', 'orderDetails'));
    }

    public function orderDetailsDelete($id)
    {
        $user = HomeController::getUserData();
        OrderDetails::find($id)->delete();
        return redirect()->back();
    }

    public function updatePosition($orderDetailId, $direction)
    {
        $orderDetail = OrderDetails::find($orderDetailId);

        if ($orderDetail) {
            $orderDetails = OrderDetails::where('report_id', $orderDetail->report_id)->where('template_id', $orderDetail->template_id)->get();
            $currentPosition = $orderDetail->position;

            if ($currentPosition == 0) {
                $count = 0;

                foreach ($orderDetails as $details) {
                    $count++;

                    $details->position = $count;
                    $details->save();
                }
            }

            $orderDetail = $orderDetails->find($orderDetailId);
            $currentPosition = $orderDetail->position;

            $swapPosition = ($direction === 'up') ? $currentPosition - 1 : $currentPosition + 1;
            $swapDetail = $orderDetails->where('position', $swapPosition)->first();

            if ($swapDetail) {
                $orderDetail->update(['position' => $swapPosition]);
                $swapDetail->update(['position' => $currentPosition]);

                return response()->json(['message' => 'Position updated successfully']);
            } else {
                return response()->json(['message' => 'Cannot move item further in the specified direction']);
            }
        }

        return response()->json(['message' => 'Order detail not found'], 404);
    }

    public function orderDetailValuesIndex($orderId, $orderDetailsId)
    {
        $user = HomeController::getUserData();
        $orderDetails = OrderDetails::where('id', $orderDetailsId)->first();
        $orderDetailValues = OrderDetailValues::where('order_detail_id', $orderDetailsId)->get();

        return view('admin.order_details_values', compact('user', 'orderDetails', 'orderDetailsId', 'orderDetailValues'));
    }

    public function addOrderDetailsIndex($orderId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $orderId)->first();

        return view('admin.add_order_details', compact('user', 'orderDetails'));
    }

    public function updateOrderDetailsIndex($orderId, $updateId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $orderId)->first();
        $orderInputData = OrderDetails::where('id', $updateId)->first();

        return view('admin.add_order_details', compact('user', 'orderDetails', 'orderInputData'));
    }

    public function templateOrderDetailsIndex($orderId, $templateId)
    {
        $user = HomeController::getUserData();
        $reportDetails = AddReport::where('report_id', $orderId)->first();
        $orderDetails = OrderDetails::where('report_id', $orderId)->where('template_id', $templateId)->orderBy('position', 'asc')->get();

        return view('admin.template_order_details', compact('user', 'reportDetails', 'orderDetails', 'templateId'));
    }

    public function addTemplateOrderDetailsIndex($orderId, $templateId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $orderId)->first();

        return view('admin.add_template_order_details', compact('user', 'orderDetails', 'templateId'));
    }

    public function updateTemplateOrderDetailsIndex($orderId, $templateId, $updateId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $orderId)->first();
        $orderInputData = OrderDetails::where('id', $updateId)->first();

        return view('admin.add_template_order_details', compact('user', 'orderDetails', 'templateId', 'orderInputData'));
    }

    public function addComponentsOfExistingOrderIndex($orderId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $orderId)->first();

        return view('admin.add_components_of_existing_order', compact('user', 'orderDetails'));
    }

    public function orderTemplateIndex($reportId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $reportId)->first();
        $orderTemplates = OrderTemplates::where('report_id', $reportId)->get();

        return view('admin.order_template', compact('user', 'orderDetails', 'orderTemplates'));
    }

    public function addOrderTemplateIndex($reportId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $reportId)->first();

        return view('admin.add_order_template', compact('user', 'orderDetails'));
    }

    public function updateOrderTemplateIndex($reportId, $updateId)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::where('report_id', $reportId)->first();
        $templateData = OrderTemplates::where('id', $updateId)->first();

        return view('admin.add_order_template', compact('user', 'orderDetails', 'templateData'));
    }

    public function saveComponentsOfExistingOrder($reportId, $ids)
    {
        $user = HomeController::getUserData();
        $idsArray = explode(",", $ids);

        foreach ($idsArray as $id) {
            $orderDetails = OrderDetails::where('id', $id)->first();
            $newOrderDetail = $orderDetails->replicate();

            $newOrderDetail->report_id = $reportId;
            $newOrderDetail->created_by = $user->id;
            $newOrderDetail->save();
        }

        return redirect('order_details/' . $reportId);
    }

    public function searchOrders($search)
    {
        $user = HomeController::getUserData();
        $returnArray = array();

        $order = AddReport::where('order_name', 'LIKE', '%' . $search . '%')->get();
        $labProfiles = LabProfiles::where('name', 'LIKE', '%' . $search . '%')->get();

        foreach ($order as $item) {
            $returnArray[] = array(
                "report_id" => $item->report_id,
                "order_name" => $item->order_name,
                "order_amount" => $item->order_amount,
                "type" => "order",
                "order_type" => OrderType::where('id', $item->order_order_type)->first()->name,
            );
        }

        foreach ($labProfiles as $profile) {
            $returnArray[] = array(
                "report_id" => $profile->id,
                "order_name" => $profile->name,
                "order_amount" => $profile->amount,
                "type" => "profile",
                "order_type" => '',
            );
        }

        if (!is_null($user->lab_location) && !empty($user->lab_location)) {
            $locationOrderRates = LocationOrderRates::where('location', $user->lab_location)->get();
            $locationProfileRates = LocationProfileRates::where('location', $user->lab_location)->get();

            foreach ($returnArray as $key => $item) {
                if ($item['type'] == "order") {
                    $matchingRate = $locationOrderRates->where('report', $item['report_id'])->first();

                    if (!is_null($matchingRate)) {
                        $returnArray[$key]['order_amount'] = $matchingRate->amount;
                    }
                } else {
                    $matchingRate = $locationProfileRates->where('profile', $item['report_id'])->first();

                    if (!is_null($matchingRate)) {
                        $returnArray[$key]['order_amount'] = $matchingRate->amount;
                    }
                }
            }
        }

        return $returnArray;
    }

    public function searchDoctors($search)
    {
        $order = Doctors::where('doc_name', 'LIKE', '%' . $search . '%')->get();
        return $order;
    }

    public function searchLocations($search)
    {
        $order = ReferralCompany::where('name', 'LIKE', '%' . $search . '%')->get();
        return $order;
    }

    public function getOrderDetails($reportId)
    {
        $order = OrderDetails::where('report_id', $reportId)->orderBy('position', 'asc')->get();
        return $order;
    }

    public function addOrder(Request $request)
    {
        $user = HomeController::getUserData();

        $checkReport = AddReport::where('order_name', $request->orderName)->first();

        if (!$checkReport) {
            $addRequest = new AddReport();
            $addRequest->created_by = $user->id;
            $addRequest->order_name = $request->orderName;
            if ($request->has('hasComponents'))
                $addRequest->has_components = "true";
            $addRequest->order_test_code = $request->testCode;
            $addRequest->order_display_name = $request->displayOrderName;
            $addRequest->order_department = $request->department;
            $addRequest->order_amount = $request->amount;
            $addRequest->order_process_time = $request->processTime;
            $addRequest->order_machine_name = $request->machineName;
            if ($request->has('sampleType')) {
                $selectedSampleTypes = $request->input('sampleType');
                $selectedSampleTypesString = implode(', ', $selectedSampleTypes);
                $addRequest->order_sample_type = $selectedSampleTypesString;
            } else {
                $addRequest->order_sample_type = NULL;
            }
            $addRequest->order_method = $request->method;
            $addRequest->order_result_notes_1 = $request->resultNotesPage1;
            $addRequest->order_result_notes_2 = $request->resultNotesPage2;
            $addRequest->order_result_notes_3 = $request->resultNotesPage3;
            $addRequest->order_advice = $request->advice;
            $addRequest->order_worksheet = $request->workSheet;
            $addRequest->order_purpose = $request->purpose;
            $addRequest->order_order_type = $request->orderType;
            $addRequest->order_ip_billing = $request->ipBillingCategoryType;
            $addRequest->order_report_format = $request->reportFormat;
            if ($request->has('recurring'))
                $addRequest->order_recurring = "true";
            if ($request->has('serviceDoctorRequired'))
                $addRequest->order_service_doctor_required = "true";
            if ($request->has('checkToInactive')) {
                $addRequest->status = "In Active";
            } else {
                $addRequest->status = "Active";
            }

            if ($addRequest->save())
                return redirect('order_maintenance');
            return redirect('add_order')->withInput();
        }

        return "Error: Order with this name already exist.";
    }

    public function updateOrder($id, Request $request)
    {
        $user = HomeController::getUserData();

        $addRequest = AddReport::where('report_id', $id)->first();
        $addRequest->created_by = $user->id;
        $addRequest->order_name = $request->orderName;
        $addRequest->has_components = $request->has('hasComponents') ? "true" : "false";
        $addRequest->order_test_code = $request->testCode;
        $addRequest->order_display_name = $request->displayOrderName;
        $addRequest->order_department = $request->department;
        $addRequest->order_amount = $request->amount;
        $addRequest->order_process_time = $request->processTime;
        $addRequest->order_machine_name = $request->machineName;

        if ($request->has('sampleType')) {
            $selectedSampleTypes = $request->input('sampleType');
            $selectedSampleTypesString = implode(', ', $selectedSampleTypes);
            $addRequest->order_sample_type = $selectedSampleTypesString;
        } else {
            $addRequest->order_sample_type = NULL;
        }

        $addRequest->order_method = $request->method;
        $addRequest->order_result_notes_1 = $request->resultNotesPage1;
        $addRequest->order_result_notes_2 = $request->resultNotesPage2;
        $addRequest->order_result_notes_3 = $request->resultNotesPage3;
        $addRequest->order_advice = $request->advice;
        $addRequest->order_worksheet = $request->workSheet;
        $addRequest->order_purpose = $request->purpose;
        $addRequest->order_order_type = $request->orderType;
        $addRequest->order_ip_billing = $request->ipBillingCategoryType;
        $addRequest->order_report_format = $request->reportFormat;
        $addRequest->order_recurring = $request->has('recurring') ? "true" : "false";
        $addRequest->order_service_doctor_required = $request->has('serviceDoctorRequired') ? "true" : "false";
        $addRequest->status = $request->has('checkToInactive') ? "In Active" : "Active";

        if ($addRequest->save()) {
            return redirect('order_maintenance');
        }

        return redirect('update_order/' . $id)->withInput();
    }

    public function deleteOrder($id)
    {
        $user = HomeController::getUserData();
        AddReport::find($id)->delete();
        return redirect()->back();
    }

    public function addOrderDetails($orderId, Request $request)
    {
        $user = HomeController::getUserData();

        $checkOrderDetails = OrderDetails::where('report_id', $orderId)->where('component_name', $request->componentName)->first();

        if (!$checkOrderDetails) {
            $orderDetails = new OrderDetails();
            $orderDetails->report_id = $orderId;
            $orderDetails->created_by = $user->id;
            $orderDetails->sub_heading = $request->subHeading;
            $orderDetails->component_name = $request->componentName;
            $orderDetails->machine_code = $request->machineCode;
            $orderDetails->specimen_code = $request->specimenCode;
            $orderDetails->order_details_range = $request->range;
            $orderDetails->from_range = $request->fromRange;
            $orderDetails->to_range = $request->toRange;
            $orderDetails->units = $request->units;
            $orderDetails->method = $request->method;
            $orderDetails->default_value = $request->defaultValue;
            $orderDetails->calculations = $request->calculations;

            if ($request->has('checkToInactive')) {
                $orderDetails->status = "In Active";
            } else {
                $orderDetails->status = "Active";
            }

            if ($orderDetails->save())
                return redirect('order_details/' . $orderId);
            return redirect('add_order_details/' . $orderId)->withInput();
        }

        return "Error: Component with this name already exist.";
    }

    public function updateOrderDetails($orderId, $updateId, Request $request)
    {
        $user = HomeController::getUserData();

        $orderDetails = OrderDetails::where('id', $updateId)->first();
        $orderDetails->report_id = $orderId;
        $orderDetails->created_by = $user->id;
        $orderDetails->sub_heading = $request->subHeading;
        $orderDetails->component_name = $request->componentName;
        $orderDetails->machine_code = $request->machineCode;
        $orderDetails->specimen_code = $request->specimenCode;
        $orderDetails->order_details_range = $request->range;
        $orderDetails->from_range = $request->fromRange;
        $orderDetails->to_range = $request->toRange;
        $orderDetails->units = $request->units;
        $orderDetails->method = $request->method;
        $orderDetails->default_value = $request->defaultValue;
        $orderDetails->calculations = $request->calculations;
        if ($request->has('checkToInactive')) {
            $orderDetails->status = "In Active";
        } else {
            $orderDetails->status = "Active";
        }

        if ($orderDetails->save())
            return redirect('order_details/' . $orderId);
        return redirect('update_order_details/' . $orderId . '/' . $updateId)->withInput();
    }

    public function addTemplateOrderDetails($orderId, $templateId, Request $request)
    {
        $user = HomeController::getUserData();

        $checkOrderDetails = OrderDetails::where('template_id', $templateId)->where('component_name', $request->componentName)->first();

        if (!$checkOrderDetails) {
            $orderDetails = new OrderDetails();
            $orderDetails->report_id = $orderId;
            $orderDetails->template_id = $templateId;
            $orderDetails->created_by = $user->id;
            $orderDetails->sub_heading = $request->subHeading;
            $orderDetails->component_name = $request->componentName;
            $orderDetails->machine_code = $request->machineCode;
            $orderDetails->specimen_code = $request->specimenCode;
            $orderDetails->order_details_range = $request->range;
            $orderDetails->from_range = $request->fromRange;
            $orderDetails->to_range = $request->toRange;
            $orderDetails->units = $request->units;
            $orderDetails->method = $request->method;
            $orderDetails->default_value = $request->defaultValue;
            $orderDetails->calculations = $request->calculations;
            if ($request->has('checkToInactive')) {
                $orderDetails->status = "In Active";
            } else {
                $orderDetails->status = "Active";
            }

            if ($orderDetails->save())
                return redirect('template_order_details/' . $orderId . '/' . $templateId);
            return redirect('add_template_order_details/' . $orderId . '/' . $templateId)->withInput();
        }

        return "Error: Component with this name already exist.";
    }

    public function updateTemplateOrderDetails($orderId, $templateId, $updateId, Request $request)
    {
        $user = HomeController::getUserData();

        $orderDetails = OrderDetails::where('id', $updateId)->first();
        $orderDetails->report_id = $orderId;
        $orderDetails->template_id = $templateId;
        $orderDetails->created_by = $user->id;
        $orderDetails->sub_heading = $request->subHeading;
        $orderDetails->component_name = $request->componentName;
        $orderDetails->machine_code = $request->machineCode;
        $orderDetails->specimen_code = $request->specimenCode;
        $orderDetails->order_details_range = $request->range;
        $orderDetails->from_range = $request->fromRange;
        $orderDetails->to_range = $request->toRange;
        $orderDetails->units = $request->units;
        $orderDetails->method = $request->method;
        $orderDetails->default_value = $request->defaultValue;
        $orderDetails->calculations = $request->calculations;
        if ($request->has('checkToInactive')) {
            $orderDetails->status = "In Active";
        } else {
            $orderDetails->status = "Active";
        }

        if ($orderDetails->save())
            return redirect('template_order_details/' . $orderId . '/' . $templateId);
        return redirect('update_template_order_details/' . $orderId . '/' . $templateId . '/' . $updateId)->withInput();
    }

    public function addOrderDetailValue($orderId, $orderDetailsId, Request $request)
    {
        $user = HomeController::getUserData();

        $orderDetailValues = new OrderDetailValues();
        $orderDetailValues->order_detail_id = $orderDetailsId;
        $orderDetailValues->created_by = $user->id;
        $orderDetailValues->value = $request->orderDetailValue;
        $orderDetailValues->save();

        return redirect('order_detail_values/' . $orderId . '/' . $orderDetailsId);
    }

    public function deleteOrderDetailValue($id)
    {
        $user = HomeController::getUserData();

        $orderDetailValues = OrderDetailValues::where('id', $id)->first();
        $orderDetailValues->delete();

        return redirect()->back();
    }

    public function addOrderTemplate($reportId, Request $request)
    {
        $user = HomeController::getUserData();

        $orderTemplates = new OrderTemplates();
        $orderTemplates->report_id = $reportId;
        $orderTemplates->created_by = $user->id;
        $orderTemplates->template_name = $request->templateName;
        $orderTemplates->template_gender = $request->gender;
        $orderTemplates->template_from_age = $request->fromAge;
        $orderTemplates->template_from_age_type = $request->fromAgeType;
        $orderTemplates->template_to_age = $request->toAge;
        $orderTemplates->template_to_age_type = $request->toAgeType;

        if ($request->has('checkToInactive')) {
            $orderTemplates->status = "In Active";
        } else {
            $orderTemplates->status = "Active";
        }

        if ($orderTemplates->save())
            return redirect('order_template/' . $reportId);
        return redirect('add_order_template/' . $reportId)->withInput();
    }

    public function updateOrderTemplate($reportId, $updateId, Request $request)
    {
        $user = HomeController::getUserData();

        $orderTemplates = OrderTemplates::where('id', $updateId)->first();
        $orderTemplates->report_id = $reportId;
        $orderTemplates->created_by = $user->id;
        $orderTemplates->template_name = $request->templateName;
        $orderTemplates->template_gender = $request->gender;
        $orderTemplates->template_from_age = $request->fromAge;
        $orderTemplates->template_from_age_type = $request->fromAgeType;
        $orderTemplates->template_to_age = $request->toAge;
        $orderTemplates->template_to_age_type = $request->toAgeType;

        if ($request->has('checkToInactive')) {
            $orderTemplates->status = "In Active";
        } else {
            $orderTemplates->status = "Active";
        }

        if ($orderTemplates->save())
            return redirect('order_template/' . $reportId);
        return redirect('update_order_template/' . $reportId . '/' . $updateId)->withInput();
    }
}
