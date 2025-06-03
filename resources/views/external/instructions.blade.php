<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>External Student Management - Setup Required</title>
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
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        .title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }
        .setup-steps {
            text-align: left;
            margin: 30px 0;
        }
        .setup-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        .step-number {
            background: var(--primary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .code-example {
            background: #f1f3f4;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 0;
            word-break: break-all;
        }
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background: #6b0c08;
            border-color: #6b0c08;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="setup-card">
            <img src="{{ asset('assets/images/PUPLogo.png') }}" alt="PUP Logo" class="logo">
            <h2 class="title">External Student Management</h2>
            <p class="text-muted">API key required to access the student management interface</p>
            
            <div class="setup-steps">
                <div class="setup-step">
                    <div class="step-number">1</div>
                    <div>
                        <h6><strong>Generate API Key</strong></h6>
                        <p class="mb-1">Go to Admin Panel → API Keys → Generate New API Key</p>
                        <p class="mb-0 text-muted">Make sure to select the required permissions for student management</p>
                    </div>
                </div>
                
                <div class="setup-step">
                    <div class="step-number">2</div>
                    <div>
                        <h6><strong>Access with API Key</strong></h6>
                        <p class="mb-1">Add your API key to the URL:</p>
                        <div class="code-example">{{ $example_url }}</div>
                    </div>
                </div>
                
                <div class="setup-step">
                    <div class="step-number">3</div>
                    <div>
                        <h6><strong>Optional Parameters</strong></h6>
                        <p class="mb-1">You can also add:</p>
                        <div class="code-example">?api_key=YOUR_KEY&base_url=http://127.0.0.1:8000&app_name=My App</div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="{{ $docs_url }}" class="btn btn-primary">
                    <i class="fas fa-book me-2"></i>View API Documentation
                </a>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-key me-2"></i>Manage API Keys
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    For testing on localhost, use: <code>http://127.0.0.1:8000</code> as base URL
                </small>
            </div>
        </div>
    </div>
</body>
</html>