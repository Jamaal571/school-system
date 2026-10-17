<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $role }} dashboard · Northwood Academy</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas font-sans text-ink antialiased">
    <main class="mx-4 w-full max-w-md rounded-2xl border border-line bg-white p-8 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-navy text-white">
            <x-app-icon name="academic-cap" class="h-6 w-6" />
        </div>
        <h1 class="mt-5 text-2xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-ink-soft">Your {{ strtolower($role) }} dashboard is being built. You are signed in to the correct area.</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button class="rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-primary-hover">Sign out</button>
        </form>
    </main>
</body>
</html>
