<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VoteMaster</title>
    <link rel="icon" type="image/png" href="{{ asset('images/votemaster.png') }}">


    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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

        @if (session('status'))
            <div class="bg-green-600 text-white px-3 py-2 rounded mb-3">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-red-600 text-white px-3 py-2 rounded mb-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
            @csrf
            <div class="mb-3">
                <label class="form-label text-white">Email address</label>
                <div class="position-relative">
                    <input type="email" name="email" required value="{{ old('email') }}" class="form-control ps-5"
                        placeholder="you@example.com" id="emailInput">
                    <i class="bi bi-envelope position-absolute input-leading-icon"></i>
                </div>
            </div>

            <div class="alert alert-info text-sm" role="alert">
                <i class="bi bi-info-circle me-1"></i>
                A verification code will be sent to your email. <strong>Check your spam folder</strong> if you don't see
                it in your inbox.
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('login') }}" class="text-sm text-info">Back to login</a>
                <button type="submit" class="btn btn-primary" id="sendCodeBtn">
                    <span class="btn-text">Send code</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Sending...
                    </span>
                </button>
            </div>
        </form>
    </div>

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

        // Loading state for send code button
        const form = document.getElementById('forgotPasswordForm');
        const sendBtn = document.getElementById('sendCodeBtn');
        const btnText = sendBtn.querySelector('.btn-text');
        const btnSpinner = sendBtn.querySelector('.btn-spinner');

        form.addEventListener('submit', function() {
            sendBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        });
    </script>

</body>

</html>
