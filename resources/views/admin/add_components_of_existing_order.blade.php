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
    .search-container {
        position: relative;
        display: inline-block;
    }

    #searchOrderTxt {
        border-radius: 5px;
        padding: 7px 10px;
        font-size: 15px;
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
    }

    .searchItemsList span {
        font-family: var(--font1);
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
    }

    .searchItemsList span:hover {
        background: var(--buttoncolor);
        color: white;
    }

    #ordersTable {
        margin-top: 20px;
        display: none;
    }

    #saveComponentsBtn {
        display: none;
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
                <button type="button" class="btn btn-primary" onclick="goToRoute('order_details/{{ $orderDetails->report_id }}')"><i class='bx bx-left-arrow-circle'></i> Back</button>
            </div>
            <div id="ordersTableBack">
                <h1 style="padding-bottom: 20px;font-size: 20px;">Add templates from existing orders for <u><b>{{ $orderDetails->order_name }}</b></u></h1>
                <div class="search-container">
                    <input id="searchOrderTxt" type="text" placeholder="Search Order">
                    <div id="searchItemsList" class="searchItemsList">
                        <span>Item 1</span>
                        <span>Item 2</span>
                        <span>Item 10</span>
                        <span>Item 255</span>
                    </div>
                </div>
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Sub Heading</th>
                        <th scope="col">Component</th>
                        <th scope="col">Range</th>
                        <th scope="col">Units</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <button id="saveComponentsBtn" type="button" class="btn btn-primary">Submit</button>
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

    <script>
        const idsList = [];
        var saveComponentsBtn = document.getElementById('saveComponentsBtn');

        saveComponentsBtn.addEventListener('click', function() {
            window.location.href = webUrl + "save_components_of_existing_order/{{ $orderDetails->report_id }}/" + idsList;
        });

        document.getElementById('searchOrderTxt').addEventListener('input', function(event) {
            var inputValue = event.target.value;
            var resultLayout = document.getElementById('searchItemsList');

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
                                    <span onclick="showOrderComponentDetails('${item.report_id}', '${item.order_name}')">${item.order_name}</span>
                                `;
                            });

                            resultLayout.innerHTML = contentHTML;
                            resultLayout.style.display = "flex";
                        }
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }
        });

        function showOrderComponentDetails(id, order_name) {
            var resultLayout = document.getElementById('searchItemsList');
            var searchOrderTxt = document.getElementById('searchOrderTxt');
            var ordersTable = document.getElementById('ordersTable');

            searchOrderTxt.value = order_name;
            resultLayout.style.display = "none";
            
            fetch(webUrl + `get_order_details/${id}`)
                .then(response => response.json())
                .then(responseJson => {
                    var size = Object.keys(responseJson).length;
                    if (size == 0) {
                        ordersTable.style.display = "none";
                        saveComponentsBtn.style.display = "none";
                    } else {
                        var tableBody = ordersTable.querySelector('tbody');
                        tableBody.innerHTML = '';
                        idsList.length = 0;

                        responseJson.forEach(order => {
                            idsList.push(order.id);

                            var row = tableBody.insertRow();
                            var cell1 = row.insertCell(0);
                            var cell2 = row.insertCell(1);
                            var cell3 = row.insertCell(2);
                            var cell4 = row.insertCell(3);

                            cell1.textContent = order.sub_heading;
                            cell2.textContent = order.component_name;
                            cell3.innerHTML = `<span style="white-space: pre-line;">${order.order_details_range}</span>`;
                            cell4.textContent = order.units;
                        });

                        ordersTable.style.display = "table";
                        saveComponentsBtn.style.display = "block";
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    </script>
</body>
</html>