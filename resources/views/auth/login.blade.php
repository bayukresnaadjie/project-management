<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login · ProjectHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-violet-950 to-gray-900 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-violet-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-white shadow-lg shadow-violet-500/30">P</div>
        <h1 class="font-bold text-white text-2xl">ProjectHub</h1>
        <p class="text-gray-400 text-sm mt-1">Kelola project tim Anda dengan mudah</p>
    </div>

    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-7">
        @if($errors->any())
        <div class="bg-red-900/50 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 placeholder-gray-500"/>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"/>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded text-violet-600"/>
                <label for="remember" class="text-sm text-gray-400 cursor-pointer">Ingat saya</label>
            </div>
            <button type="submit" class="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 rounded-xl transition shadow-lg shadow-violet-500/20">
                Masuk ke ProjectHub
            </button>
        </form>

        <div class="mt-5 p-3 bg-gray-700/50 rounded-xl text-xs text-gray-400 space-y-1.5">
            <div class="font-semibold text-gray-300 mb-2">Akun Demo:</div>
            <div>👑 admin@pm.test — Admin</div>
            <div>📋 rina@pm.test — Project Manager</div>
            <div>💻 dika@pm.test — Developer</div>
            <div>🎨 sari@pm.test — Designer</div>
            <div class="text-gray-500 mt-1">Password: password</div>
        </div>
    </div>
</div>
</body>
</html>
