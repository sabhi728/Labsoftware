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
            <div id="ordersTableBack">
                <form class="input-group mb-3" action="" method="get">
                    <span class="input-group-text" id="inputGroup-sizing-default">Search</span>
                    <input type="text" name="search" class="form-control" value="{{ $searchValue }}">
                </form>
                <table class="table">
                    <thead>
                        <tr>
                        <th scope="col"></th>
                        <th scope="col">Bill Number</th>
                        <th scope="col">Bill Date</th>
                        <th scope="col">Patient Name</th>
                        <th scope="col">Phone Number</th>
                        <th scope="col">Age/Gender</th>
                        <th scope="col">Orders</th>
                        <th scope="col">Reff Doctor</th>
                        <th scope="col">Reff Company</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderDetails as $order)
                            @if ($user->department_access != null)
                                @if (!empty($order->order_name_txt))
                                    <tr>
                                        <th><button type="button" class="btn btn-primary" onclick="goToRoute('orderbills/bill_details/{{ $order->bill_no }}')">Orders List</button></th>
                                        <td style="color: {{ $order->report_color }};">{{ $order->bill_no }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->order_date }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->patient_phone }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->patient_age }} {{ $order->patient_age_type }} / {{ $order->patient_gender }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->order_name_txt }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->doc_name }}</td>
                                        <td style="color: {{ $order->report_color }};">{{ $order->referred_by }}</td>
                                    </tr>
                                @endif
                            @else
                                <tr>
                                    <th><button type="button" class="btn btn-primary" onclick="goToRoute('orderbills/bill_details/{{ $order->bill_no }}')">Orders List</button></th>
                                    <td style="color: {{ $order->report_color }};">{{ $order->bill_no }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->order_date }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->patient_phone }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->patient_age }} {{ $order->patient_age_type }} / {{ $order->patient_gender }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->order_name_txt }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->doc_name }}</td>
                                    <td style="color: {{ $order->report_color }};">{{ $order->referred_by }}</td>
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
