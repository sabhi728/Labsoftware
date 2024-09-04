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
                <button type="button" onclick="goToRoute('order_template/{{ $orderDetails->report_id }}')" class="btn btn-primary">Cancel</button>
            </div>
            @if(isset($templateData))
                <form id="formBack" action="{{ url('update_order_template', ['report_id' => $orderDetails->report_id, 'update_id' => $templateData->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('add_order_template', ['report_id' => $orderDetails->report_id]) }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Template Name:</label>
                    @if(isset($templateData))
                        <input name="templateName" type="text" placeholder="Template Name" value="{{ $templateData->template_name }}" required>
                    @else
                        <input name="templateName" type="text" placeholder="Template Name" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Gender:</label>
                    @if(isset($templateData))
                        <select name="gender" required>
                            @if($templateData->template_gender == "male")
                                <option value="male" selected>Male</option>
                                <option value="female">Female</option>
                            @else
                                <option value="male">Male</option>
                                <option value="female" selected>Female</option>
                            @endif
                        </select>
                    @else
                        <select name="gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>From Age:</label>
                    @if(isset($templateData))
                        <input name="fromAge" type="number" placeholder="From Age" value="{{ $templateData->template_from_age }}" required>
                    @else
                        <input name="fromAge" type="number" placeholder="From Age" required>
                    @endif
                    <select name="fromAgeType">
                        @if(isset($templateData) && $templateData->template_from_age_type == "Years")
                            <option value="Years" selected>Years</option>
                        @else
                            <option value="Years">Years</option>
                        @endif
                        @if(isset($templateData) && $templateData->template_from_age_type == "Months")
                            <option value="Months" selected>Months</option>
                        @else
                            <option value="Months">Months</option>
                        @endif
                        @if(isset($templateData) && $templateData->template_from_age_type == "Days")
                            <option value="Days" selected>Days</option>
                        @else
                            <option value="Days">Days</option>
                        @endif
                    </select>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>To Age:</label>
                    @if(isset($templateData))
                        <input name="toAge" type="number" placeholder="To Age" value="{{ $templateData->template_to_age }}" required>
                    @else
                        <input name="toAge" type="number" placeholder="To Age" required>
                    @endif
                    <select name="toAgeType">
                        @if(isset($templateData) && $templateData->template_to_age_type == "Years")
                            <option value="Years" selected>Years</option>
                        @else
                            <option value="Years">Years</option>
                        @endif
                        @if(isset($templateData) && $templateData->template_to_age_type == "Months")
                            <option value="Months" selected>Months</option>
                        @else
                            <option value="Months">Months</option>
                        @endif
                        @if(isset($templateData) && $templateData->template_to_age_type == "Days")
                            <option value="Days" selected>Days</option>
                        @else
                            <option value="Days">Days</option>
                        @endif
                    </select>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Check to Inactive:</label>
                    @if(isset($templateData) && $templateData->status == "In Active")
                        <input name="checkToInactive" type="checkbox" checked>
                    @else
                        <input name="checkToInactive" type="checkbox">
                    @endif
                </div>
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save Details</button>
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
    let table = new DataTable('#ordersTable');

    const clearButton = document.getElementById('clearButton');
    const inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

    clearButton.addEventListener('click', function() {
        inputElements.forEach(element => {
            if (element.type === 'text' || element.type === 'textarea' || element.type === 'number') {
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
