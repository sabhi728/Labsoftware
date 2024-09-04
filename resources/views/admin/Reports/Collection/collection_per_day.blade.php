<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_details.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .main {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .BoldColumns {
        font-weight: bold;
    }

    .table thead > tr > th {
        color: #2A94D7;
    }
</style>
<body>
    <div class="main_container" style="text-align: center; font-family: 'Microsoft JhengHei', Arial;">
        <div class="main">
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Exit</button>
                <button type="button" class="btn btn-primary" onclick="printrprt()">Print</button>
            </div>
            <br>
            <div id="BillRpt">
                <h4 style="text-align:center;margin-top:30px;">Collection Per Day Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b></h4>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1100px; text-align: left; margin-top: 30px; background: white;" id="GridDiv">
                    <div class="grid-mvc" data-lang="en" data-gridname="BillRpt" data-selectable="true" data-multiplefilters="false">
                        <div class="grid-wrap">
                            <div id="DataTable_wrapper" class="dataTables_wrapper no-footer">
                                <table class="table" id="DataTable" role="grid">
                                <thead>
                                    <tr role="row">
                                        <th class="grid-header Dateclmn sorting_disabled" style="width: 106px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Billed Date</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Gross Amount</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Paid Amount</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Discount</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Cash</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Card</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Cheque</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Paytm</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>UPI</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Previous Dues</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Return</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Balance</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 87px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>CancelledBIll</span></div></th>
                                        <th class="grid-header GridColumn sorting_disabled" style="width: 60px;" rowspan="1" colspan="1"><div class="grid-header-title"><span>Credits</span></div></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allBalance = 0;
                                        $allGrossAmount = 0;
                                        $allPaidAmount = 0;
                                        $allDiscount = 0;

                                        $allCashPaid = 0;
                                        $allCardPaid = 0;
                                        $allChequePaid = 0;
                                        $allPaytmPaid = 0;
                                        $allUPIPaid = 0;
                                        $allPreviousPaid = 0;

                                        $allReturnAmount = 0;
                                        $allCancelledBills = 0;
                                    @endphp

                                    @foreach($allData as $userKey => $userData)
                                        @php
                                            $totalBalance = 0;
                                            $totalGrossAmount = 0;
                                            $totalPaidAmount = 0;
                                            $totalDiscount = 0;

                                            $totalCashPaid = 0;
                                            $totalCardPaid = 0;
                                            $totalChequePaid = 0;
                                            $totalPaytmPaid = 0;
                                            $totalUPIPaid = 0;
                                            $totalPreviousPaid = 0;

                                            $totalReturnAmount = 0;
                                            $totalCancelledBills = 0;
                                        @endphp

                                        @foreach($userData as $transactionKey => $transactionData)
                                            @php
                                                $totalBalance += $transactionData['balance'];
                                                $totalGrossAmount += $transactionData['gross_amount'];
                                                $totalPaidAmount += $transactionData['paid_amount'];
                                                $totalDiscount += $transactionData['discount'];

                                                $totalCashPaid += $transactionData['cash_paid'];
                                                $totalCardPaid += $transactionData['card_paid'];
                                                $totalChequePaid += $transactionData['cheque_paid'];
                                                $totalPaytmPaid += $transactionData['paytm_paid'];
                                                $totalUPIPaid += $transactionData['upi_paid'];
                                                $totalPreviousPaid += $transactionData['previous_dues'];

                                                $totalReturnAmount += $transactionData['return_amount'];
                                                $totalCancelledBills += $transactionData['cancelled_bills'];
                                            @endphp

                                            @if($transactionKey == count($userData) - 1)
                                                <tr class="grid-row  odd grid-row-selected" role="row">
                                                    <td class="grid-cell Dateclmn" data-name="">{{ $transactionData['date'] }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalGrossAmount }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalPaidAmount }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalDiscount }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalCashPaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalCardPaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalChequePaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalPaytmPaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalUPIPaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalPreviousPaid }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalReturnAmount }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalBalance }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">{{ $totalCancelledBills }}</td>
                                                    <td class="grid-cell GridColumn" data-name="">0</td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @php
                                            $allBalance += $totalBalance;
                                            $allGrossAmount += $totalGrossAmount;
                                            $allPaidAmount += $totalPaidAmount;
                                            $allDiscount += $totalDiscount;

                                            $allCashPaid += $totalCashPaid;
                                            $allCardPaid += $totalCardPaid;
                                            $allChequePaid += $totalChequePaid;
                                            $allPaytmPaid += $totalPaytmPaid;
                                            $allUPIPaid += $totalUPIPaid;
                                            $allPreviousPaid += $totalPreviousPaid;

                                            $allReturnAmount += $totalReturnAmount;
                                            $allCancelledBills += $totalCancelledBills;
                                        @endphp
                                    @endforeach
                                    <tr class="grid-row  even" role="row">
                                        <td class="grid-cell Dateclmn" data-name="">Total Amount</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allGrossAmount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allPaidAmount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allDiscount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allCashPaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allCardPaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allChequePaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allPaytmPaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allUPIPaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allPreviousPaid }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allReturnAmount }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allBalance }}</td>
                                        <td class="grid-cell GridColumn" data-name="">{{ $allCancelledBills }}</td>
                                        <td class="grid-cell GridColumn" data-name="">0</td>
                                    </tr>
                                </tbody>
                                </table>
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
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
</body>
<script>
    function printrprt() {
        $("div.dt-buttons").remove();
        var divElements = document.getElementById("BillRpt").innerHTML;
        var oldPage = document.body.innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title></head><body style="font-family: \'Microsoft JhengHei\', Arial;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
        printWindow.close();

        document.body.innerHTML = oldPage;
    }
</script>
</html>
