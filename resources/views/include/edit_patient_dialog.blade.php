@php
    $systemSettings = DB::select('SELECT * FROM system_settings WHERE 1');
    $systemSettings = reset($systemSettings)
@endphp

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
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        width: 150px;
    }

    #ageTypeSelect {
        width: max-content;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #formBack {
        background: white;
        border-radius: 10px;
        border: 0;
        margin-top: 10px;
    }

    .inputBack {
        font-family: var(--font1);
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-bottom: 15px;
    }

    .inputBack label {
        font-weight: 300;
        font-size: 15px;
        width: 200px;
    }

    .inputBack input {
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 15px;
        border: 1px solid var(--lightdarkgray);
        width: 100%;
    }

    .inputBack select {
        padding: 5px 10px;
        border-radius: 5px;
        border: 1px solid var(--lightdarkgray);
        background: white;
        width: 100%;
        font-size: 15px;
    }

    .search-container {
        width: 100%;
        position: relative;
        display: inline-block;
    }

    .searchItemsList {
        flex-direction: column;
        border: 1px solid var(--lightdarkgray);
        border-radius: 5px;
        background: var(--lightgray);
        font-weight: 300;
        font-size: 14px;
        padding: 3px;
        width: 306px;
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        box-sizing: border-box;
        z-index: 9999;
    }

    .searchItemsList span {
        font-family: var(--font1);
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .searchItemsList span:hover {
        background: var(--buttoncolor);
        color: white;
    }
</style>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="main">
                <form id="formBack" action="{{ url('orderbills/update_patient') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bill_no" value="{{ $orderDetails->bill_no }}">
                    <input type="hidden" name="umr_number" value="{{ $orderDetails->umr_number }}">
                    <input type="hidden" id="doctor" name="doctor" value="{{ $orderDetails->doctor }}">
                    <input type="hidden" id="referred_by_id" name="referred_by_id" value="{{ $orderDetails->referred_by_id }}">
                    <input type="hidden" id="redirect_url" name="redirect_url" value="">

                    <sectionHeader>Edit Patient Details</sectionHeader>
                    <div class="inputBack">
                        <label>Patient Name:</label>
                        <select name="patientTitlename" id="patientStartnameSelect">
                            <option value="MR." data-bs="Male" {{ strtoupper($orderDetails->patient_title_name) == 'MR.' ? 'selected' : '' }}>MR.</option>
                            <option value="MRS." data-bs="Female" {{ strtoupper($orderDetails->patient_title_name) == 'MRS.' ? 'selected' : '' }}>MRS.</option>
                            <option value="MS." data-bs="Female" {{ strtoupper($orderDetails->patient_title_name) == 'MS.' ? 'selected' : '' }}>MS.</option>
                            <option value="MASTER." data-bs="Male" {{ strtoupper($orderDetails->patient_title_name) == 'MASTER.' ? 'selected' : '' }}>MASTER.</option>
                            <option value="BABY." data-bs="Female" {{ strtoupper($orderDetails->patient_title_name) == 'BABY.' ? 'selected' : '' }}>BABY.</option>
                            <option value="BABY OF." data-bs="Male" {{ strtoupper($orderDetails->patient_title_name) == 'BABY OF.' ? 'selected' : '' }}>BABY OF.</option>
                            <option value="DR." data-bs="Male" {{ strtoupper($orderDetails->patient_title_name) == 'DR.' ? 'selected' : '' }}>DR.</option>
                        </select>
                        <input id="patientNameInput" name="patientName" type="text" placeholder="" value="{{ $orderDetails->patient_name }}" required>
                    </div>
                    <div class="inputBack">
                        <label>Age:</label>
                        <input id="ageInput" name="age" type="text" placeholder="" value="{{ $orderDetails->patient_age }}" required>
                        @php
                            $string = $orderDetails->patient_age_type;
                            $firstSpacePos = strpos($string, ' ');

                            if ($firstSpacePos !== false) {
                                $part1 = substr($string, 0, $firstSpacePos);
                                $part2 = substr($string, $firstSpacePos + 1);
                                $part2AgeType = '';

                                $secondSpacePos = strpos($part2, ' ');

                                if ($secondSpacePos !== false) {
                                    $part2AgeType = substr($part2, $secondSpacePos + 1);
                                    $part2 = substr($part2, 0, $secondSpacePos);
                                }
                            } else {
                                $part1 = $string;
                                $part2 = '';
                                $part2AgeType = '';
                            }
                        @endphp
                        <select name="ageType" id="ageTypeSelect">
                            @if($part1 == "Years")
                                <option value="Years" selected>Years</option>
                            @else
                                <option value="Years">Years</option>
                            @endif
                            @if($part1 == "Months")
                                <option value="Months" selected>Months</option>
                            @else
                                <option value="Months">Months</option>
                            @endif
                            @if($part1 == "Days")
                                <option value="Days" selected>Days</option>
                            @else
                                <option value="Days">Days</option>
                            @endif
                        </select>
                        <input id="additionalAgeInput" name="additionalAgeInput" type="text" placeholder="Additional Age" value="{{ $part2 }}">
                        <select name="additionalAgeType" id="additionalAgeTypeSelect">
                            <option value="Months" @if ($part2AgeType == "Months") @selected(true) @endif>Months</option>
                            <option value="Days" @if ($part2AgeType == "Days") @selected(true) @endif>Days</option>
                        </select>
                    </div>
                    <div class="inputBack">
                        <label>Gender:</label>
                        <select name="gender" id="genderSelect">
                            @if($orderDetails->patient_gender == "Male")
                                <option value="Male" selected>Male</option>
                            @else
                                <option value="Male">Male</option>
                            @endif
                            @if($orderDetails->patient_gender == "Female")
                                <option value="Female" selected>Female</option>
                            @else
                                <option value="Female">Female</option>
                            @endif
                        </select>
                    </div>
                    <div class="inputBack">
                        <label>Phone:</label>
                        <input name="phone" type="text" placeholder="" value="{{ $orderDetails->patient_phone }}" {{ ($systemSettings->patient_phone_not_required == "false") ? "required" : "" }}>
                    </div>
                    <hr>
                    <div id="additionEditOptions">
                        <div class="inputBack">
                            <label>Doctor:</label>
                            <div class="search-container">
                                <input id="doctorInput" name="doctorInput" placeholder="Doctor" value="{{ $orderDetails->doc_name }}" autocomplete="off">
                                <div id="doctorInputList" class="searchItemsList">
                                    <span>Item 1</span>
                                </div>
                            </div>
                        </div>
                        <div class="inputBack">
                            <label>Referred By:</label>
                            <div class="search-container">
                                <input id="referredByInput" name="referredByInput" placeholder="Referred By" value="{{ $orderDetails->referred_by }}" autocomplete="off">
                                <div id="referredByInputList" class="searchItemsList">
                                    <span>Item 1</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button id="saveOrderBtn" type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
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
<script>
    var patientStartnameSelect = document.getElementById("patientStartnameSelect");
    function handleSelectChange() {
        var selectedOption = patientStartnameSelect.options[patientStartnameSelect.selectedIndex];
        var gender = selectedOption.getAttribute("data-bs");

        document.getElementById('genderSelect').value = gender;
    }
    patientStartnameSelect.addEventListener("change", handleSelectChange);

    var selectedDoctorId = "{{ $orderDetails->doctor }}";
    var selectedReferredById = "{{ $orderDetails->referred_by_id }}";

    var doctorInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('doctorInputList');

        selectedDoctorId = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_doctors/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <span onclick="selectDoctor('${item.id}', '${item.doc_name}')">${item.doc_name}</span>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('doctorInput').addEventListener('input', doctorInputHandler);

    function selectDoctor(doctorId, doctorName) {
        selectedDoctorId = doctorId;
        document.getElementById('doctor').value = doctorId;

        var doctorInputList = document.getElementById('doctorInputList');
        var doctorInput = document.getElementById('doctorInput');

        document.getElementById('doctorInput').removeEventListener('input', doctorInputHandler);
        doctorInput.value = doctorName;
        document.getElementById('doctorInput').addEventListener('input', doctorInputHandler);

        doctorInputList.style.display = "none";
    }

    var referredByInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('referredByInputList');

        selectedReferredById = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_locations/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <span onclick="selectReferredBy('${item.id}', '${item.name}')">${item.name}</span>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('referredByInput').addEventListener('input', referredByInputHandler);

    function selectReferredBy(referredById, referredByName) {
        selectedReferredById = referredById;
        document.getElementById('referred_by_id').value = referredById;

        var referredByInputList = document.getElementById('referredByInputList');
        var referredByInput = document.getElementById('referredByInput');

        document.getElementById('referredByInput').removeEventListener('input', referredByInputHandler);
        referredByInput.value = referredByName;
        document.getElementById('referredByInput').addEventListener('input', referredByInputHandler);

        referredByInputList.style.display = "none";
    }
</script>
