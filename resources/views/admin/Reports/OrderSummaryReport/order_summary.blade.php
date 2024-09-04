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
            <div id="OrdRptDiv" style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:1%; margin: 0 auto; width: 950px; text-align: left; margin-top: 30px; background:white;">
                <div>
                    Order Summary Report Between the dates {{ $fromDate }} and {{ $toDate }}
                    <br>
                    NumberOfOrders: <b>{{ $totalNoOrders }}</b>
                    <br>
                    Total Amount: <b>{{ $totalAmount }}</b>
                    <br>
                </div>
                <div class="grid-mvc" data-lang="en" data-gridname="usersGrid" data-selectable="true" data-multiplefilters="false">
                    <div class="grid-wrap">
                        <table class="table table-striped grid-table dataTable no-footer" id="DataTable" role="grid">
                            <thead>
                                    <tr role="row">
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 307px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Order Name</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 307px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Number Of Orders</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 307px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Total Amount</span></div></th>
                                    </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataArray as $data)
                                <tr class="grid-row odd" role="row">
                                    <td class="grid-cell GridColumn" data-name="">{{ $data['order_name'] }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $data['no_of_orders'] }}</td>
                                    <td class="grid-cell GridColumn" data-name="">{{ $data['total_amount'] }}</td>
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
        var divElements = document.getElementById("OrdRptDiv").innerHTML;
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
