<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<style>
    .headerDialogInputBack {
        border-radius: 5px;
        padding: 10px 15px;
        font-size: 15px;
        border: 1px solid var(--lightdarkgray);
        width: 100%;
    }
</style>

<header>
    <name>LAB MANAGEMENT</name>
    <div id="profileCard" class="profileCard">
        <img src="{{ $user->profile_pic }}">
        <vert>
            <span>{{ $user->first_name }} {{ $user->last_name }}</span>
            <role>{{ $user->role }}</role>
        </vert>
    </div>
</header>

<div class="modal fade" id="profileDialog" tabindex="-1" aria-labelledby="profileDialogLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileDialogLabel">Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($user->role == "Admin")
                    <button class="btn btn-success" onclick="goToRoute('edit_profile')">Edit Profile</button>
                @endif
                <button class="btn btn-primary" id="changePasswordButton">Change Password</button>
                <button class="btn btn-danger" id="logoutButton">Logout</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changePasswordDialog" tabindex="-1" aria-labelledby="changePasswordDialogLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordDialogLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input class="headerDialogInputBack" id="oldPassword" type="password" placeholder="Old Password" required>
                <br><br>
                <input class="headerDialogInputBack" id="newPassword" type="password" placeholder="New Password" required>
                <br><br>
                <input class="headerDialogInputBack" id="confirmPassword" type="password" placeholder="Confirm Password" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="changePassword()">Save</button>
                <button class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function changePassword() {
        var oldPassword = document.getElementById('oldPassword').value;
        var newPassword = document.getElementById('newPassword').value;
        var confirmPassword = document.getElementById('confirmPassword').value;

        if (oldPassword == "") {
            alert('Enter old password.');
            return;
        }

        if (newPassword == "") {
            alert('Enter new password.');
            return;
        }

        if (newPassword == oldPassword) {
            alert('Old password and new password cannot be same.');
            return;
        }

        if (newPassword != confirmPassword) {
            alert('New Password and Confirm Password didn\'t match.');
            return;
        }

        var formData = new FormData();
        formData.append('oldPassword', oldPassword);
        formData.append('newPassword', newPassword);
        formData.append('confirmPassword', confirmPassword);

        var csrfToken = "{{ csrf_token() }}";
        var hiddenInput = createHiddenInput("_token", csrfToken);
        var tokenValue = hiddenInput.value;
        formData.append("_token", tokenValue);

        fetch(webUrl + 'change_password', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status == "failed") {
                alert(data.message);
            } else {
                alert(data.message);
                window.location.replace(webUrl + 'logout');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function createHiddenInput(name, value) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        return input;
    }
</script>
