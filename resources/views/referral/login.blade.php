<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <form id="loginCard" method="post" action="{{ route('loginReferralUser') }}">
            @csrf
            <img src="{{ asset($settings->referral_panel_icon) }}" height="100px" class="mb-3">
            <span>Enter your details to get sign in<br>to your account</span>
            {{-- <input type="hidden" id="latitude" name="latitude" value="">
            <input type="hidden" id="longitude" name="longitude" value=""> --}}
            <input type="text" name="email" id="emailTxt" placeholder="Enter username" value="{{ old('email') }}" required>
            <input type="password" name="password" id="passwordTxt" placeholder="Enter password" value="{{ old('password') }}" required>
            <button type="submit" id="loginBtn">Sign in</button>
            @if ($errors->any())
                <error class="error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </error>
            @endif
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
</body>
<script>
    // navigator.geolocation.getCurrentPosition(function(position) {
    //     let latitude = position.coords.latitude;
    //     let longitude = position.coords.longitude;

    //     document.getElementById('latitude').value = latitude;
    //     document.getElementById('longitude').value = longitude;
    // });
</script>
</html>
