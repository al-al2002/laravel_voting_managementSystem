<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Code - VoteMaster</title>
    <link rel="icon" type="image/png" href="{{ asset('images/votemaster.png') }}">


    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">

</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-dark">
    <div class="card shadow-lg rounded-4 border-0 p-4 position-relative w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary text-white">VoteMaster</h3>
            <p class=" text-white">Enter verification code</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-600 text-white px-3 py-2 rounded mb-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="bg-green-600 text-white px-3 py-2 rounded mb-3">{{ session('status') }}</div>
        @endif

        {{-- Debug code displayed for local/testing environments when mail sending fails or is logged --}}
        @if (session('debug_code') && app()->environment('local'))
            <div class="bg-yellow-100 text-black px-3 py-2 rounded mb-3">
                <strong>Debug code (local only):</strong>
                <div class="mt-1 text-xl font-mono">{{ session('debug_code') }}</div>
                <div class="text-sm text-gray-600 mt-1">If you didn't receive an email, check
                    <code>storage/logs/laravel.log</code> or set <code>MAIL_MAILER=log</code> in your .env for local
                    testing.
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.check_code') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Verification Code</label>
                <input type="text" name="code" required class="form-control" placeholder="123456">
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('login') }}" class="text-info me-3">Back to login</a>
                <a href="{{ route('password.request') }}" class="text-info me-auto">Resend code</a>
                <button type="submit" class="btn btn-primary">Verify code</button>
            </div>
        </form>
    </div>
</body>

</html>
