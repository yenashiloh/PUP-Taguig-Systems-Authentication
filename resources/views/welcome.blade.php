<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig Systems Authentication</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f2f2f2;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            height: 600px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .main-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/api/placeholder/1200/600');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
        }

        .diagonal-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #8B0000 0%, #8B0000 30%, transparent 30%, transparent 100%);
            border-radius: 20px;
        }

        .logo-container {
            position: absolute;
            left: 15%;
            top: 50%;
            transform: translateY(-50%);
            width: 250px;
            height: 250px;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .logo-container:hover {
            transform: translateY(-50%) scale(1.05);
        }

        .logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-form-container {
            position: absolute;
            right: 10%;
            top: 50%;
            transform: translateY(-50%);
            width: 430px;
            z-index: 3;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-form-container:hover {
            transform: translateY(-50%) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .login-top-bar {
            background-color: rgba(183, 107, 107, 0.9);
            padding: 15px 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .sign-in-text {
            color: black;
            font-size: 18px;
            font-weight: 600;
            position: relative;
            display: inline-block;
            padding-bottom: 5px;
        }

        .sign-in-text::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: black;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .sign-in-text:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .login-form {
            background-color: white;
            padding: 25px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-title h2 {
            color: #8B0000;
            font-size: 26px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .form-title h3 {
            color: #8B0000;
            font-size: 20px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #333;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 2px;
            font-size: 16px;
            border: none;
            border-bottom: 1px solid #333;
            background-color: transparent;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-bottom-color: #8B0000;
        }

        .sign-in-btn {
            width: 100%;
            padding: 12px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .sign-in-btn:hover {
            background-color: #6B0000;
            transform: translateY(-2px);
        }

        .sign-in-btn:active {
            transform: translateY(0);
        }

        /* New CSS for the background image behind the logo and form */
        .logo-form-bg {
            position: absolute;
            left: 10%;
            top: 50%;
            transform: translateY(-50%);
            width: 80%;
            height: 400px;
            background-image: url('assets/images/background.jpeg'); /* Replace with your image path */
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            opacity: 0.3;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay"></div>
        <div class="logo-form-bg"></div> <!-- New background image div -->
        
        <div class="logo-container">
            <img src="assets/images/PUPLogo.png" alt="PUP Logo" class="logo">
        </div>
        
        <div class="login-form-container">
            <div class="login-top-bar">
                <span class="sign-in-text">Sign in</span>
            </div>
            
            <div class="login-form">
                <div class="form-title">
                    <h2>PUP-Taguig Systems</h2>
                    <h2>Authentication</h2>
                </div>
                
                <form>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="hello@reallygreatsite.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control" placeholder="********">
                    </div>
                    
                    <button type="submit" class="sign-in-btn">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>