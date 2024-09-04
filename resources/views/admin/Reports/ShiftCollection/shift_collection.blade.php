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
</style>
<body>
    <div class="main_container" style="text-align: center; font-family: 'Microsoft JhengHei', Arial;">
        <div class="main">
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Exit</button>
                <button type="button" class="btn btn-primary" onclick="printrprt()">Print</button>
            </div>
            <br>
            <div id="BillRpt" style="paddig-bottom:100px;">
                <h4 style="text-align:center;margin-top:30px;">
                    Shift Collection Report Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b> Username: {{ $username }}
                </h4>
                <br>
                <div style="border: groove; overflow-y: auto; padding-top: 10px; padding-bottom:2%; margin: 0 auto; width: 1000px; text-align: left; margin-top: 30px; ">
                    <table style="width: 100%; margin-bottom: 10px; border-bottom: groove;">
                        <thead style="border-bottom:groove;">
                            <tr style="width:100%">
                                <th style="width:12%"><b>ReqNo</b></th>
                                <th style="width:22%"><b>Patient Name</b></th>
                                <th style="width:21%"><b>User Name</b></th>
                                <th style="width:18%"><b>Return Amount</b></th>
                                <th style="width: 20%; padding-right: 5px;"><b>Invoice/Cheque No.</b></th>
                                <th style="width:15%"><b>Mode</b></th>
                                <th style="width:15%"><b>Amount Received(Rs.)</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allTransactionsData as $paymentMethod => $transactions)
                                @php $sumForCurrentPaymentMethod = 0; @endphp
                                @foreach($transactions as $transaction)
                                    @if (!$transaction['is_previous_due'])
                                        <tr style="width:100%">
                                            <td style="width:11.5%" class="GridColumn">{{ $transaction['req_no'] }}</td>
                                            <td style="width:21%" class="GridColumn">{{ $transaction['patient_name'] }}</td>
                                            <td style="width:20%" class="GridColumn">{{ $transaction['user_name'] }}</td>
                                            <td style="width:18%" class="GridColumn">{{ $transaction['return_amount'] }}</td>
                                            <td style="width:20%" class="GridColumn">{{ $transaction['txn_id'] }}</td>
                                            <td style="width:15%" class="GridColumn">{{ $transaction['mode'] }}</td>
                                            <td style="width:15%" class="GridColumn">{{ $transaction['amount'] }}</td>
                                        </tr>

                                        @php
                                            $sumForCurrentPaymentMethod += $transaction['amount'];
                                        @endphp
                                    @endif
                                @endforeach

                                @if ($sumForCurrentPaymentMethod > 0)
                                    <tr style="width:100%">
                                        <td style="width:11.5%" class="BoldColumns"></td>
                                        <td style="width:21%" class="BoldColumns"></td>
                                        <td style="width:20%" class="BoldColumns"></td>
                                        <td style="width:18%" class="BoldColumns"></td>
                                        <td style="width:25%" class="BoldColumns" colspan="2">Sum of {{ $paymentMethod }} :</td>
                                        <td style="width:12%" class="BoldColumns">
                                            {{ $sumForCurrentPaymentMethod }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            @foreach($allTransactionsData as $paymentMethod => $transactions)
                                @php $sumForCurrentPaymentMethod = 0; @endphp
                                @foreach($transactions as $transaction)
                                    @if ($transaction['is_previous_due'])
                                        <tr style="width:100%">
                                            <td style="width:11.5%" class="GridColumn">{{ $transaction['req_no'] }}</td>
                                            <td style="width:21%" class="GridColumn">{{ $transaction['patient_name'] }}</td>
                                            <td style="width:20%" class="GridColumn">{{ $transaction['user_name'] }}</td>
                                            <td style="width:18%" class="GridColumn">{{ $transaction['return_amount'] }}</td>
                                            <td style="width:20%" class="GridColumn">{{ $transaction['txn_id'] }}</td>
                                            <td style="width:15%" class="GridColumn">{{ $transaction['mode'] }}</td>
                                            <td style="width:15%" class="GridColumn">{{ $transaction['amount'] }}</td>
                                        </tr>

                                        @php
                                            $sumForCurrentPaymentMethod += $transaction['amount'];
                                        @endphp
                                    @endif
                                @endforeach

                                @if ($sumForCurrentPaymentMethod > 0)
                                    <tr style="width:100%">
                                        <td style="width:11.5%" class="BoldColumns"></td>
                                        <td style="width:21%" class="BoldColumns"></td>
                                        <td style="width:20%" class="BoldColumns"></td>
                                        <td style="width:18%" class="BoldColumns"></td>
                                        <td style="width:25%" class="BoldColumns" colspan="2">Sum of Previous Due {{ $paymentMethod }} :</td>
                                        <td style="width:12%" class="BoldColumns">
                                            {{ $sumForCurrentPaymentMethod }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr style="width:100%">
                                <td style="width:11.5%" class="BoldColumns"></td>
                                <td style="width:21%" class="BoldColumns"></td>
                                <td style="width:20%" class="BoldColumns"></td>
                                <td style="width:18%" class="BoldColumns"></td>
                                <td style="width:25%" class="BoldColumns" colspan="2"> Amt :</td>
                                <td style="width:12%" class="BoldColumns">{{ $finalAmount }}</td>
                            </tr>
                            @if($cashPaid != 0)
                                <tr style="width:100%">
                                    <td style="width:11.5%" class="BoldColumns"></td>
                                    <td style="width:21%" class="BoldColumns"></td>
                                    <td style="width:20%" class="BoldColumns"></td>
                                    <td style="width:18%" class="BoldColumns"></td>
                                    <td style="width:25%" class="BoldColumns" colspan="2">Cash Total : </td>
                                    <td style="width:12%" class="BoldColumns">{{ $cashPaid }}</td>
                                </tr>
                            @endif
                            @if($cardPaid != 0)
                                <tr style="width:100%">
                                    <td style="width:11.5%" class="BoldColumns"></td>
                                    <td style="width:21%" class="BoldColumns"></td>
                                    <td style="width:20%" class="BoldColumns"></td>
                                    <td style="width:18%" class="BoldColumns"></td>
                                    <td style="width:25%" class="BoldColumns" colspan="2">Card Total : </td>
                                    <td style="width:12%" class="BoldColumns">{{ $cardPaid }}</td>
                                </tr>
                            @endif
                            @if($chequePaid != 0)
                                <tr style="width:100%">
                                    <td style="width:11.5%" class="BoldColumns"></td>
                                    <td style="width:21%" class="BoldColumns"></td>
                                    <td style="width:20%" class="BoldColumns"></td>
                                    <td style="width:18%" class="BoldColumns"></td>
                                    <td style="width:25%" class="BoldColumns" colspan="2">Cheque Total : </td>
                                    <td style="width:12%" class="BoldColumns">{{ $chequePaid }}</td>
                                </tr>
                            @endif
                            @if($paytmPaid != 0)
                                <tr style="width:100%">
                                    <td style="width:11.5%" class="BoldColumns"></td>
                                    <td style="width:21%" class="BoldColumns"></td>
                                    <td style="width:20%" class="BoldColumns"></td>
                                    <td style="width:18%" class="BoldColumns"></td>
                                    <td style="width:25%" class="BoldColumns" colspan="2">Paytm Total : </td>
                                    <td style="width:12%" class="BoldColumns">{{ $paytmPaid }}</td>
                                </tr>
                            @endif
                            @if($upiPaid != 0)
                                <tr style="width:100%">
                                    <td style="width:11.5%" class="BoldColumns"></td>
                                    <td style="width:21%" class="BoldColumns"></td>
                                    <td style="width:20%" class="BoldColumns"></td>
                                    <td style="width:18%" class="BoldColumns"></td>
                                    <td style="width:25%" class="BoldColumns" colspan="2">UPI Total : </td>
                                    <td style="width:12%" class="BoldColumns">{{ $upiPaid }}</td>
                                </tr>
                            @endif
                            <tr style="width:100%">
                                <td style="width:11.5%" class="BoldColumns"></td>
                                <td style="width:21%" class="BoldColumns"></td>
                                <td style="width:20%" class="BoldColumns"></td>
                                <td style="width:18%" class="BoldColumns"></td>
                                <td style="width:25%" class="BoldColumns" colspan="2">Total Return Amount : </td>
                                <td style="width:12%" class="BoldColumns">0</td>
                            </tr>
                            <tr style="width:100%">
                                <td style="width:11.5%" class="BoldColumns"></td>
                                <td style="width:21%" class="BoldColumns"></td>
                                <td style="width:20%" class="BoldColumns"></td>
                                <td style="width:18%" class="BoldColumns"></td>
                                <td style="width:25%" class="BoldColumns" colspan="2">Grand Total : </td>
                                <td style="width:12%" class="BoldColumns">{{ $finalAmount }}</td>
                            </tr>
                        </tbody>
                    </table>
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
