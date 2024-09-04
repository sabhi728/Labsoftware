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

    #barcodeInput {
        border-radius: 5px;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0px;
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    #barcodeSearchBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
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
        width: 120px;
    }

    .inputReadonly {
        background: var(--lightgray);
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
        width: 250px;
    }

    .inputWriteable {
        border-radius: 5px;
        padding: 7.5px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
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
            <div id="ordersTableBack">
                <h5>Sample Receival</h5>
                <div id="headerHorizonatl" class="horizontal">
                    <input id="barcodeInput" placeholder="Enter barcode" type="number">
                    <button id="barcodeSearchBtn" type="submit" class="btn btn-primary" onclick="searchOrder('')">Search</button>
                </div>
                <br>
                <div id="sampleDataLayout" style="display: none;">
                    <div id="headerHorizonatl" class="horizontal">
                        <div class="vertical2">
                            <div class="horizontal inputForm">
                                <div class="vertical">
                                    <span>Bill No:</span>
                                    <input id="billNoInput" class="inputReadonly" readonly>
                                </div>
                                <div class="vertical">
                                    <span>Patient Name:</span>
                                    <input id="nameInput" class="inputReadonly" readonly>
                                </div>
                                <div class="vertical">
                                    <span>Phone:</span>
                                    <input id="phoneInput" class="inputReadonly" readonly>
                                </div>
                            </div>
                            <space style="height: 10px;"></space>
                            <div class="horizontal inputForm">
                                <div class="vertical">
                                    <span>Gender:</span>
                                    <input id="genderInput" class="inputReadonly" readonly>
                                </div>

                                <div class="vertical">
                                    <span>Age:</span>
                                    <input id="ageInput" class="inputReadonly" readonly>
                                </div>

                                <div class="vertical">
                                    <span>UMR:</span>
                                    <input id="umrInput" class="inputReadonly" readonly>
                                </div>
                            </div>
                            <space style="height: 10px;"></space>
                            <div class="horizontal inputForm">
                                <div class="vertical">
                                    <span>Sample Type:</span>
                                    <div id="sampleTypeInput" class="inputReadonly" readonly></div>
                                </div>
                                <div class="vertical">
                                    <span>Barcode:</span>
                                    <input id="barcodeContentInput" class="inputReadonly" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                            <th scope="col">Sl.No</th>
                            <th scope="col">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <br>
                    <div id="actionButtons">
                        <button id="acceptReceivalBtn" type="submit" class="btn btn-success" onclick="updateSampleBarcodeStatus('received')">Accept</button>
                        <button id="rejectReceivalBtn" type="submit" class="btn btn-danger" onclick="updateSampleBarcodeStatus('rejected')">Reject</button>
                    </div>
                    <span id="barcodeStatusMessage"></span>
                </div>
                <br>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Bill Number</th>
                        <th scope="col">Collected On</th>
                        <th scope="col">Sample Type</th>
                        <th scope="col">Barcode</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sampleBarcodes as $barcode)
                        <tr>
                            <td>{{ $barcode->bill_no }}</td>
                            <td>{{ $barcode->created_at }}</td>
                            <td>{!! $barcode->sample_type !!}</td>
                            <td>{{ $barcode->barcode }}</td>
                            <td><button type="submit" class="btn btn-primary" onclick="searchOrder('{{ $barcode->barcode }}')">Open</button></td>
                        </tr>
                        @endforeach
                    </tbody>
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
    var updateBarcodeValue = '';

    function searchOrder(barcode) {
        var barcodeInputValue = document.getElementById('barcodeInput').value;
        var sampleDataLayout = document.getElementById('sampleDataLayout');

        sampleDataLayout.style.display = "none";
        updateBarcodeValue = '';

        if (barcode != "") {
            barcodeInputValue = barcode;
        }

        if (barcodeInputValue == '') {
            alert('Enter barcode to search');
            return;
        }

        fetch(webUrl + `samplebarcodes/search_order_with_barcode/${barcodeInputValue}`)
            .then(response => response.json())
            .then(responseJson => {
                if (responseJson.status == 'success') {
                    updateBarcodeValue = responseJson.barcode;

                    document.getElementById('billNoInput').value = responseJson.bill_no;
                    document.getElementById('nameInput').value = responseJson.patient_name;
                    document.getElementById('phoneInput').value = responseJson.phone_number;
                    document.getElementById('genderInput').value = responseJson.gender;
                    document.getElementById('ageInput').value = responseJson.age;
                    document.getElementById('umrInput').value = responseJson.umr;
                    document.getElementById('sampleTypeInput').innerHTML = responseJson.sample_type;
                    document.getElementById('barcodeContentInput').value = responseJson.barcode;

                    var ordersTable = document.getElementById('ordersTable');
                    var tableBody = ordersTable.querySelector('tbody');
                    tableBody.innerHTML = '';

                    var count = 0;
                    responseJson.orderData.forEach(order => {
                        count++;
                        var row = tableBody.insertRow();
                        var cell1 = row.insertCell(0);
                        var cell2 = row.insertCell(1);
                        cell1.textContent = count;
                        cell2.textContent = order.order_name;
                    });

                    if (responseJson.barcode_status != "collected") {
                        document.getElementById('actionButtons').style.display = "none";
                        document.getElementById('barcodeStatusMessage').style.display = "block";
                        document.getElementById('barcodeStatusMessage').textContent = "Sample of this barcode is " + responseJson.barcode_status;
                    } else {
                        document.getElementById('actionButtons').style.display = "block";
                        document.getElementById('barcodeStatusMessage').style.display = "none";
                        document.getElementById('barcodeStatusMessage').textContent = "";
                    }

                    sampleDataLayout.style.display = "block";
                } else {
                    alert(responseJson.message);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateSampleBarcodeStatus(status) {
        var rejectReason = "";
        if (status == 'rejected') {
            var userInput = prompt("Enter reason for rejecting:");
            if (userInput === null || userInput === "") {
                return;
            } else {
                rejectReason = userInput;
            }
        }

        fetch(webUrl + `samplebarcodes/update_sample_barcode_status/${updateBarcodeValue}/${status}/${rejectReason}`)
            .then(response => response.text())
            .then(responseText => {
                if (responseText == 'success') {
                    document.getElementById('actionButtons').style.display = "none";
                    window.location.reload();
                } else {
                    alert(responseText);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }
</script>
</html>
