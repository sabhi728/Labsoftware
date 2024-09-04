<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_maintenance.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>

</style>
<body>
    <div class="header">
        @include('referral.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('referral.sidebar')
        </div>
        <div class="main">
            <div id="ordersTableBack">
                <form action="{{ url('referralpanel/submitted-sample') }}" method="get" class="main">
                    <h1 class="fs-5">Submitted Samples</h1>
                    <hr>
                    <div id="formBack">
                        @csrf
                        <div class="d-flex flex-row align-items-center justify-content-center mb-3">
                            <div class="input-group">
                                <span class="input-group-text fw-bold">From Date:</span>
                                <input type="date" class="form-control" name="fromDate" id="fromDate" value="{{ $fromDate }}" required>
                                <span class="input-group-text fw-bold">To Date:</span>
                                <input type="date" class="form-control" name="toDate" id="toDate" value="{{ $toDate }}" required>
                            </div>
                        </div>
                        <div class="d-flex flex-row align-items-center justify-content-center">
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Search By:</span>
                                <select class="form-control" name="searchType">
                                    <option value="InvName" @if ($searchType == 'InvName') @selected(true) @endif>Investigation Name</option>
                                    <option value="BillNo" @if ($searchType == 'BillNo') @selected(true) @endif>Bill No</option>
                                    <option value="PatName" @if ($searchType == 'PatName') @selected(true) @endif>Patient Name</option>
                                </select>
                                <input type="text" class="form-control" name="searchValue" value="{{ $searchValue }}" placeholder="Enter search content here...">
                                <button class="btn btn-primary" type="submit">View</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if (isset($orderDetails))
                <div id="ordersTableBack">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">Bill No</th>
                                <th scope="col">Order Date</th>
                                <th scope="col">Name</th>
                                <th scope="col">Age</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Doc Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orderDetails as $order)
                                <tr>
                                    <td style="cursor: pointer;" class="openSubItemsTable"><i class='bx bx-plus text-primary'></i></td>
                                    <td>{{ $order->bill_no }}</td>
                                    <td>{{ $order->formatted_created_at }}</td>
                                    <td><span class="text-uppercase">{{ $order->patient_title_name }} {{ $order->patient_name }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->patient_age }} {{ $order->patient_age_type }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->patient_gender }}</span></td>
                                    <td>{{ $order->doc_name }}</td>
                                </tr>
                                <tr class="subItemsTable" style="display: none;">
                                    <td colspan="7">
                                        <table class="table table-bordered bg-white">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th scope="col">INVNAME</th>
                                                    <th scope="col">BARCODENUMBER</th>
                                                    <th scope="col">SAMPLENAME</th>
                                                    <th scope="col">SAMPLECOLLECTEDSTATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($order->items))
                                                    @foreach ($order->items as $items)
                                                        <tr>
                                                            <td>{{ $items['order_name'] }}</td>
                                                            <td>{{ $items['barcode_number'] }}</td>
                                                            <td>{!! $items['sample_name'] !!}</td>
                                                            <td>{{ $items['status'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggles = document.querySelectorAll('.openSubItemsTable');

        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var nextRow = toggle.closest('tr').nextElementSibling;

                if (nextRow && nextRow.classList.contains('subItemsTable')) {
                    if (nextRow.style.display === 'none') {
                        nextRow.style.display = 'table-row';

                        toggle.querySelector('i').classList.remove('bx-plus');
                        toggle.querySelector('i').classList.add('bx-minus');
                    } else {
                        nextRow.style.display = 'none';

                        toggle.querySelector('i').classList.remove('bx-minus');
                        toggle.querySelector('i').classList.add('bx-plus');
                    }
                }
            });
        });
    });
</script>
</html>
