<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VoteMaster</title>
    <link rel="icon" type="image/png" href="{{ asset('images/votemaster.png') }}">


    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


    <!-- Custom CSS -->
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-dark">

    <div class="card shadow-lg rounded-4 border-0 p-4 position-relative w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary text-white">VoteMaster</h3>
            <p class=" text-white">Voting Management System</p>
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

            <!-- Pass token -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email field -->
            <div class="mb-3">
                <label class="form-label text-white">Email address</label>
                <div class="position-relative">
                    <input type="email" name="email" required value="{{ $email ?? old('email') }}"
                        class="form-control ps-5" placeholder="you@example.com" readonly tabindex="-1">
                    <i class="bi bi-envelope position-absolute input-leading-icon"></i>
                </div>
            </div>

            <!-- New password -->
            <div class="mb-3">
                <label class="form-label text-white">New password</label>
                <div class="position-relative">
                    <input id="resetPassword" type="password" name="password" required class="form-control ps-5 pe-5"
                        placeholder="Enter new password">
                    <i class="bi bi-lock position-absolute input-leading-icon"></i>
                    <i class="bi bi-eye position-absolute input-trailing-icon"
                        onclick="togglePassword('resetPassword', this)"></i>
                </div>
            </div>

            <!-- Confirm password -->
            <div class="mb-3">
                <label class="form-label text-white">Confirm password</label>
                <div class="position-relative">
                    <input id="resetPasswordConfirmation" type="password" name="password_confirmation" required
                        class="form-control ps-5 pe-5" placeholder="Re-enter password">
                    <i class="bi bi-lock position-absolute input-leading-icon"></i>
                    <i class="bi bi-eye position-absolute input-trailing-icon"
                        onclick="togglePassword('resetPasswordConfirmation', this)"></i>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('login') }}" class="text-sm text-info mr-auto">Back to login</a>
                <button type="submit" class="btn btn-primary">Reset password</button>
            </div>
        </form>

    </div>

    <script src="{{ asset('js/auth.js') }}"></script>
    <!-- Error + Success handling -->
    <script>
        window.errors = {!! $errors->any() ? json_encode($errors->all()) : '[]' !!};
        window.sessionError = "{{ session('error') ?? '' }}";
        window.sessionSuccess = "{{ session('success') ?? '' }}";

        if (window.errors.length > 0) {
            Swal.fire("Error", window.errors.join("<br>"), "error");
        }

        if (window.sessionError) {
            Swal.fire("Error", window.sessionError, "error");
        }

        if (window.sessionSuccess) {
            Swal.fire("Success", window.sessionSuccess, "success");
        }
    </script>

</body>

</html>
