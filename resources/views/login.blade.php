<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig Systems Authentication</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">
    <!-- Font Awesome for eye icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                    <h2>PUP-Taguig Systems</h2>
                    <h2>Authentication</h2>
                </div>

                <form method="POST" action="{{ route('loginPost') }}">
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
                            value="{{ old('email') }}" placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Enter your password">
                            <span class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="sign-in-btn">Sign in</button>
                    <div class="signup-text">
                        <a href="{{ route('forgot-password') }}" class="signup-link">Forgot Password?</a>
                    </div>
                    <div class="signup-text">
                        <a href="{{ route('home') }}" class="signup-link">Go Back</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Toggle the type attribute
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle the eye icon
                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
</body>

</html>
