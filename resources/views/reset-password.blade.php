<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">
    <style>
        .logo-form-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../assets/images/login.jpg');
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
            <img src="../assets/images/PUPLogo.png" alt="PUP Logo" class="logo">
        </div>

        <div class="login-form-container">
            <div class="login-form">
                <div class="form-title">
                    <h2>Reset Password</h2>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    @if (session('status'))
                        <div
                            style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 10px 15px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input id="password" type="password" name="password" required class="form-control"
                            value="{{ old('password') }}">
                        <small id="password-error" class="form-text text-danger" style="color: red;"></small>

                        @error('password')
                            <div class="alert alert-danger mt-2" style="color: red;">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="form-control">
                        <small id="password-confirmation-error" class="form-text text-danger"
                            style="color: red;"></small>

                        @error('password_confirmation')
                            <div class="alert alert-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="sign-in-btn" id="reset-password-btn">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/reset-password.js') }}"></script>

</body>

</html>
