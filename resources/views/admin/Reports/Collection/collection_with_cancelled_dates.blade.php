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
            <div id="BillRpt">
                <h4 style="text-align:center;margin-top:30px;">Collection With Dues Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></h4>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1100px; text-align: left; margin-top: 30px; ">
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
                                <td><b>Paid amount</b></td>
                                <td>:</td>
                                <td>{{ $paidAmount }}</td>
                                <td><b>Balance amount</b></td>
                                <td>:</td>
                                <td>{{ $balanceAmount }}</td>
                                <td><b>Cancelled amount</b></td>
                                <td>:</td>
                                <td>{{ $cancelledAmount }}</td>
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
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Prev Cash</b></td>
                                <td>:</td>
                                <td>{{ $previousCashPaid }}</td>
                                <td><b>Prev Card</b></td>
                                <td>:</td>
                                <td>{{ $previousCardPaid }}</td>
                                <td><b>Prev Cheque</b></td>
                                <td>:</td>
                                <td>{{ $previousChequePaid }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Prev Paytm</b></td>
                                <td>:</td>
                                <td>{{ $previousPaytmPaid }}</td>
                                <td><b>Prev UPI</b></td>
                                <td>:</td>
                                <td>{{ $previousUpiPaid }}</td>
                                <td><b>Prev Amount Received</b></td>
                                <td>:</td>
                                <td>{{ $prevAmountReceived }}</td>
                            </tr>
                            <tr style="width: 100%; ">
                                <td><b>Total Amount Received</b></td>
                                <td>:</td>
                                <td>{{ $paidAmount - $cancelledAmount }}</td>
                                <td><b>Total Return Amount</b></td>
                                <td>:</td>
                                <td>{{ $returnAmount }}</td>
                                <td><b>Total Credits</b></td>
                                <td>:</td>
                                <td>0</td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width: 100%; margin-bottom: 10px; border-bottom: groove;" id="DataTable" class="dataTable no-footer" role="grid">
                        <thead>
                            <tr style="width:100%" role="row">
                                <th style="width:10px" rowspan="1" colspan="1">Bill Date</th>
                                <th style="width:12px" rowspan="1" colspan="1">Bill no.</th>
                                <th style="width:150px" rowspan="1" colspan="1">Patient</th>
                                <th style="width:150px" rowspan="1" colspan="1">Age</th>
                                <th style="width:50px" rowspan="1" colspan="1">Sex</th>
                                <th style="width:50px" rowspan="1" colspan="1">C/O</th>
                                <th style="width:50px" rowspan="1" colspan="1">Test</th>
                                <th style="width:50px" rowspan="1" colspan="1">Billed</th>
                                <th style="width:50px" rowspan="1" colspan="1">Dst</th>
                                <th style="width:50px" rowspan="1" colspan="1">Final Amount</th>
                                <th style="width:50px" rowspan="1" colspan="1">Paid</th>
                                <th style="width:50px" rowspan="1" colspan="1">Cash</th>
                                <th style="width:50px" rowspan="1" colspan="1">Card</th>
                                <th style="width:50px" rowspan="1" colspan="1">Cheque</th>
                                <th style="width:50px" rowspan="1" colspan="1">Paytm</th>
                                <th style="width:50px" rowspan="1" colspan="1">UPI</th>
                                <th style="width:50px" rowspan="1" colspan="1">Return</th>
                                <th style="width:50px" rowspan="1" colspan="1">Bal</th>
                                <th style="width:50px" rowspan="1" colspan="1">Credits</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalBill = 0;
                                $overallDis = 0;
                                $finalAmount = 0;
                                $paidAmount = 0;
                                $cashPaid = 0;
                                $cardPaid = 0;
                                $chequePaid = 0;
                                $paytmPaid = 0;
                                $upiPaid = 0;
                                $returnAmount = 0;
                                $balance = 0;
                            @endphp

                            @foreach($orderDetails as $order)
                                <tr role="row" class="odd" style="@if($order->status == "cancelled") color: red; @endif">
                                    <td style="width:4%" class="BoldColumns">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                    <td style="width:7%" class="Dateclmn BoldColumns">{{ $order->bill_no }}</td>
                                    <td style="width:16.5%" class="BoldColumns">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                    <td style="width:16%" class="BoldColumns">{{ $order->patient_age }}{{ $order->patient_age_type }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ Str::substr($order->patient_gender, 0, 1) }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->doc_name }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->order_name_txt }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ str_replace('.00', '', $order->total_bill) }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->overall_dis }}{{ ($order->is_dis_percentage == 'true') ? "%" : "" }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->final_amount }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->paid_amount }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->cash_paid }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->card_paid }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->cheque_paid }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->paytm_paid }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->upi_paid }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->return_amount }}</td>
                                    <td style="width: 5%" class="BoldColumns">{{ $order->balance }}</td>
                                    <td style="width: 5%" class="BoldColumns">0</td>
                                </tr>

                                @php
                                    $totalBill += $order->total_bill;
                                    $overallDis += $order->discount_amount;
                                    $finalAmount += $order->final_amount;
                                    $paidAmount += $order->paid_amount;
                                    $cashPaid += $order->cash_paid;
                                    $cardPaid += $order->card_paid;
                                    $chequePaid += $order->cheque_paid;
                                    $paytmPaid += $order->paytm_paid;
                                    $upiPaid += $order->upi_paid;
                                    $returnAmount += $order->return_amount;
                                    $balance += $order->balance;
                                @endphp
                            @endforeach

                            <tr>
                                <td colspan="19" style="border-top: 1px solid black;"></td>
                            </tr>

                            <tr role="row" class="odd">
                                <td style="width:4%" class="BoldColumns"></td>
                                <td style="width:7%" class="BoldColumns"></td>
                                <td style="width:16.5%" class="BoldColumns"></td>
                                <td style="width:16%" class="BoldColumns"></td>
                                <td style="width: 5%" class="BoldColumns"></td>
                                <td style="width: 5%" class="BoldColumns"></td>
                                <td style="width: 5%" class="BoldColumns">Total</td>
                                <td style="width: 5%" class="BoldColumns">{{ str_replace('.00', '', $totalBill) }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $overallDis }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $finalAmount }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $paidAmount }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $cashPaid }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $cardPaid }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $chequePaid }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $paytmPaid }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $upiPaid }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $returnAmount }}</td>
                                <td style="width: 5%" class="BoldColumns">{{ $balance }}</td>
                                <td style="width: 5%" class="BoldColumns">0</td>
                            </tr>

                            <tr>
                                <td colspan="19" style="border-bottom: 1px solid black;"></td>
                            </tr>

                            <tr>
                                <td colspan="19" style="text-align:center;font-size:large;border-bottom: 1px solid black;">Previous Dues</td>
                            </tr>

                            @foreach($orderDetails as $order)
                                @if ($order->is_previous)
                                    <tr role="row" class="odd" style="@if($order->status == "cancelled") color: red; @endif">
                                        <td style="width:4%" class="BoldColumns">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                        <td style="width:7%" class="Dateclmn BoldColumns">{{ $order->bill_no }}</td>
                                        <td style="width:16.5%" class="BoldColumns">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td style="width:16%" class="BoldColumns">{{ $order->patient_age }}{{ $order->patient_age_type }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ Str::substr($order->patient_gender, 0, 1) }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->doc_name }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->order_name_txt }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ str_replace('.00', '', $order->total_bill) }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->overall_dis }}{{ ($order->is_dis_percentage == 'true') ? "%" : "" }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->final_amount }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->paid_amount }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->cash_paid }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->card_paid }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->cheque_paid }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->paytm_paid }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->upi_paid }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->return_amount }}</td>
                                        <td style="width: 5%" class="BoldColumns">{{ $order->balance }}</td>
                                        <td style="width: 5%" class="BoldColumns">0</td>
                                    </tr>
                                @endif
                            @endforeach
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
