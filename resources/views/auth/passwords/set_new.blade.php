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

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        <form method="POST" action="{{ route('password.set_new.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <div class="position-relative">
                    <input type="email" name="email" value="{{ $email }}" required class="form-control ps-5"
                        placeholder="you@example.com" readonly tabindex="-1">
                    <i class="bi bi-envelope position-absolute input-leading-icon"></i>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">New password</label>
                <div class="position-relative">
                    <input id="setNewPassword" type="password" name="password" required class="form-control ps-5 pe-5"
                        placeholder="Enter new password">
                    <i class="bi bi-lock position-absolute input-leading-icon"></i>
                    <i class="bi bi-eye position-absolute input-trailing-icon"
                        onclick="togglePassword('setNewPassword', this)"></i>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Confirm new password</label>
                <div class="position-relative">
                    <input id="setNewPasswordConfirm" type="password" name="password_confirmation" required
                        class="form-control ps-5 pe-5" placeholder="Confirm password">
                    <i class="bi bi-lock position-absolute input-leading-icon"></i>
                    <i class="bi bi-eye position-absolute input-trailing-icon"
                        onclick="togglePassword('setNewPasswordConfirm', this)"></i>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('login') }}" class="text-info">Back to login</a>
                <button type="submit" class="btn btn-primary">Set new password</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/auth.js') }}"></script>
    <script>
        window.sessionSuccess = "{{ session('success') ?? '' }}";
        if (window.sessionSuccess) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: window.sessionSuccess,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        }
    </script>
</body>

</html>
