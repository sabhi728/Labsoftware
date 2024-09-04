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
    #ordersTable>tbody>tr:hover {
        background: var(--lightgray);
        cursor: pointer;
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
                <button type="button" class="btn btn-primary" onclick="goToRoute('lab_profile/add')"><i class='bx bx-plus-circle'></i> Add Profile</button>
                <button type="button" class="btn btn-primary" onclick="printPriceList()">Print Price List</button>
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Profile Name</th>
                        <th scope="col">Profile Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labProfiles as $profile)
                        <tr>
                            <td onclick="goToRoute('lab_profile/edit/{{ $profile->id }}')">{{ $profile->name }}</td>
                            <td>{{ $profile->amount }}</td>
                            <td style="text-transform: capitalize;">{{ $profile->status }}</td>
                            <td style="display: flex; flex-direction: row; align-items: center;">
                                <button type="button" class="btn btn-danger" onclick="goToRoute('lab_profile/profile_details/{{ $profile->id }}')">Details</button>
                                {{-- <i class='bx bx-trash' style="font-size: 30px;cursor: pointer;color: red;margin-left:15px;"
                                    onclick="if (confirm('Are you sure you want to delete this item?')) {
                                        goToRoute('lab_profile/delete/{{ $profile->id }}');
                                    }">
                                </i> --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="printPriceList" style="width:75%;margin:auto;display:none;">
                <h3>Profiles Price List for {{ $user->settings['lab_name'] }}</h3>
                    <div class="grid-mvc" data-lang="en" data-gridname="OrdersGrid" data-selectable="true" data-multiplefilters="false">
                    <div class="grid-wrap">
                        <table class="table table-striped grid-table">
                            <thead>
                                <tr>
                                    <th class="grid-header LabMF-OrderNotes"><div class="grid-header-title"><span>Profile Name</span></div></th>
                                    <th class="grid-header LabMF-OrderNotes RightAlign"><div class="grid-header-title"><span>Profile Amount</span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($labProfiles as $profile)
                                    <tr class="grid-row ">
                                        <td class="grid-cell LabMF-OrderNotes" data-name="ProfileName">{{ $profile->name }}</td>
                                        <td class="grid-cell LabMF-OrderNotes RightAlign" data-name="ProfileAmount">{{ $profile->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
    function printPriceList() {
        var divElements = document.getElementById("printPriceList").innerHTML;
        var oldPage = document.body.innerHTML;

        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          divElements + "</body>";
        window.print();

        document.body.innerHTML = oldPage;
    }
</script>
</html>
