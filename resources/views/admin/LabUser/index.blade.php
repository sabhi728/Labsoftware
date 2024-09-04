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
                <button type="button" class="btn btn-primary" onclick="goToRoute('labuser/add')"><i class='bx bx-plus-circle'></i> Add User</button>
                <button type="button" class="btn btn-primary" onclick="goToRoute('labuser/roles_index')">Manage Roles</button>
            </div>
            <div id="ordersTableBack">
                <table class="table" id="ordersTable">
                    <thead>
                        <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Location</th>
                        <th scope="col">Status</th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            @if($user->role != "Admin")
                                <td style="cursor: pointer;" onclick="goToRoute('labuser/edit/{{ $user->id }}')">{{ $user->email }}</td>
                            @else
                                <td>{{ $user->email }}</td>
                            @endif
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->location_name }}</td>
                            <td style="text-transform: capitalize;">{{ $user->status }}</td>
                            <td>
                                <button class="btn btn-success" onclick="changePasswordDialog('{{ $user->id }}')">Change Password</button>

                                @if($user->role != "Admin")
                                    <i class='bx bx-trash' style="font-size: 30px;cursor: pointer;color: red;"
                                        onclick="if (confirm('Are you sure you want to delete this item?')) {
                                            goToRoute('labuser/delete/{{ $user->id }}');
                                        }">
                                    </i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
<script>
    var csrfToken = "{{ csrf_token() }}";

    function changePasswordDialog(userId) {
        Swal.fire({
            title: "Enter new password",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Change",
            showLoaderOnConfirm: true,
            preConfirm: async (newPassword) => {
                try {
                    if (newPassword.trim() == "") {
                        return Swal.showValidationMessage(`Enter password`);
                    }

                    const requestUrl = `${webUrl}labuser/update_password/${userId}`;

                    const data = {
                        _token: csrfToken,
                        password: newPassword.trim()
                    };

                    const response = await fetch(requestUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        return Swal.showValidationMessage(`Error: ${response.status} ${await response.text()}`);
                    }

                    return response.json();
                } catch (error) {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: `${result.value.message}`,
                });
            }
        });
    }
</script>
</html>
