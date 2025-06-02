@include('admin.partials.link')
<title>API Key Details</title>

@include('admin.partials.side-bar')

<main class="main-wrapper">
    @include('admin.partials.header')

    <section class="section">
        <div class="container-fluid">
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>API Key Details</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.api-keys.index') }}">API Keys</a></li>
                                    <li class="breadcrumb-item active">{{ $apiKey->application_name }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('raw_key'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> This is the only time you'll see this API key. Please copy it now:
                    <div class="mt-2">
                        <code style="background: #f8f9fa; padding: 10px; border-radius: 4px; display: block; word-break: break-all;">
                            {{ session('raw_key') }}
                        </code>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- API Key Information -->
                <div class="col-lg-8">
                    <div class="card-style mb-30">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">{{ $apiKey->application_name }}</h4>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button class="btn btn-secondary btn-sm" onclick="toggleKeyStatus({{ $apiKey->id }})">
                                    <i class="fas fa-{{ $apiKey->is_active ? 'pause' : 'play' }} me-1"></i> 
                                    {{ $apiKey->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-info btn-sm" onclick="regenerateKey({{ $apiKey->id }})">
                                    <i class="fas fa-sync me-1"></i> Regenerate
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteKey({{ $apiKey->id }})">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Application Name</label>
                                <p class="mb-0">{{ $apiKey->application_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Key Name</label>
                                <p class="mb-0">{{ $apiKey->key_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Developer Name</label>
                                <p class="mb-0">{{ $apiKey->developer_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Developer Email</label>
                                <p class="mb-0">{{ $apiKey->developer_email }}</p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="mb-0">{{ $apiKey->description ?: 'No description provided' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Rate Limit</label>
                                <p class="mb-0">{{ number_format($apiKey->request_limit_per_minute) }} requests/minute</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Total Requests</label>
                                <p class="mb-0">{{ number_format($apiKey->total_requests) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created At</label>
                                <p class="mb-0">{{ $apiKey->created_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Last Used</label>
                                <p class="mb-0">
                                    {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('M d, Y \a\t h:i A') : 'Never used' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Expires At</label>
                                <p class="mb-0">
                                    {{ $apiKey->expires_at ? $apiKey->expires_at->format('M d, Y \a\t h:i A') : 'Never expires' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="mb-0">{{ $apiKey->createdBy->first_name ?? 'Unknown' }} {{ $apiKey->createdBy->last_name ?? '' }}</p>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Permissions</label>
                                <div class="mt-2">
                                    @foreach ($apiKey->formatted_permissions as $permission)
                                        <span class="badge bg-primary me-1 mb-1">{{ $permission }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Allowed Domains -->
                        @if (!empty($apiKey->allowed_domains))
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Allowed Domains</label>
                                    <div class="mt-2">
                                        @foreach ($apiKey->allowed_domains as $domain)
                                            <span class="badge bg-secondary me-1 mb-1">{{ $domain }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Status and Quick Actions -->
                <div class="col-lg-4">
                    <div class="card-style mb-30">
                        <h5 class="mb-3">Status & Actions</h5>
                        <hr>

                        <!-- Status Badge -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Status</label>
                            <div>
                                @if ($apiKey->is_active)
                                    @if ($apiKey->expires_at && $apiKey->expires_at->isPast())
                                        <span class="badge bg-warning fs-6">Expired</span>
                                    @else
                                        <span class="badge bg-success fs-6">Active</span>
                                    @endif
                                @else
                                    <span class="badge bg-danger fs-6">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Test -->
                        <div class="mb-3">
                            <button class="btn btn-outline-primary w-100" onclick="testApiKey({{ $apiKey->id }})">
                                <i class="fas fa-flask me-1"></i> Test API Key
                            </button>
                        </div>

                        <!-- Usage Stats -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Usage Statistics</label>
                            <div class="mt-2">
                                <div class="d-flex justify-content-between">
                                    <span>Total Requests:</span>
                                    <strong>{{ number_format($apiKey->total_requests) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Rate Limit:</span>
                                    <strong>{{ $apiKey->request_limit_per_minute }}/min</strong>
                                </div>
                                @if ($apiKey->last_used_at)
                                    <div class="d-flex justify-content-between">
                                        <span>Last Activity:</span>
                                        <strong>{{ $apiKey->last_used_at->diffForHumans() }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Expiry Warning -->
                        @if ($apiKey->expires_at)
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Expires:</strong> {{ $apiKey->expires_at->format('M d, Y') }}
                                <br>
                                <small>({{ $apiKey->expires_at->diffForHumans() }})</small>
                            </div>
                        @endif
                    </div>

                    <!-- API Documentation Card -->
                    <div class="card-style mb-30">
                        <h5 class="mb-3">API Documentation</h5>
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Base URL</label>
                            <code class="d-block p-2 bg-light rounded">{{ url('/api') }}</code>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Authentication Header</label>
                            <code class="d-block p-2 bg-light rounded">X-API-Key: YOUR_API_KEY</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Test Results Modal -->
<div class="modal fade" id="testResultsModal" tabindex="-1" aria-labelledby="testResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testResultsModalLabel">API Key Test Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="testResultsContent">
                    <!-- Test results will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/admin/api/show.js"></script>
@include('admin.partials.footer')
</body>
</html>