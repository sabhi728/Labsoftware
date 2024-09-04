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
    .inputBack {
        font-family: var(--font1);
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-bottom: 15px;
    }

    .inputBack label {
        font-weight: 300;
        font-size: 15px;
    }

    .inputBack input {
        border-radius: 5px;
        padding: 7px 10px;
        font-size: 15px;
        border: 1px solid var(--lightdarkgray);
        margin: 0px 15px;
    }

    .valueItem {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .valueItem i {
        font-size: 22px;
        color: red;
    }

    #valueDeleteBtn {
        color: red;
        border: 0;
        background: none;
        font-size: 25px;
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
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back();"><i class='bx bx-left-arrow-circle'></i> Back</button>
            </div>
            <div id="ordersTableBack">
                <h1 style="padding-bottom: 20px;font-size: 20px;">List of Values for <u><b>{{ $orderDetails->component_name }}</b></u></h1>
                <form class="inputBack" action="{{ url('order_detail_values', ['order_id' => $orderDetails->report_id, 'order_details_id' => $orderDetailsId]) }}" method="post">
                    @csrf
                    <label>Order Detail Value:</label>
                    <input name="orderDetailValue" type="text" placeholder="Order Detail Value">
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
                <div class="valuesList">
                    @foreach($orderDetailValues as $orderDetailValue)
                    <div class="valueItem">
                        <span>{{ $orderDetailValue->value }}</span>
                        <form action="{{ url('order_detail_values_delete', ['id' => $orderDetailValue->id]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button id="valueDeleteBtn" class='bx bx-x'></button>
                        </form>
                    </div>
                    @endforeach
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
</html>