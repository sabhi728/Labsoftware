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
    <div class="d-flex flex-row align-items-center">
        <img class="rounded-2 bg-white" src="{{ asset($user->settings->referral_panel_icon) }}" alt="" height="60px">
        <name>{{ $user->name }}</name>
    </div>
    <div id="profileCard" class="profileCard">
        <vert>
            <a onclick="goToRoute('referralpanel/logout')">Logout</a>
        </vert>
    </div>
</header>
