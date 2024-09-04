<?php

use App\Http\Controllers\AdminControllers\PrescriptionsController;
use App\Http\Controllers\AdminControllers\ReferralCompanyController;
use App\Http\Middleware\ReferralMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

use App\Http\Controllers\AdminControllers\HomeController;
use App\Http\Controllers\AdminControllers\OrderMaintenance;
use App\Http\Controllers\AdminControllers\ServiceGroupController;
use App\Http\Controllers\AdminControllers\OrderEntryController;
use App\Http\Controllers\AdminControllers\SampleBarcodesController;
use App\Http\Controllers\AdminControllers\OrderBillsController;
use App\Http\Controllers\AdminControllers\LocationsController;
use App\Http\Controllers\AdminControllers\IPCertificateController;
use App\Http\Controllers\AdminControllers\DepartmentMaintenanceController;
use App\Http\Controllers\AdminControllers\DoctorsController;
use App\Http\Controllers\AdminControllers\LabUserController;
use App\Http\Controllers\AdminControllers\SampleTypeController;
use App\Http\Controllers\AdminControllers\ReportsController;
use App\Http\Controllers\AdminControllers\LabProfileController;

use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LoginController::class, 'index'])->name("login");

Route::get('/login', [LoginController::class, 'index'])->name("login");
Route::get('/logout', [LoginController::class, 'logout'])->name("logout");

Route::post('/login', [LoginController::class, 'loginUser'])->name("loginUser");
// Route::get('/routeAdd', [LoginController::class, 'routeAdd'])->name("routeAdd");

// Route::get('/viewbill/{bill_no}/{order_no}', [OrderBillsController::class, 'userViewResult'])->name("viewbill");
Route::get('/viewbill/{bill_no}', [OrderBillsController::class, 'userViewResult'])->name("viewbill");

Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/edit_profile', [LoginController::class, 'editProfile'])->name("edit_profile");
    Route::get('/remove_header_file/{which}', [LoginController::class, 'removeHeaderFile'])->name("remove_header_file");

    Route::post('/change_password', [LoginController::class, 'changePassword'])->name("change_password");
    Route::post('/update_profile', [LoginController::class, 'updateProfile'])->name("update_profile");

    Route::get('/home', [HomeController::class, 'index'])->name("home");
    Route::get('/dashboard', [HomeController::class, 'indexDashbaord'])->name("dashboard");

    Route::get('/send_dispatch_sms/{bill_no}/{order_no}', [HomeController::class, 'sendDispatchSMS'])->name("send_dispatch_sms");
    Route::get('/send_all_dispatch_sms/{bill_no}/{order_ids}', [HomeController::class, 'sendAllDispatchSMS'])->name("send_all_dispatch_sms");
    Route::get('/send_payment_reminder/{bill_no}', [HomeController::class, 'sendPaymentReminder'])->name("send_payment_reminder");

    Route::get('/order_maintenance', [OrderMaintenance::class, 'index'])->name("order_maintenance");
    Route::get('/add_order', [OrderMaintenance::class, 'addOrderIndex'])->name("add_order");
    Route::get('/update_order/{id}', [OrderMaintenance::class, 'updateOrderIndex'])->name("update_order");
    Route::post('/add_order', [OrderMaintenance::class, 'addOrder'])->name("add_order");

    Route::post('/update_order/{id}', [OrderMaintenance::class, 'updateOrder'])->name("update_order");
    Route::get('/delete_order/{id}', [OrderMaintenance::class, 'deleteOrder'])->name("delete_order");

    Route::get('/order_details/{order_id}', [OrderMaintenance::class, 'orderDetailsIndex'])->name("order_details");
    Route::get('/order_detail_delete/{id}', [OrderMaintenance::class, 'orderDetailsDelete'])->name("order_detail_delete");

    Route::post('/update-position/{orderDetailId}/{direction}', [OrderMaintenance::class, 'updatePosition']);

    Route::get('/add_order_details/{order_id}', [OrderMaintenance::class, 'addOrderDetailsIndex'])->name("add_order_details");
    Route::get('/update_order_details/{order_id}/{update_id}', [OrderMaintenance::class, 'updateOrderDetailsIndex'])->name("update_order_details");
    Route::post('/add_order_details/{order_id}', [OrderMaintenance::class, 'addOrderDetails'])->name("add_order_details");
    Route::post('/update_order_details/{order_id}/{update_id}', [OrderMaintenance::class, 'updateOrderDetails'])->name("update_order_details");

    Route::get('/order_detail_values/{order_id}/{order_details_id}', [OrderMaintenance::class, 'orderDetailValuesIndex'])->name("order_detail_values");
    Route::post('/order_detail_values/{order_id}/{order_details_id}', [OrderMaintenance::class, 'addOrderDetailValue'])->name("order_detail_values");
    Route::delete('/order_detail_values_delete/{id}', [OrderMaintenance::class, 'deleteOrderDetailValue'])->name("order_detail_values_delete");

    Route::get('/add_components_of_existing_order/{id}', [OrderMaintenance::class, 'addComponentsOfExistingOrderIndex'])->name("add_components_of_existing_order");
    Route::get('/save_components_of_existing_order/{report_id}/{ids}', [OrderMaintenance::class, 'saveComponentsOfExistingOrder'])->name("save_components_of_existing_order");
    Route::get('/search_orders/{search}', [OrderMaintenance::class, 'searchOrders'])->name("search_orders");
    Route::get('/search_doctors/{search}', [OrderMaintenance::class, 'searchDoctors'])->name("search_doctors");
    Route::get('/search_locations/{search}', [OrderMaintenance::class, 'searchLocations'])->name("search_locations");
    Route::get('/get_order_details/{report_id}', [OrderMaintenance::class, 'getOrderDetails'])->name("get_order_details");

    Route::get('/order_template/{report_id}', [OrderMaintenance::class, 'orderTemplateIndex'])->name("order_template");
    Route::get('/add_order_template/{report_id}', [OrderMaintenance::class, 'addOrderTemplateIndex'])->name("add_order_template");
    Route::get('/update_order_template/{report_id}/{update_id}', [OrderMaintenance::class, 'updateOrderTemplateIndex'])->name("update_order_template");
    Route::post('/add_order_template/{report_id}', [OrderMaintenance::class, 'addOrderTemplate'])->name("add_order_template");
    Route::post('/update_order_template/{report_id}/{update_id}', [OrderMaintenance::class, 'updateOrderTemplate'])->name("update_order_template");

    Route::get('/template_order_details/{order_id}/{template_id}', [OrderMaintenance::class, 'templateOrderDetailsIndex'])->name("template_order_details");
    Route::get('/add_template_order_details/{order_id}/{template_id}', [OrderMaintenance::class, 'addTemplateOrderDetailsIndex'])->name("add_template_order_details");
    Route::get('/update_template_order_details/{order_id}/{template_id}/{update_id}', [OrderMaintenance::class, 'updateTemplateOrderDetailsIndex'])->name("update_template_order_details");
    Route::post('/add_template_order_details/{order_id}/{template_id}', [OrderMaintenance::class, 'addTemplateOrderDetails'])->name("add_template_order_details");
    Route::post('/update_template_order_details/{order_id}/{template_id}/{update_id}', [OrderMaintenance::class, 'updateTemplateOrderDetails'])->name("update_template_order_details");

    //Service Group Routes
    Route::prefix('servicegroup')->group(function () {
        Route::get('/index', [ServiceGroupController::class, 'index'])->name("index");
        Route::get('/add', [ServiceGroupController::class, 'addIndex'])->name("add");
        Route::get('/update/{id}', [ServiceGroupController::class, 'updateIndex'])->name("update");
        Route::post('/add', [ServiceGroupController::class, 'addToDb'])->name("add");
        Route::post('/update/{id}', [ServiceGroupController::class, 'updateToDb'])->name("update");
        Route::get('/delete/{id}', [ServiceGroupController::class, 'deleteServiceGroup'])->name("delete");

        Route::get('/ordersindex/{id}', [ServiceGroupController::class, 'ordersIndex'])->name("ordersindex");
        Route::get('/add_service_order/{id}', [ServiceGroupController::class, 'addOrderIndex'])->name("add_service_order");
        Route::get('/save_order/{service_group_id}/{report_id}', [ServiceGroupController::class, 'saveOrder'])->name("save_order");
        Route::get('/delete_order/{service_group_id}/{id}', [ServiceGroupController::class, 'deleteServiceOrder'])->name("delete_order");
    });

    //Order Entry Routes
    Route::prefix('orderentry')->group(function () {
        Route::get('/index', [OrderEntryController::class, 'index'])->name("index");
        Route::get('/index/{search}', [OrderEntryController::class, 'indexSearch'])->name("index");

        Route::get('/add_patient', [OrderEntryController::class, 'addPatientIndex'])->name("add_patient");
        Route::post('/add_new_patient', [OrderEntryController::class, 'addPatient'])->name("add_new_patient");

        Route::get('/search_patient/{search}', [OrderEntryController::class, 'searchPatient'])->name("search_patient");

        Route::post('/add_order_entry', [OrderEntryController::class, 'addOrderEntry'])->name("add_order_entry");
        Route::post('/update_order_entry/{bill_no}', [OrderEntryController::class, 'updateOrderEntry'])->name("update_order_entry");

        Route::get('/bill_details/{bill_no}', [OrderEntryController::class, 'indexBillDetails'])->name("bill_details");
        Route::get('/cancel_bill/{bill_no}', [OrderEntryController::class, 'cancelBill'])->name("cancel_bill");
    });

    //Sample Barcodes Route
    Route::prefix('samplebarcodes')->group(function () {
        Route::get('/sample_collection', [SampleBarcodesController::class, 'sampleCollectionIndex'])->name("sample_collection");
        Route::get('/sample_collection_details', [SampleBarcodesController::class, 'sampleCollectionDetailsIndex'])->name("sample_collection_details");
        Route::get('/sample_collection_search/{search}', [SampleBarcodesController::class, 'sampleCollectionSearch'])->name("sample_collection_search");
        Route::get('/generate_sample_barcode/{bill_no}/{sample_type}/{order_ids}/{barcode}', [SampleBarcodesController::class, 'generateSampleBarcode'])->where('sample_type', '.*')->name("generate_sample_barcode");
        Route::get('/regenerate_sample_barcode/{bill_no}/{sample_type}/{reason}', [SampleBarcodesController::class, 'regenerateSampleBarcode'])->where('sample_type', '.*')->name("regenerate_sample_barcode");

        Route::get('/sample_receival', [SampleBarcodesController::class, 'sampleReceivalIndex'])->name("sample_receival");
        Route::get('/search_order_with_barcode/{barcode}', [SampleBarcodesController::class, 'searchOrderWithBarcode'])->name("search_order_with_barcode");

        Route::get('/update_sample_barcode_status/{barcode}/{status}/{reject_reason}', [SampleBarcodesController::class, 'updateSampleBarcodeStatus'])->name("update_sample_barcode_status");
        Route::get('/update_sample_barcode_status/{barcode}/{status}', [SampleBarcodesController::class, 'updateSampleBarcodeStatus'])->name("update_sample_barcode_status");
    });

    //Order Bills Route
    Route::prefix('orderbills')->group(function () {
        Route::get('/in_process_bills', [OrderBillsController::class, 'inProcessBillsIndex'])->name("in_process_bills");
        Route::get('/approve_reports', [OrderBillsController::class, 'approveReports'])->name("approve_reports");
        Route::get('/completed_bills', [OrderBillsController::class, 'completedBillsIndex'])->name("completed_bills");
        Route::get('/previous_bills', [OrderBillsController::class, 'previousBillsIndex'])->name("previous_bills");
        Route::get('/cancelled_bills', [OrderBillsController::class, 'cancelledBillsIndex'])->name("cancelled_bills");

        Route::get('/bill_details/{bill_no}', [OrderBillsController::class, 'billDetailsIndex'])->name("bill_details");
        Route::get('/approve_report_details/{bill_no}/{report_no}', [OrderBillsController::class, 'approveReportDetailsIndex'])->name("approve_report_details");
        Route::get('/completed_bill_details/{bill_no}', [OrderBillsController::class, 'completedBillDetailsIndex'])->name("completed_bill_details");
        Route::get('/previous_bill_details/{bill_no}', [OrderBillsController::class, 'previousBillDetailsIndex'])->name("previous_bill_details");

        Route::get('/result_entry/{bill_no}/{order_no}', [OrderBillsController::class, 'resultEntryIndex'])->name("result_entry");
        Route::get('/edit_result_entry/{bill_no}/{order_no}', [OrderBillsController::class, 'editResultEntryIndex'])->name("edit_result_entry");
        Route::post('/return_order_amount', [OrderBillsController::class, 'returnOrderAmount'])->name("return_order_amount");

        Route::get('/next_result_entry/{bill_no}/{order_no}', [OrderBillsController::class, 'nextResultEntry'])->name("next_result_entry");
        Route::get('/print_result/{bill_no}/{order_no}', [OrderBillsController::class, 'printResult'])->name("print_result");
        Route::get('/change_result/{bill_no}/{order_no}', [OrderBillsController::class, 'changeResult'])->name("change_result");
        Route::get('/result_preview/{bill_no}/{order_no}', [OrderBillsController::class, 'previewResult'])->name("result_preview");

        Route::get('/update_report_status/{bill_no}/{order_no}/{status}', [OrderBillsController::class, 'updateReportStatus'])->name("update_report_status");
        Route::get('/next_report_for_approval/{bill_no}/{order_no}', [OrderBillsController::class, 'nextReportForApproval'])->name("next_report_for_approval");

        Route::get('/result_attachments/{bill_no}/{order_no}', [OrderBillsController::class, 'resultAttachmentsIndex'])->name("result_attachments");
        Route::post('/add_result_attachments', [OrderBillsController::class, 'addAttachment'])->name("add_result_attachments");
        Route::get('/delete_result_attachments/{id}', [OrderBillsController::class, 'deleteAttachment'])->name("delete_result_attachments");

        Route::post('/save_result', [OrderBillsController::class, 'saveResult'])->name("save_result");
        Route::post('/update_patient', [OrderBillsController::class, 'updatePatient'])->name("update_patient");

        Route::get('/result_dispatch/{bill_no}', [OrderBillsController::class, 'resultDispatchIndex'])->name("result_dispatch");
        Route::get('/result_dispatched/{bill_no}/{report_id}', [OrderBillsController::class, 'resultDispatched'])->name("result_dispatched");

        Route::get('/result_all_dispatched/{bill_no}/{order_ids}', [OrderBillsController::class, 'resultAllDispatched'])->name("result_all_dispatched");
        Route::get('/dispatch_page_go_back/{bill_no}', [OrderBillsController::class, 'dispatchPageGoBack'])->name("dispatch_page_go_back");
        Route::get('/result_all_dispatched_completed_bills/{bill_no}', [OrderBillsController::class, 'resultAllDispatchedCompletedBills'])->name("result_all_dispatched_completed_bills");

        Route::get('/get_order_entry_data/{bill_no}', [OrderBillsController::class, 'getOrderEntryData'])->name("get_order_entry_data");
        Route::get('/get_order_transactions_data/{bill_no}', [OrderBillsController::class, 'getOrderEntryTransactionsData'])->name("get_order_transactions_data");
        Route::post('/save_bill_payment', [OrderBillsController::class, 'saveBillPayment'])->name("save_bill_payment");

        Route::get('/get_report_dates/{bill_no}/{report_id}', [OrderBillsController::class, 'getReportDates'])->name("get_report_dates");
        Route::post('/update_report_dates', [OrderBillsController::class, 'updateReportDates'])->name("update_report_dates");

        Route::post('/send_report_whatsapp', [OrderBillsController::class, 'sendReportWhatsapp'])->name("send_report_whatsapp");
        Route::post('/send_selected_report_whatsapp', [OrderBillsController::class, 'sendSelectedReportWhatsapp'])->name("send_selected_report_whatsapp");
        Route::post('/send_bill_whatsapp', [OrderBillsController::class, 'sendBillWhatsapp'])->name("send_bill_whatsapp");

        Route::get('/hard_refresh_report_results/{bill_no}', [OrderBillsController::class, 'hardRefreshResultReports'])->name("hard_refresh_report_results");
        Route::get('/hard_refresh_report_result/{bill_no}/{order_no}', [OrderBillsController::class, 'hardRefreshResultReport'])->name("hard_refresh_report_result");

        Route::get('/search_order_detail_values/{component_id}/{search}', [OrderBillsController::class, 'searchOrderDetailValues'])->name("search_order_detail_values");
        Route::get('/search_order_detail_values/{component_id}', [OrderBillsController::class, 'searchOrderDetailValues'])->name("search_order_detail_values");
    });

    //Locations Route
    Route::prefix('locations')->group(function () {
        Route::get('/index', [LocationsController::class, 'index'])->name("index");
        Route::get('/add', [LocationsController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [LocationsController::class, 'editIndex'])->name("edit");

        Route::post('/add', [LocationsController::class, 'add'])->name("add");
        Route::post('/update/{id}', [LocationsController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [LocationsController::class, 'delete'])->name("delete");
        Route::get('/remove_location_header_file/{location}/{which}', [LocationsController::class, 'removeLocationHeaderFile'])->name("remove_location_header_file");

        Route::prefix('order_rates')->group(function () {
            Route::get('/index/{location}', [LocationsController::class, 'indexOrderRates'])->name("index");
            Route::get('/add/{location}', [LocationsController::class, 'addOrderRatesIndex'])->name("add");
            Route::get('/edit/{location}/{id}', [LocationsController::class, 'editOrderRatesIndex'])->name("edit");

            Route::post('/add/{location}', [LocationsController::class, 'addOrderRates'])->name("add");
            Route::post('/update/{location}/{id}', [LocationsController::class, 'updateOrderRates'])->name("update");

            Route::get('/delete/{location}', [LocationsController::class, 'deleteOrderRates'])->name("delete");
        });

        Route::prefix('profile_rates')->group(function () {
            Route::get('/index/{location}', [LocationsController::class, 'indexProfileRates'])->name("index");
            Route::get('/add/{location}', [LocationsController::class, 'addProfileRatesIndex'])->name("add");
            Route::get('/edit/{location}/{id}', [LocationsController::class, 'editProfileRatesIndex'])->name("edit");

            Route::post('/add/{location}', [LocationsController::class, 'addProfileRates'])->name("add");
            Route::post('/update/{location}/{id}', [LocationsController::class, 'updateProfileRates'])->name("update");

            Route::get('/delete/{location}', [LocationsController::class, 'deleteProfileRates'])->name("delete");
        });
    });

    //IPCertificate Route
    Route::prefix('ip_certificate')->group(function () {
        Route::get('/index', [IPCertificateController::class, 'index'])->name("index");
        Route::get('/add', [IPCertificateController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [IPCertificateController::class, 'editIndex'])->name("edit");

        Route::post('/add', [IPCertificateController::class, 'add'])->name("add");
        Route::post('/update/{id}', [IPCertificateController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [IPCertificateController::class, 'delete'])->name("delete");
    });

    //Prescriptions Route
    Route::prefix('prescriptions')->group(function () {
        Route::get('/index', [PrescriptionsController::class, 'index'])->name("index");
        Route::get('/add', [PrescriptionsController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [PrescriptionsController::class, 'editIndex'])->name("edit");

        Route::post('/add', [PrescriptionsController::class, 'add'])->name("add");
        Route::post('/update/{id}', [PrescriptionsController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [PrescriptionsController::class, 'delete'])->name("delete");

        Route::post('/add_result_attachments/{prescription_id}', [PrescriptionsController::class, 'addAttachment'])->name("add_result_attachments");
        Route::get('/delete_result_attachments/{prescription_id}', [PrescriptionsController::class, 'deleteAttachment'])->name("delete_result_attachments");
    });

    //Department Maintenance Route
    Route::prefix('department')->group(function () {
        Route::get('/index', [DepartmentMaintenanceController::class, 'index'])->name("index");
        Route::get('/no_department', [DepartmentMaintenanceController::class, 'noDepartmentIndex'])->name("no_department");
        Route::get('/signatures_list_index/{department_id}', [DepartmentMaintenanceController::class, 'signaturesListIndex'])->name("signatures_list_index");

        Route::get('/add', [DepartmentMaintenanceController::class, 'addIndex'])->name("add");
        Route::get('/signature_add/{department_id}', [DepartmentMaintenanceController::class, 'signatureAddIndex'])->name("signature_add");

        Route::get('/edit/{id}', [DepartmentMaintenanceController::class, 'editIndex'])->name("edit");
        Route::get('/department_edit/{id}', [DepartmentMaintenanceController::class, 'signatureEditIndex'])->name("department_edit");

        Route::post('/add', [DepartmentMaintenanceController::class, 'add'])->name("add");
        Route::post('/signature_add/{department_id}', [DepartmentMaintenanceController::class, 'signatureAdd'])->name("signature_add");

        Route::post('/update/{id}', [DepartmentMaintenanceController::class, 'update'])->name("update");
        Route::post('/signature_update/{id}', [DepartmentMaintenanceController::class, 'signatureUpdate'])->name("signature_update");

        Route::get('/delete/{id}', [DepartmentMaintenanceController::class, 'delete'])->name("delete");
        Route::get('remove_department_signature_image/{position}/{id}', [DepartmentMaintenanceController::class, 'removeDepartmentSignatureImage'])->name("remove_department_signature_image");
        Route::get('remove_signature_image/{position}/{id}', [DepartmentMaintenanceController::class, 'removeSignatureImage'])->name("remove_signature_image");
    });

    //Doctors Route
    Route::prefix('doctors')->group(function () {
        Route::get('/index', [DoctorsController::class, 'index'])->name("index");
        Route::get('/add', [DoctorsController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [DoctorsController::class, 'editIndex'])->name("edit");

        Route::post('/add', [DoctorsController::class, 'add'])->name("add");
        Route::post('/update/{id}', [DoctorsController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [DoctorsController::class, 'delete'])->name("delete");
    });

    //Sample Type Route
    Route::prefix('sampletype')->group(function () {
        Route::get('/index', [SampleTypeController::class, 'index'])->name("index");
        Route::get('/add', [SampleTypeController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [SampleTypeController::class, 'editIndex'])->name("edit");

        Route::post('/add', [SampleTypeController::class, 'add'])->name("add");
        Route::post('/update/{id}', [SampleTypeController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [SampleTypeController::class, 'delete'])->name("delete");
    });

    //Lab User Route
    Route::prefix('labuser')->group(function () {
        Route::get('/index', [LabUserController::class, 'index'])->name("index");
        Route::get('/roles_index', [LabUserController::class, 'rolesIndex'])->name("roles_index");

        Route::get('/add', [LabUserController::class, 'addIndex'])->name("add");
        Route::get('/role_add', [LabUserController::class, 'roleAddIndex'])->name("role_add");

        Route::get('/edit/{id}', [LabUserController::class, 'editIndex'])->name("edit");
        Route::get('/role_edit/{id}', [LabUserController::class, 'roleEditIndex'])->name("role_edit");

        Route::post('/add', [LabUserController::class, 'add'])->name("add");
        Route::post('/role_add', [LabUserController::class, 'roleAdd'])->name("role_add");

        Route::post('/update/{id}', [LabUserController::class, 'update'])->name("update");
        Route::post('/update_password/{id}', [LabUserController::class, 'updatePassword'])->name("update_password");
        Route::post('/role_update/{id}', [LabUserController::class, 'roleUpdate'])->name("role_update");

        Route::get('/delete/{id}', [LabUserController::class, 'delete'])->name("delete");
        Route::get('/role_delete/{id}', [LabUserController::class, 'roleDelete'])->name("role_delete");
    });

    //Reports Route
    Route::prefix('reports')->group(function () {
        Route::prefix('login_report')->group(function () {
            Route::get('/index', [ReportsController::class, 'loginReportIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'loginReportIndexData'])->name("report");
        });

        Route::prefix('bill_reports')->group(function () {
            Route::get('/index', [ReportsController::class, 'billReportsIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'billReportsIndexData'])->name("report");
        });

        Route::prefix('order_summary_reports')->group(function () {
            Route::get('/index', [ReportsController::class, 'orderSummaryIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'orderSummaryIndexData'])->name("report");
        });

        Route::prefix('shift_collection')->group(function () {
            Route::get('/index', [ReportsController::class, 'shiftCollectionIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'shiftCollectionIndexData'])->name("report");
        });

        Route::prefix('collection')->group(function () {
            Route::get('/index', [ReportsController::class, 'collectionIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'collectionIndexData'])->name("report");
        });

        Route::prefix('doctor_reports')->group(function () {
            Route::get('/index', [ReportsController::class, 'doctorReportsIndex'])->name("index");
            Route::post('/report', [ReportsController::class, 'doctorReportsData'])->name("report");
        });
    });

    //Lab Profile Route
    Route::prefix('lab_profile')->group(function () {
        Route::get('/index', [LabProfileController::class, 'index'])->name("index");
        Route::get('/profile_details/{id}', [LabProfileController::class, 'profileDetailsIndex'])->name("profile_details");
        Route::get('/sms', [LabProfileController::class, 'smsIndex'])->name("sms");

        Route::get('/add', [LabProfileController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [LabProfileController::class, 'editIndex'])->name("edit");

        Route::post('/add', [LabProfileController::class, 'add'])->name("add");
        Route::post('/add_profile_details', [LabProfileController::class, 'addProfileDetails'])->name("add_profile_details");

        Route::post('/update/{id}', [LabProfileController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [LabProfileController::class, 'delete'])->name("delete");
        Route::delete('/delete_profile_detail/{id}', [LabProfileController::class, 'deleteProfileDetail'])->name("delete_profile_detail");

        Route::get('/send_offer_message/{phone_numbers}/{message}', [LabProfileController::class, 'sendOfferMessage'])->name("send_offer_message");
        Route::get('/letterhad', [LabProfileController::class, 'letterhad'])->name("letterhad");
    });

    //Referral Company Route
    Route::prefix('referral_company')->group(function () {
        Route::get('/index', [ReferralCompanyController::class, 'index'])->name("index");
        Route::get('/add', [ReferralCompanyController::class, 'addIndex'])->name("add");
        Route::get('/edit/{id}', [ReferralCompanyController::class, 'editIndex'])->name("edit");

        Route::post('/add', [ReferralCompanyController::class, 'add'])->name("add");
        Route::post('/update/{id}', [ReferralCompanyController::class, 'update'])->name("update");

        Route::get('/delete/{id}', [ReferralCompanyController::class, 'delete'])->name("delete");
    });
});

Route::prefix('referralpanel')->group(function () {
    Route::get('/', [\App\Http\Controllers\ReferralControllers\ReferralLoginController::class, 'index']);

    Route::get('/login', [\App\Http\Controllers\ReferralControllers\ReferralLoginController::class, 'index']);
    Route::post('/login', [\App\Http\Controllers\ReferralControllers\ReferralLoginController::class, 'loginUser'])->name("loginReferralUser");

    Route::get('/logout', [\App\Http\Controllers\ReferralControllers\ReferralLoginController::class, 'logout'])->name("logoutReferralUser");

    Route::middleware([ReferralMiddleware::class])->group(function () {
        Route::get('/home', [\App\Http\Controllers\ReferralControllers\HomeController::class, 'index']);
        Route::get('/contact-us', [\App\Http\Controllers\ReferralControllers\HomeController::class, 'indexContactUs']);
        Route::get('/viewbill/{bill_no}/{order_no}/{with_header}', [\App\Http\Controllers\ReferralControllers\HomeController::class, 'userViewResult']);
        Route::get('/mark-result-as-printed/{bill_no}/{order_no}', [\App\Http\Controllers\ReferralControllers\HomeController::class, 'markResultAsPrinted']);

        // Route::get('/submitted-sample', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'indexSubmittedSamples']);
        // Route::post('/submitted-sample', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'searchSubmittedSamples']);
        Route::get('/submitted-sample', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'searchSubmittedSamples']);

        // Route::get('/sample-status', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'indexSampleStatus']);
        // Route::post('/sample-status', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'searchSampleStatus']);
        Route::get('/sample-status', [\App\Http\Controllers\ReferralControllers\TransactionsController::class, 'searchSampleStatus']);

        Route::get('/search_orders/{search}', [\App\Http\Controllers\ReferralControllers\CommonController::class, 'searchOrders'])->name("search_orders");
        Route::get('/search_doctors/{search}', [\App\Http\Controllers\ReferralControllers\CommonController::class, 'searchDoctors'])->name("search_doctors");

        //Order Entry Routes
        Route::prefix('orderentry')->group(function () {
            Route::get('/index', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'index'])->name("index");
            Route::get('/index/{search}', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'indexSearch'])->name("index");

            Route::get('/add_patient', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'addPatientIndex'])->name("add_patient");
            Route::post('/add_new_patient', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'addPatient'])->name("add_new_patient");

            Route::get('/search_patient/{search}', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'searchPatient'])->name("search_patient");

            Route::post('/add_order_entry', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'addOrderEntry'])->name("add_order_entry");
            Route::post('/update_order_entry/{bill_no}', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'updateOrderEntry'])->name("update_order_entry");

            Route::get('/bill_details/{bill_no}', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'indexBillDetails'])->name("bill_details");
            Route::get('/cancel_bill/{bill_no}', [\App\Http\Controllers\ReferralControllers\OrderEntryController::class, 'cancelBill'])->name("cancel_bill");
        });

        Route::prefix('reports')->group(function () {
            Route::prefix('bill-reports')->group(function () {
                Route::get('/index', [\App\Http\Controllers\ReferralControllers\ReportsController::class, 'billReportsIndex'])->name("index");
                Route::post('/report', [\App\Http\Controllers\ReferralControllers\ReportsController::class, 'billReportsIndexData'])->name("report");
            });
        });
    });
});
