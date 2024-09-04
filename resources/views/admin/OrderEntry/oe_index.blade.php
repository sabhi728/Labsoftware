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
        /* border-bottom-right-radius: 0px;
        border-top-right-radius: 0px; */
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
    .table-responsive{
        height: 140px;
        overflow-y: scroll;
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
            <div class="actions" style="position: fixed;z-index: 999;top: 15px;left: 0;justify-content: center;align-items: center;display: flex;width: max-content;left: 50%;transform: translate(-50%, 0);">
                <button id="submitBillBtn" type="button" class="btn btn-primary">Submit</button>
                <button id="clearOrderItemsBtn" type="button" class="btn btn-primary">Clear</button>
                <button type="button" class="btn btn-primary" id="discountBtn">Discount</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('doctors/add')">Add Doctor</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('add_order')">Add Order</button>
                <!-- <button type="button" class="btn btn-primary">Add Expense</button> -->
            </div>
            <div id="ordersTableBack">
                <div id="headerHorizonatl" class="horizontal">
                    <img src="{{ asset('assets/patient.jpg') }}">
                    <div class="vertical">
                        <div class="horizontal inputForm">
                            <span>Phone/UMR:</span>
                            <div class="search-container">
                                <input id="phoneUmrInput" class="inputField" placeholder="Search Patient">
                                <div id="phoneUmrInputList" class="searchItemsList">
                                    <span>Item 1</span>
                                </div>
                            </div>
                            {{-- <button id="phoneUmrSearchBtn" onclick="searchPatient()" type="button" class="btn btn-primary">Search</button> --}}
                            <space style="width: 10px;"></space>
                            <button type="button" class="btn btn-primary" onclick="goToRoute('orderentry/add_patient')">Add New Patient</button>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="d-flex">
                            <div>
                                <div class="horizontal inputForm">
                                    <span>Name:</span>
                                    @if(isset($patientDetails))
                                        <input id="nameInput" class="inputReadonly" value="{{ $patientDetails->patient_title_name }} {{ $patientDetails->patient_name }}" readonly>
                                    @else
                                        <input id="nameInput" class="inputReadonly" readonly>
                                    @endif
                                </div>
                                <div class="horizontal inputForm mt-2">
                                    <span>Age/Gender:</span>
                                    @if(isset($patientDetails))
                                        <input id="ageGenderInput" class="inputReadonly" value="{{ $patientDetails->age }} {{ $patientDetails->age_type }} / {{ $patientDetails->gender }}" readonly>
                                    @else
                                        <input id="ageGenderInput" class="inputReadonly" readonly>
                                    @endif
                                </div>
                            </div>
                            <div style="margin-left:20px;">
                                <div class="horizontal inputForm">
                                    <span>Phone:</span>
                                    @if(isset($patientDetails))
                                        <input id="phoneInput" class="inputReadonly" value="{{ $patientDetails->phone }}" readonly>
                                    @else
                                        <input id="phoneInput" class="inputReadonly" readonly>
                                    @endif
                                </div>
                                <div class="horizontal inputForm mt-2">
                                    <span>UMR:</span>
                                    @if(isset($patientDetails))
                                        <input id="umrInput" class="inputReadonly" value="{{ $patientDetails->umr_number }}" readonly>
                                    @else
                                        <input id="umrInput" class="inputReadonly" readonly>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="horizontalAllCenter">
                    <div class="horizontal inputForm">
                        <span>Doctor:</span>
                        <div class="search-container">
                            <input id="doctorInput" class="inputField" placeholder="Doctor">
                            <div id="doctorInputList" class="searchItemsList">
                                <span>Item 1</span>
                            </div>
                        </div>
                    </div>
                    <div class="horizontal inputForm">
                        <span>Referred By:</span>
                        <div class="search-container">
                            <input id="referredByInput" class="inputField" placeholder="Referred By">
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
                <div class="table-responsive">
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
                            <tr id="noDataOrderTable">
                                <td colspan="5" style="text-align: center;font-size: 14px;">
                                    There are no items to display
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <hr>
                <div style="align-items: end;" class="vertical">
                    <div class="horizontal inputForm">
                        <span>Total Bill</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <balancespan id="totalBill" style="text-align: center;width: 100px;">0</balancespan>
                    </div>
                    <space style="height: 10px;"></space>
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
                    <div class="horizontal inputForm">
                        <span>Payment Type</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <select id="paymentMethodSelect" style="width: 100px;">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Paytm">Paytm</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                    <space style="height: 3px;"></space>
                    <div id="paymentNumberLayout" class="horizontal inputForm" style="display: none;">
                        <span id="paymentNumberSpan">Invoice Number</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <input id="paymentNumber" style="text-align: center;width: 100px;" class="inputField">
                    </div>
                    <space style="height: 3px;"></space>
                    <div class="horizontal inputForm">
                        <span>Paid Amount</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <input id="paidAmount" style="text-align: center;width: 100px;" class="inputField" value="0">
                    </div>
                    <space style="height: 3px;"></space>
                    <div class="horizontal inputForm">
                        <span>Balance</span>
                        <colon style="width: 50px;text-align:center;">:</colon>
                        <balancespan id="balanceLeft" style="text-align: center;width: 100px;">0</balancespan>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
<script>
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

    document.addEventListener("click", function(event) {
        var searchItemsLists = document.querySelectorAll(".searchItemsList");

        searchItemsLists.forEach(function(searchItemsList) {
            if (!searchItemsList.contains(event.target)) {
                searchItemsList.style.display = "none";
            }
        });
    });

    const ordersTable = document.getElementById('ordersTable');
    var selectedDoctorId = "";
    var selectedReferredById = "";

    var phoneUmrInputHandler = function(event) {
        const phoneUmrInput = document.getElementById('phoneUmrInput');
        const nameInput = document.getElementById('nameInput');
        const ageGenderInput = document.getElementById('ageGenderInput');
        const phoneInput = document.getElementById('phoneInput');
        const umrInput = document.getElementById('umrInput');
        const phoneUmrInputList = document.getElementById('phoneUmrInputList');

        if (phoneUmrInput.value.trim() == ""){
            phoneUmrInputList.style.display = "none";
        } else {
            if (phoneUmrInput.value.trim() != "") {
                nameInput.value = "";
                ageGenderInput.value = "";
                phoneInput.value = "";
                umrInput.value = "";

                fetch(webUrl + `orderentry/search_patient/${phoneUmrInput.value}`)
                    .then(response => response.json())
                    .then(responseJson => {
                        var size = Object.keys(responseJson).length;

                        if (size == 0) {
                            phoneUmrInputList.style.display = "none";
                        } else {
                            let contentHTML = '';
                            responseJson.forEach(item => {
                                contentHTML += `
                                    <div class="setBox" onclick="selectPatient('${item.patient_title_name}','${item.patient_name}','${item.age}','${item.age_type}','${item.gender}','${item.phone}','${item.umr_number}')">${item.patient_title_name} ${item.patient_name} (${item.umr_number}) (${item.age} ${item.age_type} / ${item.gender})</div>
                                `;
                            });

                            phoneUmrInputList.innerHTML = contentHTML;
                            phoneUmrInputList.style.display = "flex";
                        }
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }
        }
    }
    document.getElementById('phoneUmrInput').addEventListener('input', phoneUmrInputHandler);

    function selectPatient(patient_title_name, patient_name, age, age_type, gender, phone, umr_number) {
        const phoneUmrInput = document.getElementById('phoneUmrInput');
        const nameInput = document.getElementById('nameInput');
        const ageGenderInput = document.getElementById('ageGenderInput');
        const phoneInput = document.getElementById('phoneInput');
        const umrInput = document.getElementById('umrInput');
        const phoneUmrInputList = document.getElementById('phoneUmrInputList');

        phoneUmrInput.removeEventListener('input', phoneUmrInputHandler);
        phoneUmrInput.value = '';
        phoneUmrInput.addEventListener('input', phoneUmrInputHandler);

        nameInput.value = `${patient_title_name} ${patient_name}`;
        ageGenderInput.value = `${age} ${age_type} / ${gender}`;
        if (phone == "null") {
            phoneInput.value = '';
        } else {
            phoneInput.value = `${phone}`;
        }
        umrInput.value = `${umr_number}`;

        phoneUmrInputList.style.display = "none";
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

        var umrInput = $("#umrInput").val();
        var referredByInput = $("#referredByInput").val();
        var totalBill = $("#totalBill").text();
        var paymentMethodSelect = $("#paymentMethodSelect").val();
        var paymentNumber = $("#paymentNumber").val();
        var paidAmount = $("#paidAmount").val();
        var reasonForDiscount = $("#dlgReasonForDiscount").val();
        var isDisPercentage = dlgIsDisPercentage.checked;

        const selectedOrderCommaSeparatedValues = selectedOrderValues.join(',');
        const selectedOrderAmountsCommaSeparatedValues = selectedOrderAmountsValues.join(',');
        var selectedOrderDate = $("#selectedOrderDate").text();

        var form = document.createElement("form");
        form.method = "POST";
        form.action = webUrl + "orderentry/add_order_entry";

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

        form.appendChild(createHiddenInput("selectedDoctorName", $("#doctorInput").val()));
        form.appendChild(createHiddenInput("selectedReferredByName", $("#referredByInput").val()));

        form.appendChild(createHiddenInput("reasonForDiscount", reasonForDiscount));
        form.appendChild(createHiddenInput("isDisPercentage", isDisPercentage));
        form.appendChild(createHiddenInput("overallDiscount", overallDiscount));

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
    searchResultArrowSelect('phoneUmrInput', 'phoneUmrInputList');

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
                        resultLayout.style.width = "200px";
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
        cell3.innerHTML = `<i style="color:red;font-size:25px;cursor:pointer;" class='bx bx-trash'></i>`;
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
