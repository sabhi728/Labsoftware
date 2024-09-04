<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\LabProfiles;
use App\Models\LocationOrderRates;
use App\Models\LocationProfileRates;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\LabLocations;
use App\Models\SampleType;
use App\Models\OrderType;
use App\Models\IPBillingCategoryType;
use App\Models\ReportFormat;

use App\Models\AddReport;
use App\Models\Patients;
use App\Models\OrderEntry;
use App\Models\OrderEntryTransactions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\AdminControllers\HomeController;

class LocationsController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $labLocations = LabLocations::get();

        return view('admin.Locations.index', compact('user', 'labLocations'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.Locations.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $location = LabLocations::find($id);
        return view('admin.Locations.add', compact('user', 'location'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();

        $locationBillHeaderAttachment = "";
        if ($request->hasFile('locationBillHeader')) {
            $locationBillHeaderAttachment = $request->file('locationBillHeader');
            $randomName = Str::random(20);

            $extension = $locationBillHeaderAttachment->getClientOriginalExtension();
            $newFileName = $randomName . '.' . $extension;

            $locationBillHeaderAttachment->move(public_path('assets/uploads/locations'), $newFileName);
            $locationBillHeaderAttachment = 'assets/uploads/locations/' . $newFileName;
        } else {
            $locationBillHeaderAttachment = null;
        }

        $consultingBillHeaderAttachment = "";
        if ($request->hasFile('consultingBillHeader')) {
            $consultingBillHeaderAttachment = $request->file('consultingBillHeader');
            $randomName = Str::random(20);

            $extension = $consultingBillHeaderAttachment->getClientOriginalExtension();
            $newFileName = $randomName . '.' . $extension;

            $consultingBillHeaderAttachment->move(public_path('assets/uploads/locations'), $newFileName);
            $consultingBillHeaderAttachment = 'assets/uploads/locations/' . $newFileName;
        } else {
            $consultingBillHeaderAttachment = null;
        }

        $labLocation = new LabLocations();
        $labLocation->location_code = $request->locationCode;
        $labLocation->location_name = $request->locationName;
        $labLocation->tag_line = $request->tagLine;
        $labLocation->address = $request->address;
        $labLocation->phone_number = $request->phoneNumber;
        $labLocation->pnr_file_text = $request->pnrFileText;
        $labLocation->location_bill_header = $locationBillHeaderAttachment;
        $labLocation->consulting_bill_header = $consultingBillHeaderAttachment;
        if ($labLocation->save()) {
            return redirect('locations/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();

        $locationBillHeaderAttachment = "";
        if ($request->hasFile('locationBillHeader')) {
            $locationBillHeaderAttachment = $request->file('locationBillHeader');
            $randomName = Str::random(20);

            $extension = $locationBillHeaderAttachment->getClientOriginalExtension();
            $newFileName = $randomName . '.' . $extension;

            $locationBillHeaderAttachment->move(public_path('assets/uploads/locations'), $newFileName);
            $locationBillHeaderAttachment = 'assets/uploads/locations/' . $newFileName;
        }

        $consultingBillHeaderAttachment = "";
        if ($request->hasFile('consultingBillHeader')) {
            $consultingBillHeaderAttachment = $request->file('consultingBillHeader');
            $randomName = Str::random(20);

            $extension = $consultingBillHeaderAttachment->getClientOriginalExtension();
            $newFileName = $randomName . '.' . $extension;

            $consultingBillHeaderAttachment->move(public_path('assets/uploads/locations'), $newFileName);
            $consultingBillHeaderAttachment = 'assets/uploads/locations/' . $newFileName;
        }

        $labLocation = LabLocations::find($id);
        $labLocation->location_code = $request->locationCode;
        $labLocation->location_name = $request->locationName;
        $labLocation->tag_line = $request->tagLine;
        $labLocation->address = $request->address;
        $labLocation->phone_number = $request->phoneNumber;
        $labLocation->pnr_file_text = $request->pnrFileText;
        if (!empty($locationBillHeaderAttachment))
            $labLocation->location_bill_header = $locationBillHeaderAttachment;
        if (!empty($consultingBillHeaderAttachment))
            $labLocation->consulting_bill_header = $consultingBillHeaderAttachment;
        if ($labLocation->save()) {
            return redirect('locations/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $labLocation = LabLocations::find($id);
        $labLocation->delete();
        return redirect()->back();
    }

    public function indexOrderRates($location)
    {
        $user = HomeController::getUserData();
        $locationOrderRates = LocationOrderRates::where('location', $location)
            ->select('*')
            ->selectRaw('(SELECT add_report.order_name FROM add_report WHERE add_report.report_id = location_order_rates.report) as order_name')
            ->selectRaw('(SELECT add_report.order_amount FROM add_report WHERE add_report.report_id = location_order_rates.report) as regular_amount')
            ->get();

        $locationName = LabLocations::where('id', $location)->first()->location_name;

        return view('admin.Locations.OrderRates.index', compact('user', 'locationOrderRates', 'location', 'locationName'));
    }

    public function addOrderRatesIndex($location)
    {
        $user = HomeController::getUserData();
        $orderDetails = AddReport::get();
        return view('admin.Locations.OrderRates.add', compact('user', 'location', 'orderDetails'));
    }

    public function editOrderRatesIndex($location, $id)
    {
        $user = HomeController::getUserData();
        $locationName = LabLocations::where('id', $location)->first()->location_name;

        $locationOrderRate = LocationOrderRates::where('id', $id)
            ->select('*')
            ->selectRaw('(SELECT add_report.order_name FROM add_report WHERE add_report.report_id = location_order_rates.report) as order_name')
            ->selectRaw('(SELECT add_report.order_amount FROM add_report WHERE add_report.report_id = location_order_rates.report) as regular_amount')
            ->first();

        return view('admin.Locations.OrderRates.update', compact('user', 'location', 'locationOrderRate', 'locationName'));
    }

    public function addOrderRates($location, Request $request)
    {
        $user = HomeController::getUserData();

        $locationOrderRates = new LocationOrderRates();
        $locationOrderRates->location = $location;
        $locationOrderRates->report = $request->order;
        $locationOrderRates->amount = $request->amount;

        if ($locationOrderRates->save()) {
            return redirect('locations/order_rates/index/' . $location);
        }

        return redirect()->back()->withInput();
    }

    public function updateOrderRates($location, $id, Request $request)
    {
        $user = HomeController::getUserData();

        $locationOrderRates = LocationOrderRates::find($id);
        $locationOrderRates->amount = $request->amount;

        if ($locationOrderRates->save()) {
            return redirect('locations/order_rates/index/' . $location);
        }

        return redirect()->back()->withInput();
    }

    public function deleteOrderRates($location)
    {
        $user = HomeController::getUserData();
        $locationOrderRates = LocationOrderRates::find($location);
        $locationOrderRates->delete();
        return redirect()->back();
    }

    public function indexProfileRates($location)
    {
        $user = HomeController::getUserData();
        $locationProfileRates = LocationProfileRates::where('location', $location)
            ->select('*')
            ->selectRaw('(SELECT lab_profiles.name FROM lab_profiles WHERE lab_profiles.id = location_profile_rates.profile) as profile_name')
            ->selectRaw('(SELECT lab_profiles.amount FROM lab_profiles WHERE lab_profiles.id = location_profile_rates.profile) as regular_amount')
            ->get();

        $locationName = LabLocations::where('id', $location)->first()->location_name;

        return view('admin.Locations.ProfileRates.index', compact('user', 'locationProfileRates', 'location', 'locationName'));
    }

    public function addProfileRatesIndex($location)
    {
        $user = HomeController::getUserData();
        $labProfiles = LabProfiles::get();
        return view('admin.Locations.ProfileRates.add', compact('user', 'location', 'labProfiles'));
    }

    public function editProfileRatesIndex($location, $id)
    {
        $user = HomeController::getUserData();
        $locationName = LabLocations::where('id', $location)->first()->location_name;

        $locationProfileRate = LocationProfileRates::where('id', $id)
            ->select('*')
            ->selectRaw('(SELECT lab_profiles.name FROM lab_profiles WHERE lab_profiles.id = location_profile_rates.profile) as profile_name')
            ->selectRaw('(SELECT lab_profiles.amount FROM lab_profiles WHERE lab_profiles.id = location_profile_rates.profile) as regular_amount')
            ->first();

        return view('admin.Locations.ProfileRates.update', compact('user', 'location', 'locationProfileRate', 'locationName'));
    }

    public function addProfileRates($location, Request $request)
    {
        $user = HomeController::getUserData();

        $locationProfileRates = new LocationProfileRates();
        $locationProfileRates->location = $location;
        $locationProfileRates->profile = $request->profile;
        $locationProfileRates->amount = $request->amount;

        if ($locationProfileRates->save()) {
            return redirect('locations/profile_rates/index/' . $location);
        }

        return redirect()->back()->withInput();
    }

    public function updateProfileRates($location, $id, Request $request)
    {
        $user = HomeController::getUserData();

        $locationProfileRates = LocationProfileRates::find($id);
        $locationProfileRates->amount = $request->amount;

        if ($locationProfileRates->save()) {
            return redirect('locations/profile_rates/index/' . $location);
        }

        return redirect()->back()->withInput();
    }

    public function deleteProfileRates($location)
    {
        $user = HomeController::getUserData();
        $locationProfileRates = LocationProfileRates::find($location);
        $locationProfileRates->delete();
        return redirect()->back();
    }

    public function removeLocationHeaderFile($location, $which)
    {
        $user = HomeController::getUserData();

        if ($user->role == "Admin") {
            $labLocation = LabLocations::find($location);

            if ($which == "bill") {
                $labLocation->location_bill_header = NULL;
            } else if ($which == "consulting") {
                $labLocation->consulting_bill_header = NULL;
            }

            $labLocation->save();
        }

        return redirect()->back();
    }
}
