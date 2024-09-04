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
    #ordersTableBack {
        width: max-content;
        border: groove;
    }

    .main {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
</style>
<body>
    <div class="main_container">
        <div class="main">
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Exit</button>
                <button type="button" class="btn btn-primary" onclick="printJS('result', 'html')">Print</button>
            </div>
            <div id="ordersTableBack">
                <div id="result" style="display: flex;flex-direction: column;">
                    <span>Login Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></span>
                    <span><b>Copy Coordinate and Paste in Google Maps for Location details</b></span>
                    <table class="table" style="margin-top:10px;">
                        <thead>
                            <tr>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">Full name</th>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">IP Address</th>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">Coordinate</th>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">Date of Login</th>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">Time of Login</th>
                                <td scope="col" style="color: #2A94D7;font-weight:bold;">Time of Logout</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loginHistory as $history)
                            @php
                                $loginDateTime = explode(' ', $history->created_at);
                                $logoutTime = ($history->logout_time == "Did not Logout") ? $history->logout_time : explode(' ', $history->logout_time)[1];
                            @endphp
                            <tr>
                                <td>{{ $history->first_name }} {{ $history->last_name }}</td>
                                <td>{{ $history->ip_address }}</td>
                                <td>{{ $history->coordinates }}</td>
                                <td>{{ $loginDateTime[0] }}</td>
                                <td>{{ $loginDateTime[1] }}</td>
                                <td>{{ $logoutTime }}</td>
                            </tr>
                            @endforeach
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
</html>
