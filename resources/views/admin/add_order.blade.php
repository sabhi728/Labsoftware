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
    .note-editor .note-editing-area .note-editable table td {
        padding: .5rem !important;
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
            <div class="actions">
                <button type="button" id="clearButton" class="btn btn-primary">Clear</button>
                <button type="button" onclick="goToRoute('order_maintenance')" class="btn btn-primary">Cancel</button>
            </div>
            @if(isset($orderData))
                <form id="formBack" action="{{ url('update_order', ['id' => $orderData->report_id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('add_order') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Order Name:</label>
                    @if(isset($orderData))
                        <input name="orderName" id="orderName" type="text" placeholder="Order Name" value="{{ $orderData->order_name }}" required>
                    @else
                        <input name="orderName" id="orderName" type="text" placeholder="Order Name" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Has Components:</label>
                    @if(isset($orderData) && $orderData->has_components == "true")
                        <input name="hasComponents" type="checkbox" checked>
                    @else
                        <input name="hasComponents" type="checkbox">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Test Code:</label>
                    @if(isset($orderData))
                        <input name="testCode" type="text" placeholder="Test Code" value="{{ $orderData->order_test_code }}">
                    @else
                        <input name="testCode" type="text" placeholder="Test Code">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Display Order Name:</label>
                    @if(isset($orderData))
                        <textarea name="displayOrderName" placeholder="Display Order Name">{{ $orderData->order_display_name }}</textarea>
                    @else
                        <textarea name="displayOrderName" placeholder="Display Order Name"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Department:</label>
                    @if(isset($orderData))
                        <select name="department">
                            <option value="">NONE</option>
                            @foreach($departments as $department)
                                @if($orderData->order_department == $department->depart_id)
                                    <option value="{{ $department->depart_id }}" selected>{{ $department->department_name }}</option>
                                @else
                                    <option value="{{ $department->depart_id }}">{{ $department->department_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="department">
                            <option value="">NONE</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->depart_id }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Amount:</label>
                    @if(isset($orderData))
                        <input name="amount" type="text" placeholder="Amount" value="{{ $orderData->order_amount }}" required>
                    @else
                        <input name="amount" type="text" placeholder="Amount" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Process Time:</label>
                    @if(isset($orderData))
                        <input name="processTime" type="text" placeholder="Process Time" value="{{ $orderData->order_process_time }}">
                    @else
                        <input name="processTime" type="text" placeholder="Process Time">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Machine Name:</label>
                    @if(isset($orderData))
                        <input name="machineName" type="text" placeholder="Machine Name" value="{{ $orderData->order_machine_name }}">
                    @else
                        <input name="machineName" type="text" placeholder="Machine Name">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Sample Type:</label>
                    @if(isset($orderData) && $orderData->order_sample_type != NULL)
                        @php
                            $sampleTypesList = explode(', ', $orderData->order_sample_type);
                        @endphp
                        <select name="sampleType[]" id="sampleTypeSelect" class="selectpicker" multiple aria-label="Default select example" data-live-search="true">
                            @foreach($sampleTypes as $sampleType)
                                <option value="{{ $sampleType->id }}"
                                    @if(in_array($sampleType->id, $sampleTypesList))
                                        selected
                                    @endif
                                >{{ $sampleType->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <select name="sampleType[]" id="sampleTypeSelect" class="selectpicker" multiple aria-label="Default select example" data-live-search="true">
                            @foreach($sampleTypes as $sampleType)
                                <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Method:</label>
                    @if(isset($orderData))
                        <input name="method" type="text" placeholder="Method" value="{{ $orderData->order_method }}">
                    @else
                        <input name="method" type="text" placeholder="Method">
                    @endif
                </div>
                <div class="resultNoteButtons">
                    <span id="resultNotePage1Btn" class="blueBtn">Page 1</span>
                    <span id="resultNotePage2Btn" class="blueBtn ms-2">Page 2</span>
                    <span id="resultNotePage3Btn" class="blueBtn ms-2">Page 3</span>
                    <span id="resultNotePreviewBtn" class="blueBtn ms-2">Preview</span>
                </div>
                <div class="inputBack">
                    <label>Result Notes:</label>
                    <div id="resultNotesPage1Back">
                        @if(isset($orderData))
                            <textarea name="resultNotesPage1" id="resultNotesPage1" class="summernote">{{ $orderData->order_result_notes_1 }}</textarea>
                        @else
                            <textarea name="resultNotesPage1" id="resultNotesPage1" class="summernote"></textarea>
                        @endif
                    </div>
                    <div id="resultNotesPage2Back">
                        @if(isset($orderData))
                            <textarea name="resultNotesPage2" id="resultNotesPage2" class="summernote">{{ $orderData->order_result_notes_2 }}</textarea>
                        @else
                            <textarea name="resultNotesPage2" id="resultNotesPage2" class="summernote"></textarea>
                        @endif
                    </div>
                    <div id="resultNotesPage3Back">
                        @if(isset($orderData))
                            <textarea name="resultNotesPage3" id="resultNotesPage3" class="summernote">{{ $orderData->order_result_notes_3 }}</textarea>
                        @else
                            <textarea name="resultNotesPage3" id="resultNotesPage3" class="summernote"></textarea>
                        @endif
                    </div>
                </div>
                <div class="inputBack">
                    <label>Work Sheet:</label>
                    @if(isset($orderData))
                        <textarea name="workSheet" id="workSheet" class="summernote">{{ $orderData->order_worksheet }}</textarea>
                    @else
                        <textarea name="workSheet" id="workSheet" class="summernote"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Advice:</label>
                    @if(isset($orderData))
                        <textarea name="advice" placeholder="Advice">{{ $orderData->order_advice }}</textarea>
                    @else
                        <textarea name="advice" placeholder="Advice"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Purpose:</label>
                    @if(isset($orderData))
                        <textarea name="purpose" placeholder="Purpose">{{ $orderData->order_purpose }}</textarea>
                    @else
                        <textarea name="purpose" placeholder="Purpose"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Order type:</label>
                    @if(isset($orderData))
                        <select name="orderType">
                            @foreach($orderTypes as $orderType)
                                @if($orderData->order_order_type == $orderType->id)
                                    <option value="{{ $orderType->id }}" selected>{{ $orderType->name }}</option>
                                @else
                                    <option value="{{ $orderType->id }}">{{ $orderType->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="orderType">
                            @foreach($orderTypes as $orderType)
                                <option value="{{ $orderType->id }}">{{ $orderType->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>IP Billing Category Type:</label>
                    @if(isset($orderData))
                        <select name="ipBillingCategoryType">
                            <option value="">Select Category</option>
                            @foreach($ipBillingCategoryTypes as $ipBillingCategoryType)
                                @if($orderData->order_ip_billing == $ipBillingCategoryType->id)
                                    <option value="{{ $ipBillingCategoryType->id }}" selected>{{ $ipBillingCategoryType->name }}</option>
                                @else
                                    <option value="{{ $ipBillingCategoryType->id }}">{{ $ipBillingCategoryType->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="ipBillingCategoryType">
                            <option value="">Select Category</option>
                            @foreach($ipBillingCategoryTypes as $ipBillingCategoryType)
                                <option value="{{ $ipBillingCategoryType->id }}">{{ $ipBillingCategoryType->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Report Format:</label>
                    @if(isset($orderData))
                        <select name="reportFormat">
                            <option value="">Select Format</option>
                            @foreach($reportFormats as $reportFormat)
                                @if($orderData->order_report_format == $reportFormat->repo_id)
                                    <option value="{{ $reportFormat->repo_id }}" selected>{{ $reportFormat->report_format_name }}</option>
                                @else
                                    <option value="{{ $reportFormat->repo_id }}">{{ $reportFormat->report_format_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="reportFormat">
                            <option value="">Select Format</option>
                            @foreach($reportFormats as $reportFormat)
                                <option value="{{ $reportFormat->repo_id }}">{{ $reportFormat->report_format_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Recurring:</label>
                    @if(isset($orderData) && $orderData->order_recurring == "true")
                        <input name="recurring" type="checkbox" checked>
                    @else
                        <input name="recurring" type="checkbox">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Service Doctor Required:</label>
                    @if(isset($orderData) && $orderData->order_service_doctor_required == "true")
                        <input name="serviceDoctorRequired" type="checkbox" checked>
                    @else
                        <input name="serviceDoctorRequired" type="checkbox">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Check to Inactive:</label>
                    @if(isset($orderData) && $orderData->status == "In Active")
                        <input name="checkToInactive" type="checkbox" checked>
                    @else
                        <input name="checkToInactive" type="checkbox">
                    @endif
                </div>
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save Order</button>
            </form>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    @include('include.order_no_components_preview')

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="{{ asset('/js/libs/bootstrap-multiselect/bootstrap-multiselect.css') }}" rel="stylesheet" />
    <script src="{{ asset('/js/libs/bootstrap.js') }}"></script>
    <script src="{{ asset('/js/libs/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
</body>
<script>
    const resultNotesPage1Back = document.getElementById('resultNotesPage1Back');
    const resultNotesPage2Back = document.getElementById('resultNotesPage2Back');
    const resultNotesPage3Back = document.getElementById('resultNotesPage3Back');

    const resultNotePage1Btn = document.getElementById('resultNotePage1Btn');
    const resultNotePage2Btn = document.getElementById('resultNotePage2Btn');
    const resultNotePage3Btn = document.getElementById('resultNotePage3Btn');

    function resultNotePage1() {
        resultNotesPage1Back.style.display = "block";
        resultNotesPage2Back.style.display = "none";
        resultNotesPage3Back.style.display = "none";

        resultNotePage1Btn.style.background = buttoncolor;
        resultNotePage2Btn.style.background = lightgray;
        resultNotePage3Btn.style.background = lightgray;
    }

    function resultNotePage2() {
        resultNotesPage1Back.style.display = "none";
        resultNotesPage2Back.style.display = "block";
        resultNotesPage3Back.style.display = "none";

        resultNotePage1Btn.style.background = lightgray;
        resultNotePage2Btn.style.background = buttoncolor;
        resultNotePage3Btn.style.background = lightgray;
    }

    function resultNotePage3() {
        resultNotesPage1Back.style.display = "none";
        resultNotesPage2Back.style.display = "none";
        resultNotesPage3Back.style.display = "block";

        resultNotePage1Btn.style.background = lightgray;
        resultNotePage2Btn.style.background = lightgray;
        resultNotePage3Btn.style.background = buttoncolor;
    }

    resultNotePage1();
    resultNotePage1Btn.addEventListener('click', function() {
        resultNotePage1();
    });

    resultNotePage2Btn.addEventListener('click', function() {
        resultNotePage2();
    });

    resultNotePage3Btn.addEventListener('click', function() {
        resultNotePage3();
    });

    $(document).ready(function() {
        $('.summernote').summernote(commonSummernoteOptions);
    });

    var resultNotePreviewBtn = document.getElementById('resultNotePreviewBtn');

    resultNotePreviewBtn.addEventListener('click', function() {
        $('#previewOrderNameTxt').text($('#orderName').val());

        var resultNotesPage1Txt = $('#resultNotesPage1').val();
        var resultNotesPage2Txt = $('#resultNotesPage2').val();
        var resultNotesPage3Txt = $('#resultNotesPage3').val();

        $('#previewResultPage1').html(resultNotesPage1Txt.replace(/<table>/g, '<table class="table table-bordered">'));
        $('#previewResultPage2').html(resultNotesPage2Txt.replace(/<table>/g, '<table class="table table-bordered">'));
        $('#previewResultPage3').html(resultNotesPage3Txt.replace(/<table>/g, '<table class="table table-bordered">'));

        toggleDivVisibility('previewResultPage1', 'previewResultPage1Tr');
        toggleDivVisibility('previewResultPage2', 'previewResultPage2Tr');
        toggleDivVisibility('previewResultPage3', 'previewResultPage3Tr');

        printNoComponentPreview();
    });

    var clearButton = document.getElementById('clearButton');
    var inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

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
