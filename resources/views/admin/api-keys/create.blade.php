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

            <div class="card-style mb-30">
                <h4 class="mb-25 fw-bold">API Key Information</h4>
                <hr>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.api-keys.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Application Name <span class="text-danger">*</span></label>
                                <input type="text" name="application_name" value="{{ old('application_name') }}"
                                    placeholder="e.g., Student Portal App" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Name <span class="text-danger">*</span></label>
                                <input type="text" name="developer_name" value="{{ old('developer_name') }}"
                                    placeholder="e.g., John Doe" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Email <span class="text-danger">*</span></label>
                                <input type="email" name="developer_email" value="{{ old('developer_email') }}"
                                    placeholder="developer@example.com" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Rate Limit (requests/minute) <span class="text-danger">*</span></label>
                                <input type="number" name="rate_limit" value="{{ old('rate_limit', 100) }}"
                                    min="10" max="1000" required>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="input-style-1">
                                <label>Description</label>
                                <textarea name="description" rows="3" placeholder="Brief description of the application">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Allowed Domains (comma-separated)</label>
                                <input type="text" name="allowed_domains" value="{{ old('allowed_domains') }}"
                                    placeholder="example.com, app.example.com">
                                <small class="text-muted">Leave empty to allow all domains</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Expires At (optional)</label>
                                <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>

                        <!-- Update your existing API Key create form (resources/views/admin/api-keys/create.blade.php) -->
                        <!-- Add this permission option to the permissions section: -->

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Permissions <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="add_user" id="perm_add_user">
                                        <label class="form-check-label" for="perm_add_user">
                                            Add User / Batch Upload
                                            <small class="d-block text-muted">Allows adding individual users or
                                                uploading in bulk</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="update_user" id="perm_update_user">
                                        <label class="form-check-label" for="perm_update_user">
                                            Update User Information
                                            <small class="d-block text-muted">Edit user details and profile
                                                information</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="deactivate_user" id="perm_deactivate_user">
                                        <label class="form-check-label" for="perm_deactivate_user">
                                            Deactivate User
                                            <small class="d-block text-muted">Temporarily disable user access</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="login_user" id="perm_login_user">
                                        <label class="form-check-label" for="perm_login_user">
                                            User Login Access
                                            <small class="d-block text-muted">Allow users to log in through this
                                                system</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="logout_user" id="perm_logout_user">
                                        <label class="form-check-label" for="perm_logout_user">
                                            User Logout Access
                                            <small class="d-block text-muted">Allows users to securely log out</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> Grant appropriate permissions based on the user's intended access
                                and functionality.
                            </small>

                            <div class="col-md-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.api-keys.index') }}"
                                        class="main-button light-btn btn-hover btn-sm">
                                        Cancel
                                    </a>
                                    <button type="submit" class="main-button primary-btn btn-hover btn-sm">
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
@include('admin.partials.footer')
<script>
    function toggleAllPermissions() {
        const selectAllCheckbox = document.getElementById('select_all_permissions');
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }

    // Update select all when individual checkboxes change
    document.addEventListener('DOMContentLoaded', function() {
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const selectAllCheckbox = document.getElementById('select_all_permissions');

        permissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.permission-checkbox:checked')
                    .length;
                const totalCount = permissionCheckboxes.length;

                if (checkedCount === totalCount) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (checkedCount === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            });
        });
    });
</script>
</body>

</html>
