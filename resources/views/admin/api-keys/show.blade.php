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
                                    <li class="breadcrumb-item active">Details</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>
                        @if(session('updated'))
                            API Key Updated!
                        @elseif(session('created'))
                            API Key Generated!
                        @else
                            Success!
                        @endif
                    </strong>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Show Raw Key (only once) -->
            @if(session('raw_key'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-key me-1"></i>
                        @if(session('updated'))
                            Your Updated API Key
                        @else
                            Your New API Key
                        @endif
                    </h6>
                    <p class="mb-2">
                        @if(session('updated'))
                            <strong>Important:</strong> This is your updated API key. The previous key is no longer valid.
                        @else
                            <strong>Important:</strong> This is your new API key. Save it now - you won't be able to see it again!
                        @endif
                    </p>
                    <div class="mt-2 p-3" style="background: #f8f9fa; border-radius: 4px; border-left: 4px solid #ffc107;">
                        <code id="apiKeyCode" style="font-size: 14px; word-break: break-all; user-select: all;">
                            {{ session('raw_key') }}
                        </code>
                        <button type="button" class="btn btn-sm btn-outline-dark ms-2" onclick="copyToClipboard()">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>
                    <small class="d-block mt-2 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Make sure to copy this key and store it securely. For security reasons, we cannot show it again.
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- API Key Information -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card-style mb-30">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">API Key Information</h4>
                            <div>
                                <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="main-button primary-btn btn-hover btn-sm me-2">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button type="button" class="main-button danger-btn btn-hover btn-sm" onclick="deleteApiKey({{ $apiKey->id }})">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>

                        <div class="table-wrapper">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" style="width: 200px;">Application Name:</td>
                                        <td>{{ $apiKey->application_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Developer Name:</td>
                                        <td>{{ $apiKey->developer_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Developer Email:</td>
                                        <td>
                                            <a href="mailto:{{ $apiKey->developer_email }}">{{ $apiKey->developer_email }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Description:</td>
                                        <td>{{ $apiKey->description ?: 'No description provided' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td>
                                            @if($apiKey->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Rate Limit:</td>
                                        <td>{{ $apiKey->request_limit_per_minute }} requests/minute</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Allowed Domains:</td>
                                        <td>
                                            @if($apiKey->allowed_domains && count($apiKey->allowed_domains) > 0)
                                                @foreach($apiKey->allowed_domains as $domain)
                                                    <span class="badge bg-secondary me-1">{{ $domain }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">All domains allowed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Permissions:</td>
                                        <td>
                                            @if($apiKey->permissions && count($apiKey->permissions) > 0)
                                                @foreach($apiKey->permissions as $permission)
                                                    <span class="badge bg-primary me-1">
                                                        {{ ucwords(str_replace('_', ' ', $permission)) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No permissions assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Expires At:</td>
                                        <td>
                                            @if($apiKey->expires_at)
                                                {{ $apiKey->expires_at->format('M d, Y') }}
                                                @if($apiKey->expires_at->isPast())
                                                    <span class="badge bg-danger ms-2">Expired</span>
                                                @elseif($apiKey->expires_at->diffInDays() <= 30)
                                                    <span class="badge bg-warning ms-2">Expires Soon</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Never expires</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created By:</td>
                                        <td>{{ $apiKey->createdBy->email ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created At:</td>
                                        <td>{{ $apiKey->created_at->format('M d, Y g:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Updated:</td>
                                        <td>{{ $apiKey->updated_at->format('M d, Y g:i A') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Usage Statistics -->
                    <div class="card-style mb-30">
                        <h5 class="mb-3">Usage Statistics</h5>
                        <div class="table-wrapper">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Total Requests:</td>
                                        <td>{{ number_format($apiKey->total_requests ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card-style mb-30">
                        <h5 class="mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleStatus({{ $apiKey->id }})">
                                @if($apiKey->is_active)
                                    <i class="fas fa-pause me-1"></i> Deactivate Key
                                @else
                                    <i class="fas fa-play me-1"></i> Activate Key
                                @endif
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="regenerateKey({{ $apiKey->id }})">
                                <i class="fas fa-sync me-1"></i> Regenerate Key
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="testKey({{ $apiKey->id }})">
                                <i class="fas fa-flask me-1"></i> Test Key
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="../../assets/admin/api/show.js"></script>
@include('admin.partials.footer')
</body>
</html>