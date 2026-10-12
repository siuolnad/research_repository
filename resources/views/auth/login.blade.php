<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — Research Repository</title>
<script>
(function () {
    var saved = localStorage.getItem('rr_theme');
    var theme = saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', theme);
})();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,600;0,700;1,400&family=Public+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/repository.css') }}?v={{ filemtime(public_path('css/repository.css')) }}">
</head>
<body>
<main class="auth-page">
    <form class="auth-card" method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <h1>Research Repository</h1>
        <p class="auth-sub">Sign in to manage research proposals.</p>

        <div class="field {{ $errors->has('email') ? 'has-err' : '' }}">
            <label for="in-email">Email</label>
            <input id="in-email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="field {{ $errors->has('password') ? 'has-err' : '' }}">
            <label for="in-password">Password</label>
            <input id="in-password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<div class="err">{{ $message }}</div>@enderror
        </div>

        <label class="auth-remember">
            <input type="checkbox" name="remember" value="1"> Keep me signed in
        </label>

        <button type="submit" class="btn-primary">Sign in</button>
    </form>
</main>
</body>
</html>
