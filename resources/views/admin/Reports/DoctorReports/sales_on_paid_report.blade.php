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
                <button type="button" class="btn btn-primary" onclick="ExportToExcel('xlsx')">Download as Excel</button>
            </div>
            <br>
            <div id="BillRpt">
                <p style="text-align:center;margin-top:30px;">Doctor Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></p>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1150px; text-align: left; margin-top: 30px; background: white;">
                    <div class="grid-mvc" data-lang="en" data-gridname="RefRptGrid" data-selectable="true" data-multiplefilters="false">
                        <div class="grid-wrap">
                            <table id="tableData" class="table table-striped grid-table">
                            <thead>
                                <tr>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Bill Date</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Bill Number</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Orders</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Doctor Name</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Patient Name</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Amount Billed</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Discount</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Paid Amount</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Sales On Paid</span></div></th>
                                    <th class="grid-header GridColumn" style="width:10px;"><div class="grid-header-title"><span>Amount To Lab</span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderDetails as $order)
                                    <tr class="grid-row">
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->order_date }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->bill_no }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->order_name_txt }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->doc_name }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->final_amount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->discount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->paid_amount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->amount_to_sales }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $order->amount_to_lab }}</td>
                                    </tr>
                                @endforeach
                                <tr class="grid-row ">
                                    <td class="grid-cell GridColumn" data-name="">Total Amount</td>
                                    <td class="grid-cell GridColumn" data-name="">Count={{ count($orderDetails) }}</td>
                                    <td class="grid-cell GridColumn" data-name=""></td>
                                    <td class="grid-cell GridColumn" data-name=""></td>
                                    <td class="grid-cell GridColumn" data-name=""></td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $finalAmount }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $totalDiscount }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $totalPaidAmount }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $amounToSales }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $amountToLab }}</td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
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

    function ExportToExcel(type, fn, dl) {
        var elt = document.getElementById('tableData');
        var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
        return dl ?
            XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) :
            XLSX.writeFile(wb, fn || ('SalesOnPaidReport.' + (type || 'xlsx')));
    }
</script>
</html>
