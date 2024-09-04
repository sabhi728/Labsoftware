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
    sectionHeader {
        font-family: var(--font1);
        font-weight: 600;
        font-size: 20px;
        border-bottom: 1px solid var(--lightdarkgray);
        width: 100%;
        display: block;
        padding-bottom: 8px;
        margin-bottom: 20px;
    }

    #patientStartnameSelect {
        width: max-content;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #patientNameInput {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #ageInput {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #additionalAgeInput {
        border-radius: 0;
        width: 150px;
    }

    #ageTypeSelect {
        width: max-content;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #additionalAgeTypeSelect {
        width: max-content;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
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
            </div>
            @if(isset($orderInputData))
                <form id="formBack" action="{{ url('orderentry/update_patient', ['update_id' => $orderInputData->id]) }}" method="post" enctype="multipart/form-data">
            @else
                <form id="formBack" action="{{ url('orderentry/add_new_patient') }}" method="post" enctype="multipart/form-data">
            @endif
                @csrf
                <sectionHeader>Details</sectionHeader>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Patient Name:</span>
                    <select name="patientTitlename" id="patientStartnameSelect" class="form-control" style="max-width: 100px !important;" required>
                        <option value="MR." data-bs="Male">MR.</option>
                        <option value="MRS." data-bs="Female">MRS.</option>
                        <option value="MS." data-bs="Female">MS.</option>
                        <option value="MASTER." data-bs="Male">MASTER.</option>
                        <option value="BABY." data-bs="Female">BABY.</option>
                        <option value="BABY OF." data-bs="Male">BABY OF.</option>
                        <option value="DR." data-bs="Male">DR.</option>
                    </select>
                    <input id="patientNameInput" name="patientName" type="text" placeholder="" class="form-control" required>

                    <span class="input-group-text fw-bold">Gender:</span>
                    <select name="gender" id="genderSelect" class="form-control">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <br>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Age:</span>
                    <input id="ageInput" name="age" type="text" placeholder="" class="form-control" required>
                    <select name="ageType" id="ageTypeSelect" style="max-width: 100px !important;" class="form-control">
                        <option value="Years">Years</option>
                        <option value="Months">Months</option>
                        <option value="Days">Days</option>
                    </select>
                    <input id="additionalAgeInput" name="additionalAgeInput" type="text" class="form-control" placeholder="Additional Age">
                    <select name="additionalAgeType" id="additionalAgeTypeSelect" style="max-width: 100px !important;" class="form-control">
                        <option value="Months">Months</option>
                        <option value="Days">Days</option>
                    </select>

                    <span class="input-group-text fw-bold">Address:</span>
                    <input name="address" type="text" placeholder="" class="form-control">
                </div>
                <br>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Phone:</span>
                    <input class="form-control" name="phone" type="text" placeholder="" {{ ($isPatientPhoneRequired) ? "required" : "" }}>

                    <span class="input-group-text fw-bold">Email:</span>
                    <input class="form-control" name="email" type="text" placeholder="">
                </div>
                <br>
                <sectionHeader>Address</sectionHeader>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Area:</span>
                    <input class="form-control" name="area" type="text" placeholder="">

                    <span class="input-group-text fw-bold">City:</span>
                    <input class="form-control" name="city" type="text" placeholder="">

                    <span class="input-group-text fw-bold">District:</span>
                    <input class="form-control" name="district" type="text" placeholder="">
                </div>
                <br>
                <div class="input-group">
                    <span class="input-group-text fw-bold">State:</span>
                    <input class="form-control" name="state" type="text" placeholder="">

                    <span class="input-group-text fw-bold">Country:</span>
                    <input class="form-control" name="country" type="text" placeholder="">
                </div>
                <br>
                <sectionHeader>Additional</sectionHeader>
                <div class="input-group">
                    <span class="input-group-text fw-bold">Attachment:</span>
                    <input class="form-control" name="attachment" type="file" placeholder="">

                    <span class="input-group-text fw-bold">Clinical History:</span>
                    <input class="form-control" name="clinicalHistory" type="text" placeholder="">
                </div>
                {{-- <div class="inputBack">
                    <label>Patient Name:</label>
                    <select name="patientTitlename" id="patientStartnameSelect">
                        <option value="Mr." data-bs="Male">Mr.</option>
                        <option value="Mrs." data-bs="Female">Mrs.</option>
                        <option value="Ms." data-bs="Female">Ms.</option>
                        <option value="Master." data-bs="Male">Master.</option>
                        <option value="Baby." data-bs="Female">Baby.</option>
                        <option value="Baby of." data-bs="Male">Baby of.</option>
                        <option value="Dr." data-bs="Male">Dr.</option>
                    </select>
                    @if(isset($orderInputData))
                        <input id="patientNameInput" name="patientName" type="text" placeholder="" value="{{ $orderInputData->sub_heading }}" required>
                    @else
                        <input id="patientNameInput" name="patientName" type="text" placeholder="" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Age:</label>
                    @if(isset($orderInputData))
                        <input id="ageInput" name="age" type="text" placeholder="" value="{{ $orderInputData->component_name }}" required>
                    @else
                        <input id="ageInput" name="age" type="text" placeholder="" required>
                    @endif
                    <select name="ageType" id="ageTypeSelect">
                        <option value="Years">Years</option>
                        <option value="Months">Months</option>
                        <option value="Days">Days</option>
                    </select>
                    <input id="additionalAgeInput" name="additionalAgeInput" type="text" placeholder="Additional Age">
                    <select name="additionalAgeType" id="additionalAgeTypeSelect">
                        <option value="Years">Years</option>
                        <option value="Months">Months</option>
                        <option value="Days">Days</option>
                    </select>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Gender:</label>
                    <select name="gender" id="genderSelect">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Address:</label>
                    @if(isset($orderInputData))
                        <input name="address" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="address" type="text" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Phone:</label>
                    @if(isset($orderInputData))
                        <input name="phone" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}" {{ ($isPatientPhoneRequired) ? "required" : "" }}>
                    @else
                        <input name="phone" type="text" placeholder="" {{ ($isPatientPhoneRequired) ? "required" : "" }}>
                    @endif
                    @if ($isPatientPhoneRequired)
                        <asterisk>*</asterisk>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Email:</label>
                    @if(isset($orderInputData))
                        <input name="email" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="email" type="text" placeholder="">
                    @endif
                </div>
                <sectionHeader>Address</sectionHeader>
                <div class="inputBack">
                    <label>Area:</label>
                    @if(isset($orderInputData))
                        <input name="area" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="area" type="text" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>City:</label>
                    @if(isset($orderInputData))
                        <input name="city" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="city" type="text" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>District:</label>
                    @if(isset($orderInputData))
                        <input name="district" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="district" type="text" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>State:</label>
                    @if(isset($orderInputData))
                        <input name="state" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="state" type="text" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Country:</label>
                    @if(isset($orderInputData))
                        <input name="country" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="country" type="text" placeholder="">
                    @endif
                </div>
                <sectionHeader>Additional</sectionHeader>
                <div class="inputBack">
                    <label>Attachment:</label>
                    @if(isset($orderInputData))
                        <input name="attachment" type="file" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="attachment" type="file" placeholder="">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Clinical History:</label>
                    @if(isset($orderInputData))
                        <input name="clinicalHistory" type="text" placeholder="" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="clinicalHistory" type="text" placeholder="">
                    @endif
                </div> --}}
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save</button>
                <button id="saveOrderBtn" type="submit" class="btn btn-primary" name="search">Save & Search</button>
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

    var patientStartnameSelect = document.getElementById("patientStartnameSelect");
    function handleSelectChange() {
        var selectedOption = patientStartnameSelect.options[patientStartnameSelect.selectedIndex];
        var gender = selectedOption.getAttribute("data-bs");

        document.getElementById('genderSelect').value = gender;
    }
    patientStartnameSelect.addEventListener("change", handleSelectChange);

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
