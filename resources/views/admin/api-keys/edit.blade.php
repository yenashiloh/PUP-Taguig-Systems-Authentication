@include('admin.partials.link')
<title>Edit API Key</title>

@include('admin.partials.side-bar')

<main class="main-wrapper">
    @include('admin.partials.header')

    <section class="section">
        <div class="container-fluid">
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Edit API Key</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.api-keys.index') }}">API
                                            Keys</a></li>
                                    <li class="breadcrumb-item active">Edit</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-style mb-30">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Edit API Key: {{ $apiKey->application_name }}</h4>
                </div>
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

                <form action="{{ route('admin.api-keys.update', $apiKey) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Application Name <span class="text-danger">*</span></label>
                                <input type="text" name="application_name"
                                    value="{{ old('application_name', $apiKey->application_name) }}"
                                    placeholder="e.g., Student Portal App" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Name <span class="text-danger">*</span></label>
                                <input type="text" name="developer_name"
                                    value="{{ old('developer_name', $apiKey->developer_name) }}"
                                    placeholder="e.g., John Doe" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Email <span class="text-danger">*</span></label>
                                <input type="email" name="developer_email"
                                    value="{{ old('developer_email', $apiKey->developer_email) }}"
                                    placeholder="developer@example.com" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Rate Limit (requests/minute) <span class="text-danger">*</span></label>
                                <input type="number" name="rate_limit"
                                    value="{{ old('rate_limit', $apiKey->request_limit_per_minute) }}" min="10"
                                    max="1000" required>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="input-style-1">
                                <label>Description</label>
                                <textarea name="description" rows="3" placeholder="Brief description of the application">{{ old('description', $apiKey->description) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Allowed Domains (comma-separated)</label>
                                <input type="text" name="allowed_domains"
                                    value="{{ old('allowed_domains', implode(', ', $apiKey->allowed_domains ?? [])) }}"
                                    placeholder="example.com, app.example.com">
                                <small class="text-muted">Leave empty to allow all domains</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Expires At (optional)</label>
                                <input type="date" name="expires_at"
                                    value="{{ old('expires_at', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : '') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Permissions <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select_all_permissions"
                                            onchange="toggleAllPermissions()">
                                        <label class="form-check-label fw-bold" for="select_all_permissions">
                                            Select All Permissions
                                        </label>
                                    </div>
                                    <hr style="margin: 10px 0;">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="add_user" id="perm_add_user"
                                            {{ in_array('add_user', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_add_user">
                                            Add User/Batch Upload
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="update_user" id="perm_update_user"
                                            {{ in_array('update_user', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_update_user">
                                            Update User Information
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="deactivate_user" id="perm_deactivate_user"
                                            {{ in_array('deactivate_user', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_deactivate_user">
                                            Deactivate User
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="login_user" id="perm_login_user"
                                            {{ in_array('login_user', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_login_user">
                                            Login User
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="logout_user" id="perm_logout_user"
                                            {{ in_array('logout_user', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_logout_user">
                                            Logout User
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.api-keys.show', $apiKey) }}"
                                    class="main-button light-btn btn-hover btn-sm">
                                    Cancel
                                </a>
                                <button type="submit" class="main-button primary-btn btn-hover btn-sm"> Update API Key
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

    // Update select all when individual checkboxes change and on page load
    document.addEventListener('DOMContentLoaded', function() {
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const selectAllCheckbox = document.getElementById('select_all_permissions');

        // Check initial state on page load
        function updateSelectAllState() {
            const checkedCount = document.querySelectorAll('.permission-checkbox:checked').length;
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
        }

        // Set initial state
        updateSelectAllState();

        // Add event listeners to individual checkboxes
        permissionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAllState);
        });
    });
</script>
</body>

</html>
