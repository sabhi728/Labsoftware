<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_details.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .main {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .BoldColumns {
        font-weight: bold;
    }
</style>
<body>
    <div class="main_container" style="text-align: center; font-family: 'Microsoft JhengHei', Arial;">
        <div class="main">
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Exit</button>
                <button type="button" class="btn btn-primary" onclick="printrprt()">Print</button>
                <button type="button" class="btn btn-primary" onclick="printWithoutTotals()">Print Without Totals</button>
            </div>
            <br>
            <div id="BillRpt" style="paddig-bottom:100px;">
                <h4 style="text-align:center;margin-top:30px;">
                    Shift Collection Detailed Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b> Username: {{ $username }}
                </h4>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1000px; text-align: left; margin-top: 30px; ">
                    <table class="Totals" style="width: 100%; margin-bottom: 10px; border-bottom: groove; ">
                        <tbody>
                            <tr style="width:100%">
                                <td style="width:17%"><b>Total amount</b></td>
                                <td style="width:2%">:</td>
                                <td style="width:14%">{{ $totalAmount }}</td>
                                <td style="width:17%"><b>Discount amount</b></td>
                                <td style="width:2%">:</td>
                                <td style="width:14%">{{ $dicountAmount }}</td>
                                <td style="width:17%"><b>Final amount</b></td>
                                <td style="width:2%">:</td>
                                <td style="width:14%">{{ $finalAmount }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Advance amount</b></td>
                                <td>:</td>
                                <td>{{ $advanceAmount }}</td>
                                <td><b>Balance amount</b></td>
                                <td>:</td>
                                <td>{{ $balanceAmount }}</td>
                                <td><b>Previous dues Paid</b></td>
                                <td>:</td>
                                <td>{{ $previousDuesPaid }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Cash</b></td>
                                <td>:</td>
                                <td>{{ $cashPaid }}</td>
                                <td><b>Card</b></td>
                                <td>:</td>
                                <td>{{ $cardPaid }}</td>
                                <td><b>Cheque</b></td>
                                <td>:</td>
                                <td>{{ $chequePaid }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Paytm</b></td>
                                <td>:</td>
                                <td>{{ $paytmPaid }}</td>
                                <td><b>UPI</b></td>
                                <td>:</td>
                                <td>{{ $upiPaid }}</td>
                                <td><b>Previous Due Cash</b></td>
                                <td>:</td>
                                <td>{{ $previousCashPaid }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Previous Due Card</b></td>
                                <td>:</td>
                                <td>{{ $previousCardPaid }}</td>
                                <td><b>Previous Due Cheque</b></td>
                                <td>:</td>
                                <td>{{ $previousChequePaid }}</td>
                                <td><b>Previous Due Paytm</b></td>
                                <td>:</td>
                                <td>{{ $previousPaytmPaid }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Previous Due UPI</b></td>
                                <td>:</td>
                                <td>{{ $previousUpiPaid }}</td>
                                <td><b>Bill Count</b></td>
                                <td>:</td>
                                <td>{{ $billCount }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Total Amount Received</b></td>
                                <td>:</td>
                                <td>{{ $paidAmount }}</td>
                                <td><b>Total Return Amount</b></td>
                                <td>:</td>
                                <td>{{ $returnAmount }}</td>
                                <td><b>Total Credits</b></td>
                                <td>:</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width: 100%; margin-bottom: 10px; border-bottom: groove;">
                        <thead>
                            <tr>
                                <td colspan="10" style="border-bottom: groove;text-align:center;font-size:large;font-weight:bolder;">OP BILLS</td>
                            </tr>
                            <tr style="width:100%;border-bottom:groove;">
                                <th style="width:10px"><b>ReqNo</b></th>
                                <th style="width:12px"><b>ReqDt</b></th>
                                <th style="width:350px" colspan="2"><b>Patient Name</b></th>
                                <th style="width:450px" colspan="6"><b>C/O</b></th>
                            </tr>
                            <tr style="width: 100%;border-bottom:groove; ">
                                <th style="padding-left:25px;" colspan="3"><b>Investigation</b></th>
                                <th><b>Rate</b></th>
                                <th><b>Total</b></th>
                                <th><b>Rcvd</b></th>
                                <th><b>Disc</b></th>
                                <th><b>Ret</b></th>
                                <th><b>Bal</b></th>
                                <th><b>Credits</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderDetails as $order)
                                <tr>
                                    <td style="width:4%" class="BoldColumns">{{ $order->bill_no }}</td>
                                    <td style="width:7%" class="Dateclmn BoldColumns">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                    <td style="width:31.5%" class="BoldColumns" colspan="2">{{ $order->patient_title_name }} {{ $order->patient_name }} ({{ $order->patient_age }}{{ $order->patient_age_type }} {{ $order->patient_gender }})</td>
                                    <td style="width: 45.5%" class="BoldColumns" colspan="6">{{ $order->doc_name }}</td>
                                </tr>
                                @php
                                    $allOrdersName = explode(', ', $order->order_name_txt);
                                    $allOrdersRate = explode(',', $order->order_amount);
                                @endphp
                                @if (count($allOrdersName) == count($allOrdersRate))
                                    @foreach($allOrdersName as $key => $orderItem)
                                        <tr>
                                            <td style="width:40%;padding-left:25px;" class="GridColumn" colspan="3">{{ $orderItem }}</td>
                                            <td style="width:6%" class="GridColumn">{{ $allOrdersRate[$key] }}</td>
                                            <td style="width:9%" class="GridColumn"></td>
                                            <td style="width:9%" class="GridColumn"></td>
                                            <td style="width:9%" class="GridColumn"></td>
                                            <td style="width:9%" class="GridColumn"></td>
                                            <td style="width:9%" class="GridColumn"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td style="width:40%;padding-left:25px;" class="GridColumn" colspan="3">{{ $orderItem }}</td>
                                        <td style="width:6%" class="GridColumn">{{ print_r($allOrdersName) }}</td>
                                        <td style="width:9%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn">{{ $order->order_ids }}</td>
                                        <td style="width:9%" class="GridColumn">ERROR</td>
                                        <td style="width:9%" class="GridColumn">{{ print_r($allOrdersRate) }}</td>
                                        <td style="width:9%" class="GridColumn">ERROR</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="width:6%" class="BoldColumns"></td>
                                    <td style="width:6%" class="Dateclmn BoldColumns"></td>
                                    <td style="width:22%" class="BoldColumns"></td>
                                    <td style="width:17.5%" class="GridColumn"></td>
                                    <td style="width:9%" class="GridColumn">{{ $order->total_bill }}</td>
                                    <td style="width:9%" class="GridColumn">{{ $order->paid_amount }}</td>
                                    <td style="width:9%" class="GridColumn">{{ $order->overall_dis }}{{ ($order->is_dis_percentage == 'true') ? "%" : "" }}</td>
                                    <td style="width:9%" class="GridColumn">{{ $order->return_amount }}</td>
                                    <td style="width:9%" class="GridColumn">{{ $order->balance }}</td>
                                    <td style="width:9%" class="GridColumn"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="10" style="border-top:groove;border-bottom: groove;text-align:center;font-size:large;font-weight:bolder;">Previous Dues</td>
                            </tr>
                            @php $anyDues = false @endphp
                            @foreach($orderDetails as $order)
                                @if($order->is_previous_dues)
                                    @php $anyDues = true @endphp
                                    <tr>
                                        <td style="width:4%" class="BoldColumns">{{ $order->bill_no }}</td>
                                        <td style="width:7%" class="Dateclmn BoldColumns">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                        <td style="width:31.5%" class="BoldColumns" colspan="2">{{ $order->patient_title_name }} {{ $order->patient_name }} ({{ $order->patient_age }}{{ $order->patient_age_type }} {{ $order->patient_gender }})</td>
                                        <td style="width: 45.5%" class="BoldColumns" colspan="6">{{ $order->doc_name }}</td>
                                    </tr>
                                    @php
                                        $allOrdersName = explode(', ', $order->order_name_txt);
                                        $allOrdersRate = explode(',', $order->order_amount);
                                    @endphp
                                    @foreach($allOrdersName as $key => $orderItem)
                                    <tr>
                                        <td style="width:40%;padding-left:25px;" class="GridColumn" colspan="3">{{ $orderItem }}</td>
                                        <td style="width:6%" class="GridColumn">{{ $allOrdersRate[$key] }}</td>
                                        <td style="width:9%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn"></td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td style="width:6%" class="BoldColumns"></td>
                                        <td style="width:6%" class="Dateclmn BoldColumns"></td>
                                        <td style="width:22%" class="BoldColumns"></td>
                                        <td style="width:17.5%" class="GridColumn"></td>
                                        <td style="width:9%" class="GridColumn">{{ $order->total_bill }}</td>
                                        <td style="width:9%" class="GridColumn">{{ $order->paid_amount }}</td>
                                        <td style="width:9%" class="GridColumn">{{ $order->overall_dis }}{{ ($order->is_dis_percentage == 'true') ? "%" : "" }}</td>
                                        <td style="width:9%" class="GridColumn">{{ $order->return_amount }}</td>
                                        <td style="width:9%" class="GridColumn">{{ $order->balance }}</td>
                                        <td style="width:9%" class="GridColumn"></td>
                                    </tr>
                                @endif
                            @endforeach
                            @if(!$anyDues)
                            <tr>
                                <td colspan="10" style="text-align:center;">No paid dues</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
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
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
</body>
<script>
    function printrprt() {
        $("div.dt-buttons").remove();
        var divElements = document.getElementById("BillRpt").innerHTML;
        var oldPage = document.body.innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title></head><body style="font-family: \'Microsoft JhengHei\', Arial;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
        printWindow.close();

        document.body.innerHTML = oldPage;
    }

    function printWithoutTotals() {
        $("div.dt-buttons").remove();
        $(".Totals").hide();
        var divElements = document.getElementById("BillRpt").innerHTML;
        $(".Totals").show();
        var oldPage = document.body.innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title></head><body style="font-family: \'Microsoft JhengHei\', Arial;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
        printWindow.close();

        document.body.innerHTML = oldPage;
    }
</script>
</html>
