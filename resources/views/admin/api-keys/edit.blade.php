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
                                    <li class="breadcrumb-item"><a href="{{ route('admin.api-keys.index') }}">API Keys</a></li>
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
                                <input type="text" name="application_name" value="{{ old('application_name', $apiKey->application_name) }}" 
                                       placeholder="e.g., Student Portal App" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Name <span class="text-danger">*</span></label>
                                <input type="text" name="developer_name" value="{{ old('developer_name', $apiKey->developer_name) }}" 
                                       placeholder="e.g., John Doe" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Developer Email <span class="text-danger">*</span></label>
                                <input type="email" name="developer_email" value="{{ old('developer_email', $apiKey->developer_email) }}" 
                                       placeholder="developer@example.com" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Rate Limit (requests/minute) <span class="text-danger">*</span></label>
                                <input type="number" name="rate_limit" value="{{ old('rate_limit', $apiKey->request_limit_per_minute) }}" 
                                       min="10" max="1000" required>
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
                                <input type="text" name="allowed_domains" value="{{ old('allowed_domains', implode(', ', $apiKey->allowed_domains ?? [])) }}" 
                                       placeholder="example.com, app.example.com">
                                <small class="text-muted">Leave empty to allow all domains</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-style-1">
                                <label>Expires At (optional)</label>
                                <input type="date" name="expires_at" value="{{ old('expires_at', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d') : '') }}" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Permissions <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                               value="basic_auth" id="perm_basic" 
                                               {{ in_array('basic_auth', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_basic">
                                            Basic Authentication
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                               value="user_profile" id="perm_profile"
                                               {{ in_array('user_profile', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_profile">
                                            User Profile Access
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                               value="student_data" id="perm_student"
                                               {{ in_array('student_data', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_student">
                                            Student Data Access
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                               value="faculty_data" id="perm_faculty"
                                               {{ in_array('faculty_data', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_faculty">
                                            Faculty Data Access
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Toggle -->
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                       value="1" {{ $apiKey->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    API Key Active
                                </label>
                                <small class="d-block text-muted">Unchecking this will immediately disable the API key</small>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="main-button light-btn btn-hover">
                                    Cancel
                                </a>
                                <button type="submit" class="main-button primary-btn btn-hover">
                                    <i class="fas fa-save me-1"></i> Update API Key
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
</body>
</html>