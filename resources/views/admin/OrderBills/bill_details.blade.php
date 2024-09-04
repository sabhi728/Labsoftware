<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_maintenance.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .horizontal {
        display: flex;
        flex-direction: row;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .vertical {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .vertical2 {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }

    .inputForm span {
        width: 120px;
    }

    .inputReadonly {
        background: var(--lightgray);
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputWriteable {
        border-radius: 5px;
        padding: 7.5px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #inputWriteableSaveBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
    }

    #phoneUmrInput {
        border-radius: 5px;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0px;
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputField {
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .ui-timepicker-container {
        z-index: 10000 !important;
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
                <button type="button" class="btn btn-primary" onclick="goToRoute(`orderbills/in_process_bills`)">Back To Bills</button>
                <button type="button" class="btn btn-primary" id="editPatientDetails">Edit Patient Details</button>
                @php $enableDispatchButton = ($orderDetails->enableDispatchButton) ? "" : "disabled" @endphp
                <button type="button" class="btn btn-primary" onclick="goToRoute(`orderbills/result_dispatch/{{$orderDetails->bill_no}}`)" {{ $enableDispatchButton }}>Dispatch</button>
                <button type="button" class="btn btn-primary" onclick="sendSelectedResultsWhatsapp()">Send Selected on WhatsApp</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute(`orderbills/hard_refresh_report_results/{{ $orderDetails->bill_no }}`)">Refresh Bill</button>
            </div>
            <div id="ordersTableBack">
                <div id="headerHorizonatl" class="horizontal">
                    <div class="vertical2">
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Bill No:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->bill_no }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Patient Name:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Phone:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_phone }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Gender:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails->patient_gender }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>Age:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->patient_age }} {{ $orderDetails->patient_age_type }}" readonly>
                            </div>

                            <div class="vertical">
                                <span>UMR:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->umr_number }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                            <th scope="col"><input id="allSelectCheckbox" type="checkbox"></th>
                            <th scope="col"></th>
                            <th scope="col">GroupNumber</th>
                            <th scope="col">Orders</th>
                            <th scope="col">Date Taken</th>
                            <th scope="col"></th>
                            <th scope="col">Sample Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $itemCount = 0; @endphp
                        @foreach($orderDetails->orderData as $order)
                        @php $itemCount++; @endphp
                        <tr>
                            <td>
                                <input class="reportSelectCheckbox" type="checkbox"
                                @if(!$order->show_whatsapp_button)
                                    @disabled(true)
                                @endif value="{{ $order->report_id }}">
                            </td>
                            <td>
                                <button style="background: {{ $order->report_color }};border:0;" type="button" class="btn btn-primary" onclick="goToRoute('orderbills/result_entry/{{ $orderDetails->bill_no }}/{{ $order->report_id }}')">Result Entry</button>
                                @if ($order->show_whatsapp_button)
                                    <button style="background: green;border:0;" type="button" class="btn btn-success" onclick="sendAllResultsWhatsapp('{{ $order->report_id }}')">Send on Whatsapp</button>
                                @endif
                            </td>
                            <td style="color: {{ $order->report_color }};">{{ $itemCount }}</td>
                            <td style="color: {{ $order->report_color }};">{{ $order->order_name }}</td>
                            <td style="color: {{ $order->report_color }};">{{ (empty($order->sample_type)) ? "" : $order->sample_taked_on }}</td>
                            <td><button class="btn btn-danger" onclick="showUpdateBillDatesDialog('{{ $orderDetails->bill_no }}', '{{ $order->report_id }}')">Edit Dates</button></td>
                            <td style="color: {{ $order->report_color }};">{!! $order->sample_type !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    @include('include.edit_patient_dialog')

    <div class="modal fade" id="updateBillDatesModel" tabindex="-1" role="dialog" aria-labelledby="updateBillDatesModelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="main">
                    <form action="{{ url('orderbills/update_report_dates') }}" method="post">
                        @csrf
                        <sectionHeader>Edit Dates</sectionHeader>
                        <div class="inputBack">
                            <label>Bill Date:</label>
                            <input id="updateBillNo" name="updateBillNo" type="hidden" required>
                            <input id="updateBillDate" name="updateBillDate" type="date" style="width: auto;" value="" required>
                            <input id="updateBillTime" name="updateBillTime" type="time" style="width: auto; margin-left: 10px;" value="" required>
                        </div>
                        <div class="inputBack" id="updateReportingLayout">
                            <label>Reporting Date:</label>
                            <input id="updateReportingNo" name="updateReportingNo" type="hidden" required>
                            <input id="updateReportingDate" name="updateReportingDate" type="date" style="width: auto;" value="" required>
                            <input id="updateReportingTime" name="updateReportingTime" type="time" style="width: auto; margin-left: 10px;" value="" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    const balance = "{{ $leftBalance }}";

    const allSelectCheckbox = document.getElementById('allSelectCheckbox');
    const reportSelectCheckbox = document.querySelectorAll('.reportSelectCheckbox');

    allSelectCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;

        reportSelectCheckbox.forEach(function(checkbox) {
            if (!checkbox.disabled) {
                checkbox.checked = isChecked;
            }
        });
    });

    function sendAllResultsWhatsapp(orderNo) {
        if (balance != "0") {
            alert("Cannot send until pending balance is clear.")
            return;
        }

        Swal.fire({
            title: "Enter whatsapp number",
            input: "text",
            inputValue: "{{ $orderDetails->patient_phone }}",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Send",
            showLoaderOnConfirm: true,
                preConfirm: async (phone) => {
                    try {
                        var formData = new FormData();
                        var csrfToken = "{{ csrf_token() }}";

                        formData.append("_token", csrfToken);
                        formData.append("phone", phone);
                        formData.append("bill_no", "{{ $orderDetails->bill_no }}");
                        formData.append("order_no", orderNo);

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', webUrl + 'orderbills/send_report_whatsapp', true);

                        xhr.onload = function () {
                            if (xhr.status === 200) {
                                Swal.fire({
                                    title: `Result sent on Whatsapp successfully`
                                });
                            } else {
                                Swal.showValidationMessage('Failed to send results on whatsapp');
                            }
                        };

                        xhr.send(formData);
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
    }

    function sendSelectedResultsWhatsapp() {
        if (balance != "0") {
            alert("Cannot send until pending balance is clear.")
            return;
        }

        const reportSelectCheckbox = document.getElementsByClassName('reportSelectCheckbox');
        var selectedOrderIds = "";

        for (var i = 0; i < reportSelectCheckbox.length; i++) {
            if (reportSelectCheckbox[i].checked) {
                if (selectedOrderIds == "") {
                    selectedOrderIds+= reportSelectCheckbox[i].value;
                } else {
                    selectedOrderIds+= "," + reportSelectCheckbox[i].value;
                }
            }
        }

        if (selectedOrderIds == "") {
            alert('Select reports to send');
            return;
        }

        Swal.fire({
            title: "Enter whatsapp number",
            input: "text",
            inputValue: "{{ $orderDetails->patient_phone }}",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Send",
            showLoaderOnConfirm: true,
                preConfirm: async (phone) => {
                    try {
                        var formData = new FormData();
                        var csrfToken = "{{ csrf_token() }}";

                        formData.append("_token", csrfToken);
                        formData.append("phone", phone);
                        formData.append("bill_no", "{{ $orderDetails->bill_no }}");
                        formData.append("order_no", selectedOrderIds);

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', webUrl + 'orderbills/send_selected_report_whatsapp', true);

                        xhr.onload = function () {
                            if (xhr.status === 200) {
                                Swal.fire({
                                    title: `Selected results sent on Whatsapp successfully`
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = window.location.href;
                                    }
                                });
                            } else {
                                Swal.showValidationMessage('Failed to send results on whatsapp');
                            }
                        };

                        xhr.send(formData);
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
    }

    function showUpdateBillDatesDialog(billNo, reportId) {
        $('#updateBillNo').val('');
        $('#updateBillDate').val('');
        $('#updateBillTime').val('');

        $('#updateReportingNo').val('');
        $('#updateReportingDate').val('');
        $('#updateReportingTime').val('');

        fetch(webUrl + `orderbills/get_report_dates/${billNo}/${reportId}`)
            .then(response => response.json())
            .then(responseJson => {
                $('#updateBillNo').val(responseJson.bill_no);
                $('#updateBillDate').val(responseJson.bill_date);
                $('#updateBillTime').val(responseJson.bill_time);

                if (responseJson.hasOwnProperty('report_date')) {
                    document.getElementById('updateReportingLayout').style.display = "flex";

                    $('#updateReportingNo').val(responseJson.report_no);
                    $('#updateReportingDate').val(responseJson.report_date);
                    $('#updateReportingTime').val(responseJson.report_time);

                    $('#updateReportingNo').prop('required', true);
                    $('#updateReportingDate').prop('required', true);
                    $('#updateReportingTime').prop('required', true);
                } else {
                    document.getElementById('updateReportingLayout').style.display = "none";

                    $('#updateReportingNo').prop('required', false);
                    $('#updateReportingDate').prop('required', false);
                    $('#updateReportingTime').prop('required', false);
                }

                $("#updateBillDatesModel").modal('show');
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    $(document).ready(function() {
        $("#redirect_url").val("{{ str_replace(env('URL'), '', url()->current()) }}");

        $("#editPatientDetails").click(function() {
            $("#myModal").modal('show');
        });
    });
</script>
</html>
