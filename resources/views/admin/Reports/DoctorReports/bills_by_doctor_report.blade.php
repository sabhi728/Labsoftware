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
                <button type="button" class="btn btn-primary" onclick="ExportToExcel('xlsx')">Download as Excel</button>
            </div>
            <br>
            <div id="BillRpt">
                <p style="text-align:center;margin-top:30px;">Doctor Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></p>
                <br>
                <div id="RefDocRprtDiv" style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:1%; margin: 0 auto; width: 950px; text-align: left; margin-top: 30px; background: white;">
                    <div>
                        <h3 style="text-align:center;color: darkred">BILLS BY DOCTOR REPORT </h3>
                    </div>
                    <table class="Totals" style="width: 100%; margin-bottom: 10px; font-weight: bolder; border-top: groove; border-bottom: groove;">
                        <tbody>
                            <tr>
                                <td colspan="4">
                                    Total Billed : {{ $totalBilled }}
                                </td>
                                <td colspan="4">
                                    Total Discount : {{ $totalDiscount }}
                                </td>
                                <td colspan="4">
                                    Total Net Amount : {{ $totalNetAmount }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    Total Paid Amount : {{ $totalPaidAmount }}
                                </td>
                                <td colspan="4">
                                    Total Due Amount : {{ $totalDueAmount }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width: 100%; margin-bottom: 10px; font-weight: bolder;">
                        <thead>
                            <tr style="border-bottom:groove;">
                                <th style="width:7%;padding:1px;color:blueviolet"><b>Bill Date</b></th>
                                <th style="width: 8%;padding:1px;color:blueviolet"><b>Bill no.</b></th>
                                <th style="width:15%;padding:1px;color:blueviolet"><b>Patient</b></th>
                                <th style="width: 15.5%;padding:1px;color:blueviolet"><b>TestName</b></th>
                                <th style="width:5.1%;padding:1px;color:blueviolet"><b>BillAmount</b></th>
                                <th style="width:5.1%;padding:1px;color:blueviolet"><b>Discount</b></th>
                                <th style="width:5.1%;padding:1px;color:blueviolet"><b>Net Amount</b></th>
                                <th style="width:5.1%;padding:1px;color:blueviolet"><b>Paid Amount</b></th>
                                <th style="width:5.1%;padding:1px;color:blueviolet"><b>Due Amount</b></th>
                            </tr>
                            <tr>
                                <th style="width:20%;padding:1px;color:blueviolet" colspan="9"><b>Doctor Name</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allData as $tag => $data)
                                @php
                                    $doctorName = explode(' | ', $tag)[1];

                                    $ordersCount = 0;
                                    $billAmount = 0;
                                    $discountAmount = 0;
                                    $netAmount = 0;
                                    $paidAmount = 0;
                                    $dueAmount = 0;
                                @endphp
                                <tr>
                                    <td colspan="9" class="GridColumn" style="color:red">{{ $doctorName }}</td>
                                </tr>
                                @foreach ($data as $order)
                                    <tr style="border-bottom:groove;font-weight:normal">
                                        <td class="Dateclmn">{{ Str::substr($order->created_at, 0, 10) }}</td>
                                        <td class="GridColumn">{{ $order->bill_no }}</td>
                                        <td class="GridColumn">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td class="GridColumn">{{ $order->order_name_txt }}</td>
                                        <td class="GridColumn">{{ $order->total_bill }}</td>
                                        <td class="GridColumn">{{ $order->discount }}</td>
                                        <td class="GridColumn">{{ $order->final_amount }}</td>
                                        <td class="GridColumn">{{ $order->paid_amount }}</td>
                                        <td class="GridColumn">{{ $order->balance }}</td>
                                    </tr>

                                    @php
                                        $ordersCount++;
                                        $billAmount += $order->total_bill;
                                        $discountAmount += $order->discount;
                                        $netAmount += $order->final_amount;
                                        $paidAmount += $order->paid_amount;
                                        $dueAmount += $order->balance;
                                    @endphp
                                @endforeach
                                <tr style="border-bottom:double">
                                    <td colspan="3" class="GridColumn">Count: {{ $ordersCount }}</td>
                                    <td style="color:darkred" class="GridColumn">Total</td>
                                    <td style="color:darkred" class="GridColumn">{{ $billAmount }}</td>
                                    <td style="color:darkred" class="GridColumn">{{ $discountAmount }}</td>
                                    <td style="color:darkred" class="GridColumn">{{ $netAmount }}</td>
                                    <td style="color:darkred" class="GridColumn">{{ $paidAmount }}</td>
                                    <td style="color:darkred" class="GridColumn">{{ $dueAmount }}</td>
                                </tr>
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
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
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

    function ExportToExcel(type, fn, dl) {
        var elt = document.getElementById('RefDocRprtDiv');
        var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
        return dl ?
            XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
            XLSX.writeFile(wb, fn || ('BillsByDoctorReport.' + (type || 'xlsx')));
    }
</script>
</html>
