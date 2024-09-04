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
                <div class="d-flex flex-column align-items-center">
                    <img class="rounded-pill mb-3" src="{{ asset($user->settings->referral_panel_icon) }}" alt="" height="100px">
                    <h1 class="fs-5 fw-bold text-center">{{ $user->settings->lab_name }}</h1>

                    <span class="w-50 mb-4 text-center">
                        <span>{{ $user->settings->address }}</span>
                    </span>

                    <span>Phone Number:
                        <a href="tel:{{ $user->settings->phone_number }}">{{ $user->settings->phone_number }}</a>,
                        <a href="tel:{{ $user->settings->phone_number_2 }}">{{ $user->settings->phone_number_2 }}</a>
                    </span>

                    <span>Email:
                        <a href="mailto:{{ $user->settings->email_address }}">{{ $user->settings->email_address }}</a>
                    </span>

                    <span>Website:
                        <a href="https://mstardiagnostics.com/" target="_blank">https://mstardiagnostics.com/</a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</body>
</html>
