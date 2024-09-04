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
</style>
<body>
    <div class="main_container" style="text-align: center; font-family: 'Microsoft JhengHei', Arial;">
        <div class="main">
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Exit</button>
                <button type="button" class="btn btn-primary" onclick="printrprt()">Print</button>
            </div>
            <br>
            <div id="BillRpt" style="text-align:left;paddig-bottom:100px;width: 100%;">
                <h4 style="margin-top:30px;">
                    Bill Report Summary Between the dates <b>{{ $fromDate }}</b> and <b>{{ $toDate }}</b> Username: {{ $username }}
                </h4>
                <br>
                <table class="table" align="left" style="margin-top: 10px; text-align: left; max-width: 440px">
                    <tbody><tr class="Bolder">
                        <td>Total billed amount:</td>
                        <td class="AmountClmn">
                            {{ $totalAmount }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td>(-)Discount amount:</td>
                        <td class="AmountClmn">
                            {{ $dicountAmount }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td>Final amount:</td>
                        <td class="AmountClmn">
                            {{ $finalAmount }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td>(-)Return Amount:</td>
                        <td class="AmountClmn">
                            {{ $returnAmount }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td>Balance amount:</td>
                        <td class="AmountClmn">
                            {{ $balanceAmount }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#1</b> Cash Received:
                        </td><td class="AmountClmn">
                            {{ $cashPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#2</b> Card Received:
                        </td><td class="AmountClmn">
                            {{ $cardPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#3</b> Cheque Received:
                        </td><td class="AmountClmn">
                            {{ $chequePaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#4</b> Paytm Received:
                        </td><td class="AmountClmn">
                            {{ $paytmPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#5</b> UPI Received:
                        </td><td class="AmountClmn">
                            {{ $upiPaid }}
                        </td>
                    </tr>

                    <tr class="Bolder">
                        <td>Total Received [{#1} + {#2} + {#3} + {#4} + {#5}]:</td>
                        <td class="AmountClmn">
                            {{ $cashPaid + $cardPaid + $chequePaid + $paytmPaid + $upiPaid }}
                        </td>
                    </tr>

                    <tr class="GridColumn">
                        <td><b>#6</b> Previous dues Received(Cash):</td>
                        <td class="AmountClmn">
                            {{ $previousCashPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#7</b> Previous dues Received(Card):</td>
                        <td class="AmountClmn">
                            {{ $previousCardPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#8</b> Previous dues Received(Cheque):</td>
                        <td class="AmountClmn">
                            {{ $previousChequePaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#9</b> Previous dues Received(Paytm):</td>
                        <td class="AmountClmn">
                            {{ $previousPaytmPaid }}
                        </td>
                    </tr>
                    <tr class="GridColumn">
                        <td><b>#10</b> Previous dues Received(UPI):</td>
                        <td class="AmountClmn">
                            {{ $previousUpiPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total Previous dues Received [{#6} + {#7} + {#8} + {#9} + {#10}]:</td>
                        <td class="AmountClmn">
                            {{ $previousCashPaid + $previousCardPaid + $previousChequePaid + $previousPaytmPaid + $previousUpiPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total Cash Received [{#1} + {#6}]:
                        </td><td class="AmountClmn">
                            {{ $cashPaid + $previousCashPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total Card Received [{#2} + {#7}]:
                        </td><td class="AmountClmn">
                            {{ $cardPaid + $previousCardPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total Cheque Received [{#3} + {#8}]:
                        </td><td class="AmountClmn">
                            {{ $chequePaid + $previousChequePaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total Paytm Received [{#4} + {#9}]:
                        </td><td class="AmountClmn">
                            {{ $paytmPaid + $previousPaytmPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Total UPI Received [{#5} + {#10}]:
                        </td><td class="AmountClmn">
                            {{ $upiPaid + $previousUpiPaid }}
                        </td>
                    </tr>
                    <tr class="Bolder">
                        <td>Remaining Amount
                        </td><td class="AmountClmn">
                            {{ $cashPaid + $cardPaid + $chequePaid + $paytmPaid + $upiPaid + $previousCashPaid + $previousCardPaid + $previousChequePaid + $previousPaytmPaid + $previousUpiPaid }}
                        </td>
                    </tr>
                </tbody></table>
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
