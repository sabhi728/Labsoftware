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
    }

    .horizontalAllCenter {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .vertical {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    #headerHorizonatl img {
        height: 120px;
        width: 120px;
        margin-right: 20px;
    }

    .inputForm span {
        width: 120px;
    }

    .horizontalAllCenter span {
        width: max-content;
        margin-right: 10px;
    }

    .inputReadonly {
        background: var(--lightgray);
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
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

    #phoneUmrSearchBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
    }

    .searchItemsList {
        flex-direction: column;
        border: 1px solid var(--lightdarkgray);
        border-radius: 5px;
        background: var(--lightgray);
        font-weight: 300;
        font-size: 11px;
        padding: 3px;
        width: 306px;
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        box-sizing: border-box;
        z-index: 9999;
        height: 150px;
        overflow: auto;
    }

    .searchItemsList .setBox {
        font-family: var(--font1);
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .searchItemsList .setBox:hover {
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
    <div class="header">
        @include('include.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('include.sidebar')
        </div>
        <div class="main">
            @if($orderDetails->isBillEditable == "true")
                <div class="actions">
                    <button id="submitBillBtn" type="button" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-primary" onclick="goToRoute('orderentry/index')">New Bill</button>
                    <button type="button" class="btn btn-primary" onclick="confirm('Are you sure you want to cancel the bill?') ? goToRoute('orderentry/cancel_bill/{{ $orderDetails->bill_no }}') : ''">Cancel Bill</button>
                    <button type="button" class="btn btn-primary" id="discountBtn">Discount</button>
                    <button type="button" class="btn btn-primary" onclick="goToRoute('doctors/add')">Add Doctor</button>
                    <button type="button" class="btn btn-primary" onclick="goToRoute('add_order')">Add Order</button>
                    <!-- <button type="button" class="btn btn-primary">Add Expense</button> -->
                </div>
            @else
                <div class="actions">
                    <button type="button" class="btn btn-primary" onclick="goToRoute('orderentry/index')">New Bill</button>
                </div>
            @endif
            @if($orderDetails->isBillEditable == "true")
            <div id="ordersTableBack">
            @else
            <div id="ordersTableBack" style="pointer-events: none;">
            @endif
                <div id="headerHorizonatl" class="horizontal">
                    <img src="{{ asset('assets/patient.jpg') }}">
                    <div class="vertical">
                        <div class="horizontal inputForm">
                            <span>Name:</span>
                            <input id="nameInput" class="inputReadonly" value="{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}" readonly>
                        </div>
                        <space style="height: 3px;"></space>
                        <div class="horizontal inputForm">
                            <span>Age/Gender:</span>
                            <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->patient_age }} {{ $orderDetails->patient_age_type }} / {{ $orderDetails->patient_gender }}" readonly>
                        </div>
                        <space style="height: 3px;"></space>
                        <div class="horizontal inputForm">
                            <span>Phone:</span>
                            <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_phone }}" readonly>
                        </div>
                        <space style="height: 3px;"></space>
                        <div class="horizontal inputForm">
                            <span>UMR:</span>
                            <input id="umrInput" class="inputReadonly" value="{{ $orderDetails->umr_number }}" readonly>
                        </div>
                        <space style="height: 3px;"></space>
                        <button type="button" class="btn btn-primary" id="editPatientDetails" style="pointer-events: auto;">Edit Patient Details</button>
                    </div>
                </div>
                <hr>
                <span>Bill No: <b style="font-size: 18px;"><i>{{ $orderDetails->bill_no }}</i></b> @if($orderDetails->status == "cancelled") <i style="color: red;">Cancelled</i> @endif</span>
                <div class="horizontalAllCenter">
                    <div class="horizontal inputForm">
                        <span>Doctor:</span>
                        <div class="search-container">
                            <input id="doctorInput" class="inputField" placeholder="Doctor" value="{{ $orderDetails->doc_name }}">
                            <div id="doctorInputList" class="searchItemsList">
                                <span>Item 1</span>
                            </div>
                        </div>
                    </div>
                    <div class="horizontal inputForm">
                        <span>Referred By:</span>
                        <div class="search-container">
                            <input id="referredByInput" class="inputField" placeholder="Referred By" value="{{ $orderDetails->referred_by }}">
                            <div id="referredByInputList" class="searchItemsList">
                                <span>Item 1</span>
                            </div>
                        </div>
                    </div>
                    <div class="horizontal inputForm">
                        <span>Order Name:</span>
                        <div class="search-container">
                            <input id="orderNameInput" class="inputField" placeholder="Order Name">
                            <div id="orderNameInputList" class="searchItemsList">
                                <span>Item 1</span>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">S.No</th>
                        <th scope="col">Order Name</th>
                        <th scope="col"></th>
                        <th scope="col">Order Date</th>
                        <th scope="col">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="noDataOrderTable" style="display: none;">
                            <td colspan="4" style="text-align: center;font-size: 14px;">
                                There are no items to display
                            </td>
                        </tr>
                        @php $itemCount = 0 @endphp
                        @foreach($orderDetails->order_data as $order)
                        @php $itemCount++ @endphp
                        <tr>
                            <td>{{ $itemCount }}</td>
                            <td><span>{{ $order['order_name'] }} @if(isset($order['return_type'])) <span style="font-style: italic">({{ $order['return_type'] }})</span> @endif</span>
                                <input style="display:none;" class="selectedOrderIds" value="{{ $order['report_id'] }}">
                                <input style="display:none;" class="selectedOrdersType" value="{{ $order['order_type'] }}">
                            </td>
                            <td>
                                @if($orderDetails->isBillEditable == "true")
                                    <button class="btn btn-danger" onClick="returnOrder(`{{ $orderDetails->bill_no }}`, `{{ $order['report_id'] }}`, `{{ isset($order['return_type']) ? $order['return_type'] : "" }}`, `{{ isset($order['return_amount']) ? $order['return_amount'] : "" }}`, `{{ isset($order['return_note']) ? $order['return_note'] : "" }}`)">Return</button>
                                @endif
                                <i style="color:red;font-size:25px;cursor:pointer;" class='bx bx-trash' onclick="deleteRow(this)"></i>
                            </td>
                            <td><span id="selectedOrderDate">{{ $orderDetails->order_date }}</span></td>
                            <td><input class="selectedOrderAmounts" value="{{ $order['order_amount'] }}"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
                <div style="align-items: end;" class="vertical">
                    <div class="horizontal inputForm">
                        <span>Total Bill</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <balancespan id="totalBill" style="text-align: center;width: 100px;">{{ $orderDetails->total_bill }}</balancespan>
                    </div>
                    <space style="height: 10px;"></space>
                    @if($orderDetails->overall_dis == 0)
                    <div id="discountLayout" style="display: none;">
                        <div class="horizontal inputForm">
                            <span>Overall Dis</span>
                            <colon style="width: 50px;text-align:center;">:</colon>
                            <input id="dlgOverallDis" style="text-align: center;width: 73px;" class="inputField" type="number" value="0" required>
                            <input id="dlgIsDisPercentage" class="checkbox" type="checkbox">
                            <percentage>%</percentage>
                        </div>
                        <div class="horizontal inputForm">
                            <span>Reason For Discount</span>
                            <colon style="width: 50px;text-align:center;">:</colon>
                            <input id="dlgReasonForDiscount" style="text-align: center;width: 100px;" class="inputField" type="text" required>
                        </div>
                        <space style="height: 10px;"></space>
                    </div>
                    @else
                    <div id="discountLayout">
                        <div class="horizontal inputForm">
                            @php
                                if ($orderDetails->is_dis_percentage == "true") {
                                    $isPercentageChecked = "checked";
                                } else {
                                    $isPercentageChecked = "";
                                }
                            @endphp
                            <span>Overall Dis</span>
                            <colon style="width: 50px;text-align:center;">:</colon>
                            <input id="dlgOverallDis" style="text-align: center;width: 73px;" class="inputField" type="number" value="{{ $orderDetails->overall_dis }}" required>
                            <input id="dlgIsDisPercentage" class="checkbox" type="checkbox" {{$isPercentageChecked}}>
                            <percentage>%</percentage>
                        </div>
                        <div class="horizontal inputForm">
                            <span>Reason For Discount</span>
                            <colon style="width: 50px;text-align:center;">:</colon>
                            <input id="dlgReasonForDiscount" style="text-align: center;width: 100px;" class="inputField" type="text" value="{{ $orderDetails->reason_for_discount }}" required>
                        </div>
                        <space style="height: 10px;"></space>
                    </div>
                    @endif
                    <div class="horizontal inputForm">
                        <span>Payment Type</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <select id="paymentMethodSelect" style="width: 100px;" autocomplete="off">
                            @if($orderDetails->transaction_details->payment_method == "Cash")
                                <option value="Cash" selected>Cash</option>
                            @else
                                <option value="Cash">Cash</option>
                            @endif
                            @if($orderDetails->transaction_details->payment_method == "Card")
                                <option value="Card" selected>Card</option>
                            @else
                                <option value="Card">Card</option>
                            @endif
                            @if($orderDetails->transaction_details->payment_method == "Cheque")
                                <option value="Cheque" selected>Cheque</option>
                            @else
                                <option value="Cheque">Cheque</option>
                            @endif
                            @if($orderDetails->transaction_details->payment_method == "Paytm")
                                <option value="Paytm" selected>Paytm</option>
                            @else
                                <option value="Paytm">Paytm</option>
                            @endif
                            @if($orderDetails->transaction_details->payment_method == "UPI")
                                <option value="UPI" selected>UPI</option>
                            @else
                                <option value="UPI">UPI</option>
                            @endif
                        </select>
                    </div>
                    <space style="height: 3px;"></space>
                    @if($orderDetails->transaction_details->payment_method == "Cash")
                    <div id="paymentNumberLayout" class="horizontal inputForm" style="display: none;">
                        <span id="paymentNumberSpan">Invoice Number</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <input id="paymentNumber" style="text-align: center;width: 100px;" class="inputField">
                    </div>
                    @else
                    <div id="paymentNumberLayout" class="horizontal inputForm">
                        <span id="paymentNumberSpan">Invoice Number</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <input id="paymentNumber" style="text-align: center;width: 100px;" class="inputField" value="{{ $orderDetails->transaction_details->txn_id }}">
                    </div>
                    @endif
                    <space style="height: 3px;"></space>
                    <div class="horizontal inputForm">
                        <span>Paid Amount</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <input id="paidAmount" style="text-align: center;width: 100px;" class="inputField" value="{{ $orderDetails->paid_amount }}">
                    </div>
                    <space style="height: 3px;"></space>
                    <div class="horizontal inputForm">
                        <span>Balance</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <balancespan id="balanceLeft" style="text-align: center;width: 100px;">{{ $orderDetails->balance }}</balancespan>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    @include('include.edit_patient_dialog')

    <div class="modal fade" id="returnModel" tabindex="-1" role="dialog" aria-labelledby="returnModelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="main">
                    <form id="returnForm" action="{{ url('orderbills/return_order_amount') }}" method="post">
                        @csrf
                        <input type="hidden" id="returnBillNo" name="returnBillNo">
                        <input type="hidden" id="returnOrderNo" name="returnOrderNo">
                        <sectionHeader>Refund/Cancel</sectionHeader>
                        <div class="horizontal" style="margin-bottom: 15px;">
                            <div>
                                <input name="returnRadioSelection" value="refund" type="radio">
                                <label>Refund</label>
                            </div>
                            <div style="margin-left: 15px;">
                                <input name="returnRadioSelection" type="radio" value="cancel" checked>
                                <label>Cancel</label>
                            </div>
                        </div>
                        <div class="inputBack">
                            <label>Return Amount:</label>
                            <input id="returnAmount" name="returnAmount" type="number" required>
                        </div>
                        <div class="inputBack">
                            <label>Return Notes:</label>
                            <textarea id="returnNotes" name="returnNotes" required></textarea>
                        </div>
                        <button id="saveOrderBtn" type="button" class="btn btn-primary" onclick="validateAmount()">Update</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
<script>
    function validateAmount() {
        var returnAmount = parseFloat(document.getElementById('returnAmount').value);
        var returnNotes = document.getElementById('returnNotes').value;

        if (isNaN(returnAmount)) {
            alert('Please enter a valid return amount.');
            return;
        }

        if (returnAmount === 0) {
            alert('Please enter a valid return amount.');
            return;
        }

        if (returnAmount > "{{ $orderDetails->paid_amount }}") {
            alert('Return amount cannot be more then the paid amount');
            return;
        }

        if (returnNotes == "") {
            alert('Enter return note');
            return;
        }

        document.getElementById('returnForm').submit();
    }

    function returnOrder(billNo, orderNo, returnType, amount, returnNote) {
        var returnBillNo = document.getElementById('returnBillNo');
        var returnOrderNo = document.getElementById('returnOrderNo');
        var returnAmount = document.getElementById('returnAmount');
        var returnNotes = document.getElementById('returnNotes');

        returnBillNo.value = billNo;
        returnOrderNo.value = orderNo;

        if (returnType != "") {
            if (returnType == "Refund") {
                document.getElementsByName('returnRadioSelection')[0].checked = true
            } else {
                document.getElementsByName('returnRadioSelection')[1].checked = true;
            }

            returnAmount.value = amount;
            returnNotes.value = returnNote;
        } else {
            document.getElementsByName('returnRadioSelection')[1].checked = true;
            returnAmount.value = "";
            returnNotes.value = "";
        }

        $("#returnModel").modal('show');
    }

    $(document).ready(function() {
        $("#redirect_url").val("{{ str_replace(env('URL'), '', url()->current()) }}");
        document.getElementById('additionEditOptions').style.display = "none";
        $("#editPatientDetails").click(function() {
            $("#myModal").modal('show');
        });
    });

    const discountBtn = document.getElementById('discountBtn');
    const discountLayout = document.getElementById('discountLayout');
    discountBtn.addEventListener('click', function() {
        if (discountLayout.style.display == "none") {
            discountLayout.style.display = "block";
        } else {
            discountLayout.style.display = "none";
        }
        updateTotalPrice();
    });

    $("#dlgOverallDis").on('change', function() {
        updateTotalPrice();
    });

    $("#dlgIsDisPercentage").on('change', function() {
        updateTotalPrice();
    });

    const ordersTable = document.getElementById('ordersTable');
    var selectedDoctorId = "{{ $orderDetails->doctor }}";
    var selectedReferredById = "{{ $orderDetails->referred_by_id }}";

    function searchPatient() {
        const phoneUmrInput = document.getElementById('phoneUmrInput');
        const nameInput = document.getElementById('nameInput');
        const ageGenderInput = document.getElementById('ageGenderInput');
        const phoneInput = document.getElementById('phoneInput');
        const umrInput = document.getElementById('umrInput');

        if (phoneUmrInput.value.trim() != "") {
            nameInput.value = "";
            ageGenderInput.value = "";
            phoneInput.value = "";
            umrInput.value = "";

            fetch(webUrl + `orderentry/search_patient/${phoneUmrInput.value}`)
                .then(response => response.json())
                .then(responseJson => {
                    nameInput.value = `${responseJson.patient_title_name} ${responseJson.patient_name}`;
                    ageGenderInput.value = `${responseJson.age} ${responseJson.age_type} / ${responseJson.gender}`;
                    phoneInput.value = `${responseJson.phone}`;
                    umrInput.value = `${responseJson.umr_number}`;
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    }

    $("#submitBillBtn").on('click', function() {
        if ($("#umrInput").val() == "") {
            alert("Search patient to submit");
            return;
        }

        var tableBody = ordersTable.querySelector('tbody');
        var rows = tableBody.rows;

        if (rows.length == 1) {
            alert("Enter order items to submit");
            return;
        }

        var paymentMethod = $("#paymentMethodSelect").val();
        var paymentNumber = document.getElementById("paymentNumber");

        if (paymentMethod != "Cash" && paymentNumber.value == "") {
            alert("Enter " + $("#paymentNumberSpan").text() + " to submit");
            return;
        }

        const dlgOverallDis = document.getElementById('dlgOverallDis');
        const dlgIsDisPercentage = document.getElementById('dlgIsDisPercentage');
        const dlgReasonForDiscount = document.getElementById('dlgReasonForDiscount');
        const discountLayout = document.getElementById('discountLayout');

        var overallDiscount = 0;
        if (discountLayout.style.display != "none") {
            if (dlgOverallDis.value == "" || dlgOverallDis == "0") {
                alert("Enter discount amount");
                return;
            }

            if (dlgReasonForDiscount.value == "") {
                alert("Enter reason for discount");
                return;
            }

            overallDiscount = dlgOverallDis.value;
        }

        var balanceLeft = $("#balanceLeft").text();

        if (balanceLeft.includes('-')) {
            alert("Balance amount cannot be in minus");
            return;
        }

        const selectedOrderIds = document.querySelectorAll('.selectedOrderIds');
        const selectedOrderValues = [];
        selectedOrderIds.forEach(element => {
            selectedOrderValues.push(element.value);
        });

        const selectedOrderAmounts = document.querySelectorAll('.selectedOrderAmounts');
        const selectedOrderAmountsValues = [];
        selectedOrderAmounts.forEach(element => {
            selectedOrderAmountsValues.push(element.value);
        });

        var smsConfirm = confirm("Do you want to send SMS to patient?");

        var umrInput = $("#umrInput").val();
        var referredByInput = $("#referredByInput").val();
        var totalBill = $("#totalBill").text();
        var paymentMethodSelect = $("#paymentMethodSelect").val();
        var paymentNumber = $("#paymentNumber").val();
        var paidAmount = $("#paidAmount").val();
        // var balanceLeft = $("#balanceLeft").text();
        var reasonForDiscount = $("#dlgReasonForDiscount").val();
        var isDisPercentage = dlgIsDisPercentage.checked;

        const selectedOrderCommaSeparatedValues = selectedOrderValues.join(',');
        const selectedOrderAmountsCommaSeparatedValues = selectedOrderAmountsValues.join(',');
        var selectedOrderDate = $("#selectedOrderDate").text();

        var form = document.createElement("form");
        form.method = "POST";
        form.action = webUrl + "orderentry/update_order_entry/{{ $orderDetails->bill_no }}";

        function createHiddenInput(name, value) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = name;
            input.value = value;
            return input;
        }

        form.appendChild(createHiddenInput("umrInput", umrInput));
        form.appendChild(createHiddenInput("referredByInput", referredByInput));
        form.appendChild(createHiddenInput("totalBill", totalBill));
        form.appendChild(createHiddenInput("paymentMethodSelect", paymentMethodSelect));
        form.appendChild(createHiddenInput("paymentNumber", paymentNumber));
        form.appendChild(createHiddenInput("paidAmount", paidAmount));
        // form.appendChild(createHiddenInput("balanceLeft", balanceLeft));
        form.appendChild(createHiddenInput("selectedOrderCommaSeparatedValues", selectedOrderCommaSeparatedValues));
        form.appendChild(createHiddenInput("selectedOrderAmountsCommaSeparatedValues", selectedOrderAmountsCommaSeparatedValues));
        form.appendChild(createHiddenInput("selectedOrderDate", selectedOrderDate));
        form.appendChild(createHiddenInput("selectedDoctorId", selectedDoctorId));
        form.appendChild(createHiddenInput("selectedReferredById", selectedReferredById));
        form.appendChild(createHiddenInput("sendUpdateSMS", smsConfirm));

        if (discountLayout.style.display != "none") {
            form.appendChild(createHiddenInput("reasonForDiscount", reasonForDiscount));
            form.appendChild(createHiddenInput("isDisPercentage", isDisPercentage));
            form.appendChild(createHiddenInput("overallDiscount", overallDiscount));
        } else {
            form.appendChild(createHiddenInput("reasonForDiscount", ""));
            form.appendChild(createHiddenInput("isDisPercentage", false));
            form.appendChild(createHiddenInput("overallDiscount", 0));
        }

        var csrfToken = "{{ csrf_token() }}";
        form.appendChild(createHiddenInput("_token", csrfToken));

        document.body.appendChild(form);
        form.submit();
    });

    $("#clearOrderItemsBtn").on('click', function() {
        removeAllRows();
    });

    var doctorInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('doctorInputList');

        selectedDoctorId = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_doctors/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <div class="setBox" onclick="selectDoctor('${item.id}', '${item.doc_name}')">${item.doc_name}</div>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('doctorInput').addEventListener('input', doctorInputHandler);

    function selectDoctor(doctorId, doctorName) {
        selectedDoctorId = doctorId;

        var doctorInputList = document.getElementById('doctorInputList');
        var doctorInput = document.getElementById('doctorInput');

        document.getElementById('doctorInput').removeEventListener('input', doctorInputHandler);
        doctorInput.value = doctorName;
        document.getElementById('doctorInput').addEventListener('input', doctorInputHandler);

        doctorInputList.style.display = "none";
    }

    searchResultArrowSelect('doctorInput', 'doctorInputList');
    searchResultArrowSelect('orderNameInput', 'orderNameInputList');
    searchResultArrowSelect('referredByInput', 'referredByInputList');
    // searchResultArrowSelect('phoneUmrInput', 'phoneUmrInputList');

    var orderInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('orderNameInputList');

        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_orders/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';

                        var tableBody = ordersTable.querySelector('tbody');
                        var rows = tableBody.rows;
                        var allAvailableItems = [];

                        if (!'{{ $isDuplicateOrdersAllowed }}') {
                            for (var i = 1; i < rows.length; i++) {
                                var spanElement = rows[i].cells[1].querySelector("span");
                                allAvailableItems.push(spanElement.textContent.trim());
                            }
                        }

                        responseJson.forEach(item => {
                            if (item.type == "profile") {
                                $reportId = item.report_id + "(profile)";
                            } else {
                                $reportId = item.report_id;
                            }

                            if (!allAvailableItems.includes(item.order_name.trim())) {
                                contentHTML += `
                                <div class="setBox" onclick="selectOrder('${$reportId}', '${item.order_name}', '${item.order_amount}', '${item.order_type}')">${item.order_name}</div>
                                `;
                            }
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('orderNameInput').addEventListener('input', orderInputHandler);

    function selectOrder(orderId, orderName, orderAmount, orderType) {
        const selectedOrdersType = document.getElementsByClassName('selectedOrdersType');
        var isConsultingOrderExist = false;

        var tableBody = ordersTable.querySelector('tbody');
        var noDataOrderTable = document.getElementById('noDataOrderTable');
        noDataOrderTable.style.display = "none";

        for (var i = 0; i < selectedOrdersType.length; i++) {
            if (selectedOrdersType[i].value == "Consulting") {
                isConsultingOrderExist = true;
            }
        }

        if (noDataOrderTable.style.display == "none" && tableBody.rows.length != 1) {
            if (isConsultingOrderExist && orderType != "Consulting") {
                alert('You can only add consulting order.');
                return;
            } else if (orderType == "Consulting") {
                alert('You cannot add consulting order.');
                return;
            }
        }

        var orderNameInputList = document.getElementById('orderNameInputList');
        var orderNameInput = document.getElementById('orderNameInput');

        document.getElementById('orderNameInput').removeEventListener('input', orderInputHandler);
        orderNameInput.value = "";
        document.getElementById('orderNameInput').addEventListener('input', orderInputHandler);
        orderNameInputList.style.display = "none";

        var currentDate = new Date();
        var monthNames = [
            "Jan", "Feb", "Mar",
            "Apr", "May", "Jun",
            "Jul", "Aug", "Sep",
            "Oct", "Nov", "Dec"
        ];

        var day = currentDate.getDate();
        var monthIndex = currentDate.getMonth();
        var year = currentDate.getFullYear();
        var formattedDate = day + ' ' + monthNames[monthIndex] + ' ' + year;

        var row = tableBody.insertRow();
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);
        var cell3 = row.insertCell(2);
        var cell4 = row.insertCell(3);
        var cell5 = row.insertCell(4);

        cell1.textContent = tableBody.rows.length - 1;
        cell2.innerHTML = `<span>${orderName}</span> <input style="display:none;" class="selectedOrderIds" value="${orderId}"> <input style="display:none;" class="selectedOrdersType" value="${orderType}">`;
        cell3.innerHTML =  `<i style="color:red;font-size:25px;cursor:pointer;" class='bx bx-trash'></i>`;
        cell4.innerHTML = `<span id="selectedOrderDate">${formattedDate}</span>`;
        cell5.innerHTML = `<input class="selectedOrderAmounts" value="${orderAmount}">`;

        var trashIcon = cell3.querySelector(".bx-trash");
        trashIcon.addEventListener("click", function() {
            removeRow(row);
        });
        updateTotalPrice();
    }

    var referredByInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('referredByInputList');

        selectedReferredById = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_locations/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <div class="setBox" onclick="selectReferredBy('${item.id}', '${item.name}')">${item.name}</div>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('referredByInput').addEventListener('input', referredByInputHandler);

    function selectReferredBy(referredById, referredByName) {
        selectedReferredById = referredById;

        var referredByInputList = document.getElementById('referredByInputList');
        var referredByInput = document.getElementById('referredByInput');

        document.getElementById('referredByInput').removeEventListener('input', referredByInputHandler);
        referredByInput.value = referredByName;
        document.getElementById('referredByInput').addEventListener('input', referredByInputHandler);

        referredByInputList.style.display = "none";
    }

    function deleteRow(button) {
        var row = button.closest('tr');
        removeRow(row);
    }

    function updateRowNumbers() {
        var tableBody = ordersTable.querySelector('tbody');
        var rows = tableBody.rows;
        if (rows.length == 1) {
            var noDataOrderTable = document.getElementById('noDataOrderTable');
            noDataOrderTable.style.display = "contents";
        } else {
            for (var i = 1; i < rows.length; i++) {
                rows[i].cells[0].textContent = i;
            }
        }
    }

    function removeRow(row) {
        row.remove();
        updateRowNumbers();
        updateTotalPrice();
    }

    function removeAllRows() {
        var tableBody = ordersTable.querySelector('tbody');
        var rows = tableBody.rows;

        while (rows.length > 1) {
            tableBody.deleteRow(rows.length - 1);
        }

        var noDataOrderTable = document.getElementById('noDataOrderTable');
        noDataOrderTable.style.display = "contents";
        updateTotalPrice();
    }

    function updateTotalPrice() {
        var tableBody = ordersTable.querySelector('tbody');
        var rows = tableBody.rows;
        var totalPrice = 0;

        for (var i = 1; i < rows.length; i++) {
            var inputElement = rows[i].cells[4].querySelector("input");
            var price = parseFloat(inputElement.value);
            if (!isNaN(price)) {
                totalPrice += price;
            }
        }

        const dlgOverallDis = document.getElementById('dlgOverallDis');
        const dlgIsDisPercentage = document.getElementById('dlgIsDisPercentage');
        const discountLayout = document.getElementById('discountLayout');

        var discount = 0;
        var paidAmount = document.getElementById("paidAmount").value;

        if (discountLayout.style.display != "none") {
            if (dlgIsDisPercentage.checked) {
                var percentage = dlgOverallDis.value;
                discount = totalPrice.toFixed(2) * (percentage / 100);
            } else {
                discount = dlgOverallDis.value;
            }
        }

        document.getElementById("totalBill").textContent = totalPrice.toFixed(2);
        document.getElementById("balanceLeft").textContent = (totalPrice.toFixed(2) - discount) - paidAmount;
    }

    var tableBody = ordersTable.querySelector('tbody');
    tableBody.addEventListener("input", function(event) {
        if (event.target.tagName === "INPUT") {
            updateTotalPrice();
        }
    });

    document.getElementById("paidAmount").addEventListener("input", function(event) {
        updateTotalPrice();
    });

    $("#paymentMethodSelect").on('change', function() {
        var paymentMethod = $("#paymentMethodSelect").val();
        var paymentNumberLayout = document.getElementById("paymentNumberLayout");
        var paymentNumberSpan = document.getElementById("paymentNumberSpan");
        var paymentNumber = document.getElementById("paymentNumber");

        paymentNumber.value = "";
        switch (paymentMethod) {
            case "Cash":
                paymentNumberLayout.style.display = "none";
                break;
            case "Card":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Invoice Number";
                break;
            case "Cheque":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Cheque Number";
                break;
            case "Paytm":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Transcn Number";
                break;
            case "UPI":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Transcn Number";
                break;
            case "Multiple":
                paymentNumberLayout.style.display = "none";
                break;
            default:
                break;
        }
    });

    function escapeHtml(string) {
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
        };
        return String(string).replace(/[&<>"'\/]/g, function (s) {
            return entityMap[s];
        });
    }

    function decodeHtml(string) {
        const entityMap = {
            '&amp;': '&',
            '&lt;': '<',
            '&gt;': '>',
            '&quot;': '"',
            '&#39;': "'",
            '&#x2F;': '/',
        };
        return String(string).replace(/(&amp;|&lt;|&gt;|&quot;|&#39;|&#x2F;)/g, function (s) {
            return entityMap[s];
        });
    }
</script>
</html>
