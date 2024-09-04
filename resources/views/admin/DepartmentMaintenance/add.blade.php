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
<style>
    .removeBtn {
        background: red;
        text-align: center;
        padding: 10px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
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
                <button type="button" id="clearButton" class="btn btn-primary">Clear</button>
                <button type="button" onclick="window.history.back()" class="btn btn-primary">Cancel</button>
                @if(isset($department))
                    <button type="button" onclick="goToRoute('department/signatures_list_index/' + {{ $department->depart_id }})" class="btn btn-primary">Signatures List</button>
                @endif
            </div>
            @if(isset($department))
                <form id="formBack" action="{{ url('department/update', ['id' => $department->depart_id]) }}" method="post" enctype="multipart/form-data">
            @else
                <form id="formBack" action="{{ url('department/add') }}" method="post" enctype="multipart/form-data">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Department Name:</label>
                    @if(isset($department))
                        <input name="departmentName" type="text" value="{{ $department->department_name }}" required>
                    @else
                        <input name="departmentName" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Signature Label:</label>
                    @if(isset($department))
                        <textarea name="signatureLabel" type="text">{{ $department->signature_label }}</textarea>
                    @else
                        <textarea name="signatureLabel" type="text">LAB INCHARGE</textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Signature Image:</label>
                    <input name="signatureImage" type="file">
                </div>
                @if(isset($department->signature_image))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('department/remove_department_signature_image/' + 'right/' + '{{ $department->depart_id }}')">Remove Signature Image</label>
                        <img height="80px" src="{{ asset($department->signature_image) }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Left Signature Label:</label>
                    @if(isset($department))
                        <textarea name="leftSignatureLabel" type="text">{{ $department->left_signature_label }}</textarea>
                    @else
                        <textarea name="leftSignatureLabel" type="text">Authorized Signatory</textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Left Signature Image:</label>
                    <input name="leftSignatureImage" type="file">
                </div>
                @if(isset($department->left_signature_image))
                    <div class="inputBack">
                        <label class="removeBtn" onclick="goToRoute('department/remove_department_signature_image/' + 'left/' + '{{ $department->depart_id }}')">Remove Left Signature Image</label>
                        <img height="80px" src="{{ asset($department->left_signature_image) }}">
                    </div>
                @endif
                <div class="inputBack">
                    <label>Print Results in Individual pages:</label>
                    @if(isset($department) && $department->print_results_in_individual_pages == "true")
                        <input name="printResultIndividualPage" type="checkbox" checked>
                    @else
                        <input name="printResultIndividualPage" type="checkbox">
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
