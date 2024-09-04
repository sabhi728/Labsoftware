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
<body>
    <div class="header">
        @include('include.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('include.sidebar')
        </div>
        <div class="main">
            <div id="ordersTableBack">
                <form class="input-group mb-3" action="" method="get">
                    <span class="input-group-text" id="inputGroup-sizing-default">Search</span>
                    <input type="text" name="search" class="form-control" value="{{ $searchValue }}">
                </form>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col">Bill Number</th>
                            <th scope="col">Bill Date</th>
                            <th scope="col">Patient Name</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Age/Gender</th>
                            <th scope="col">Orders</th>
                            <th scope="col">Reff Doctor</th>
                            <th scope="col">Reff Company</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderDetails as $order)
                            @if ($user->department_access != null)
                                @if (!empty($order->order_name_txt))
                                    <tr>
                                        <th></th>
                                        <th>
                                            <div style="display: flex;flex-direction:row;align-items: center;">
                                                @if($order->balance == "0")
                                                    <button style="width: 120px;" type="button" class="btn btn-primary" onclick="showBillPaymentDialog('{{ $order->bill_no }}')">Bill Details</button>
                                                @else
                                                    <button style="width: 120px;" type="button" class="btn btn-success" onclick="showBillPaymentDialog('{{ $order->bill_no }}')">Bill Payment</button>
                                                @endif
                                                <space style="width:10px;"></space>
                                                <button style="width: 120px;" type="button" class="btn btn-success" onclick="goToRoute('orderentry/bill_details/{{ $order->bill_no }}')">Print Bill</button>
                                            </div>
                                            <div style="display: flex;flex-direction:row;align-items: center;margin-top:10px;">
                                                {{-- @if($order->status == "completed") --}}
                                                    <button style="width: 120px;" type="button" class="btn btn-danger" onclick="goToRoute('orderbills/completed_bill_details/{{ $order->bill_no }}')">Orders List</button>
                                                    <space style="width:10px;"></space>
                                                {{-- @endif --}}
                                                <button style="width: 120px;" type="button" class="btn btn-secondary" onclick="showTransactionsDialog('{{ $order->bill_no }}')">Transactions</button>
                                            </div>
                                        </th>
                                        <td>{{ $order->bill_no }}</td>
                                        <td>{{ $order->order_date }}</td>
                                        <td>{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td>{{ $order->patient_phone }}</td>
                                        <td>{{ $order->patient_age }} {{ $order->patient_age_type }} / {{ $order->patient_gender }}</td>
                                        <td>{{ $order->order_name_txt }}</td>
                                        <td>{{ $order->doc_name }}</td>
                                        <td>{{ $order->referred_by }}</td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <th></th>
                                    <th>
                                        <div style="display: flex;flex-direction:row;align-items: center;">
                                            @if($order->balance == "0")
                                                <button style="width: 120px;" type="button" class="btn btn-primary" onclick="showBillPaymentDialog('{{ $order->bill_no }}')">Bill Details</button>
                                            @else
                                                <button style="width: 120px;" type="button" class="btn btn-success" onclick="showBillPaymentDialog('{{ $order->bill_no }}')">Bill Payment</button>
                                            @endif
                                            <space style="width:10px;"></space>
                                            <button style="width: 120px;" type="button" class="btn btn-success" onclick="goToRoute('orderentry/bill_details/{{ $order->bill_no }}')">Print Bill</button>
                                        </div>
                                        <div style="display: flex;flex-direction:row;align-items: center;margin-top:10px;">
                                            {{-- @if($order->status == "completed") --}}
                                                <button style="width: 120px;" type="button" class="btn btn-danger" onclick="goToRoute('orderbills/completed_bill_details/{{ $order->bill_no }}')">Orders List</button>
                                                <space style="width:10px;"></space>
                                            {{-- @endif --}}
                                            <button style="width: 120px;" type="button" class="btn btn-secondary" onclick="showTransactionsDialog('{{ $order->bill_no }}')">Transactions</button>
                                        </div>
                                    </th>
                                    <td>{{ $order->bill_no }}</td>
                                    <td>{{ $order->order_date }}</td>
                                    <td>{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                    <td>{{ $order->patient_phone }}</td>
                                    <td>{{ $order->patient_age }} {{ $order->patient_age_type }} / {{ $order->patient_gender }}</td>
                                    <td>{{ $order->order_name_txt }}</td>
                                    <td>{{ $order->doc_name }}</td>
                                    <td>{{ $order->referred_by }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                {{ $orderDetails->appends(request()->input())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <div class="modal fade" id="billPaymentModel" tabindex="-1" role="dialog" aria-labelledby="billPaymentModelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <style>
                    sectionHeader {
                        font-family: var(--font1);
                        font-weight: 600;
                        font-size: 20px;
                        border-bottom: 1px solid var(--lightdarkgray);
                        width: 100%;
                        display: block;
                        padding-bottom: 8px;
                        margin-bottom: 20px;
                    }

                    #patientStartnameSelect {
                        width: max-content;
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                    }

                    #patientNameInput {
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;
                    }

                    #ageInput {
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                    }

                    #ageTypeSelect {
                        width: max-content;
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;
                    }

                    #formBack {
                        background: white;
                        border-radius: 10px;
                        border: 0;
                        margin-top: 10px;
                    }

                    .inputBack {
                        font-family: var(--font1);
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        margin-bottom: 15px;
                    }

                    .inputBack label {
                        font-weight: 300;
                        font-size: 15px;
                        width: 200px;
                    }

                    .inputBack input {
                        border-radius: 5px;
                        padding: 5px 10px;
                        font-size: 15px;
                        border: 1px solid var(--lightdarkgray);
                        width: 100%;
                    }

                    .inputBack select {
                        padding: 5px 10px;
                        border-radius: 5px;
                        border: 1px solid var(--lightdarkgray);
                        background: white;
                        width: 100%;
                        font-size: 15px;
                    }

                    .search-container {
                        width: 100%;
                        position: relative;
                        display: inline-block;
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

                    .checkbox {
                        width: 22px !important;
                        margin-left: 10px;
                        margin-right: 10px;
                        height: 22px;
                    }
                </style>
                <div class="main">
                    <div id="formBack">
                        @csrf
                        <input type="hidden" name="bill_no" value="">
                        <input type="hidden" name="umr_number" value="">
                        <input type="hidden" id="doctor" name="doctor" value="">
                        <input type="hidden" id="redirect_url" name="redirect_url" value="">
                        <sectionHeader>Bill Payment</sectionHeader>
                        <div class="inputBack">
                            <label id="dlgBilledAmount">Billed Amount: 400</label>
                        </div>
                        <div class="inputBack">
                            <label>Overall Dis:</label>
                            <input id="dlgOverallDis" type="number" value="0" required>
                            <input id="dlgIsDisPercentage" class="checkbox" type="checkbox">
                            <span>%</span>
                        </div>
                        <div class="inputBack">
                            <label>Reason For Discount:</label>
                            <input id="dlgReasonForDiscount" type="text" required>
                        </div>
                        <div class="inputBack">
                            <label id="dlgPaidTillNow">Paid till now: 400</label>
                        </div>
                        <div class="inputBack">
                            <label id="dlgBalanceLeft">Balance: 0</label>
                        </div>
                        <div class="inputBack">
                            <label>Amount paid now:</label>
                            <input id="dlgAmountPaidNow" type="number" required>
                        </div>
                        <div class="inputBack">
                            <label>Payment Method:</label>
                            <select id="dlgPaymentMethodSelect">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Paytm">Paytm</option>
                                <option value="UPI">UPI</option>
                            </select>
                        </div>
                        <div id="paymentNumberLayout" class="inputBack" style="display: none;">
                            <label id="paymentNumberSpan">Invoice Number:</label>
                            <input id="paymentNumber">
                        </div>
                        <div style="display: flex;">
                            <button id="dlgSubmitBillPaymentBtn" type="submit" class="btn btn-primary" style="margin-right:10px;">Submit</button>
                            <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" style="margin-right:10px;">Close</button>
                            <button id="sendPaymentReminderBtn" class="btn btn-success">Send Payment Reminder</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionsModel" tabindex="-1" role="dialog" aria-labelledby="transactionsModelModelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 600px;">
            <div class="modal-content">
                <style>
                    sectionHeader {
                        font-family: var(--font1);
                        font-weight: 600;
                        font-size: 20px;
                        border-bottom: 1px solid var(--lightdarkgray);
                        width: 100%;
                        display: block;
                        padding-bottom: 8px;
                        margin-bottom: 20px;
                    }
                </style>
                <div class="main">
                    <div id="formBack">
                        @csrf
                        <sectionHeader>Transactions</sectionHeader>
                        <table class="table" id="transactionsDataTable">
                            <thead>
                                <tr>
                                <th scope="col"></th>
                                <th scope="col">Amount</th>
                                <th scope="col">Payment Method</th>
                                <th scope="col">Txn Id</th>
                                <th scope="col">Received On</th>
                                <th scope="col">Received By</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div style="display: flex;">
                            <button type="submit" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    // let table = new DataTable('#ordersTable');
    var userLeftBalance = "";
    var currentBillNo = "";

    function showBillPaymentDialog(billNo) {
        currentBillNo = billNo;

        var dlgBilledAmount = document.getElementById('dlgBilledAmount');
        var dlgOverallDis = document.getElementById('dlgOverallDis');
        var dlgIsDisPercentage = document.getElementById('dlgIsDisPercentage');
        var dlgReasonForDiscount = document.getElementById('dlgReasonForDiscount');
        var dlgPaidTillNow = document.getElementById('dlgPaidTillNow');
        var dlgBalanceLeft = document.getElementById('dlgBalanceLeft');
        var dlgAmountPaidNow = document.getElementById('dlgAmountPaidNow');
        var dlgPaymentMethodSelect = document.getElementById('dlgPaymentMethodSelect');
        var dlgSubmitBillPaymentBtn = document.getElementById('dlgSubmitBillPaymentBtn');
        var sendPaymentReminderBtn = document.getElementById('sendPaymentReminderBtn');

        dlgOverallDis.value = "0";
        dlgIsDisPercentage.checked = false;
        dlgReasonForDiscount.value = "";
        dlgAmountPaidNow.value = "";
        dlgPaymentMethodSelect.selectedIndex = 0;
        $("#paymentNumber").val("");
        document.getElementById('paymentNumberLayout').style.display = "none";

        dlgSubmitBillPaymentBtn.style.display = "none";
        sendPaymentReminderBtn.style.display = "none";

        fetch(webUrl + `orderbills/get_order_entry_data/${billNo}`)
                .then(response => response.json())
                .then(responseJson => {
                    dlgBilledAmount.textContent = "Billed Amount: " + responseJson.total_bill;
                    dlgPaidTillNow.textContent = "Paid till now: " + responseJson.paid_amount;
                    dlgBalanceLeft.textContent = "Balance: " + responseJson.balance;
                    userLeftBalance = responseJson.balance;

                    dlgOverallDis.value = responseJson.overall_dis;
                    var checkedBool = (responseJson.is_dis_percentage == "false") ? false : true;
                    dlgIsDisPercentage.checked = checkedBool;
                    dlgReasonForDiscount.value = responseJson.reason_for_discount;

                    if (responseJson.balance == "0") {
                        dlgSubmitBillPaymentBtn.style.display = "none";
                        sendPaymentReminderBtn.style.display = "none";
                    } else {
                        dlgSubmitBillPaymentBtn.style.display = "block";
                        sendPaymentReminderBtn.style.display = "block";

                        sendPaymentReminderBtn.addEventListener('click', function() {
                            Swal.fire({
                                title: 'Sending Reminder...',
                                text: 'Please wait and do not close the window.',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                            });

                            var xhr = new XMLHttpRequest();
                            xhr.open('GET', webUrl + 'send_payment_reminder/' + billNo, true);

                            xhr.onload = function () {
                                if (xhr.status === 200) {
                                    var jsonResponse = JSON.parse(xhr.response);
                                    var message = jsonResponse.message;

                                    Swal.fire({
                                        title: message
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Failed to send payment reminder.'
                                    });
                                }
                            };

                            xhr.send();
                        });
                    }

                    $("#billPaymentModel").modal('show');
                })
                .catch(error => console.error('Error fetching data:', error));
    }

    function showTransactionsDialog(billNo) {
        var transactionsDataTable = document.getElementById('transactionsDataTable');

        fetch(webUrl + `orderbills/get_order_transactions_data/${billNo}`)
                .then(response => response.json())
                .then(responseJson => {
                    var tableBody = transactionsDataTable.querySelector('tbody');
                    tableBody.innerHTML = '';
                    var count = 0;

                    responseJson.forEach(transaction => {
                        var row = tableBody.insertRow();
                        var cell0 = row.insertCell(0);
                        var cell1 = row.insertCell(1);
                        var cell2 = row.insertCell(2);
                        var cell3 = row.insertCell(3);
                        var cell4 = row.insertCell(4);
                        var cell5 = row.insertCell(5);

                        count++;
                        cell0.textContent = count;
                        cell1.textContent = transaction.amount;
                        cell2.textContent = transaction.payment_method;
                        cell3.textContent = transaction.txn_id;

                        const dateObject = new Date(transaction.created_at);
                        const readableDate = dateObject.toLocaleString();

                        cell4.textContent = readableDate;
                        cell5.textContent = transaction.full_name;
                    });

                    $("#transactionsModel").modal('show');
                })
                .catch(error => console.error('Error fetching data:', error));
    }

    $("#dlgSubmitBillPaymentBtn").on('click', function() {
        var discountAmount = 0;
        if ($("#dlgOverallDis").val() == "" || $("#dlgOverallDis").val() == "0") {
            discountAmount = 0;
        } else {
            discountAmount = $("#dlgOverallDis").val();
        }

        if (discountAmount != 0) {
            if ($("#dlgReasonForDiscount").val() == "") {
                alert("Enter reason for discount");
                return;
            }
        }

        if ($("#dlgAmountPaidNow").val() != "0") {
            if (document.getElementById('paymentNumberLayout').style.display != "none") {
                if ($("#paymentNumber").val() == "") {
                    alert("Enter payment number");
                    return;
                }
            }
        }

        var balanceLeft = $("#dlgBalanceLeft").text().replace('Balance: ', '').trim();
        var amountPaidNow = $("#dlgAmountPaidNow").val().trim();
        var afterBalance = parseFloat(balanceLeft) - parseFloat(amountPaidNow);

        if (afterBalance.toLocaleString().includes('-')) {
            alert("Balance amount cannot be in minus");
            return;
        }

        var form = document.createElement("form");
        form.method = "POST";
        form.action = webUrl + "orderbills/save_bill_payment";

        function createHiddenInput(name, value) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = name;
            input.value = value;
            return input;
        }

        form.appendChild(createHiddenInput("billNo", currentBillNo));
        form.appendChild(createHiddenInput("overallDis", discountAmount));
        form.appendChild(createHiddenInput("isDisPercentage", document.getElementById('dlgIsDisPercentage').checked));
        form.appendChild(createHiddenInput("reasonForDiscount", $("#dlgReasonForDiscount").val()));
        form.appendChild(createHiddenInput("amountPaidNow", $("#dlgAmountPaidNow").val()));
        form.appendChild(createHiddenInput("paymentMethod", $("#dlgPaymentMethodSelect").val()));
        form.appendChild(createHiddenInput("paymentNumber", $("#paymentNumber").val()));

        var csrfToken = "{{ csrf_token() }}";
        form.appendChild(createHiddenInput("_token", csrfToken));

        document.body.appendChild(form);
        form.submit();
    });

    $("#dlgOverallDis").on('change', function() {
        updateDiscount();
    });

    $("#dlgIsDisPercentage").on('change', function() {
        updateDiscount();
    });

    function updateDiscount() {
        if ($('#dlgIsDisPercentage').prop('checked')) {
            var percentage = parseFloat($("#dlgOverallDis").val());
            var minusPercentage = userLeftBalance * (percentage / 100);
            dlgBalanceLeft.textContent = "Balance: " + (userLeftBalance - minusPercentage);
        } else {
            var overallDis = parseFloat($("#dlgOverallDis").val());
            dlgBalanceLeft.textContent = "Balance: " + (userLeftBalance - overallDis);
        }
    }

    $("#dlgPaymentMethodSelect").on('change', function() {
        var paymentMethod = $("#dlgPaymentMethodSelect").val();
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
                paymentNumberSpan.textContent = "Invoice Number:";
                break;
            case "Cheque":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Cheque Number:";
                break;
            case "Paytm":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Transcn Number:";
                break;
            case "UPI":
                paymentNumberLayout.style.display = "flex";
                paymentNumberSpan.textContent = "Transcn Number:";
                break;
            case "Multiple":
                paymentNumberLayout.style.display = "none";
                break;
            default:
                break;
        }
    });
</script>
</html>
