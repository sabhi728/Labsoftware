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
                <button type="button" class="btn btn-primary" onclick="goToRoute('servicegroup/add')"><i class='bx bx-plus-circle'></i> Add Service Group</button>
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Service Group Name</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Orders</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceGroups as $serviceGroup)
                        <tr>
                            <td onclick="goToRoute('servicegroup/update/{{ $serviceGroup->id }}')" style="cursor: pointer;">{{ $serviceGroup->name }}</td>
                            <td>{{ $serviceGroup->totalAmount }}</td>
                            <td><button type="button" class="btn btn-danger" onclick="goToRoute('servicegroup/ordersindex/{{ $serviceGroup->id }}')">Orders</button></td>
                            <td><i class='bx bx-trash' style="font-size: 30px;cursor: pointer;color: red;"
                                    onclick="if (confirm('Are you sure you want to delete this item?')) {
                                        goToRoute('servicegroup/delete/{{ $serviceGroup->id }}');
                                    }">
                                </i>
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

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
<script>
    let table = new DataTable('#ordersTable');
</script>
</html>