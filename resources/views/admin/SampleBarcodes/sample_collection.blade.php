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
    }

    #billNoInput {
        border-radius: 5px;
        /* border-bottom-right-radius: 0px;
        border-top-right-radius: 0px; */
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    #billNoSearchBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
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
        height: 150px;
        overflow: auto;
    }

    .searchItemsList .setBox {
        font-family: var(--font1);
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .searchItemsList .setBox:hover {
        background: var(--buttoncolor);
        color: white;
    }

    .search_selected {
        background: var(--buttoncolor);
        color: white;
    }

    .search-container {
        position: relative;
        display: inline-block;
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
                <div class="search-container">
                    <input id="billNoInput" class="inputField" placeholder="Search by (bill, umr, phone, name)">
                    <div id="billNoInputList" class="searchItemsList">
                        <span>Item 1</span>
                    </div>
                </div>
                {{-- <form id="headerHorizonatl" class="horizontal" action="{{ url('samplebarcodes/sample_collection_details') }}" method="get">
                    <input id="billNoInput" name="bill_no" placeholder="Search by (bill, umr, phone, name)" required>f
                    <button id="billNoSearchBtn" type="submit" class="btn btn-primary">Search</button>
                </form> --}}
                <br><br>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Bill Number</th>
                        <th scope="col">Bill Date</th>
                        <th scope="col">Patient Name</th>
                        <th scope="col">Orders</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderDetails as $order)
                        <tr>
                            <td style="color: {{ $order->color }};">{{ $order->bill_no }}</td>
                            <td style="color: {{ $order->color }};">{{ $order->order_date }}</td>
                            <td style="color: {{ $order->color }};">{{ $order->patient_title_name }} {{ $order->patient_name }}</td>
                            <td style="color: {{ $order->color }};">{{ $order->order_name_txt }}</td>
                            <td><button type="submit" class="btn btn-primary" onclick="goToRoute('samplebarcodes/sample_collection_details?bill_no={{ $order->bill_no }}')">Open</button></td>
                        </tr>
                        @endforeach

                        @if (count($orderDetails) == 0)
                            <tr>
                                <td colspan="5" align="center">No data found on this page</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $orderDetails->appends(request()->input())->links('pagination::bootstrap-5') }}
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
    var billNoInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('billNoInputList');

        selectedDoctorId = "";
        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `samplebarcodes/sample_collection_search/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';
                        responseJson.forEach(item => {
                            contentHTML += `
                                <div class= "setBox" onclick="selectBillNo('${item.bill_no}')">${item.bill_no} (${item.umr_number}) (${item.patients.patient_title_name} ${item.patients.patient_name}) (${item.patients.phone})</div>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('billNoInput').addEventListener('input', billNoInputHandler);

    function selectBillNo(billNo) {
        document.getElementById('billNoInput').removeEventListener('input', billNoInputHandler);
        document.getElementById('billNoInput').value = "";
        document.getElementById('billNoInput').addEventListener('input', billNoInputHandler);
        document.getElementById('billNoInputList').style.display = "none";

        goToRoute('samplebarcodes/sample_collection_details?bill_no=' + billNo);
    }

    searchResultArrowSelect('billNoInput', 'billNoInputList');
</script>
</html>
