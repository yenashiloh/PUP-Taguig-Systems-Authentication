<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Required - PUP-Taguig Systems</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #7e0e09;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .error-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 15px;
        }
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background: #6b0c08;
            border-color: #6b0c08;
        }
        .solution-steps {
            text-align: left;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .url-example {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-key"></i>
                <i class="fas fa-question-circle position-absolute" style="margin-left: -1rem; margin-top: 1rem; font-size: 2rem; color: #ffc107;"></i>
            </div>
            
            <h2 class="error-title">API Key Required</h2>
            <p class="text-muted mb-4">You need to provide a valid API key to access the Student Management system.</p>
            
            <div class="solution-steps">
                <h5><i class="fas fa-list-ol me-2"></i>How to Get Started:</h5>
                <ol class="mb-3">
                    <li><strong>Generate an API Key:</strong>
                        <ul>
                            <li>Go to Admin Panel → API Keys</li>
                            <li>Click "Generate New API Key"</li>
                            <li>Fill in the required information</li>
                            <li>Select appropriate permissions</li>
                            <li>Copy the generated API key</li>
                        </ul>
                    </li>
                    <li><strong>Add API Key to URL:</strong>
                        <p class="mb-2">Add <code>?api_key=YOUR_API_KEY</code> to the URL:</p>
                        
                        <div class="url-example">
                            <strong>Development:</strong><br>
                            http://127.0.0.1:8000/external/student-management?api_key=YOUR_API_KEY
                        </div>
                        
                        <div class="url-example">
                            <strong>Production:</strong><br>
                            https://pupt-registration.site/external/student-management?api_key=YOUR_API_KEY
                        </div>
                    </li>
                </ol>
                
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Keep your API key secure and do not share it publicly.
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="{{ route('admin.api-keys.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Generate New API Key
                </a>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-key me-2"></i>Manage Existing API Keys
                </a>
                <button class="btn btn-outline-info" onclick="showApiKeyInput()">
                    <i class="fas fa-edit me-2"></i>I Have an API Key
                </button>
            </div>
            
            <!-- API Key Input Section (Hidden by default) -->
            <div id="apiKeyInputSection" class="mt-4" style="display: none;">
                <div class="solution-steps">
                    <h6><i class="fas fa-key me-2"></i>Enter Your API Key:</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="apiKeyInput" placeholder="Enter your API key here">
                        <button class="btn btn-success" onclick="redirectWithApiKey()">
                            <i class="fas fa-arrow-right me-1"></i>Go
                        </button>
                    </div>
                    <small class="text-muted">Your API key will be added to the URL automatically.</small>
                </div>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    API keys help secure access to the Student Management system and track usage.
                </small>
            </div>
        </div>
    </div>

    <script>
        function showApiKeyInput() {
            const section = document.getElementById('apiKeyInputSection');
            const input = document.getElementById('apiKeyInput');
            
            if (section.style.display === 'none') {
                section.style.display = 'block';
                input.focus();
            } else {
                section.style.display = 'none';
            }
        }
        
        function redirectWithApiKey() {
            const apiKey = document.getElementById('apiKeyInput').value.trim();
            
            if (!apiKey) {
                alert('Please enter your API key.');
                return;
            }
            
            // Get current domain and construct URL
            const currentUrl = new URL(window.location);
            const baseUrl = `${currentUrl.protocol}//${currentUrl.host}`;
            const newUrl = `${baseUrl}/external/student-management?api_key=${encodeURIComponent(apiKey)}`;
            
            window.location.href = newUrl;
        }
        
        // Allow Enter key to submit API key
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('apiKeyInput');
            if (input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        redirectWithApiKey();
                    }
                });
            }
        });
    </script>
</body>
</html>