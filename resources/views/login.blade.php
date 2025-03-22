<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig Systems Authentication</title>
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
                    <h2>PUP-Taguig Systems</h2>
                    <h2>Authentication</h2>
                </div>
                
                <form>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="Enter your email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control" placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="sign-in-btn">Sign in</button>
                    <div class="signup-text">
                        Don't have an account? <a href="{{{route ('sign-up')}}}" class="signup-link">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>