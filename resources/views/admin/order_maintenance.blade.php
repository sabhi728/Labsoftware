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
                <button type="button" class="btn btn-primary" onclick="goToRoute('add_order')"><i class='bx bx-plus-circle' ></i> Add Order</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('servicegroup/index')">Service Groups</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('lab_profile/index')">Lab Profiles</button>
                <button type="button" class="btn btn-primary" onclick="printOrderPriceList()">Print Price List</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('ip_certificate/index')">IP Billing Categories</button>
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">#</th>
                        <th scope="col">Order Name</th>
                        <th scope="col">Order Amount</th>
                        <th scope="col">Order Type</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $count = 0; @endphp
                        @foreach($reports as $report)
                        <tr>
                            @php $count++; @endphp
                            <th scope="row">{{ $count }}</th>
                            <td onclick="goToRoute('update_order/{{ $report->report_id }}')" style="cursor: pointer;">{{ $report->order_name }}</td>
                            <td>{{ $report->order_amount }}</td>
                            <td>{{ $report->orderType->name }}</td>
                            @if($report->status == "Active")
                                <td><span>{{ $report->status }}</span></td>
                            @else
                                <td><span style="color: red;">{{ $report->status }}</span></td>
                            @endif
                            @if($report->has_components == "true")
                                <td><button type="button" class="btn btn-primary" onclick="goToRoute('order_details/{{ $report->report_id }}')">Details</button></td>
                            @else
                                <td></td>
                            @endif
                            <td>
                                {{-- <button type="button" class="btn btn-danger" onclick="if (confirm('Are you sure you want to delete this order?')) { goToRoute('delete_order/{{ $report->report_id }}'); }">Delete</button> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="orderPriceList" style="width:75%;margin:auto;display:none;">
                <h3>Orders Price List for M STAR MEDICAL DIAGNOSTICS PVT.LTD</h3>
                <div class="grid-mvc" data-lang="en" data-gridname="OrdersGrid" data-selectable="true" data-multiplefilters="false">
                    <div class="grid-wrap">
                        <table class="table grid-table">
                            <thead>
                                <tr>
                                    <th class="grid-header LabMF-OrderNotes"><div class="grid-header-title"><span>Order Name</span></div></th>
                                    <th class="grid-header LabMF-OrderNotes RightAlign"><div class="grid-header-title"><span>Order Amount</span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $report)
                                    <tr class="grid-row ">
                                        <td class="grid-cell LabMF-OrderNotes" data-name="OrderName">{{ $report->order_name }}</td>
                                        <td class="grid-cell LabMF-OrderNotes RightAlign" data-name="OrderAmount">{{ $report->order_amount }}</td>
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
</body>
<script>
    let table = new DataTable('#ordersTable');

    function printOrderPriceList() {
        var divElements = document.getElementById("orderPriceList").innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;
    }
</script>
</html>
