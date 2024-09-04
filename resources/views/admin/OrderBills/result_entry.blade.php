<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_maintenance.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .horizontal {
        display: flex;
        flex-direction: row;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .vertical {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .vertical2 {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }

    .inputForm span {
        width: 100%;
    }

    .inputReadonly {
        background: var(--lightgray);
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputWriteable {
        border-radius: 5px;
        padding: 7.5px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    #inputWriteableSaveBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
    }

    #phoneUmrInput {
        border-radius: 5px;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0px;
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputField {
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .resultNoteButtons {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .blueBtn {
        background: var(--buttoncolor);
        padding: 8px 20px;
        font-size: 15px;
        border-radius: 10px;
        border: 0;
        font-family: var(--font1);
        cursor: pointer;
    }

    .searchItemsList {
        flex-direction: column;
        border: 1px solid var(--lightdarkgray);
        border-radius: 5px;
        background: var(--lightgray);
        font-weight: 300;
        font-size: 14px;
        padding: 3px;
        width: 306px;
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        box-sizing: border-box;
        z-index: 9999;
    }

    .searchItemsList div {
        font-family: var(--font1);
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .searchItemsList span:hover {
        background: var(--buttoncolor);
        color: white;
    }

    .search_selected {
        background: var(--buttoncolor);
        color: white;
    }

    .search-container {
        position: relative;
        display: inline-block;
    }
</style>

<script src="{{ asset('js/main.js') }}"></script>

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
                <button type="button" class="btn btn-primary" onclick="goToRoute('orderbills/bill_details/{{ $orderDetails['bill_no'] }}')">Bill Order</button>
                <button type="button" class="btn btn-primary" onclick="openPreview('orderbills/result_preview/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}', false, 'false')">Preview</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute(`orderbills/hard_refresh_report_result/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`)">Refresh Order</button>

                @if ($orderDetails->disableForm)
                    <button id="enableEditBtn" type="button" class="btn btn-primary" onclick="reportDetailsEditable()">Enable Edit</button>
                @endif
            </div>
            <div id="ordersTableBack" style="@if ($orderDetails->disableForm) pointer-events: none; @endif">
                <div id="headerHorizonatl" class="horizontal">
                    <div class="vertical2">
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Bill No:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->bill_no }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Department:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails['orderData']['order_department_name'] }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Order:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails['orderData']['order_name'] }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Status:</span>
                                <input style="color:green;" id="phoneInput" class="inputReadonly" value="{{ $orderDetails->reportStatus }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>UMR(Card):</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails->umr_number }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Patient Name:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Phone Number:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_phone }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Gender:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_gender }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Age:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_age }} {{ $orderDetails->patient_age_type }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Reff. Doctor:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->doc_name }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h4 style="color:red;text-align:center;">{{ $orderDetails['orderData']['order_name'] }}</h4>
                <form action="{{ url('orderbills/save_result') }}" method="post">
                    @csrf
                    <input type="hidden" name="billNo" value="{{ $orderDetails->bill_no }}">
                    <input type="hidden" name="reportNo" value="{{ $orderDetails['orderData']['report_id'] }}">

                    @if(empty($orderDetails['componentsData']))
                        <div class="resultNoteButtons">
                            <span id="resultNotePage1Btn" style="pointer-events: auto;" class="blueBtn">Page 1</span>
                            <span id="resultNotePage2Btn" style="pointer-events: auto;" class="blueBtn ms-2">Page 2</span>
                            <span id="resultNotePage3Btn" style="pointer-events: auto;" class="blueBtn ms-2">Page 3</span>
                        </div>
                        <div class="inputBack">
                            <div id="resultNotesPage1Back">
                                @if(empty($orderDetails['orderData']['result_page_1']))
                                    <textarea name="resultNotesPage1" id="resultNotesPage1" class="summernote">{{ $orderDetails['orderData']['order_result_notes_1'] }}</textarea>
                                @else
                                    <textarea name="resultNotesPage1" id="resultNotesPage1" class="summernote">{{ $orderDetails['orderData']['result_page_1'] }}</textarea>
                                @endif
                            </div>
                            <div id="resultNotesPage2Back">
                                @if(empty($orderDetails['orderData']['result_page_2']))
                                    <textarea name="resultNotesPage2" id="resultNotesPage2" class="summernote">{{ $orderDetails['orderData']['order_result_notes_2'] }}</textarea>
                                @else
                                    <textarea name="resultNotesPage2" id="resultNotesPage2" class="summernote">{{ $orderDetails['orderData']['result_page_2'] }}</textarea>
                                @endif
                            </div>
                            <div id="resultNotesPage3Back">
                                @if(empty($orderDetails['orderData']['result_page_3']))
                                    <textarea name="resultNotesPage3" id="resultNotesPage3" class="summernote">{{ $orderDetails['orderData']['order_result_notes_3'] }}</textarea>
                                @else
                                    <textarea name="resultNotesPage3" id="resultNotesPage3" class="summernote">{{ $orderDetails['orderData']['result_page_3'] }}</textarea>
                                @endif
                            </div>
                        </div>
                        <hr>
                    @else
                        <table class="table" id="ordersTable">
                            <thead>
                                <tr>
                                <th scope="col">Component</th>
                                <th scope="col">Results</th>
                                <th scope="col">Abnormal</th>
                                <th scope="col">Range</th>
                                <th scope="col">Units</th>
                                <th scope="col">Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $count = 0; @endphp
                                @foreach($orderDetails['componentsData'] as $component)
                                @php $count++; @endphp
                                <tr>
                                    <td>{{ $component['component_name'] }}</td>
                                    <input type="hidden" id="{{ $count }}_componentId" name="componentId[]" value="{{ $component['id'] }}">
                                    <td>
                                        <div class="search-container">
                                            <input autocomplete="off" class="resultsInput"
                                                id="{{ $count }}_resultsInput"
                                                name="results[]"
                                                value="{{ $component['results'] }}"
                                                min="{{ $component['from_range'] }}"
                                                max="{{ $component['to_range'] }}"
                                                data-component-name="{{ $component['component_name'] }}"
                                                data-formula="{{ $component['calculations'] }}"
                                                @php if ($count == 1) {echo "autofocus";} @endphp>

                                            <div id="{{ $count }}_resultsInputList" class="searchItemsList resultsInputList">
                                                <span>Item 1</span>
                                            </div>

                                            <script>
                                                searchResultArrowSelect(`{{ $count }}_resultsInput`, `{{ $count }}_resultsInputList`);
                                            </script>
                                        </div>
                                    </td>
                                    @if($component['abnormal'] == 'on')
                                        <td><input class="abnormalCheckbox" name="{{ $component['id'] }}_abnormal" type="checkbox" checked="checked"></td>
                                    @else
                                        <td><input class="abnormalCheckbox" name="{{ $component['id'] }}_abnormal" type="checkbox"></td>
                                    @endif
                                    <td><textarea name="range[]" style="white-space: pre-wrap;">{{ $component['order_details_range'] }}</textarea></td>
                                    <td><input name="units[]" value="{{ $component['units'] }}"></td>
                                    <td><input name="method[]" value="{{ $component['method'] }}"></td>
                                    <input type="hidden" name="position[]" value="{{ $component['position'] }}">
                                </tr>
                                @endforeach
                                <script>
                                    var resultsInputs = document.getElementsByClassName('resultsInput');
                                    var abnormalCheckboxes = document.getElementsByClassName('abnormalCheckbox');

                                    for (var i = 0; i < resultsInputs.length; i++) {
                                        (function(index) {
                                            resultsInputs[index].addEventListener('input', function() {
                                                if (resultsInputs[index].value === '' && resultsInputs[index].hasAttribute('data-user-input')) {
                                                    resultsInputs[index].removeAttribute('data-user-input');
                                                } else {
                                                    resultsInputs[index].setAttribute('data-user-input', 'true');
                                                }

                                                createEventListener(index);
                                            });

                                            resultsInputs[index].addEventListener('calculation', function() {
                                                createEventListener(index);
                                            });
                                        })(i);
                                    }

                                    function createEventListener(index) {
                                        var inputValue = parseFloat(resultsInputs[index].value);
                                        var minValue = parseFloat(resultsInputs[index].min);
                                        var maxValue = parseFloat(resultsInputs[index].max);
                                        var abnormalCheckbox = abnormalCheckboxes[index];

                                        if (inputValue < minValue || inputValue > maxValue) {
                                            abnormalCheckbox.checked = true;
                                        } else {
                                            abnormalCheckbox.checked = false;
                                        }
                                    }
                                </script>
                            </tbody>
                        </table>
                    @endif
                    <div class="horizontal inputForm mb-3">
                        <div class="d-flex flex-row align-items-center">
                            <span class="me-3">METHOD:</span>
                            <input name="resultMethod" class="inputWriteable" value="{{ $orderDetails['orderData']['order_method'] }}">
                        </div>
                        <div class="d-flex flex-row align-items-center">
                            <span class="me-3">Upload Result File:</span>
                            <input value="Add Attachments" onclick="goToRoute(`orderbills/result_attachments/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`)" style="border: 0px;background: black;width: 150px;color: #fff;text-align: center;border-radius: 5px;padding: 5px;font-size: 14px;cursor:pointer;" readonly>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="input-group mb-3">
                            <span class="input-group-text">NOTES:</span>
                            <div class="form-control">
                                @if(empty($orderDetails['componentsData']))
                                    <textarea name="resultNotes" class="summernote"></textarea>
                                @else
                                    <textarea name="resultNotes" class="summernote">{{ $orderDetails['orderData']['order_notes'] }}</textarea>
                                @endif
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">ADVICE:</span>
                            <div class="form-control">
                                <textarea name="resultAdvice" class="summernote">{{ $orderDetails['orderData']['order_advice'] }}</textarea>
                            </div>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">Signature:</span>
                            <select name="signature" class="form-control">
                                <option value="">Select Signature</option>
                                @foreach($orderDetails['signaturesList'] as $signature)
                                    @php $isSelected = ($orderDetails['orderData']['signature'] == $signature['id']) ? "selected" : "" @endphp
                                    <option value="{{ $signature->id }}" {{ $isSelected }}>{{ $signature['signature_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div style="display: flex;flex-direction: row;justify-content: flex-end;">
                        @if($orderDetails->showSaveBtn)
                            <button type="submit" name="status" value="Save" class="btn btn-primary">Save</button>
                        @endif

                        <button type="submit"
                            name="status"
                            value="Save And Complete"
                            class="btn btn-primary ms-2"
                        >Save & Complete</button>

                        @if ($orderDetails['orderData']['order_type_name'] == "Consulting")
                            <button type="button" class="btn btn-primary ms-2"
                                onclick="showPrintPrescriptionDialog()"
                            >Print Prescription</button>
                        @endif

                        @if ($orderDetails->showPrintBtn)
                            <button type="button"
                                class="btn btn-primary ms-2"
                                style="pointer-events: auto;"
                                onclick="openPreview(`orderbills/result_preview/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`, true, `{{ $orderDetails['reportStatus'] }}`)"
                                {{-- onclick="openPreview(`orderbills/result_preview/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`, true, true)" --}}
                            >Print</button>
                        @endif

                        @php $nextRoute = "orderbills/next_result_entry/".$orderDetails['bill_no']."/".$orderDetails['orderData']['report_id']; @endphp
                        <button type="button" class="btn btn-primary ms-2" onclick="goToRoute('{{ $nextRoute }}')" style="pointer-events: auto;">Next</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <dialog id="urlDialog" style="width: 90%; height: 100%;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">
        <div style="position: relative; width: 100%; height: 100%; overflow: hidden;">
            <iframe id="urlFrame" style="width: 100%; height: 100%; padding-bottom: 40px;" allow="fullscreen"></iframe>
            <button id="closeDialogButton" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; cursor: pointer; color: black; font-size: 28px;"><i class='bx bx-x'></i></button>
            <div id="urlDialogActionButtons" style="display: none;position: sticky;bottom: 0;display: flex;flex-direction: row;align-items: center;justify-content: end;background: black;padding: 5px;box-sizing: border-box;">
                <button id="urlDialogPrintBtn" class="btn btn-primary">Approve And Print</button>
            </div>
        </div>
    </dialog>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @if ($orderDetails['orderData']['order_type_name'] == "Consulting")
        <div style="display: none;">
            @include('include.prescription_report')
        </div>

        <div id="printPrescriptionDialog" class="modal">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Print Prescription</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Weight:</span>
                            <input type="text" class="form-control" id="prescriptionWeightInput">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Height:</span>
                            <input type="text" class="form-control" id="prescriptionHeightInput">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">BP:</span>
                            <input type="text" class="form-control" id="prescriptionBPInput">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Temp:</span>
                            <input type="text" class="form-control" id="prescriptionTempInput">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Spo2:</span>
                            <input type="text" class="form-control" id="prescriptionSpo2Input">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">PR:</span>
                            <input type="text" class="form-control" id="prescriptionPRInput">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text w-100">Patient Past History:</span>
                            <textarea id="patientPastHistoryTxtArea" class="form-control summernote"></textarea>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text w-100">Clinical Notes / Investigations / Treatment:</span>
                            <textarea id="clinicalNotesTxtArea" class="form-control summernote"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" onclick="printPrescriptionReport()" class="btn btn-primary" data-bs-dismiss="modal">Print</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $('#patientPastHistoryTxtArea').summernote($.extend({}, commonSummernoteOptions, {
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('#patientPastHistoryDiv').html(contents.replace(/<table>/g, '<table class="table table-bordered">'));
                    }
                }
            }));

            $('#clinicalNotesTxtArea').summernote($.extend({}, commonSummernoteOptions,{
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('#clinicalNotesDiv').html(contents.replace(/<table>/g, '<table class="table table-bordered">'));
                    }
                }
            }));

            const inputs = [
                {input: document.getElementById('prescriptionWeightInput'), span: document.getElementById('prescriptionWeightTxt')},
                {input: document.getElementById('prescriptionHeightInput'), span: document.getElementById('prescriptionHeightTxt')},
                {input: document.getElementById('prescriptionBPInput'), span: document.getElementById('prescriptionBPTxt')},
                {input: document.getElementById('prescriptionTempInput'), span: document.getElementById('prescriptionTempTxt')},
                {input: document.getElementById('prescriptionSpo2Input'), span: document.getElementById('prescriptionSpo2Txt')},
                {input: document.getElementById('prescriptionPRInput'), span: document.getElementById('prescriptionPRTxt')}
            ];

            inputs.forEach(item => {
                item.input.addEventListener('input', function() {
                    item.span.textContent = ' ' + item.input.value;
                });
            });

            function showPrintPrescriptionDialog() {
                const printPrescriptionDialog = new bootstrap.Modal('#printPrescriptionDialog', {});
                printPrescriptionDialog.show();
            }
        </script>
    @endif
</body>
<script>
    document.addEventListener('keydown', function(e) {
        var resultsInputList = document.getElementsByClassName('resultsInputList');

        for (var i = 0; i < resultsInputList.length; i++) {
            if (resultsInputList[i].style.display !== "" && resultsInputList[i].style.display != "none") {
                return;
            }
        }

        var activeElement = document.activeElement;
        var isInputField = activeElement.tagName === 'INPUT' && activeElement.name === 'results[]';

        if (isInputField) {
            if (e.key === 'Tab' || (e.key === 'ArrowDown' && !e.shiftKey)) {
                e.preventDefault();

                var inputs = document.querySelectorAll('input[name="results[]"]');

                if (e.shiftKey) {
                    var currentIndex = Array.from(inputs).indexOf(activeElement);
                    var prevIndex = (currentIndex - 1 + inputs.length) % inputs.length;
                    inputs[prevIndex].focus();
                    inputs[prevIndex].dispatchEvent(new Event('click', { bubbles: true }))
                } else {
                    var currentIndex = Array.from(inputs).indexOf(activeElement);
                    var nextIndex = (currentIndex + 1) % inputs.length;
                    inputs[nextIndex].focus();
                    inputs[nextIndex].dispatchEvent(new Event('click', { bubbles: true }))
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
            }
        }
    });

    const urlDialog = document.getElementById("urlDialog");
    const urlFrame = document.getElementById("urlFrame");
    const closeDialogButton = document.getElementById("closeDialogButton");

    function openPreview(url, isActionsVisible, isPrinted) {
        if (isActionsVisible) {
            @if($orderDetails->showSaveBtn)
                alert('Save and Complete the report before printing it.');
                return;
            @endif
        }

        urlFrame.src = webUrl + url;
        urlDialog.showModal();

        if (isActionsVisible) {
            $('#urlDialogActionButtons').css('display', 'flex');

            if (isPrinted == 'Save And Complete') {
                $('#urlDialogPrintBtn').text('Approve And Print');
            } else {
                $('#urlDialogPrintBtn').text('Print');
            }

            $('#urlDialogPrintBtn').on('click', function() {
                var printUrl = url.replaceAll('result_preview', 'print_result');

                if ($('#urlDialogPrintBtn').text() == "Approve And Print") {
                    if (window.confirm('Are you sure?')) {
                        goToRoute(printUrl);
                    }
                } else {
                    goToRoute(printUrl);
                }

            });
        } else {
            $('#urlDialogActionButtons').css('display', 'none');
        }
    }

    closeDialogButton.addEventListener("click", () => {
        urlFrame.src = "";
        urlDialog.close();
    });

    const resultNotesPage1Back = document.getElementById('resultNotesPage1Back');
    const resultNotesPage2Back = document.getElementById('resultNotesPage2Back');
    const resultNotesPage3Back = document.getElementById('resultNotesPage3Back');

    const resultNotePage1Btn = document.getElementById('resultNotePage1Btn');
    const resultNotePage2Btn = document.getElementById('resultNotePage2Btn');
    const resultNotePage3Btn = document.getElementById('resultNotePage3Btn');

    if (resultNotePage1Btn != null) {
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
            var noteBar = $('.note-toolbar');
            noteBar.find('[data-toggle]').each(function() {
                $(this).attr('data-bs-toggle', $(this).attr('data-toggle')).removeAttr('data-toggle');
            });
        });
    }

    $('.summernote').summernote(commonSummernoteOptions);

    function resultsInputHandler(event, inputElement, listElement) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById(listElement);

        var idParts = document.getElementById(inputElement).id.split("_");
        var componentId = document.getElementById(idParts[0] + "_componentId").value;
        var valueInputText = document.getElementById(idParts[0] + "_resultsInput");

        document.addEventListener('click', function(event) {
            if (!resultLayout.contains(event.target) && !valueInputText.contains(event.target)) {
                resultLayout.style.display = 'none';
            }
        });

        fetch(webUrl + `orderbills/search_order_detail_values/${componentId}/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;

                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';

                        responseJson.forEach(item => {
                            if (responseJson.length === 1 && item.value === inputValue) {
                                resultLayout.style.display = 'none';
                                return;
                            }

                            contentHTML += `
                                <div onclick="selectResultValue('${item.value}', '${inputElement}', '${listElement}')">${item.value}</div>
                            `;
                        });

                        if (contentHTML !== '') {
                            resultLayout.innerHTML = contentHTML;
                            resultLayout.style.display = "flex";
                        }
                    }
                })
                .catch(error => console.error('Error fetching data:', error));

        // if (inputValue == "") {
        //     resultLayout.style.display = "none";
        // } else {
        //     fetch(webUrl + `orderbills/search_order_detail_values/${componentId}/${inputValue}`)
        //         .then(response => response.json())
        //         .then(responseJson => {
        //             var size = Object.keys(responseJson).length;

        //             if (size == 0) {
        //                 resultLayout.style.display = "none";
        //             } else {
        //                 let contentHTML = '';
        //                 responseJson.forEach(item => {
        //                     contentHTML += `
        //                         <span onclick="selectResultValue('${item.value}', '${inputElement}', '${listElement}')">${item.value}</span>
        //                     `;
        //                 });

        //                 resultLayout.innerHTML = contentHTML;
        //                 resultLayout.style.display = "flex";
        //             }
        //         })
        //         .catch(error => console.error('Error fetching data:', error));
        // }
    }

    function selectResultValue(valueText, inputElement, listElement) {
        var resultsInput = document.getElementById(inputElement);
        var resultsInputList = document.getElementById(listElement);

        resultsInput.removeEventListener('input', function(event) {
            resultsInputHandler(event, inputElement, listElement);
        });

        resultsInput.value = valueText;

        resultsInput.addEventListener('input', function(event) {
            resultsInputHandler(event, inputElement, listElement);
        });

        resultsInputList.style.display = "none";

        resultsInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var resultsInput = document.getElementsByClassName('resultsInput');
    var resultsInputList = document.getElementsByClassName('resultsInputList');

    if (resultsInput != null) {
        for (var i = 0; i < resultsInput.length; i++) {
            (function(index) {
                resultsInput[index].addEventListener('input', function(event) {
                    resultsInputHandler(event, resultsInput[index].id, resultsInputList[index].id);
                });

                resultsInput[index].addEventListener('click', function(event) {
                    resultsInputHandler(event, resultsInput[index].id, resultsInputList[index].id);
                });
            })(i);
        }

        document.addEventListener('input', function(element) {
            var componentName = element.target.getAttribute('data-component-name');

            if (componentName !== null) {
                for (var i = 0; i < resultsInput.length; i++) {
                    var currentElement = resultsInput[i];

                    if (element.target != currentElement) {
                        var componentName = currentElement.getAttribute('data-component-name');
                        var componentFormula = currentElement.getAttribute('data-formula');

                        if (componentFormula !== "") {
                            var valueInputs = document.querySelectorAll('input[data-component-name]');

                            valueInputs.forEach(function(input) {
                                var dataName = input.getAttribute('data-component-name');
                                var value = input.value;

                                if (componentFormula.includes(dataName)) {
                                    if (value !== "") {
                                        componentFormula = componentFormula.replaceAll(dataName, value);
                                    }
                                }
                            });

                            try {
                                if (!currentElement.hasAttribute('data-user-input')) {
                                    var result = eval(componentFormula);

                                    if (!Number.isInteger(result)) {
                                        currentElement.value = result.toFixed(1);
                                    } else {
                                        currentElement.value = result;
                                    }

                                    currentElement.dispatchEvent(new Event('calculation', { bubbles: true }));
                                }
                            } catch (e) {
                                console.log(e);
                            }
                        }
                    }
                }
            }
        });
    }

    function reportDetailsEditable() {
        if ($('#enableEditBtn').text() === "Enable Edit") {
            $('#enableEditBtn').text('Disable Edit');
            $('#ordersTableBack').css('pointer-events', 'auto');
        } else {
            $('#enableEditBtn').text('Enable Edit');
            $('#ordersTableBack').css('pointer-events', 'none');
        }
    }
</script>
</html>

@php
    $showPreview = session('showPreview');
    $isPrinted = session('isPrinted');

    if ($showPreview) {
        echo '<script>openPreview("orderbills/result_preview/' . $orderDetails['bill_no'] . '/' . $orderDetails['orderData']['report_id'] . '", true, ' . $isPrinted . ');</script>';
    }
@endphp
