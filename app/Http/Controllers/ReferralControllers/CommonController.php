<?php

namespace App\Http\Controllers\ReferralControllers;

use App\Http\Controllers\Controller;

use App\Models\AddReport;
use App\Models\Doctors;
use App\Models\LabProfiles;
use App\Models\LocationOrderRates;
use App\Models\LocationProfileRates;
use App\Models\OrderType;
use App\Models\ReferralCompany;
use App\Models\SystemSettings;
use Session;


class CommonController extends Controller
{
    public $todayDate;
    public $settings;
    public $orderTypeProfile = "(profile)";

    public function __construct()
    {
        $this->todayDate = now()->toDateString();
        $this->settings = SystemSettings::first();
    }

    public static function getUserData()
    {
        $session = Session::get('ref_user');
        $user = ReferralCompany::find($session->id);
        $user->settings = SystemSettings::first();
        return $user;
    }

    public static function isUserLoggedIn()
    {
        return Session::has('ref_user');
    }

    public function searchOrders($search)
    {
        $user = $this->getUserData();
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

        if (!is_null($user->location) && !empty($user->location)) {
            $locationOrderRates = LocationOrderRates::where('location', $user->location)->get();
            $locationProfileRates = LocationProfileRates::where('location', $user->location)->get();

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
}
