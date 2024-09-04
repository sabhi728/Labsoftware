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
                <button type="button" class="btn btn-primary" onclick="goToRoute('add_order_template/{{ $orderDetails->report_id }}')"><i class='bx bx-plus-circle'></i> Add Order Detail Templates</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('order_details/{{ $orderDetails->report_id }}')"><i class='bx bx-left-arrow-circle'></i> Back To Order Details</button>
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Template Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">From Age</th>
                        <th scope="col">To Age</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderTemplates as $orderTemplate)
                        <tr>
                            <td onclick="goToRoute('update_order_template/{{ $orderDetails->report_id }}/{{ $orderTemplate->id }}')" style="cursor: pointer;">{{ $orderTemplate->template_name }}</td>
                            @if($orderTemplate->status == "Active")
                                <td><span>{{ $orderTemplate->status }}</span></td>
                            @else
                                <td><span style="color: red;">{{ $orderTemplate->status }}</span></td>
                            @endif
                            <td>{{ $orderTemplate->template_from_age }}</td>
                            <td>{{ $orderTemplate->template_to_age }}</td>
                            <td><button type="button" class="btn btn-danger" onclick="goToRoute('template_order_details/{{ $orderDetails->report_id }}/{{ $orderTemplate->id }}')">Template Details</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
</script>
</html>