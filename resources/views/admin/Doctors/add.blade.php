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
            @if(isset($doctor))
                <form id="formBack" action="{{ url('doctors/update', ['id' => $doctor->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('doctors/add') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Doctor Name:</label>
                    @if(isset($doctor))
                        <input name="doctorName" type="text" value="{{ $doctor->doc_name }}" required>
                    @else
                        <input name="doctorName" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Doctor Type:</label>
                    @if(isset($doctor))
                        <select name="doctorType" required>
                            @if($doctor->doc_type == "Referral")
                                <option value="Referral" selected>Referral</option>
                            @else
                                <option value="Referral">Referral</option>
                            @endif
                            @if($doctor->doc_type == "Service Provider")
                                <option value="Service Provider" selected>Service Provider</option>
                            @else
                                <option value="Service Provider">Service Provider</option>
                            @endif
                            @if($doctor->doc_type == "Both")
                                <option value="Both" selected>Both</option>
                            @else
                                <option value="Both">Both</option>
                            @endif
                        </select>
                    @else
                        <select name="doctorType" required>
                            <option value="Referral">Referral</option>
                            <option value="Service Provider">Service Provider</option>
                            <option value="Both">Both</option>
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Percentage To Doctor:</label>
                    @if(isset($doctor))
                        <input name="doctorPercentage" type="text" value="{{ $doctor->doc_percentage }}">
                    @else
                        <input name="doctorPercentage" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Address:</label>
                    @if(isset($doctor))
                        <input name="doctorAddress" type="text" value="{{ $doctor->doc_address }}">
                    @else
                        <input name="doctorAddress" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Phone Number:</label>
                    @if(isset($doctor))
                        <input name="doctorPhoneNumber" type="text" value="{{ $doctor->doc_phone_num }}">
                    @else
                        <input name="doctorPhoneNumber" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Email:</label>
                    @if(isset($doctor))
                        <input name="doctorEmail" type="text" value="{{ $doctor->doc_email }}">
                    @else
                        <input name="doctorEmail" type="text">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Category:</label>
                    @if(isset($doctor))
                        <input name="doctorCategory" type="text" value="{{ $doctor->doc_category }}">
                    @else
                        <input name="doctorCategory" type="text">
                    @endif
                </div>
                <div class="action_top">
                    <button type="submit" class="btn btn-primary">Save</button>
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
