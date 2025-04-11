<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">
    <style>
        .logo-form-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/images/login.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            z-index: 2;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay"></div>
        <div class="logo-form-bg"></div>
        <div class="logo-container">
            <img src="assets/images/PUPLogo.png" alt="PUP Logo" class="logo">
        </div>

        <div class="login-form-container">
            <div class="login-form">
                <div class="form-title">
                    <h2>Forgot your Password</h2>
                    <h5 class="fw-light">Please enter your email. You will receive a link to create a new password via
                        email.</h5>
                </div>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    @if (session('status'))
                        <div
                            style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 10px 15px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 10px 15px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
                            <ul style="list-style-type: none; padding-left: 0; margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="Enter your email address">
                    </div>
                    <button type="submit" class="sign-in-btn">Forgot Password</button>

                    <div class="signup-text">
                        <a href="{{ route('login') }}" class="signup-link">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
