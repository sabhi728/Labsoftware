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
        <div class="main">
            <div class="actions">
                <button type="button" id="clearButton" class="btn btn-primary">Clear</button>
                <button type="button" onclick="window.history.back()" class="btn btn-primary">Cancel</button>
            </div>
            @if(isset($referralCompany))
                <form id="formBack" action="{{ url('referral_company/update', ['id' => $referralCompany->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('referral_company/add') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Customer Name:</label>
                    @if(isset($referralCompany))
                        <input name="customerName" type="text" value="{{ $referralCompany->name }}" required>
                    @else
                        <input name="customerName" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Address:</label>
                    @if(isset($referralCompany))
                        <input name="address" type="text" value="{{ $referralCompany->address }}">
                    @else
                        <input name="address" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Phone Number:</label>
                    @if(isset($referralCompany))
                        <input name="phoneNumber" type="text" value="{{ $referralCompany->phone_number }}">
                    @else
                        <input name="phoneNumber" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Username:</label>
                    @if(isset($referralCompany))
                        <input name="username" type="text" value="{{ $referralCompany->username }}" placeholder="ex. mstar">
                    @else
                        <input name="username" type="text" placeholder="ex. mstar">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Password:</label>
                    <input name="password" type="text" placeholder="******">
                </div>
                <div class="inputBack">
                    <label>Show Dashboard:</label>
                    @if(isset($referralCompany) && $referralCompany->show_dashboard == "true")
                        <input name="showDashboard" type="checkbox" checked>
                    @else
                        <input name="showDashboard" type="checkbox">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Show Bill Reports:</label>
                    @if(isset($referralCompany) && $referralCompany->show_bill_reports == "true")
                        <input name="showBillReports" type="checkbox" checked>
                    @else
                        <input name="showBillReports" type="checkbox">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Discount (%):</label>
                    @if(isset($referralCompany))
                        <input name="discount" type="number" value="{{ $referralCompany->discount }}" required>
                    @else
                        <input name="discount" type="number" required>
                    @endif
                </div>
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save</button>
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
</body>
<script>
    const clearButton = document.getElementById('clearButton');
    const inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

    clearButton.addEventListener('click', function() {
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
</script>
</html>
