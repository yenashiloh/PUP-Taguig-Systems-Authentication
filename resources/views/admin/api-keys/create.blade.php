@include('admin.partials.link')
<title>Generate API Key</title>

@include('admin.partials.side-bar')

<main class="main-wrapper">
    @include('admin.partials.header')

    <section class="section">
        <div class="container-fluid">
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Generate New API Key</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.api-keys.index') }}">API
                                            Keys</a></li>
                                    <li class="breadcrumb-item active">Generate</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> If an API key already exists for this application or developer email, it will be
                updated with new settings and a new key will be generated.
            </div>

            <div class="card-style mb-30">
                <h4 class="mb-25 fw-bold">API Key Information</h4>
                <hr>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Please fix the
                            following errors:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.api-keys.store') }}" method="POST" id="apiKeyForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Application Name <span class="text-danger">*</span></label>
                                <input type="text" name="application_name" value="{{ old('application_name') }}"
                                    placeholder="e.g., Student Portal App" required
                                    class="form-control @error('application_name') is-invalid @enderror">
                                @error('application_name')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">3-255 characters, letters, numbers, spaces, hyphens,
                                    underscores, and periods only</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Name <span class="text-danger">*</span></label>
                                <input type="text" name="developer_name" value="{{ old('developer_name') }}"
                                    placeholder="e.g., John Doe" required
                                    class="form-control @error('developer_name') is-invalid @enderror">
                                @error('developer_name')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">2-255 characters, letters, spaces, hyphens, periods, and
                                    apostrophes only</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Email <span class="text-danger">*</span></label>
                                <input type="email" name="developer_email" value="{{ old('developer_email') }}"
                                    placeholder="developer@example.com" required
                                    class="form-control @error('developer_email') is-invalid @enderror">
                                @error('developer_email')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Must be a valid email address</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Rate Limit (requests/minute) <span class="text-danger">*</span></label>
                                <input type="number" name="rate_limit" value="{{ old('rate_limit', 100) }}"
                                    min="10" max="1000" required
                                    class="form-control @error('rate_limit') is-invalid @enderror">
                                @error('rate_limit')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Between 10 and 1000 requests per minute</small>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="input-style-1">
                                <label>Description</label>
                                <textarea name="description" rows="3"
                                    placeholder="Brief description of the application (minimum 10 characters if provided)"
                                    class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Optional. If provided, must be 10-1000 characters</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Allowed Domains (comma-separated)</label>
                                <input type="text" name="allowed_domains" value="{{ old('allowed_domains') }}"
                                    placeholder="example.com, app.example.com"
                                    class="form-control @error('allowed_domains') is-invalid @enderror">
                                @error('allowed_domains')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Leave empty to allow all domains. Format: domain1.com,
                                    domain2.com</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Expires At (optional)</label>
                                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    max="{{ date('Y-m-d', strtotime('+5 years')) }}"
                                    class="form-control @error('expires_at') is-invalid @enderror">
                                @error('expires_at')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Must be between tomorrow and 5 years from now</small>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Permissions <span class="text-danger">*</span></label>
                            @error('permissions')
                                <div class="alert alert-danger py-2">
                                    <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="add_user" id="perm_add_user"
                                            {{ in_array('add_user', old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_add_user">
                                            <strong>Add User</strong><br>
                                            <small class="text-muted">Create new users and batch upload
                                                functionality</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="update_user" id="perm_update_user"
                                            {{ in_array('update_user', old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_update_user">
                                            <strong>Update User</strong><br>
                                            <small class="text-muted">Update user information and details</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="deactivate_user" id="perm_deactivate_user"
                                            {{ in_array('deactivate_user', old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_deactivate_user">
                                            <strong>Deactivate User</strong><br>
                                            <small class="text-muted">Activate/deactivate user accounts</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="login_user" id="perm_login_user"
                                            {{ in_array('login_user', old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_login_user">
                                            <strong>Login User</strong><br>
                                            <small class="text-muted">Authenticate faculty/students via API</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="logout_user" id="perm_logout_user"
                                            {{ in_array('logout_user', old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_logout_user">
                                            <strong>Logout User</strong><br>
                                            <small class="text-muted">End user sessions via API</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Select at least one permission that this API key will have access
                                to</small>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('admin.api-keys.index') }}"
                                    class="main-button deactive-btn btn-hover ms-2">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="main-button primary-btn btn-hover" id="submitBtn">
                                    <i class="fas fa-key me-1"></i> Generate API Key
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('apiKeyForm');
        const submitBtn = document.getElementById('submitBtn');
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

        // Real-time permission validation
        function validatePermissions() {
            const checkedPermissions = document.querySelectorAll('.permission-checkbox:checked');
            if (checkedPermissions.length === 0) {
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.setCustomValidity('Please select at least one permission.');
                });
                return false;
            } else {
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.setCustomValidity('');
                });
                return true;
            }
        }

        // Add event listeners to permission checkboxes
        permissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', validatePermissions);
        });

        // Form submission handling
        form.addEventListener('submit', function(e) {
            if (!validatePermissions()) {
                e.preventDefault();
                alert('Please select at least one permission.');
                return false;
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';

            // Re-enable button after 10 seconds (in case of error)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-key me-1"></i> Generate API Key';
            }, 10000);
        });

        // Domain validation
        const domainsInput = document.querySelector('input[name="allowed_domains"]');
        if (domainsInput) {
            domainsInput.addEventListener('blur', function() {
                const domains = this.value.split(',').map(d => d.trim()).filter(d => d);
                const invalidDomains = domains.filter(domain => {
                    // Simple domain validation regex
                    const domainRegex =
                        /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.([a-zA-Z]{2,}|[a-zA-Z]{2,}\.[a-zA-Z]{2,})$/;
                    return !domainRegex.test(domain);
                });

                if (invalidDomains.length > 0) {
                    this.setCustomValidity(`Invalid domain(s): ${invalidDomains.join(', ')}`);
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }

        // Character count for description
        const descriptionTextarea = document.querySelector('textarea[name="description"]');
        if (descriptionTextarea) {
            const charCountDiv = document.createElement('div');
            charCountDiv.className = 'text-muted small mt-1';
            charCountDiv.innerHTML = '<span id="charCount">0</span>/1000 characters';
            descriptionTextarea.parentNode.appendChild(charCountDiv);

            const charCountSpan = document.getElementById('charCount');

            descriptionTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCountSpan.textContent = count;

                if (count > 1000) {
                    charCountDiv.className = 'text-danger small mt-1';
                } else if (count > 900) {
                    charCountDiv.className = 'text-warning small mt-1';
                } else {
                    charCountDiv.className = 'text-muted small mt-1';
                }
            });
        }

        // Check for existing API key when application name or email changes
        const appNameInput = document.querySelector('input[name="application_name"]');
        const emailInput = document.querySelector('input[name="developer_email"]');
        const existingKeyAlert = document.createElement('div');
        existingKeyAlert.className = 'alert alert-warning mt-2 d-none';
        existingKeyAlert.innerHTML =
            '<i class="fas fa-exclamation-triangle me-1"></i> <span id="alertText"></span>';

        if (appNameInput && emailInput) {
            appNameInput.parentNode.appendChild(existingKeyAlert);

            function checkExistingKey() {
                const appName = appNameInput.value.trim();
                const email = emailInput.value.trim();

                if (appName.length > 2 || email.includes('@')) {
                    // Show warning that it might update existing key
                    existingKeyAlert.classList.remove('d-none');
                    document.getElementById('alertText').textContent =
                        'If an API key already exists for this application or email, it will be updated with new settings.';
                } else {
                    existingKeyAlert.classList.add('d-none');
                }
            }

            appNameInput.addEventListener('blur', checkExistingKey);
            emailInput.addEventListener('blur', checkExistingKey);
        }
    });
</script>

@include('admin.partials.footer')
</body>

</html>
