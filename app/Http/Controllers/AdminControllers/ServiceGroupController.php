<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\Department;
use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\IPBillingCategoryType;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\OrderDetails;
use App\Models\OrderDetailValues;
use App\Models\OrderTemplates;

use App\Models\ServiceGroups;
use App\Models\ServiceGroupOrders;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use App\Http\Controllers\AdminControllers\HomeController;

class ServiceGroupController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $serviceGroups = ServiceGroups::get();

        foreach ($serviceGroups as $serviceGroup) {
            $totalAmount = 0;
            $serviceGroupOrders = ServiceGroupOrders::where('service_group_id', $serviceGroup->id)->get();
            foreach ($serviceGroupOrders as $order) {
                $reportList = AddReport::where('report_id', $order->report_id)->first();
                if ($reportList) {
                    $totalAmount += $reportList->order_amount;
                }
            }
            $serviceGroup->totalAmount = $totalAmount;
        }

        return view('admin.ServiceGroup.index', compact('user', 'serviceGroups'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.ServiceGroup.add', compact('user'));
    }

    public function updateIndex($id)
    {
        $user = HomeController::getUserData();
        $serviceGroupData = ServiceGroups::where('id', $id)->first();
        return view('admin.ServiceGroup.add', compact('user', 'serviceGroupData'));
    }

    public function addToDb(Request $request)
    {
        $user = HomeController::getUserData();

        $serviceGroup = new ServiceGroups();
        $serviceGroup->created_by = $user->id;
        $serviceGroup->name = $request->serviceGroupName;

        if ($serviceGroup->save())
            return redirect('servicegroup/index');
        return redirect('servicegroup/add')->withInput();
    }

    public function updateToDb($id, Request $request)
    {
        $user = HomeController::getUserData();

        $serviceGroup = ServiceGroups::where('id', $id)->first();
        $serviceGroup->created_by = $user->id;
        $serviceGroup->name = $request->serviceGroupName;

        if ($serviceGroup->save())
            return redirect('servicegroup/index');
        return redirect('servicegroup/add')->withInput();
    }

    public function deleteServiceGroup($id)
    {
        $user = HomeController::getUserData();
        $serviceGroup = ServiceGroups::where('id', $id)->first();
        $serviceGroup->delete();

        $serviceGroupOrders = ServiceGroupOrders::where('service_group_id', $id)->get();
        foreach ($serviceGroupOrders as $order) {
            $order->delete();
        }

        return redirect('servicegroup/index');
    }

    public function ordersIndex($id)
    {
        $user = HomeController::getUserData();
        $serviceGroup = ServiceGroups::where('id', $id)->first();
        $totalAmount = 0;
        $serviceGroupOrders = ServiceGroupOrders::where('service_group_id', $serviceGroup->id)->get();
        foreach ($serviceGroupOrders as $order) {
            $reportList = AddReport::where('report_id', $order->report_id)->first();
            if ($reportList) {
                $totalAmount += $reportList->order_amount;
            }
        }
        $serviceGroup->totalAmount = $totalAmount;

        $serviceGroupOrders = DB::table('service_group_orders')
            ->selectRaw('*, (SELECT add_report.order_name FROM add_report WHERE add_report.report_id = service_group_orders.report_id) AS order_name')
            ->selectRaw('(SELECT add_report.order_amount FROM add_report WHERE add_report.report_id = service_group_orders.report_id) AS order_amount')
            ->where('service_group_id', $id)
            ->get();

        return view('admin.ServiceGroup.orders_index', compact('user', 'serviceGroupOrders', 'id', 'serviceGroup', 'totalAmount'));
    }

    public function addOrderIndex($id)
    {
        $user = HomeController::getUserData();
        return view('admin.ServiceGroup.add_order', compact('user', 'id'));
    }

    public function saveOrder($serviceGroupId, $reportId)
    {
        $user = HomeController::getUserData();

        $serviceGroupOrders = new ServiceGroupOrders();
        $serviceGroupOrders->created_by = $user->id;
        $serviceGroupOrders->service_group_id = $serviceGroupId;
        $serviceGroupOrders->report_id = $reportId;
        $serviceGroupOrders->save();

        return redirect('servicegroup/ordersindex/' . $serviceGroupId);
    }

    public function deleteServiceOrder($id, $serviceGroupId)
    {
        $user = HomeController::getUserData();
        $serviceGroupOrders = ServiceGroupOrders::where('id', $serviceGroupId)->first();
        $serviceGroupOrders->delete();

        return redirect('servicegroup/ordersindex/' . $serviceGroupId);
    }
}
