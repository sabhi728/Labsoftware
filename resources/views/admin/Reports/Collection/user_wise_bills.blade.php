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

    .table thead > tr > th {
        color: #2A94D7;
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
                <h4 style="text-align:center;margin-top:30px;">User Wise Bills Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b> Username: {{ $username }}</h4>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1100px; text-align: left; margin-top: 30px; background: white;" id="GridDiv">
                    <table class="Totals" style="width: 100%; margin-bottom: 10px; border-bottom: groove; ">
                        <tbody>
                            <tr style="width:100%">
                                <td style="width:17%"><b>Total amount</b></td>
                                <td style="width:2%">:</td>
                                <td style="width:14%">{{ $grossAmount }}</td>
                                <td style="width:17%"><b>Discount amount</b></td>
                                <td style="width:2%">:</td>
                                <td style="width:14%">{{ $discount }}</td>
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
                                <td>{{ $balance }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
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
                    <table style="width: 100%; margin-bottom: 10px; font-weight: bolder;" id="DataTable" class="dataTable no-footer" role="grid">
                        <thead>
                            <tr style="border-bottom:groove;" role="row">
                                <th style="width: 103px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Bill Date</b></th>
                                <th style="width: 64px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Bill no.</b></th>
                                <th style="width: 75px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Patient</b></th>
                                <th style="width: 34px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Age</b></th>
                                <th style="width: 29px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Sex</b></th>
                                <th style="width: 78px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>C/O</b></th>
                                <th style="width: 129px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Test</b></th>
                                <th style="width: 49px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Billed</b></th>
                                <th style="width: 45px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Dst</b></th>
                                <th style="width: 56px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Final Amount</b></th>
                                <th style="width: 47px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Paid</b></th>
                                <th style="width: 48px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Cash</b></th>
                                <th style="width: 47px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Card</b></th>
                                <th style="width: 53px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Cheque</b></th>
                                <th style="width: 50px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Paytm</b></th>
                                <th style="width: 45px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>UPI</b></th>
                                <th style="width: 46px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Return</b></th>
                                <th style="width: 21px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Bal</b></th>
                                <th style="width: 48px; padding: 1px;" class="sorting_disabled" rowspan="1" colspan="1"><b>Credits</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allData as $tag => $data)
                                @php
                                    $username = explode(' | ', $tag)[1];
                                @endphp
                                <tr role="row" class="odd">
                                    <td style="width:8.5%; padding:1px; font-size: 20px;"><u>UserName</u></td>
                                    <td style="width:7.5%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:8%; padding:1px; font-size: 20px;"><u>{{ $username }}</u></td>
                                    <td style="width:3.5%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:3%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:9%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:15.5%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:5%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:5%; padding:1px; font-size: 20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                    <td style="width:5%;padding:1px; font-size:20px;"></td>
                                </tr>
                                @foreach($data as $order)
                                    <tr role="row" class="even">
                                        <td style="width:8.5%;padding:1px;" class="Dateclmn">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                        <td style="width:7.5%;padding:1px;" class="GridColumn">{{ $order->bill_no }}</td>
                                        <td style="width:8%;padding:1px;" class="GridColumn">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td style="width:3.5%;padding:1px;" class="GridColumn">{{ $order->patient_age }}{{ $order->patient_age_type }}</td>
                                        <td style="width:3%;padding:1px;" class="GridColumn">{{ $order->patient_gender }}</td>
                                        <td style="width:9%;padding:1px;" class="GridColumn">{{ $order->doc_name }}</td>
                                        <td style="width:15.5%;padding:1px;" class="GridColumn">{{ $order->ordersNameTxt }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->total_bill }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->dst }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->final_amount }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->paid_amount }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->cash }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->card }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->cheque }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->paytm }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->upi }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->return_amount }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">{{ $order->bal }}</td>
                                        <td style="width:5%;padding:1px;" class="GridColumn">0</td>
                                    </tr>
                                @endforeach
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
