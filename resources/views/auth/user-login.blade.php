<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appInfo['app_name'] }} - Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">
    <!-- Font Awesome for eye icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .logo-form-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('assets/images/login.jpg') }}');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            z-index: 2;
        }

        .app-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }

        .app-info h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .app-info p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .api-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(126, 14, 9, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }

        .powered-by {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #7e0e09;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Authenticating...</p>
        </div>
    </div>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay"></div>
        <div class="logo-form-bg"></div>

        <div class="logo-container">
            <img src="{{ asset('assets/images/PUPLogo.png') }}" alt="PUP Logo" class="logo">
        </div>

        <div class="login-form-container">
            <div class="login-form">

                <div class="form-title">
                    <h2>PUP-Taguig Systems</h2>
                    <h2>Authentication</h2>
                </div>

                <!-- Login Form -->
                <form id="loginForm" method="POST">
                    @csrf
                    <input type="hidden" name="api_key" value="{{ $apiKey }}">

                    <!-- Alert Container -->
                    <div id="alertContainer" style="display: none;"></div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="Enter your email address" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="Enter your password" required>
                            <span class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="sign-in-btn" id="loginBtn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Authenticating...
                        </span>
                    </button>

                    {{-- <div class="signup-text">
                        <a href="{{ route('forgot-password') }}" class="signup-link">Forgot Password?</a>
                    </div> --}}

                </form>

                <!-- Powered by PUP-Taguig -->
                <div class="powered-by">
                    <p>Powered by <strong>PUP-Taguig Systems Authentication</strong></p>
                    <p style="font-size: 11px; margin-top: 5px;">
                        Secure API-based authentication for external applications
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            const btnLoading = loginBtn.querySelector('.btn-loading');
            const alertContainer = document.getElementById('alertContainer');
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function(e) {
                    e.preventDefault();

                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }

            // Form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Show loading state
                showLoading();

                const formData = new FormData(loginForm);

                // Make API request to login endpoint
                fetch('{{ route('api.user.login') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'X-API-Key': '{{ $apiKey }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();

                        if (data.success) {
                            showAlert('Login successful! Redirecting to application...', 'success');

                            // Store session token securely (you might want to use httpOnly cookies in production)
                            if (data.data && data.data.session_token) {
                                localStorage.setItem('pup_session_token', data.data.session_token);
                                localStorage.setItem('pup_user_data', JSON.stringify(data.data.user));
                            }

                            // Redirect to the developer's domain
                            setTimeout(() => {
                                if (data.data.redirect_url) {
                                    // Redirect to developer's domain with session data
                                    const redirectUrl = new URL(data.data.redirect_url);
                                    redirectUrl.searchParams.set('session_token', data.data
                                        .session_token);
                                    redirectUrl.searchParams.set('user_id', data.data.user.id);
                                    redirectUrl.searchParams.set('user_role', data.data.user
                                        .role);
                                    redirectUrl.searchParams.set('app_name', data.data
                                        .application.name);

                                    window.location.href = redirectUrl.toString();
                                } else {
                                    // Fallback: communicate with parent window if in iframe
                                    if (window.parent !== window) {
                                        window.parent.postMessage({
                                            type: 'login_success',
                                            data: data.data
                                        }, '*');
                                    } else {
                                        // Show success message if no redirect URL
                                        showAlert(
                                            'Login successful! Please contact the application developer for next steps.',
                                            'success');
                                    }
                                }
                            }, 1500);
                        } else {
                            showAlert(data.message || 'Login failed. Please try again.', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Login error:', error);
                        showAlert('Network error. Please check your connection and try again.',
                        'error');
                    });
            });

            function showLoading() {
                loginBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                loadingOverlay.style.display = 'flex';
            }

            function hideLoading() {
                loginBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                loadingOverlay.style.display = 'none';
            }

            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

                alertContainer.innerHTML = `
                    <div class="alert ${alertClass}" style="margin-bottom: 15px; padding: 12px; border-radius: 5px; ${type === 'success' ? 'background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;' : 'background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;'}">
                        <i class="fas ${iconClass} me-2"></i>
                        ${message}
                    </div>
                `;
                alertContainer.style.display = 'block';

                // Auto hide success messages
                if (type === 'success') {
                    setTimeout(() => {
                        alertContainer.style.display = 'none';
                    }, 3000);
                }
            }

            function showUserInfo(userData) {
                // For demonstration purposes, show user info in console
                console.log('Login successful:', userData);

                // In a real implementation, you would use this data to:
                // 1. Store session token securely
                // 2. Update UI with user information
                // 3. Redirect to appropriate dashboard
                // 4. Enable features based on user role and permissions
            }
        });

        // Handle logout (if needed)
        function logout() {
            const sessionToken = localStorage.getItem('session_token'); // or however you store it

            fetch('{{ route('api.user.logout') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-API-Key': '{{ $apiKey }}',
                        'X-Session-Token': sessionToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.removeItem('session_token');
                        // Redirect to login or home page
                    }
                });
        }
    </script>
</body>

</html>
