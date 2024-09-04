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

    .searchItemsList span {
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
<body>
    <div class="main_container">
        <div class="main">
            <div id="ordersTableBack">
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
                            <span id="resultNotePage1Btn" class="blueBtn">Page 1</span>
                            <span id="resultNotePage2Btn" class="blueBtn">Page 2</span>
                            <span id="resultNotePage3Btn" class="blueBtn">Page 3</span>
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
                                            <input autocomplete="off" class="resultsInput" id="{{ $count }}_resultsInput" name="results[]" value="{{ $component['results'] }}" min="{{ $component['from_range'] }}" max="{{ $component['to_range'] }}">
                                            <div id="{{ $count }}_resultsInputList" class="searchItemsList resultsInputList">
                                                <span>Item 1</span>
                                            </div>
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
                                </tr>
                                @endforeach
                                <script>
                                    var resultsInputs = document.getElementsByClassName('resultsInput');
                                    var abnormalCheckboxes = document.getElementsByClassName('abnormalCheckbox');

                                    for (var i = 0; i < resultsInputs.length; i++) {
                                        resultsInputs[i].addEventListener('input', createEventListener(i));
                                    }

                                    function createEventListener(index) {
                                        return function () {
                                            var inputValue = parseFloat(this.value);
                                            var minValue = parseFloat(this.min);
                                            var maxValue = parseFloat(this.max);
                                            var abnormalCheckbox = abnormalCheckboxes[index];

                                            if (inputValue < minValue || inputValue > maxValue) {
                                                abnormalCheckbox.checked = true;
                                            } else {
                                                abnormalCheckbox.checked = false;
                                            }
                                        };
                                    }
                                </script>
                            </tbody>
                        </table>
                    @endif
                    <div class="horizontal inputForm">
                        <div class="vertical">
                            <span>Method:</span>
                            <input name="resultMethod" class="inputWriteable" value="{{ $orderDetails['orderData']['order_method'] }}">
                        </div>
                        {{-- <div class="vertical">
                            <span>Upload Result File:</span>
                            <input value="Add Attachments" onclick="goToRoute(`orderbills/result_attachments/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`)" style="border: 0px;background: black;width: 150px;color: #fff;text-align: center;border-radius: 5px;padding: 5px;font-size: 14px;cursor:pointer;" readonly>
                        </div> --}}
                    </div>
                    <div class="horizontal inputForm">
                        <div class="vertical">
                            <span>NOTES:</span>
                            @if(empty($orderDetails['componentsData']))
                                <textarea name="resultNotes" class="inputWriteable"></textarea>
                            @else
                                <textarea name="resultNotes" class="inputWriteable">{{ $orderDetails['orderData']['order_notes'] }}</textarea>
                            @endif
                        </div>
                        <div class="vertical">
                            <span>ADVICE:</span>
                            <textarea name="resultAdvice" type="file" class="inputWriteable">{{ $orderDetails['orderData']['order_advice'] }}</textarea>
                        </div>
                    </div>
                    <div class="horizontal inputForm">
                        <div class="vertical">
                            <span>Signature:</span>
                            <select name="signature">
                                <option value="">Select Signature</option>
                                @foreach($orderDetails['signaturesList'] as $signature)
                                    @php $isSelected = ($orderDetails['orderData']['signature'] == $signature['id']) ? "selected" : "" @endphp
                                    <option value="{{ $signature->id }}" {{ $isSelected }}>{{ $signature['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="actions">
                        {{-- @if($orderDetails->showSaveBtn)
                            <button type="submit" name="status" value="Save" class="btn btn-primary">Save</button>
                        @endif --}}
                        <button type="submit" name="status" value="Save And Complete" class="btn btn-primary">Save & Complete</button>
                        {{-- <button type="button" class="btn btn-primary" onclick="goToRoute(`orderbills/print_result/{{ $orderDetails['bill_no'] }}/{{ $orderDetails['orderData']['report_id'] }}`)">Print</button>
                        @php $nextRoute = "orderbills/next_result_entry/".$orderDetails['bill_no']."/".$orderDetails['orderData']['report_id']; @endphp
                        <button type="button" class="btn btn-primary" onclick="goToRoute('{{ $nextRoute }}')">Next</button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <dialog id="urlDialog" style="width: 90%; height: 100%;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">
        <div style="position: relative; width: 100%; height: 100%;">
            <iframe id="urlFrame" style="width: 100%; height: 100%;" allow="fullscreen"></iframe>
            <button id="closeDialogButton" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; cursor: pointer; color: black; font-size: 28px;"><i class='bx bx-x'></i></button>
        </div>
    </dialog>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
<script>
    document.addEventListener('keydown', function(e) {
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
                } else {
                    var currentIndex = Array.from(inputs).indexOf(activeElement);
                    var nextIndex = (currentIndex + 1) % inputs.length;
                    inputs[nextIndex].focus();
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
            }
        }
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
            $('.summernote').summernote(commonSummernoteOptions);
        });
    }

    function resultsInputHandler(event, inputElement, listElement) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById(listElement);

        var idParts = document.getElementById(inputElement).id.split("_");
        var componentId = document.getElementById(idParts[0] + "_componentId").value;

        selectedDoctorId = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `orderbills/search_order_detail_values/${componentId}/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <span onclick="selectResultValue('${item.value}', '${inputElement}', '${listElement}')">${item.value}</span>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
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
    }

    var resultsInput = document.getElementsByClassName('resultsInput');
    var resultsInputList = document.getElementsByClassName('resultsInputList');

    if (resultsInput != null) {
        for (var i = 0; i < resultsInput.length; i++) {
            (function(index) {
                resultsInput[index].addEventListener('input', function(event) {
                    resultsInputHandler(event, resultsInput[index].id, resultsInputList[index].id);
                });
            })(i);
        }
    }
</script>
</html>
