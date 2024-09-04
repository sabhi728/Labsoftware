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
                <button type="button" class="btn btn-primary" onclick="goToRoute('add_template_order_details/{{ $reportDetails->report_id }}/{{ $templateId }}')"><i class='bx bx-plus-circle'></i> Add Order Details</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('order_template/{{ $reportDetails->report_id }}')"><i class='bx bx-left-arrow-circle'></i> Back To Order Detail Templates</button>
                <button type="button" class="btn btn-primary" onclick="printDiv()">Print Preview</button>
            </div>
            <div id="ordersTableBack">
                <h1 style="padding-bottom: 20px;font-size: 15px;">Order Details For <u><b>{{ $reportDetails->order_name }}</b></u></h1>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Sub Heading</th>
                        <th scope="col">Component</th>
                        <th scope="col">Range</th>
                        <th scope="col">Units</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderDetails as $orderDetail)
                        <tr>
                            <td>{{ $orderDetail->sub_heading }}</td>
                            <td onclick="goToRoute('update_template_order_details/{{ $reportDetails->report_id }}/{{ $templateId }}/{{ $orderDetail->id }}')" style="cursor: pointer;">{{ $orderDetail->component_name }}</td>
                            <td><span style="white-space: pre-line;">{{ $orderDetail->order_details_range }}</span></td>
                            <td>{{ $orderDetail->units }}</td>
                            @if($orderDetail->status == "Active")
                                <td><span>{{ $orderDetail->status }}</span></td>
                            @else
                                <td><span style="color: red;">{{ $orderDetail->status }}</span></td>
                            @endif
                            <td>
                                <button type="button" class="btn btn-success" onclick="moveRowUp(this, '{{ $orderDetail->id }}')"><i class='bx bx-up-arrow-alt'></i></button>
                                <button type="button" class="btn btn-primary" onclick="moveRowDown(this, '{{ $orderDetail->id }}')"><i class='bx bx-down-arrow-alt'></i></button>
                                <button type="button" class="btn btn-primary" onclick="goToRoute('order_detail_values/{{ $orderDetail->report_id }}/{{ $orderDetail->id }}')">Values</button>
                                {{-- <button type="button" class="btn btn-danger" onclick="if (confirm('Are you sure you want to delete this order detail?')) { goToRoute('order_detail_delete/{{ $orderDetail->id }}'); }">Delete</button> --}}
                            </td>
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

    @include('include.order_components_preview')

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
<script>
    // let table = new DataTable('#ordersTable');

    function moveRowUp(button, orderDetailId) {
        const row = button.closest('tr');
        if (row.previousElementSibling) {
            row.parentNode.insertBefore(row, row.previousElementSibling);
            updatePosition(orderDetailId, 'up');
        }
    }

    function moveRowDown(button, orderDetailId) {
        const row = button.closest('tr');
        if (row.nextElementSibling) {
            row.parentNode.insertBefore(row.nextElementSibling, row);
            updatePosition(orderDetailId, 'down');
        }
    }

    function updatePosition(orderDetailId, direction) {
        $.ajax({
            url: webUrl + `update-position/${orderDetailId}/${direction}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                console.log(response);
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
</script>
</html>
