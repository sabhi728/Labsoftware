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
                <button type="button" class="btn btn-primary" onclick="goToRoute('orderbills/dispatch_page_go_back/{{ $billNo }}')">Back To Bills</button>
                @if(count($resultReports) != 0)
                    <button type="button" class="btn btn-danger" onclick="sendAllDispatchSms('{{ $billNo }}')">SMS Selected Reports</button>
                    <button type="button" class="btn btn-success" onclick="dispatchAllBill('{{ $billNo }}')">Dispatch Selected Reports</button>
                @endif
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                            <th><input id="allSelectCheckbox" type="checkbox"></th>
                            <th scope="col">Orders</th>
                            <th scope="col">Date Taken</th>
                            <th scope="col">Sample Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $itemCount = 0; @endphp
                        @foreach($resultReports as $report)
                        @php $itemCount++; @endphp
                            <tr>
                                <td>
                                    <input class="reportSelectCheckbox" type="checkbox"
                                    @if($report->status == "Dispatched")
                                        @disabled(true)
                                    @endif value="{{ $report->report_id }}">
                                </td>
                                <td>{{ $report->order_name }}</td>
                                <td>{{ $report->order_date }}</td>
                                <td>{!! $report->sample_type !!}</td>
                                <td>
                                    <button type="button" class="btn btn-primary" onclick="printBill('{{$itemCount}}_billDiv')">Download PDF</button>
                                    <button type="button" class="btn btn-danger" onclick="sendDispatchSms('{{ $report->bill_no }}','{{ $report->report_no }}')">Send SMS</button>
                                    @if($report->status != "Dispatched")
                                        <button type="button" class="btn btn-success" onclick="dispatchBill('{{ $report->bill_no }}','{{ $report->id }}')">Dispatch</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    @php $itemCount = 0; @endphp
    @foreach($resultReports as $order)
        @php $itemCount++; @endphp
        @php $orderDetails = $order['printable_content'] @endphp
        <div class="billDiv" id="{{$itemCount}}_billDiv" style="display:none;">
            @include('include.order_result_report')
        </div>
    @endforeach

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    const balance = "{{ $leftBalance }}";

    function printBill($divId) {
        if (balance != "0") {
            alert("Cannot download until pending balance is clear.")
            return;
        }

        var divElements = document.getElementById($divId).innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"><link rel="stylesheet" href="{{ asset("css/print.css") }}"></head><body style="font-family: \'Microsoft JhengHei\', Arial;padding-left:15mm;padding-right:15mm;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 200);
    }

    function dispatchBill(billNo, id) {
        if (balance != "0") {
            alert("Cannot dispatch until pending balance is clear.")
            return;
        }

        goToRoute(`orderbills/result_dispatched/${billNo}/${id}`)
    }

    function dispatchAllBill(billNo) {
        if (balance != "0") {
            alert("Cannot dispatch until pending balance is clear.")
            return;
        }

        const reportSelectCheckbox = document.getElementsByClassName('reportSelectCheckbox');
        var selectedOrderIds = "";

        for (var i = 0; i < reportSelectCheckbox.length; i++) {
            if (reportSelectCheckbox[i].checked) {
                if (selectedOrderIds == "") {
                    selectedOrderIds+= reportSelectCheckbox[i].value;
                } else {
                    selectedOrderIds+= "," + reportSelectCheckbox[i].value;
                }
            }
        }

        if (selectedOrderIds == "") {
            alert('Select orders to dispatch');
            return;
        }

        goToRoute(`orderbills/result_all_dispatched/${billNo}/${selectedOrderIds}`)
    }

    function sendDispatchSms(billNo, reportNo) {
        if (balance != "0") {
            alert("Cannot sms until pending balance is clear.")
            return;
        }

        goToRoute(`send_dispatch_sms/${billNo}/${reportNo}`)
    }

    function sendAllDispatchSms(billNo) {
        if (balance != "0") {
            alert("Cannot sms until pending balance is clear.")
            return;
        }

        const reportSelectCheckbox = document.getElementsByClassName('reportSelectCheckbox');
        var selectedOrderIds = "";

        for (var i = 0; i < reportSelectCheckbox.length; i++) {
            if (reportSelectCheckbox[i].checked) {
                if (selectedOrderIds == "") {
                    selectedOrderIds+= reportSelectCheckbox[i].value;
                } else {
                    selectedOrderIds+= "," + reportSelectCheckbox[i].value;
                }
            }
        }

        if (selectedOrderIds == "") {
            alert('Select orders to dispatch');
            return;
        }

        goToRoute(`send_all_dispatch_sms/${billNo}/${selectedOrderIds}`)
    }

    const allSelectCheckbox = document.getElementById('allSelectCheckbox');
    const reportSelectCheckbox = document.querySelectorAll('.reportSelectCheckbox');

    allSelectCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;

        reportSelectCheckbox.forEach(function(checkbox) {
            if (!checkbox.disabled) {
                checkbox.checked = isChecked;
            }
        });
    });
</script>
</html>

@php
    $actionSuccess = session('actionSuccess');
    if ($actionSuccess) {
        $actionMessage = session('actionMessage');

        if ($actionMessage == "go_back") {
            echo '<script>window.history.back();</script>';
        } else {
            echo '<script>alert(`'.$actionMessage.'`);</script>';
        }
    }
@endphp
