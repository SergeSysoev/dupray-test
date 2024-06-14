<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{  config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<header class="mb-4 bg-slate-300">
    <div class="container mx-auto py-4 px-4 md:px-0">
        <a href="/">
            <h1 class="text-3xl font-bold">NBA Games Calendar</h1>
        </a>
    </div>
</header>
<div class="container mx-auto mt-8 pb-8 px-4 md:px-0">
    <main>
        @yield('content')
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.4/axios.min.js"></script>
@yield('scripts')
</body>
</html>
