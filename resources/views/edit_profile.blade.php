<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/add_order.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .removeBtn {
        background: red;
        text-align: center;
        padding: 10px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
    }
</style>
<body>
    <div class="header">
        @include('include.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('include.sidebar')
        </div>
        <div class="main">
            <form id="formBack" action="{{ url('update_profile') }}" method="post" enctype="multipart/form-data">
                @csrf
                <h1 style="padding-bottom: 20px;font-size: 20px;color:gray;">Edit Profile</h1>
                <div class="inputBack">
                    <label>Lab Name:</label>
                    <input name="labName" type="text" value="{{ (isset($systemSettings->lab_name)) ? $systemSettings->lab_name : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Phone Number:</label>
                    <input name="phoneNumber" type="number" value="{{ (isset($systemSettings->phone_number)) ? $systemSettings->phone_number : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Email Address:</label>
                    <input name="emailAddress" type="email" value="{{ (isset($systemSettings->email_address)) ? $systemSettings->email_address : "" }}">
                </div>
                <div class="inputBack">
                    <label>Phone Number 2:</label>
                    <input name="phoneNumber2" type="text" value="{{ (isset($systemSettings->phone_number_2)) ? $systemSettings->phone_number_2 : "" }}">
                </div>
                <div class="inputBack">
                    <label>Address:</label>
                    <textarea name="address" required>{{ (isset($systemSettings->address)) ? $systemSettings->address : "" }}</textarea>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Location:</label>
                    <input name="location" type="text" value="{{ (isset($systemSettings->location)) ? $systemSettings->location : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Bill Footer:</label>
                    <input name="billFooter" type="text" value="{{ (isset($systemSettings->bill_footer)) ? $systemSettings->bill_footer : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Lab Name On Bill:</label>
                    <input name="labNameOnBill" type="text" value="{{ (isset($systemSettings->lab_name_on_bill)) ? $systemSettings->lab_name_on_bill : "" }}">
                </div>
                <div class="inputBack">
                    <label>Pnr File Text:</label>
                    <textarea name="pnrFileText" required>{{ (isset($systemSettings->barcode_machince_code)) ? $systemSettings->barcode_machince_code : "" }}</textarea>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Lab Address For Invoice:</label>
                    <textarea name="labAddressForInvoice">{{ (isset($systemSettings->lab_address_for_invoice)) ? $systemSettings->lab_address_for_invoice : "" }}</textarea>
                </div>
                <div class="inputBack">
                    <label>Upload Result Header:</label>
                    <input name="uploadResultHeader" type="file">
                </div>
                @if(isset($systemSettings->result_header))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'result')">Remove Result Header</label>
                        <img height="80px" src="{{ $systemSettings->result_header }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Upload Result Footer:</label>
                    <input name="reportBackground" type="file">
                </div>
                @if(isset($systemSettings->report_background))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'report')">Remove Result Footer</label>
                        <img height="80px" src="{{ $systemSettings->report_background }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Upload Bill Header:</label>
                    <input name="uploadBillHeader" type="file">
                </div>
                @if(isset($systemSettings->bill_header))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'bill')">Remove Bill Header</label>
                        <img height="80px" src="{{ $systemSettings->bill_header }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Upload Consulting Bill Header:</label>
                    <input name="uploadConsultingBillHeader" type="file">
                </div>
                @if(isset($systemSettings->header_consulting_bill))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'consulting')">Remove Consulting Bill Header</label>
                        <img height="80px" src="{{ $systemSettings->header_consulting_bill }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Upload IP Bill Header:</label>
                    <input name="uploadIPBillHeader" type="file">
                </div>
                @if(isset($systemSettings->header_ip_billing))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'ip')">Remove IP Bill Header</label>
                        <img height="80px" src="{{ $systemSettings->header_ip_billing }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Billing Message Format:</label>
                    <textarea name="billingMessageFormat" required>{{ (isset($systemSettings->billing_message_format)) ? $systemSettings->billing_message_format : "" }}</textarea>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Billing Message Format:</label>
                    <span>[PatientName] - Patient's Name<br>[UMRNumber] - Patient's UMR Number<br>[BillNumber] - Created Bill Number<br>[Orders] - Order Names<br>[LabName] - Lab Name<br>[PhoneNo] - Lab's Phone Number </span>
                </div>
                <div class="inputBack">
                    <label>Do Not Print and send Reports with Balance:</label>
                    <input name="no_print_balance_reports" type="checkbox" {{ (isset($systemSettings->no_print_balance_reports) && $systemSettings->no_print_balance_reports == "true") ? "checked" : "" }}>
                </div>
                <div class="inputBack">
                    <label>Do Not Allow Duplicate Order in Order Entry:</label>
                    <input name="unique_order_entry" type="checkbox" {{ (isset($systemSettings->unique_order_entry) && $systemSettings->unique_order_entry == "true") ? "checked" : "" }}>
                </div>
                <div class="inputBack">
                    <label>Do Not Send SMS After Billing:</label>
                    <input name="no_sms_after_billing" type="checkbox" {{ (isset($systemSettings->no_sms_after_billing) && $systemSettings->no_sms_after_billing == "true") ? "checked" : "" }}>
                </div>
                <div class="inputBack">
                    <label>Show Sample Received Time in Reports:</label>
                    <input name="sample_time_in_reports" type="checkbox" {{ (isset($systemSettings->sample_time_in_reports) && $systemSettings->sample_time_in_reports == "true") ? "checked" : "" }}>
                </div>
                <div class="inputBack">
                    <label>Patient Phone Not Mandatory in order entry:</label>
                    <input name="patient_phone_not_required" type="checkbox" {{ (isset($systemSettings->patient_phone_not_required) && $systemSettings->patient_phone_not_required == "true") ? "checked" : "" }}>
                </div>
                <div class="inputBack">
                    <label>Bill Stamp:</label>
                    <input name="billStamp" type="file">
                </div>
                @if(isset($systemSettings->bill_stamp))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'bill_stamp')">Remove Bill Stamp</label>
                        <img height="80px" src="{{ $systemSettings->bill_stamp }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Bill Signature:</label>
                    <input name="billSignature" type="file">
                </div>
                @if(isset($systemSettings->bill_signature))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'bill_signature')">Remove Bill Signature</label>
                        <img height="80px" src="{{ $systemSettings->bill_signature }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Prescription Background Image:</label>
                    <input name="prescriptionBackgroundImage" type="file">
                </div>
                @if(isset($systemSettings->prescription_background_image))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'prescription_background_image')">Remove Prescription Background Image</label>
                        <img height="80px" src="{{ $systemSettings->prescription_background_image }}">
                    </div>
                @endif

                <div class="inputBack">
                    <label>Referral Panel Icon:</label>
                    <input name="referralPanelIcon" type="file">
                </div>
                @if(isset($systemSettings->referral_panel_icon))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('remove_header_file/' + 'referral_panel_icon')">Remove Referral Panel Icon</label>
                        <img height="80px" src="{{ $systemSettings->referral_panel_icon }}">
                    </div>
                @endif
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
<script>
    const clearButton = document.getElementById('clearButton');
    const inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

    clearButton.addEventListener('click', function() {
        inputElements.forEach(element => {
            if (element.type === 'text' || element.type === 'textarea') {
                element.value = '';
            } else if (element.type === 'checkbox') {
                element.checked = false;
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            }
        });
    });
</script>
</html>
