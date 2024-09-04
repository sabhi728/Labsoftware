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
                @if(isset($location))
                    <button type="button" onclick="goToRoute('locations/order_rates/index/{{ $location->id }}')" class="btn btn-primary">Order Rates</button>
                    <button type="button" onclick="goToRoute('locations/profile_rates/index/{{ $location->id }}')" class="btn btn-primary">Profile Rates</button>
                @endif
            </div>
            @if(isset($location))
                <form id="formBack" action="{{ url('locations/update', ['id' => $location->id]) }}" method="post" enctype="multipart/form-data">
            @else
                <form id="formBack" action="{{ url('locations/add') }}" method="post" enctype="multipart/form-data">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Location Code:</label>
                    @if(isset($location))
                        <input name="locationCode" type="text" value="{{ $location->location_code }}" required>
                    @else
                        <input name="locationCode" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Location Name:</label>
                    @if(isset($location))
                        <input name="locationName" type="text" value="{{ $location->location_name }}" required>
                    @else
                        <input name="locationName" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Tag Line:</label>
                    @if(isset($location))
                        <input name="tagLine" type="text" value="{{ $location->tag_line }}">
                    @else
                        <input name="tagLine" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Address:</label>
                    @if(isset($location))
                        <input name="address" type="text" value="{{ $location->address }}">
                    @else
                        <input name="address" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Phone Number:</label>
                    @if(isset($location))
                        <input name="phoneNumber" type="text" value="{{ $location->phone_number }}">
                    @else
                        <input name="phoneNumber" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Pnr File Text:</label>
                    @if(isset($location))
                        <textarea name="pnrFileText">{{ $location->pnr_file_text }}</textarea>
                    @else
                        <textarea name="pnrFileText"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Location Bill Header:</label>
                    @if(isset($location))
                        <input name="locationBillHeader" type="file">
                        @if(!empty($location->location_bill_header))
                            <img src="{{ env('URL') }}{{ $location->location_bill_header }}" width="80px">
                            <label onclick="goToRoute('locations/remove_location_header_file/' + {{ $location->id }} + '/bill')">Remove</label>
                        @endif
                    @else
                        <input name="locationBillHeader" type="file">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Consulting Bill Header:</label>
                    @if(isset($location))
                        <input name="consultingBillHeader" type="file">
                        @if(!empty($location->consulting_bill_header))
                            <img src="{{ env('URL') }}{{ $location->consulting_bill_header }}" width="80px">
                            <label onclick="goToRoute('locations/remove_location_header_file/' + {{ $location->id }} + '/consulting')">Remove</label>
                        @endif
                    @else
                        <input name="consultingBillHeader" type="file">
                    @endif
                </div>
                <div class="action_top" style="top:5px;">
                    <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save</button>
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
