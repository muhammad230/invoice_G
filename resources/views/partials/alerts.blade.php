@if (session('success'))
    <div class="alert alert-success d-flex align-items-start gap-2 mb-4" role="alert" style="border-radius: var(--radius);">
        <i class="bi bi-check-circle-fill mt-1"></i>
        <div>{!! session('success') !!}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert" style="border-radius: var(--radius);">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>{!! session('error') !!}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-4" role="alert" style="border-radius: var(--radius);">
        <div class="d-flex align-items-start gap-2 mb-2">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <strong>Please fix the following issues and try again:</strong>
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
