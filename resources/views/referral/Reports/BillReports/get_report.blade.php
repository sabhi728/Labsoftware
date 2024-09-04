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
                <h4 style="text-align:center;margin-top:30px;">Bill Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></h4>
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
                            <tr style="width: 100%;">
                                <td><b>Paid amount</b></td>
                                <td>:</td>
                                <td>{{ $paidAmount }}</td>
                                <td><b>Balance amount</b></td>
                                <td>:</td>
                                <td>{{ $balanceAmount }}</td>
                                <td><b>Return amount</b></td>
                                <td>:</td>
                                <td>{{ $returnAmount }}</td>
                            </tr>
                        </tbody>
                    </table>
                        <table style="width: 100%; margin-bottom: 10px;" id="DataTable" class="dataTable no-footer" role="grid">
                            <thead>
                                <tr style="border-bottom:groove;" role="row">
                                    <th style="width:10%;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"><b>Bill Date</b></th>
                                    <th style="width: 6%;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"><b>Bill no.</b></th>
                                    <th style="width: 6.6% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"><b>Patient Name</b></th>
                                    <th style="width: 6.6% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width: 6.6% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8%;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"><b>Test</b></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1">Ref Amount Paid</th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                    <th style="width:8% ;padding:1px;font-weight: bolder;" rowspan="1" colspan="1"></th>
                                </tr>
                                <tr style="border-bottom:groove;" role="row">
                                    <th style="padding: 1px; width: 81px;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                    <th style="padding: 1px; width: 78px;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                    <th style="width: 155px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Billed</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Dst</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Final Amount</b></th>
                                    <th style="width: 126px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Paid</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Cash</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Card</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Cheque</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Paytm</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>UPI</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Return</b></th>
                                    <th style="width: 78px; padding: 1px; font-weight: bolder;" class="sorting_disabled" rowspan="1" colspan="1"><b>Bal</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderDetails as $order)
                                <tr role="row" class="odd">
                                    <td class="Dateclmn" style="font-weight:bold">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                    <td class="GridColumn" style="font-weight:bold">{{ $order->bill_no }}</td>
                                    <td class="GridColumn" style="font-weight:bold">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold">{{ $order->order_name_txt }}</td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                    <td class="GridColumn" style="font-weight:bold"></td>
                                </tr>
                                <tr role="row" class="even">
                                    <td style="padding:1px;"></td>
                                    <td style="padding:1px;"></td>
                                    <td class="GridColumn">{{ $order->total_bill }}</td>
                                    <td class="GridColumn">{{ $order->overall_dis }}{{ ($order->is_dis_percentage == 'true') ? "%" : "" }}</td>
                                    <td class="GridColumn">{{ $order->final_amount }}</td>
                                    <td class="GridColumn">{{ $order->paid_amount }}</td>
                                    <td class="GridColumn">{{ $order->cash_paid }}</td>
                                    <td class="GridColumn">{{ $order->card_paid }}</td>
                                    <td class="GridColumn">{{ $order->cheque_paid }}</td>
                                    <td class="GridColumn">{{ $order->paytm_paid }}</td>
                                    <td class="GridColumn">{{ $order->upi_paid }}</td>
                                    <td class="GridColumn">{{ $order->return_amount }}</td>
                                    <td class="GridColumn">{{ $order->balance }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
