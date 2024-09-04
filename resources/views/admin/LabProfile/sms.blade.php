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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<style>
    textarea {
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 15px;
        border: 1px solid var(--lightdarkgray);
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
                <button type="button" id="clearButton" class="btn btn-primary" onclick="sendSMS(`{{ $allNumbers }}`)">Send To All Numbers</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#numberSelectDialog">Send To Selected Numbers</button>
            </div>
            <div id="formBack">
                <div>
                    <h5>Customer will receive following SMS</h5>
                    <textarea style="width: 100%;height:200px;" id="smsMessage">{{ $message }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <div class="modal" id="numberSelectDialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Select Numbers</h4>
                    <div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="sendSelectedOptions()">Send</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" class="form-control" placeholder="Search" oninput="filterOptions()">
                    </div>
                    <div class="mb-2">
                        <button class="btn btn-secondary" onclick="selectAll()">Select All</button>
                        <button class="btn btn-secondary" onclick="deselectAll()">Deselect All</button>
                    </div>
                    <ul class="list-group options-list">
                        @foreach ($patientDetails as $patient)
                            <li class="list-group-item"><input name="phoneNumbers[]" type="checkbox" value="{{ $patient->phone }}" checked> {{ $patient->phone }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
</body>
<script>
    function filterOptions() {
        const searchValue = document.querySelector('#numberSelectDialog input[type="text"]').value.toLowerCase();
        const options = document.querySelectorAll('.options-list li');

        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            const display = text.includes(searchValue) ? 'block' : 'none';
            option.style.display = display;
        });
    }

    function selectAll() {
        const checkboxes = document.querySelectorAll('.options-list input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = true);
    }

    function deselectAll() {
        const checkboxes = document.querySelectorAll('.options-list input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }

    function sendSelectedOptions() {
        const checkboxes = document.querySelectorAll('.options-list input[type="checkbox"]:checked');
        const selectedOptions = Array.from(checkboxes).map(checkbox => checkbox.value);
        $('#numberSelectDialog').modal('hide');

        sendSMS(selectedOptions.toString().replace('[', '').replace(']', ''));
    }

    function sendSMS(phoneNumbers) {
        if (phoneNumbers != "") {
            var smsMessage = document.getElementById('smsMessage').value;
            goToRoute('lab_profile/send_offer_message/' + phoneNumbers + '/' + smsMessage);
        }
    }
</script>
</html>
