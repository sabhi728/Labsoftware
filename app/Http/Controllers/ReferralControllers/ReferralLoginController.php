<?php

namespace App\Http\Controllers\ReferralControllers;

use App\Models\ReferralCompany;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ReferralLoginController extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $settings = $this->settings;
        return view('referral.login', compact('settings'));
    }

    public function loginUser(Request $request)
    {
        if (empty($request->email)) {
            return redirect()->back()->withErrors("Enter valid username")->withInput();
        }

        if (empty($request->password)) {
            return redirect()->back()->withErrors("Enter valid password")->withInput();
        }

        $username = $request->email;
        $password = $request->password;

        $isValidUser = ReferralCompany::where('username', $username)->first();

        if ($isValidUser) {
            if (Hash::check($password, $isValidUser->password)) {
                Session::put('ref_user', $isValidUser);
                return redirect('referralpanel/orderentry/index');
            } else {
                return redirect()->back()->withErrors("Password is not valid")->withInput();
            }
        } else {
            return redirect()->back()->withErrors("Username is not valid")->withInput();
        }
    }

    public function logout()
    {
        Session::remove('ref_user');
        return redirect('referralpanel/login');
    }
}
