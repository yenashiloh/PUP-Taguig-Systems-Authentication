{{-- resources/views/admin/api-keys/index.blade.php --}}
@include('admin.partials.link')
<title>API Key Management</title>

@include('admin.partials.side-bar')

<main class="main-wrapper">
    @include('admin.partials.header')

    <section class="section">
        <div class="container-fluid">
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>API Key Management</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item active">API Keys</li>
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

            <div class="card-style mb-30">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">API Keys</h4>
                    <a href="{{ route('admin.api-keys.create') }}" class="main-button primary-btn btn-hover btn-sm">
                        <i class="fas fa-plus me-1"></i> Generate New API Key
                    </a>
                </div>

                <div class="table-wrapper table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Application</th>
                                <th>Developer</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Last Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($apiKeys as $key)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $key->application_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($key->description, 50) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ $key->developer_name }}
                                            <br>
                                            <small class="text-muted">{{ $key->developer_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach ($key->formatted_permissions as $permission)
                                            <span class="badge bg-secondary me-1">{{ $permission }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ($key->is_active)
                                            @if ($key->expires_at && $key->expires_at->isPast())
                                                <span class="badge bg-warning">Expired</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($key->last_used_at)
                                            {{ $key->last_used_at->diffForHumans() }}
                                            <br>
                                            <small class="text-muted">{{ number_format($key->total_requests) }} requests</small>
                                        @else
                                            <span class="text-muted">Never used</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.api-keys.show', $key) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.api-keys.edit', $key) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-secondary btn-sm toggle-key" data-id="{{ $key->id }}">
                                                <i class="fas fa-{{ $key->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm delete-key" data-id="{{ $key->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-key fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">No API keys generated yet.</p>
                                        <a href="{{ route('admin.api-keys.create') }}" class="btn btn-primary">
                                            Generate Your First API Key
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($apiKeys->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $apiKeys->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</main>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle key status
    document.querySelectorAll('.toggle-key').forEach(button => {
        button.addEventListener('click', function() {
            const keyId = this.dataset.id;
            
            fetch(`/admin/api-keys/${keyId}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An error occurred.', 'error');
            });
        });
    });

    // Delete key
    document.querySelectorAll('.delete-key').forEach(button => {
        button.addEventListener('click', function() {
            const keyId = this.dataset.id;
            
            Swal.fire({
                title: 'Delete API Key?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/api-keys/${keyId}`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>

@include('admin.partials.footer')
</body>
</html>