<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="container">
        <form id="loginCard" method="post" action="{{ route('loginUser') }}">
            @csrf
            <h1>Login</h1>
            <span>Hey, Enter your details to get sign in<br>to your account</span>
            <input type="hidden" id="latitude" name="latitude" value="">
            <input type="hidden" id="longitude" name="longitude" value="">
            <input type="email" name="email" id="emailTxt" placeholder="Enter email" value="{{ old('email') }}" required>
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
</body>
<script>
    navigator.geolocation.getCurrentPosition(function(position) {
        let latitude = position.coords.latitude;
        let longitude = position.coords.longitude;

        document.getElementById('latitude').value = latitude;
        document.getElementById('longitude').value = longitude;
    });
</script>
</html>
