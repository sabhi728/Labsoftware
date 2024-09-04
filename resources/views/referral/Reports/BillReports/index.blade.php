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
        @include('referral.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('referral.sidebar')
        </div>
        <div class="main">
            <div id="ordersTableBack">
                <form action="{{ url('referralpanel/reports/bill-reports/report') }}" method="post" id="formBack">
                    @csrf
                    <h1 class="fs-5">Bill Reports</h1>
                    <hr>
                    <div class="d-flex flex-row align-items-center justify-content-center">
                        <div class="input-group">
                            <span class="input-group-text fw-bold">From Date:</span>
                            <input type="date" class="form-control" name="fromDate" id="fromDate" value="{{ $fromDate }}" required>
                            <span class="input-group-text fw-bold">To Date:</span>
                            <input type="date" class="form-control" name="toDate" id="toDate" value="{{ $toDate }}" required>
                            <button class="btn btn-primary" type="submit">View</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
