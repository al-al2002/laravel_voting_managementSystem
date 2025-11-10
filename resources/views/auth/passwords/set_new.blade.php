<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - VoteMaster</title>
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
            <p class=" text-white">Set a new password</p>
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

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <input type="email" name="email" value="{{ $email }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label text-white">New password</label>
                <input type="password" name="password" required class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Confirm new password</label>
                <input type="password" name="password_confirmation" required class="form-control">
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('login') }}" class="text-info mr-auto">Back to login</a>
                <button type="submit" class="btn btn-primary">Set new password</button>
            </div>
        </form>
    </div>
</body>

</html>
