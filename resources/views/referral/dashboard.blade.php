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
    .horizontal {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

    .cust-card {
        width: 100%;
        border: 1px solid var(--lightgray);
        border-radius: 15px;
    }

    .card-body {
        font-family: var(--font1);
        text-transform: uppercase;
        border-radius: 15px;
        padding: 0 !important;
        height: 100%;
    }

    .card-horizontal {
        padding: 20px;
        display: flex;
        flex-direction: row;
        margin-left: 6px;
        background: white;
        border-radius: 14px;
        height: 100%;
    }

    .card-text-heading {
        font-size: 13px;
    }

    .card-text-value {
        font-size: 25px;
        font-weight: 600;
    }

    .card-text-view-report {
        color: blue;
        cursor: pointer;
        font-size: 15px;
        text-transform: none
    }

    .gridView {
        display: grid;
        grid-template-columns: auto auto auto;
        grid-row-gap: 15px;
        grid-column-gap: 15px;
    }
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
                <form action="" method="GET" class="input-group">
                    <span class="input-group-text fw-bold">From Date:</span>
                    <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}" required="">
                    <span class="input-group-text fw-bold">To Date:</span>
                    <input type="date" class="form-control" name="toDate" value="{{ $toDate }}" required="">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                <br>
                <div class="gridView">
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #4e73df;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Amount</div>
                                    <div class="card-text-value" style="color: #4e73df;">{{ $totalAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #1cc88a;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Paid Amount</div>
                                    <div class="card-text-value" style="color: #1cc88a;">{{ $paidAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #be0000;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Balance</div>
                                    <div class="card-text-value" style="color: #be0000;">{{ $balanceAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
