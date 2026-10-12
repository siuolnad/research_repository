<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Research Repository')</title>
<script>
(function () {
    var saved = localStorage.getItem('rr_theme');
    var theme = saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-rail', localStorage.getItem('rr_side') === 'collapsed' ? '1' : '0');
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,600;0,700;1,400&family=Public+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/repository.css') }}?v={{ filemtime(public_path('css/repository.css')) }}">
</head>
<body>
<script>
if (document.documentElement.getAttribute('data-rail') === '1') {
    document.body.classList.add('side-collapsed', 'no-anim');
}
</script>

@php($activeStatus = request('status', ''))

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-text">
            <div class="wordmark">Research Repository</div>
            <div class="sub">Research proposals</div>
        </div>
        <button class="collapse-btn" id="collapse-btn" aria-expanded="true" title="Collapse sidebar" aria-label="Collapse sidebar">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M10 3 5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    <nav class="nav" aria-label="Repository views">
        <div class="nav-label">Repository views</div>

        <a class="nav-item {{ $activeStatus === '' && request()->routeIs('proposals.index') ? 'active' : '' }}" href="{{ route('proposals.index') }}" title="Show all proposals">
            <svg class="nav-ico all" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M8 2 14 5 8 8 2 5 8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                <path d="M2 8.2 8 11.2 14 8.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 11.2 8 14.2 14 11.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg><span class="label">All proposals</span>
        </a>

        @foreach (\App\Enums\ProposalStatus::cases() as $status)
            <a class="nav-item {{ $activeStatus === $status->value ? 'active' : '' }}" href="{{ route('proposals.index', ['status' => $status->value]) }}" title="Show {{ strtolower($status->label()) }} proposals">
                <svg class="nav-ico {{ $status->value }}" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    @switch($status)
                        @case(\App\Enums\ProposalStatus::New)
                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 5.2v5.6M5.2 8h5.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            @break
                        @case(\App\Enums\ProposalStatus::Approved)
                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="m5.4 8.2 1.9 1.9 3.4-3.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            @break
                        @case(\App\Enums\ProposalStatus::Disapproved)
                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="m5.8 5.8 4.4 4.4M10.2 5.8l-4.4 4.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            @break
                    @endswitch
                </svg><span class="label">{{ $status->label() }}</span>
            </a>
        @endforeach
    </nav>

    <div class="side-foot">
        <form class="role-switch" method="POST" action="{{ route('role.update') }}" aria-label="Switch role">
            @csrf
            <span class="role-label">Viewing as</span>
            <div class="role-options">
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <button type="submit" name="role" value="{{ $role->value }}" class="{{ \App\Enums\UserRole::current() === $role ? 'active' : '' }}" aria-pressed="{{ \App\Enums\UserRole::current() === $role ? 'true' : 'false' }}">{{ $role->label() }}</button>
                @endforeach
            </div>
        </form>
        <button class="theme-toggle" id="theme-toggle" title="Switch to dark mode" aria-label="Switch to dark mode">
            <svg class="icon-moon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M13.5 9.3A5.6 5.6 0 0 1 6.7 2.5a5.6 5.6 0 1 0 6.8 6.8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
            <svg class="icon-sun" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <circle cx="8" cy="8" r="3.2" stroke="currentColor" stroke-width="1.5"/>
                <path d="M8 1.3v1.6M8 13.1v1.6M2.8 8H1.3M14.7 8h-1.5M3.8 3.8l1.1 1.1M11.1 11.1l1.1 1.1M12.2 3.8l-1.1 1.1M4.9 11.1l-1.1 1.1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
</aside>

<button class="scrim" id="scrim" aria-label="Close menu" tabindex="-1"></button>

<main class="content">
    <div class="content-inner">
        @yield('content')
    </div>
</main>

<dialog class="dialog" id="disapprove-dialog" aria-labelledby="disapprove-title">
    <form method="POST" id="disapprove-form">
        @csrf
        <h2 id="disapprove-title">Disapprove proposal</h2>
        <p class="dialog-note">This decision is final. Your remarks are saved with the proposal.</p>
        <div class="field">
            <label for="in-remarks">Remarks</label>
            <textarea id="in-remarks" name="remarks" required maxlength="2000" placeholder="Explain why this proposal is not approved"></textarea>
        </div>
        <div class="form-foot">
            <button type="button" class="btn-ghost" id="disapprove-cancel">Cancel</button>
            <button type="submit" class="btn-danger">Disapprove</button>
        </div>
    </form>
</dialog>

@if ($errors->has('remarks'))
    <div class="toast" role="alert">{{ $errors->first('remarks') }}</div>
@elseif (session('status'))
    <div class="toast" role="status">{{ session('status') }}</div>
@endif

<script>
(function () {
    var root = document.documentElement;
    var toggle = document.getElementById('theme-toggle');

    function syncLabel() {
        var label = root.getAttribute('data-theme') === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        toggle.title = label;
        toggle.setAttribute('aria-label', label);
    }
    syncLabel();
    toggle.addEventListener('click', function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        localStorage.setItem('rr_theme', next);
        syncLabel();
    });

    var collapse = document.getElementById('collapse-btn');
    collapse.addEventListener('click', function () {
        var collapsed = document.body.classList.toggle('side-collapsed');
        document.body.classList.remove('no-anim');
        collapse.setAttribute('aria-expanded', String(!collapsed));
        localStorage.setItem('rr_side', collapsed ? 'collapsed' : 'open');
    });

    var menu = document.getElementById('menu-btn');
    function setNav(open) {
        document.body.classList.toggle('nav-open', open);
        if (menu) menu.setAttribute('aria-expanded', String(open));
    }
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.menu[open]').forEach(function (d) {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('details.menu[open]').forEach(function (d) {
            d.removeAttribute('open');
            d.querySelector('summary').focus();
        });
    });
    var dialog = document.getElementById('disapprove-dialog');
    var dialogForm = document.getElementById('disapprove-form');
    document.querySelectorAll('[data-disapprove-url]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            dialogForm.action = btn.dataset.disapproveUrl;
            dialogForm.reset();
            var d = btn.closest('details.menu');
            if (d) d.removeAttribute('open');
            dialog.showModal();
            document.getElementById('in-remarks').focus();
        });
    });
    document.getElementById('disapprove-cancel').addEventListener('click', function () { dialog.close(); });
    dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close(); });

    document.querySelectorAll('details.menu').forEach(function (d) {
        d.addEventListener('toggle', function () {
            if (!d.open) return;
            d.classList.remove('flip');
            var list = d.querySelector('.menu-list').getBoundingClientRect();
            if (list.bottom > window.innerHeight && d.getBoundingClientRect().top > list.height) {
                d.classList.add('flip');
            }
        });
    });
    if (menu) menu.addEventListener('click', function () { setNav(true); });
    document.getElementById('scrim').addEventListener('click', function () { setNav(false); });
})();
</script>
</body>
</html>
