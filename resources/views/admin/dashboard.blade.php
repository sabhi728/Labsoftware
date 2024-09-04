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
</head>
<style>
    .horizontal {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

    .cust-card {
        width: 100%;
        border: 1px solid var(--lightgray);
        border-radius: 15px;
    }

    .card-body {
        font-family: var(--font1);
        text-transform: uppercase;
        border-radius: 15px;
        padding: 0 !important;
        height: 100%;
    }

    .card-horizontal {
        padding: 20px;
        display: flex;
        flex-direction: row;
        margin-left: 6px;
        background: white;
        border-radius: 14px;
        height: 100%;
    }

    .card-text-heading {
        font-size: 13px;
    }

    .card-text-value {
        font-size: 25px;
        font-weight: 600;
    }

    .card-text-view-report {
        color: blue;
        cursor: pointer;
        font-size: 15px;
        text-transform: none
    }

    .gridView {
        display: grid;
        grid-template-columns: auto auto auto;
        grid-row-gap: 15px;
        grid-column-gap: 15px;
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
            <div id="ordersTableBack">
                <form action="" method="GET" class="input-group">
                    <span class="input-group-text fw-bold">From Date:</span>
                    <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}" required="">
                    <span class="input-group-text fw-bold">To Date:</span>
                    <input type="date" class="form-control" name="toDate" value="{{ $toDate }}" required="">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                <br>
                <div class="gridView">
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #4e73df;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">No Of Logged In Users</div>
                                    <div class="card-text-value" style="color: #4e73df;">{{ $noLoggedUsers }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #1cc88a;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">No of Bills</div>
                                    <div class="card-text-value" style="color: #1cc88a;">{{ $noOfBills }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #f6c23e;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Billed Amount</div>
                                    <div class="card-text-value" style="color: #f6c23e;">{{ $totalBilledAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #4edf66;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Discount Amount</div>
                                    <div class="card-text-value" style="color: #4edf66;">{{ $totalDiscountAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #1ca3c8;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Paid Amount</div>
                                    <div class="card-text-value" style="color: #1ca3c8;">{{ $totalPaidAmount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #f63e3e;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Pending Amount</div>
                                    <div class="card-text-value" style="color: #f63e3e;">{{ $totalPendingBalance }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #000000;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Total Consultation Amount</div>
                                    <div class="card-text-value" style="color: #000000;">{{ $totalConsultationsBalance }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('Consultation')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #a40988;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">UPI</div>
                                    <div class="card-text-value" style="color: #a40988;">{{ $upiPaidAmount }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('UPI')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #be0000;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">CASH</div>
                                    <div class="card-text-value" style="color: #be0000;">{{ $cashPaidAmount }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('Cash')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #656565;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">CARD</div>
                                    <div class="card-text-value" style="color: #656565;">{{ $cardPaidAmount }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('Card')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #000000;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Previous</div>
                                    <div class="card-text-value" style="color: #000000;">{{ $previousPaidAmount }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('Previous')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cust-card">
                        <div class="card-body" style="background-color: #ff0000;">
                            <div class="card-horizontal">
                                <div class="card-vertical">
                                    <div class="card-text-heading">Cancel/Refund Amount</div>
                                    <div class="card-text-value" style="color: #ff0000;">{{ $cancelRefundAmount }}</div>
                                    <div class="card-text-view-report" onclick="viewReports('Return')">View Report</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
</body>
<script>
    function viewReports(paymentType) {
        var form = document.createElement("form");
        var csrfToken = "{{ csrf_token() }}";
        var fromDate = "{{ $fromDate }}";
        var toDate = "{{ $toDate }}";

        form.method = "POST";
        form.action = webUrl + "reports/bill_reports/report";

        form.append(createHiddenInput("_token", csrfToken));
        form.append(createHiddenInput("fromDate", fromDate));
        form.append(createHiddenInput("toDate", toDate));
        form.append(createHiddenInput("getReport", ""));

        switch (paymentType) {
            case "Previous":
                form.append(createHiddenInput("previous", "true"));
                break;
            case "Return":
                form.append(createHiddenInput("return", "true"));
                break;
            case "Consultation":
                form.append(createHiddenInput("orderType[]", "2"));
                break;
            default:
                form.append(createHiddenInput("paymentType", paymentType));
                break;
        }

        document.body.appendChild(form);
        form.submit();
    }

    function createHiddenInput(name, value) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        return input;
    }
</script>
</html>
