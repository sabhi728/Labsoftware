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
        width: 120px;
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
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="goToRoute('samplebarcodes/sample_collection')">Back</button>
                <button id="btnPrintAll" type="button" class="btn btn-primary" onclick="printAllBarcodes()">Print All</button>
                <button type="button" class="btn btn-primary" onclick="location.reload()">Refresh Samples</button>
            </div>
            <div id="ordersTableBack" style="width: 100%;">
                <div id="headerHorizonatl" class="horizontal">
                    <div class="vertical2">
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Bill No:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderEntry->bill_no }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Patient Name:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $patientDetails->patient_title_name }} {{ $patientDetails->patient_name }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Phone:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $patientDetails->phone }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Gender:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $patientDetails->gender }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Age:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $patientDetails->age }} {{ $patientDetails->age_type }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>UMR:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $patientDetails->umr_number }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col"></th>
                        <th scope="col">Sample Name</th>
                        <th scope="col">Orders</th>
                        <th scope="col">Reject Reason</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $orderGroups = [];
                            $itemCount = 0;
                            $billNo = $orderDetails->bill_no;
                            foreach ($orderDetails->orderData as $orderItems) {
                                $sampleType = $orderItems->sample_type;

                                if (!isset($orderGroups[$sampleType])) {
                                    $orderGroups[$sampleType] = [];
                                }

                                $orderGroups[$sampleType][] = $orderItems;
                            }
                        @endphp
                        @foreach ($orderGroups as $sampleType => $orders)
                            @php
                                $itemCount++;
                                $allBarcodesData[] = array(
                                    'itemCount' => $itemCount,
                                    'billNo' => $billNo,
                                    'sampleType' => $sampleType
                                );
                            @endphp
                            <tr class="barcodePrintColumn_{{ $itemCount }}">
                                <input class="orderId_{{ $itemCount }}" type="hidden" value="{{ $orders[0]->report_id }}" readonly>
                                @if($orders[0]->barcode_number == "")
                                    <td><button type="button" class="btn btn-primary" id="printBarcodeBtn_{{ $itemCount }}" onclick="printBarcode('{{ $itemCount }}', '{{ $billNo }}', '{{ $sampleType }}', 'false', null)">Print Barcode</button></td>
                                @else
                                    <td><button type="button" class="btn btn-primary" id="printBarcodeBtn_{{ $itemCount }}" onclick="printBarcode('{{ $itemCount }}', '{{ $billNo }}', '{{ $sampleType }}', 'false', null)">Reprint Barcode</button></td>
                                @endif
                                <td>{!! $sampleType !!}</td>
                                <td>{{ $orders[0]->order_name }}</td>
                                <td style="color: {{ $orders[0]->color }};">{{ $orders[0]->reject_reason }}</td>
                                @if($orders[0]->barcode_number == "")
                                    <td>
                                        <div id="inputWriteableLayout_{{ $itemCount }}" style="display: flex; flex-direction: row; justify-content: center; align-items: center;">
                                            <input id="inputWriteable_{{ $itemCount }}" class="inputWriteable" placeholder="Enter Barcode">
                                            <button id="inputWriteableSaveBtn_{{ $itemCount }}" onclick="printBarcode('{{ $itemCount }}', '{{ $billNo }}', '{{ $sampleType }}', 'true', null)" type="button" class="btn btn-primary">Save</button>
                                        </div>
                                    </td>
                                @else
                                    <td>{{ $orders[0]->barcode_number }}</td>
                                    <script>document.getElementById('btnPrintAll').style.display = "none";</script>
                                @endif
                            </tr>

                            @foreach ($orders as $order)
                                @if (!$loop->first)
                                    <tr class="barcodePrintColumn_{{ $itemCount }}">
                                        <input class="orderId_{{ $itemCount }}" type="hidden" value="{{ $order->report_id }}" readonly>
                                        <td></td>
                                        <td>{!! $sampleType !!}</td>
                                        <td>{{ $order->order_name }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                        @php $allBarcodesData = (isset($allBarcodesData)) ? json_encode($allBarcodesData) : json_encode(array()); @endphp
                    </tbody>
                </table>
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
    // if ("{{ $itemCount }}" == 0) {
    //     goToRoute('samplebarcodes/sample_collection');
    // }

    function printAllBarcodes() {
        var jsonData = <?php echo $allBarcodesData; ?>;
        var printingText = "";
        for (let i = 0; i < jsonData.length; i++) {
            printBarcode(`${jsonData[i].itemCount}`, `${jsonData[i].billNo}`, `${jsonData[i].sampleType}`, 'false', function(responseText) {
                printingText = printingText + responseText;
                if (i == jsonData.length - 1) {
                    sendToMachine(printingText);
                }
            });
        }
    }

    function printBarcode(id, billNo, sampleType, isInput, printAll) {
        var elements = document.getElementsByClassName("orderId_" + id);

        var values = "";
        for (var i = 0; i < elements.length; i++) {
            if (values == "") {
                values = elements[i].value;
            } else {
                values = values + "," + elements[i].value;
            }
        }

        $barcode = '0';
        if (isInput == 'true') {
            var barcodeInputValue = document.getElementById('inputWriteable_' + id).value;
            if (barcodeInputValue == '') {
                alert('Enter barcode to print');
                return;
            } else {
                $barcode = barcodeInputValue;
            }
        } else {
            var printButtonValue = document.getElementById('printBarcodeBtn_' + id).textContent;
            if (printButtonValue == "Reprint Barcode") {
                reprintBarcode(id, billNo, sampleType);
                return;
            }
        }

        fetch(webUrl + `samplebarcodes/generate_sample_barcode/${encodeURIComponent(billNo)}/${encodeURIComponent(sampleType)}/${encodeURIComponent(values)}/${encodeURIComponent($barcode)}`)
            .then(response => response.text())
            .then(responseText => {
                if (responseText == "Barcode already exist") {
                    alert(responseText);
                    return;
                }

                document.getElementById('inputWriteableLayout_' + id).style.display = "none";
                document.getElementById('btnPrintAll').style.display = "none";
                document.getElementById('printBarcodeBtn_' + id).textContent = "Reprint Barcode";
                $('.barcodePrintColumn_' + id).css('display', 'none');

                if (printAll != null) {
                    printAll(responseText);
                } else {
                    sendToMachine(responseText);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function reprintBarcode(id, billNo, sampleType) {
        var userInput = prompt("Enter reason for reprinting the barcode:");
        if (userInput === '' || userInput === null) {
            alert("You cannot reprint the barcode without reason.");
        } else {
            fetch(webUrl + `samplebarcodes/regenerate_sample_barcode/${encodeURIComponent(billNo)}/${encodeURIComponent(sampleType)}/${encodeURIComponent(userInput)}`)
                .then(response => response.text())
                .then(responseText => {
                    sendToMachine(responseText);
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    }

    function sendToMachine(responseText) {
        const blob = new Blob([responseText], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'barcode.txt';
        a.click();
        URL.revokeObjectURL(url);
        // window.location.reload();
        // const printWindow = window.open('', 'PRINT', 'height=400,width=600');
        // printWindow.document.write(`<pre>${responseText}</pre>`);
        // printWindow.document.close();

        // printWindow.onload = function() {
        //     printWindow.print();
        //     printWindow.close();
        // };
    }
</script>
</html>
