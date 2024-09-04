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
            </div>
            <br>
            <div id="BillRpt">
                <p style="text-align:center;margin-top:30px;">Doctor Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></p>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 950px; text-align: left; margin-top: 30px; background: white;">
                    <table class="table table-striped grid-table">
                    <thead>
                        <tr>
                            <th align="left" width="15%"><div class="grid-header-title"><span>Patient Name</span></div></th>
                            <th align="left" width="10%"><div class="grid-header-title"><span>Bill Number</span></div></th>
                            <th align="left" width="35%"><div class="grid-header-title"><span>Order Name</span></div></th>
                            <th align="left" width="8%"><div class="grid-header-title"><span>Straight</span></div></th>
                            <th align="left" width="8%"><div class="grid-header-title"><span>Other</span></div></th>
                            <th align="left" width="8%"><div class="grid-header-title"><span>Total Amount</span></div></th>
                            <th align="left" width="9%"><div class="grid-header-title"><span>Amount Collected</span></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $order)
                            <tr>
                                <td align="left">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                <td align="left">{{ $order->bill_no }}</td>
                                <td align="left">{{ $order->order_name_txt }}</td>
                                <td align="left">{{ $order->amount_to_sales }}</td>
                                <td align="left">0</td>
                                <td align="left">{{ $order->final_amount }}</td>
                                <td align="left">{{ $order->paid_amount }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" align="right">Total Doctor Amount</td>
                            <td align="center">{{ $amounToSales }}</td>
                        <td>
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
</script>
</html>
