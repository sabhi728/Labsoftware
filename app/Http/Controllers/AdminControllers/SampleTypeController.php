<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Session;

use App\Models\SampleType;
use App\Models\OrderType;
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

class SampleTypeController extends Controller
{
    public function index()
    {
        $user = HomeController::getUserData();
        $sampleType = SampleType::get();
        return view('admin.SampleType.index', compact('user', 'sampleType'));
    }

    public function addIndex()
    {
        $user = HomeController::getUserData();
        return view('admin.SampleType.add', compact('user'));
    }

    public function editIndex($id)
    {
        $user = HomeController::getUserData();
        $sampleType = SampleType::find($id);
        return view('admin.SampleType.add', compact('user', 'sampleType'));
    }

    public function add(Request $request)
    {
        $user = HomeController::getUserData();
        $sample = new SampleType();
        $sample->name = $request->sampleName;

        if ($sample->save()) {
            return redirect('sampletype/index');
        }
        return redirect()->back()->withInput();
    }

    public function update($id, Request $request)
    {
        $user = HomeController::getUserData();
        $sample = SampleType::find($id);
        $sample->name = $request->sampleName;

        if ($sample->save()) {
            return redirect('sampletype/index');
        }
        return redirect()->back()->withInput();
    }

    public function delete($id)
    {
        $user = HomeController::getUserData();
        $sample = SampleType::find($id);
        $sample->delete();
        return redirect()->back();
    }
}
