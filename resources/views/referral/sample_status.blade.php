<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_maintenance.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.css" integrity="sha512-UiKdzM5DL+I+2YFxK+7TDedVyVm7HMp/bN85NeWMJNYortoll+Nd6PU9ZDrZiaOsdarOyk9egQm6LOJZi36L2g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>

</style>
<body>
    <div class="header">
        @include('referral.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('referral.sidebar')
        </div>
        <div class="main">
            <div id="ordersTableBack">
                <form action="{{ url('referralpanel/sample-status') }}" method="get" class="main">
                    <h1 class="fs-5">Samples Status</h1>
                    <hr>
                    <div id="formBack">
                        @csrf
                        <div class="d-flex flex-row align-items-center justify-content-center mb-3">
                            <div class="input-group">
                                <span class="input-group-text fw-bold">From Date:</span>
                                <input type="date" class="form-control" name="fromDate" id="fromDate" value="{{ $fromDate }}" required>
                                <span class="input-group-text fw-bold">To Date:</span>
                                <input type="date" class="form-control" name="toDate" id="toDate" value="{{ $toDate }}" required>
                            </div>
                        </div>
                        <div class="d-flex flex-row align-items-center justify-content-center">
                            <div class="input-group">
                                <span class="input-group-text fw-bold">Search By:</span>
                                <select class="form-control" name="searchType">
                                    <option value="InvName" @if ($searchType == 'InvName') @selected(true) @endif>Investigation Name</option>
                                    <option value="BillNo" @if ($searchType == 'BillNo') @selected(true) @endif>Bill No</option>
                                    <option value="PatName" @if ($searchType == 'PatName') @selected(true) @endif>Patient Name</option>
                                </select>
                                <input type="text" class="form-control" name="searchValue" value="{{ $searchValue }}" placeholder="Enter search content here...">
                                <span class="input-group-text fw-bold">Status:</span>
                                <select class="form-control" name="status">
                                    <option value="">All</option>
                                    <option value="Registered" @if ($status == 'Registered') @selected(true) @endif>Registered</option>
                                    <option value="Pending" @if ($status == 'Pending') @selected(true) @endif>Pending</option>
                                    <option value="In-Process" @if ($status == 'In-Process') @selected(true) @endif>In-Process</option>
                                    <option value="Rejected" @if ($status == 'Rejected') @selected(true) @endif>Rejected</option>
                                    <option value="Accepted" @if ($status == 'Accepted') @selected(true) @endif>Accepted</option>
                                    <option value="Received" @if ($status == 'Received') @selected(true) @endif>Received</option>
                                    <option value="Processed" @if ($status == 'Processed') @selected(true) @endif>Processed</option>
                                    <option value="Pending for approval" @if ($status == 'Pending for approval') @selected(true) @endif>Pending for approval</option>
                                    <option value="Cancelled" @if ($status == 'Cancelled') @selected(true) @endif>Cancelled</option>
                                    <option value="Completed" @if ($status == 'Completed') @selected(true) @endif>Completed</option>
                                    <option value="Printed" @if ($status == 'Printed') @selected(true) @endif>Printed</option>
                                </select>
                                <button class="btn btn-primary" type="submit">View</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if (isset($orderDetails))
                <div id="ordersTableBack">
                    <div class="container text-center mb-3">
                        <div class="row w-100 p-3">
                            <div class="col">
                                <div>Patients Billed</div>
                                <div class="fs-4 fw-bold" style="color: #4e73df;">{{ $totalPatients }}</div>
                            </div>
                            <div class="col">
                                <div>Samples Received</div>
                                <div class="fs-4 fw-bold" style="color: #1cc88a;">{{ $totalSampleReceived }}</div>
                            </div>
                            <div class="col">
                                <div>Reports Ready</div>
                                <div class="fs-4 fw-bold" style="color: #be0000;">{{ $totalReportsReady }}</div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover">
                        {{-- <thead>
                            <tr>
                                {{-- <th scope="col"></th> --}}
                                {{-- <th scope="col" width="10%">Bill No</th>
                                <th scope="col" width="20%">Order Date</th>
                                <th scope="col" width="15%">Name</th>
                                <th scope="col" width="20%">Age/Gender</th>
                                <th scope="col" width="15%">Referral</th>
                                <th scope="col" width="10%"><span class="text-warning">Pending ({{ $totalPendingReports }})</span></th>
                                <th scope="col" width="10%"><span class="text-success">Dispatch ({{ $totalDispatchReports }})</span></th>
                            </tr>
                        </thead> --}}
                        <tbody>
                            <tr style="background-color: #eef0f1 !important;pointer-events: none;">
                                <td colspan="7">
                                    <table class="w-100">
                                        <tr>
                                            <td scope="col" width="10%" class="fw-bold">Bill No</td>
                                            <td scope="col" width="20%" class="fw-bold">Order Date</td>
                                            <td scope="col" width="15%" class="fw-bold">Name</td>
                                            <td scope="col" width="15%" class="fw-bold">Age/Gender</td>
                                            <td scope="col" width="20%" class="fw-bold">Referral</td>
                                            <td scope="col" width="10%" class="fw-bold text-center"><span class="text-warning">Pending ({{ $totalPendingReports }})</span></td>
                                            <td scope="col" width="10%" class="fw-bold text-center"><span class="text-success">Dispatch ({{ $totalDispatchReports }})</span></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            @foreach ($orderDetails as $order)
                                {{-- <tr style="background: {{ $order['background_color'] }};">
                                    <td style="color: white;">{{ $order['bill_no'] }}</td>
                                    <td style="color: white;">{{ $order['created_at'] }}</td>
                                    <td style="color: white;"><span class="text-uppercase">{{ $order['patient_name'] }}</span></td>
                                    <td style="color: white;"><span class="text-uppercase">{{ $order['age_gender'] }}</span></td>
                                    <td style="color: white;">{{ $order['order_name'] }}</td>
                                    <td style="color: white;">
                                        @if (!empty($order['print']))
                                            <button onclick="showPrintDialog('{{ $order['print'] }}')" class="btn btn-light"><i class='bx bxs-printer fs-5'></i></button>
                                        @endif
                                    </td>
                                    <td style="color: white;"><span class="btn btn-light">{{ $order['status'] }}</span></td>
                                    <td style="color: white;">{{ $order['remark'] }}</td>
                                    <td style="color: white;">{{ $order['order_department_name'] }}</td>
                                </tr> --}}

                                <tr style="cursor: pointer;" data-items="{{ json_encode($order->items) }}" data-patient-name="{{ $order->patient_title_name }} {{ $order->patient_name }}" data-bill-no="{{ $order->bill_no }}" class="billItem">
                                    <td colspan="7">
                                        <div>
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td width="10%">{{ $order->bill_no }}</td>
                                                    <td width="20%">{{ $order->formatted_created_at }}</td>
                                                    <td width="15%"><span class="text-uppercase">{{ $order->patient_title_name }} {{ $order->patient_name }}</span></td>
                                                    <td width="15%"><span class="text-uppercase">{{ $order->patient_age }} {{ $order->patient_age_type }}/{{ $order->patient_gender }}</span></td>
                                                    <td width="20%"><span class="text-uppercase">{{ $order->doc_name }}</span></td>
                                                    <td width="10%" class="text-center"><span class="text-uppercase">{{ $order->currentPatientPendingReports }}</span></td>
                                                    <td width="10%" class="text-center"><span class="text-uppercase">{{ $order->currentPatientDispatchReports }}</span></td>
                                                </tr>
                                            </table>
                                            <table style="width: 100%;" class="ms-2 mt-2">
                                                @if (isset($order->items))
                                                    @foreach ($order->items as $key => $item)
                                                        <tr>
                                                            <td><span style="font-size: 13px;">{{ $item['order_name'] }}</span></td>
                                                        </tr>

                                                        @if ($key >= 1)
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                                {{-- <tr style="cursor: pointer;"> --}}
                                    {{-- <td style="cursor: pointer;" class="openSubItemsTable"><i class='bx bx-plus text-primary'></i></td> --}}
                                    {{-- <td>{{ $order->bill_no }}</td>
                                    <td>{{ $order->formatted_created_at }}</td>
                                    <td><span class="text-uppercase">{{ $order->patient_title_name }} {{ $order->patient_name }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->patient_age }} {{ $order->patient_age_type }}/{{ $order->patient_gender }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->doc_name }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->currentPatientPendingReports }}</span></td>
                                    <td><span class="text-uppercase">{{ $order->currentPatientDispatchReports }}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="7">
                                        <table>
                                            <tbody>
                                                @if (isset($order->items))
                                                    @foreach ($order->items as $key => $item)
                                                        <tr>
                                                            <td><span class="ms-3" style="font-size: 13px;">{{ $item['order_name'] }}</span></td>
                                                        </tr>

                                                        @if ($key >= 1)
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </td>
                                </tr> --}}
                                {{-- <tr class="subItemsTable" style="display: none;">
                                    <td colspan="7">
                                        <table class="table table-bordered bg-white">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th scope="col">INVNAME</th>
                                                    <th scope="col">Print</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Remarks</th>
                                                    <th scope="col">Department Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($order->items))
                                                    @foreach ($order->items as $item)
                                                        <tr style="background: {{ $item['background_color'] }};">
                                                            <td style="color: white;">{{ $item['order_name'] }}</td>
                                                            <td style="color: white;">
                                                                @if (!empty($item['print']))
                                                                    <button onclick="showPrintDialog('{{ $item['print'] }}')" class="btn btn-light"><i class='bx bxs-printer fs-5'></i></button>
                                                                @endif
                                                            </td>
                                                            <td style="color: white;"><span class="btn btn-light">{{ $item['status'] }}</span></td>
                                                            <td style="color: white;">{{ $item['remark'] }}</td>
                                                            <td style="color: white;">{{ $item['order_department_name'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </td>
                                </tr> --}}
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div id="billModel" class="modal">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex flex-column">
                        <h1 id="billModelPatientName" class="modal-title fs-5 text-uppercase fw-bold"></h1>
                        <span id="billModelNumber" class="text-secondary">Bill:</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Sample Date</th>
                                <th>Accession No</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Print</th>
                            </tr>
                        </thead>
                        <tbody id="billModelItems">
                        </tbody>
                    </table>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> --}}
            </div>
        </div>
    </div>

    <div id="printModel" class="modal">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header d-flex flex-row justify-content-center align-items-center">
                    <div class="d-flex flex-row justify-content-center align-items-center">
                        <span>Not able to view PDF?</span>
                        <a href="" id="printModelPdfLink" target="_blank" class="ms-1">Click here</a>
                    </div>
                    <div class="d-flex flex-row justify-content-center align-items-center ms-4">
                        <button id="btnPrintModelWithLetterhead" class="btn btn-primary btn-sm">With Letterhead</button>
                        <button id="btnPrintModelMarkDone" class="ms-2 btn btn-outline-success btn-sm">Mark As Print Done</button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 d-flex flex-column justify-content-center align-items-center">
                    <div id="printModelLoading" class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <iframe id="printModelFrame" width="100%" height="100%"></iframe>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div> --}}
            </div>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.6.1/toastify.min.js" integrity="sha512-79j1YQOJuI8mLseq9icSQKT6bLlLtWknKwj1OpJZMdPt2pFBry3vQTt+NZuJw7NSd1pHhZlu0s12Ngqfa371EA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

<script>
    var printDialogUrl = '';
    var printDialogPrintStatus = '';

    $('#btnPrintModelWithLetterhead').on('click', function() {
        var buttonText = $(this).text().trim();
        var withHeader = (buttonText === 'Without Letterhead') ? 'false' : 'true';

        showPrintDialog(printDialogUrl, withHeader, printDialogPrintStatus);

        $(this).text((buttonText === 'Without Letterhead') ? 'With Letterhead' : 'Without Letterhead');
    });

    $('#btnPrintModelMarkDone').on('click', function() {
        const url = printDialogUrl.replace('viewbill', 'mark-result-as-printed');

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                Toastify({
                    text: "Marked as printed successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#198754"
                }).showToast();

                showPrintDialogPrintDone();
            })
            .catch(error => {
            });
    });

    function showPrintDialog(url, withHeader, status) {
        printDialogUrl = url;
        printDialogPrintStatus = status;

        showPrintDialogLoading();

        $('#printModelFrame').attr('src', url + '/' + withHeader);
        $('#printModelPdfLink').attr('href', url + '/' + withHeader);

        $('#printModelFrame').on('load', function() {
            showPrintDialogMain();
        });

        if (status === 'Printed') {
            showPrintDialogPrintDone();
        } else {
            showPrintDialogMarkAsPrint();
        }

        $('#printModel').modal('show');
    }

    function showPrintDialogPrintDone() {
        $('#btnPrintModelMarkDone').text('Print Done');
        $('#btnPrintModelMarkDone').addClass('cursor-not-allowed disabled');
    }

    function showPrintDialogMarkAsPrint() {
        $('#btnPrintModelMarkDone').text('Mark As Print Done');
        $('#btnPrintModelMarkDone').removeClass('cursor-not-allowed disabled');
    }

    function showPrintDialogLoading() {
        $('#printModelFrame').css('display', 'none');
        $('#printModelLoading').css('display', 'flex');
    }

    function showPrintDialogMain() {
        $('#printModelFrame').css('display', 'flex');
        $('#printModelLoading').css('display', 'none');
    }

    // function showPrintDialog(url, withHeader) {
    //     $('#printModelFrame').css('display', 'none');
    //     $('#printModelFrame').attr('src', url + '/' + withHeader);
    //     $('#printModelFrame').on('load', function() {
    //         $(this).show();
    //     });

    //     $('#printModelPdfLink').attr('href', url + '/false');

    //     $('#btnPrintModelWithLetterhead').on('click', function() {
    //         var buttonText = $(this).text().trim();

    //         if (buttonText === 'Without Letterhead') {
    //             showPrintDialog(url, 'false');
    //             $(this).text('With Letterhead');
    //         } else {
    //             showPrintDialog(url, 'true');
    //             $(this).text('Without Letterhead');
    //         }
    //     });

    //     $('#printModel').modal('show');

    //     // Swal.fire({
    //     //     title: "Print",
    //     //     showDenyButton: true,
    //     //     showCancelButton: true,
    //     //     confirmButtonText: "With Header",
    //     //     denyButtonText: `Without Header`,
    //     //     html: `
    //     //         <iframe src="${url + '/false'}"></iframe>
    //     //     `,
    //     //     }).then((result) => {
    //     //         // if (result.isConfirmed) {
    //     //         //     window.open(url + '/true', '_blank');
    //     //         // } else if (result.isDenied) {
    //     //         //     window.open(url + '/false', '_blank');
    //     //         // }
    //     //     });
    // }

    document.addEventListener('DOMContentLoaded', function() {
        $('.billItem').on('click', function() {
            var items = $(this).data('items');
            var patientName = $(this).data('patient-name');
            var billNo = $(this).data('bill-no');

            $('#billModelPatientName').text(patientName);
            $('#billModelNumber').text('Bill: ' + billNo);

            var modalBody = '';

            $.each(items, function(index, item){
                modalBody += '<tr>';
                modalBody += '<td>' + item.order_name + '</td>';
                modalBody += '<td>' + item.sample_date + '</td>';
                modalBody += '<td>' + item.sample_barcode + '</td>';
                modalBody += `<td><span class="btn" style="background: ${item.background_color};color:white;">${item.status}</span></td>`;
                modalBody += '<td>' + item.remark + '</td>';
                if (item.print === '') {
                    modalBody += `<td></td>`;
                } else {
                    modalBody += `<td><button onclick="showPrintDialog('${item.print}', 'false', '${item.status}')" class="btn btn-success"><i class='bx bxs-printer fs-5'></i></button></td>`;
                }
                modalBody += '</tr>';
            });

            $('#billModelItems').html(modalBody);
            $('#billModel').modal('show');
        });

        // var toggles = document.querySelectorAll('.openSubItemsTable');

        // toggles.forEach(function(toggle) {
        //     toggle.addEventListener('click', function() {
        //         var nextRow = toggle.closest('tr').nextElementSibling;

        //         if (nextRow && nextRow.classList.contains('subItemsTable')) {
        //             if (nextRow.style.display === 'none') {
        //                 nextRow.style.display = 'table-row';

        //                 toggle.querySelector('i').classList.remove('bx-plus');
        //                 toggle.querySelector('i').classList.add('bx-minus');
        //             } else {
        //                 nextRow.style.display = 'none';

        //                 toggle.querySelector('i').classList.remove('bx-minus');
        //                 toggle.querySelector('i').classList.add('bx-plus');
        //             }
        //         }
        //     });
        // });
    });
</script>
</html>
