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
            @if(isset($labProfile))
                <form id="formBack" action="{{ url('lab_profile/update', ['id' => $labProfile->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('lab_profile/add') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Profile Name:</label>
                    <input name="profileName" type="text" value="{{ isset($labProfile) ? $labProfile->name : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Profile Amount:</label>
                    <input name="profileAmount" type="number" value="{{ isset($labProfile) ? $labProfile->amount : "" }}" required>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Status:</label>
                    @if(isset($labProfile))
                        <select name="status" required>
                            @if($labProfile->status == "active")
                                <option value="active" selected>Active</option>
                            @else
                                <option value="active">Active</option>
                            @endif
                            @if($labProfile->status == "in-active")
                                <option value="in-active" selected>In-Active</option>
                            @else
                                <option value="in-active">In-Active</option>
                            @endif
                        </select>
                    @else
                        <select name="status" required>
                            <option value="active">Active</option>
                            <option value="in-active">In-Active</option>
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
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
