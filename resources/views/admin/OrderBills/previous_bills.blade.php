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
    #ordersTable>tbody>tr>td:hover {
        cursor: pointer;
        background-color: var(--lightgray) !important;
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
            <div id="ordersTableBack">
                <form class="input-group mb-3" action="" method="get">
                    <span class="input-group-text" id="inputGroup-sizing-default">Search</span>
                    <input type="text" name="search" class="form-control" value="{{ $searchValue }}">
                </form>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col"></th>
                        <th scope="col">Bill Number</th>
                        <th scope="col">Bill Date</th>
                        <th scope="col">Patient Name</th>
                        <th scope="col">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderDetails as $order)
                        @if ($user->department_access != null)
                            @if (!empty($order->order_name_txt))
                                <tr onclick="goToRoute('orderbills/previous_bill_details/{{ $order->bill_no }}')">
                                    <td></td>
                                    <td>{{ $order->bill_no }}</td>
                                    <td>{{ $order->order_date }}</td>
                                    <td>{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                    <td>{{ $order->order_name_txt }}</td>
                                </tr>
                            @endif
                        @else
                            <tr onclick="goToRoute('orderbills/previous_bill_details/{{ $order->bill_no }}')">
                                <td></td>
                                <td>{{ $order->bill_no }}</td>
                                <td>{{ $order->order_date }}</td>
                                <td>{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                <td>{{ $order->order_name_txt }}</td>
                            </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
                {{ $orderDetails->appends(request()->input())->links('pagination::bootstrap-5') }}
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
    // let table = new DataTable('#ordersTable');
</script>
</html>
