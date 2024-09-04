<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/add_order.css') }}">

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
        <form action="{{ url('reports/order_summary_reports/report') }}" method="post" class="main">
            <div class="actions">
                <button type="submit" class="btn btn-primary" name="orderSummary">Order Summary</button>
                <button type="submit" class="btn btn-primary" name="orderTypeSummary">Order Type Summary</button>
                <button type="submit" class="btn btn-primary" name="externalOrders">External Orders</button>
                <button type="submit" class="btn btn-primary" name="externalTypeSummary">External Type Summary</button>
                <button type="submit" class="btn btn-primary" name="orderTypeDetailed">Order Type Detailed</button>
                <button type="submit" class="btn btn-primary" name="orderDetailed">Order Detailed</button>
                <button type="button" id="clearButton" class="btn btn-primary">Clear</button>
            </div>
            <div id="formBack">
                @csrf
                <div class="inputBack">
                    <label>From Date:</label>
                    <input name="fromDate" type="date" id="fromDate" required>
                </div>
                <div class="inputBack">
                    <label>To Date:</label>
                    <input name="toDate" type="date" id="toDate" required>
                </div>
                <div class="inputBack">
                    <label>Location:</label>
                    <select name="location">
                        <option value="">Select Location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="inputBack">
                    <label>Order Type:</label>
                    <select id="orderType" name="orderType[]" class="selectpicker" multiple aria-label="Default select example" data-live-search="true">
                        @foreach ($orderTypes as $orderType)
                            <option value="{{ $orderType->id }}">{{ $orderType->name }}</option>
                        @endforeach
                    </select>
                    (Valid for Order Type Detailed and Order Type Summary)
                </div>
                <div class="inputBack">
                    <label>Order:</label>
                    <select id="order" name="order[]" class="selectpicker" multiple aria-label="Default select example" data-live-search="true">
                        @foreach ($orderDetails as $orderDetail)
                            <option value="{{ $orderDetail->report_id }}">{{ $orderDetail->order_name }}</option>
                        @endforeach
                    </select>
                    (Valid for Order Detailed)
                </div>
            </form>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <link href="{{ asset('/js/libs/bootstrap-multiselect/bootstrap-multiselect.css') }}" rel="stylesheet" />
    <script src="{{ asset('/js/libs/bootstrap.js') }}"></script>
    <script src="{{ asset('/js/libs/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
</body>
<script>
    const clearButton = document.getElementById('clearButton');
    const inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

    clearButton.addEventListener('click', function() {
        setCurrentDate();
        inputElements.forEach(element => {
            if (element.type === 'text' || element.type === 'textarea') {
                element.value = '';
            } else if (element.type === 'checkbox') {
                element.checked = false;
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            }
        });
    });

    setCurrentDate();
    function setCurrentDate() {
        const currentDate = new Date();

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;

        document.getElementById('fromDate').value = formattedDate;
        document.getElementById('toDate').value = formattedDate;
    }
</script>
</html>
