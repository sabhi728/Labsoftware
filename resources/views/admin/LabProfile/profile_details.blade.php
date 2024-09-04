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
    }

    .inputBack input {
        border-radius: 5px;
        padding: 7px 10px;
        font-size: 15px;
        border: 1px solid var(--lightdarkgray);
        margin: 0px 15px;
    }

    .valueItem {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .valueItem i {
        font-size: 22px;
        color: red;
    }

    #valueDeleteBtn {
        color: red;
        border: 0;
        background: none;
        font-size: 25px;
    }

    .horizontal {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .vertical {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .inputForm span {
        width: 120px;
    }

    #orderNameInput {
        border-radius: 5px;
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
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
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="goToRoute('lab_profile/index')"><i class='bx bx-left-arrow-circle'></i> Back</button>
            </div>
            <div id="ordersTableBack">
                <h1 style="padding-bottom: 20px;font-size: 20px;">List Of Orders In <u><b>{{ $labProfile->name }}</b></u></h1>
                <div class="horizontal inputForm">
                    <span>Order Name:</span>
                    <div class="search-container">
                        <input id="orderNameInput" class="inputField">
                        <div id="orderNameInputList" class="searchItemsList">
                            <span>Item 1</span>
                        </div>
                    </div>
                </div>
                <div class="valuesList">
                    @foreach($profileDetails as $profile)
                    <div class="valueItem">
                        <span>{{ $profile->order_name }}</span>
                        <form action="{{ url('lab_profile/delete_profile_detail', ['id' => $profile->id]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button id="valueDeleteBtn" class='bx bx-x'></button>
                        </form>
                    </div>
                    @endforeach
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
    var orderInputHandler = function(event) {
        var inputValue = event.target.value;
        var resultLayout = document.getElementById('orderNameInputList');

        if (inputValue == "") {
            resultLayout.style.display = "none";
        } else {
            fetch(webUrl + `search_orders/${inputValue}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        resultLayout.style.display = "none";
                    } else {
                        let contentHTML = '';

                        responseJson.forEach(item => {
                            contentHTML += `
                                <span onclick="selectOrder('${item.report_id}', '${item.order_name}', '${item.order_amount}')">${item.order_name}</span>
                            `;
                        });

                        resultLayout.innerHTML = contentHTML;
                        resultLayout.style.display = "flex";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    };
    document.getElementById('orderNameInput').addEventListener('input', orderInputHandler);
    searchResultArrowSelect('orderNameInput', 'orderNameInputList');

    function selectOrder(orderId, orderName, orderAmount) {
        var form = document.createElement("form");
        form.method = "POST";
        form.action = webUrl + "lab_profile/add_profile_details";

        var csrfToken = "{{ csrf_token() }}";
        form.appendChild(createHiddenInput("_token", csrfToken));

        form.appendChild(createHiddenInput("profileId", "{{ $labProfile->id }}"));
        form.appendChild(createHiddenInput("orderId", orderId));

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
