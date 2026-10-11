{{--
    Settings sub-navigation, shared by the Business Profile and Account pages.
    $active : 'business' | 'account'
--}}
@php $active = $active ?? ''; @endphp

<div class="card" style="border-radius: var(--radius); border: none; box-shadow: var(--shadow-sm);">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <a href="{{ route('settings.business-profile.edit') }}"
               class="list-group-item list-group-item-action d-flex align-items-center {{ $active === 'business' ? 'active' : '' }}"
               style="border: none; border-radius: 0;"
               @if ($active === 'business') aria-current="page" @endif>
                <i class="bi bi-building me-2"></i>
                Business Profile
            </a>
            <a href="{{ route('account.edit') }}"
               class="list-group-item list-group-item-action d-flex align-items-center {{ $active === 'account' ? 'active' : '' }}"
               style="border: none; border-radius: 0;"
               @if ($active === 'account') aria-current="page" @endif>
                <i class="bi bi-person-gear me-2"></i>
                Account
            </a>
            <span class="list-group-item d-flex align-items-center ink-soft"
                  style="border: none; background: transparent; cursor: not-allowed; opacity: 0.55;">
                <i class="bi bi-receipt me-2"></i>
                Invoice defaults
                <span class="ms-auto badge bg-light ink-soft">Soon</span>
            </span>
        </div>
    </div>
</div>
