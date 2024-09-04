@php
    $billNo = $orderDetails->bill_no;
@endphp

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
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Back To Bills</button>
                <button type="button" class="btn btn-danger" onclick="printAllResults()">Print All Results</button>
                <button style="background: green;border:0;" type="button" class="btn btn-success" onclick="sendAllResultsWhatsapp('')">Send All on WhatsApp</button>
                <button style="background: rgb(204, 170, 0);border:0;" type="button" class="btn btn-success" onclick="sendSelectedResultsWhatsapp()">Send Selected on WhatsApp</button>
                <button type="button" class="btn btn-secondary" onclick="dispatchAllBill('{{ $orderDetails->bill_no }}')">Dispatch All Reports</button>
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
                            <th><input id="allSelectCheckbox" type="checkbox"></th>
                            <th scope="col"></th>
                            <th scope="col">Orders</th>
                            <th scope="col">Date Taken</th>
                            <th scope="col">Sample Type</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $itemCount = 0; @endphp
                        @foreach($orderDetails->orderData as $order)
                            @php $itemCount++; @endphp
                            <tr>
                                <td>
                                    <input class="reportSelectCheckbox" type="checkbox" value="{{ $order->report_id }}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger" onclick="printDiv('{{$itemCount}}_billDiv')">Print Result</button>
                                    {{-- <button style="background: orange;border:0;" type="button" class="btn btn-primary" onclick="openPreview('orderbills/edit_result_entry/{{ $orderDetails->bill_no }}/{{ $order->report_id }}')">Edit Result</button> --}}
                                    <button style="background: green;border:0;" type="button" class="btn btn-primary" onclick="sendAllResultsWhatsapp('{{ $order->report_id }}')">Send Report</button>
                                </td>
                                <td>{{ $order->order_name }}</td>
                                <td>{{ $order->sample_taked_on }}</td>
                                <td>{!! $order->sample_type !!}</td>
                                <td>{{ $order->report_status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(count($orderDetails['attachmentsList']) != 0)
                    <span style="text-align: center;display:block;font-weight: bolder;">Attachments</span>
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th scope="col">File Name</th>
                                <th scope="col">Date</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderDetails['attachmentsList'] as $attachment)
                                <tr>
                                    <input type="hidden" name="attachmentId" value="{{ $attachment['id'] }}">
                                    <td>{{ $attachment['file_name'] }}</td>
                                    <td>{{ $attachment['created_at'] }}</td>
                                    <td><button type="submit" class="btn btn-primary" onclick="openNewTab(`{{ $attachment['file_path'] }}`)">View</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <div id="allResultReports">
        @php $itemCount = 0; @endphp
        @foreach($orderDetails->orderData as $order)
            @php $itemCount++; @endphp
            @php $orderDetails = $order['printable_content'] @endphp
            <div class="billDiv" id="{{$itemCount}}_billDiv" style="display: none;">
                @include('include.order_result_report')
            </div>
        @endforeach
    </div>

    <dialog id="resultEditDialog" style="width: 90%; height: 100%;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);">
        <div style="position: relative; width: 100%; height: 100%;">
            <iframe id="resultEditFrame" style="width: 100%; height: 100%;" allow="fullscreen"></iframe>
            <button id="resultEditCloseDialogButton" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; cursor: pointer; color: black; font-size: 28px;"><i class='bx bx-x'></i></button>
        </div>
    </dialog>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    const headerImage = document.getElementsByClassName('headerImage');
    const headerSpacer = document.getElementsByClassName('headerSpacer');
    const balance = "{{ $leftBalance }}";
    const noPrintBalanceReports = "{{ $user->settings['no_print_balance_reports'] }}";

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

    if (headerImage != null) {
        for (var i = 0; i < headerImage.length; i++) {
            headerImage[i].style.display = "block";

            if (headerSpacer[i] != null) {
                headerSpacer[i].style.display = "none";
            }
        }
    }

    function dispatchAllBill(billNo) {
        if (balance != "0") {
            alert("Cannot dispatch until pending balance is clear.")
            return;
        }

        goToRoute(`orderbills/result_all_dispatched_completed_bills/${billNo}`)
    }

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

    function printAllResults() {
        if (noPrintBalanceReports == "true" && balance != "0") {
            alert("Cannot print until pending balance is clear.")
            return;
        }

        if (headerImage != null) {
            if (confirm("Print with headers?")) {
                for (var i = 0; i < headerImage.length; i++) {
                    headerImage[i].style.display = "block";

                    if (headerSpacer[i] != null) {
                        headerSpacer[i].style.display = "none";
                    }
                }
            } else {
                for (var i = 0; i < headerImage.length; i++) {
                    headerImage[i].style.display = "none";

                    if (headerSpacer[i] != null) {
                        headerSpacer[i].style.display = "block";
                    }
                }
            }
        }

        const billDiv = document.getElementsByClassName('billDiv');
        for (var i = 0; i < billDiv.length; i++) {
            billDiv[i].style.display = "block";
        }

        var divElements = document.getElementById('allResultReports').innerHTML;
        divElements = divElements.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');

        var html =
        "<html lang=\"en\">" +
        "<head>" +
            "<meta charset=\"UTF-8\">" +
            "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">" +
            "<title></title>" +
            "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9\" crossorigin=\"anonymous\">"+
            "<link rel=\"stylesheet\" href=\"{{ asset('css/print.css') }}\">"+
            "<style>" +
                "@media print {" +
                    ".billDiv { " +
                        "page-break-after: always; " +
                    "} " +
                "}" +
            "</style>" +
        "</head>" +
        "<body style=\"padding-left:15mm;padding-right:15mm;\">" +
            divElements +
        "</body>" +
        "</html>";

        var printWindow = window.open('', '_blank');
        printWindow.document.write(html);
        printWindow.document.close();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);

        for (var i = 0; i < billDiv.length; i++) {
            billDiv[i].style.display = "none";
        }
    }

    function printDiv(divId) {
        if (noPrintBalanceReports == "true" && balance != "0") {
            alert("Cannot print until pending balance is clear.")
            return;
        }

        if (headerImage != null) {
            if (confirm("Print with headers?")) {
                for (var i = 0; i < headerImage.length; i++) {
                    headerImage[i].style.display = "block";

                    if (headerSpacer[i] != null) {
                        headerSpacer[i].style.display = "none";
                    }
                }
            } else {
                for (var i = 0; i < headerImage.length; i++) {
                    headerImage[i].style.display = "none";

                    if (headerSpacer[i] != null) {
                        headerSpacer[i].style.display = "block";
                    }
                }
            }
        }

        var divElements = document.getElementById(divId).innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"><link rel="stylesheet" href="{{ asset("css/print.css") }}"></head><body style="font-family: \'Microsoft JhengHei\', Arial;padding-left:15mm;padding-right:15mm;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 200);
    }

    const resultEditDialog = document.getElementById("resultEditDialog");
    const resultEditFrame = document.getElementById("resultEditFrame");
    const resultEditCloseDialogButton = document.getElementById("resultEditCloseDialogButton");

    function openPreview(url) {
        resultEditFrame.src = webUrl + url;
        resultEditDialog.showModal();
    }

    resultEditCloseDialogButton.addEventListener("click", () => {
        resultEditFrame.src = "";
        resultEditDialog.close();
        window.location.reload();
    });
</script>
</html>

@php
    $actionSuccess = session('actionSuccess');
    if ($actionSuccess) {
        $actionMessage = session('actionMessage');
        echo '<script>alert(`'.$actionMessage.'`);</script>';
    }
@endphp
