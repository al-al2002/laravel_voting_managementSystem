<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoteMaster</title>
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

        <!-- Nav Tabs -->
        <ul class="nav nav-pills nav-justified mb-3" id="authTabs">
            <li class="nav-item">
                <button class="nav-link active" id="login-tab" onclick="showForm('login')">Login</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="register-tab" onclick="showForm('register')">Register</button>
            </li>
        </ul>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3 position-relative">
                <input type="text" name="login" class="form-control ps-5" placeholder="Voter ID or Email" required>
                <i class="bi bi-card-text position-absolute"
                    style="top:50%; left:15px; transform:translateY(-50%);"></i>
            </div>

            <div class="mb-3 position-relative">
                <input id="loginPassword" type="password" name="password" class="form-control ps-5 pe-5"
                    placeholder="Enter your password" required>
                <i class="bi bi-lock position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
                <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;"
                    onclick="togglePassword('loginPassword', this)"></i>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label for="remember" class="form-check-label text-white">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="d-none">
            @csrf
            <div class="mb-3 position-relative">
                <input type="text" name="name" class="form-control ps-5" placeholder="Full Name" required>
                <i class="bi bi-person position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
            </div>

            <div class="mb-3 position-relative">
                <input type="email" name="email" class="form-control ps-5" placeholder="Email Address" required>
                <i class="bi bi-envelope position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
            </div>

            <div class="mb-3 position-relative">
                <input type="text" name="voter_id" class="form-control ps-5" placeholder="Voter ID Number" required>
                <i class="bi bi-card-text position-absolute"
                    style="top:50%; left:15px; transform:translateY(-50%);"></i>
            </div>

            <div class="mb-3 position-relative">
                <input id="registerPassword" type="password" name="password" class="form-control ps-5 pe-5"
                    placeholder="Create password" required>
                <i class="bi bi-lock position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
                <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;"
                    onclick="togglePassword('registerPassword', this)"></i>
            </div>

            <div class="mb-3 position-relative">
                <input id="registerConfirmPassword" type="password" name="password_confirmation"
                    class="form-control ps-5 pe-5" placeholder="Confirm password" required>
                <i class="bi bi-lock position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
                <i class="bi bi-eye position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;"
                    onclick="togglePassword('registerConfirmPassword', this)"></i>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                <label class="form-check-label" for="agree">
                    I agree to the <a href="#" class="text-info">terms and conditions</a>
                </label>
            </div>

            <button type="submit" class="btn btn-success w-100">Create Account</button>
        </form>
    </div>

    <!-- Error + Success handling -->
    <script>
        window.errors = {!! $errors->any() ? json_encode($errors->all()) : '[]' !!};
        window.sessionError = "{{ session('error') ?? '' }}";
        window.sessionSuccess = "{{ session('success') ?? '' }}";

        if (window.errors.length > 0) {
            Swal.fire("Error", window.errors.join("<br>"), "error");
            document.getElementById("register-tab").click(); // auto open register if validation fails
        }

        if (window.sessionError) {
            Swal.fire("Error", window.sessionError, "error");
        }

        if (window.sessionSuccess) {
            Swal.fire("Success", window.sessionSuccess, "success");
        }
    </script>

    <!-- Custom JS -->
    <script src="{{ asset('js/auth.js') }}"></script>
</body>

</html>
