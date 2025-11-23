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
            <p class="text-white">Voting Management System</p>
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
                <input type="text" name="login" class="form-control ps-5 @error('login') is-invalid @enderror"
                    placeholder="Voter ID or Email" value="{{ old('login') }}" required>
                <i class="bi bi-card-text position-absolute input-leading-icon"></i>
                @error('login')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative">
                <input id="loginPassword" type="password" name="password"
                    class="form-control ps-5 pe-5 @error('password') is-invalid @enderror"
                    placeholder="Enter your password" required>
                <i class="bi bi-lock position-absolute input-leading-icon"></i>
                <i class="bi bi-eye position-absolute input-trailing-icon"
                    onclick="togglePassword('loginPassword', this)"></i>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label for="remember" class="form-check-label text-white">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                <span id="loginBtnText">Sign In</span>
                <span id="loginBtnSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Signing in...
                </span>
            </button>
        </form>

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="d-none">
            @csrf
            <div class="mb-3 position-relative">
                <input type="text" name="name" class="form-control ps-5 @error('name') is-invalid @enderror"
                    placeholder="Full Name" value="{{ old('name') }}" required>
                <i class="bi bi-person position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative">
                <input type="email" name="email" class="form-control ps-5 @error('email') is-invalid @enderror"
                    placeholder="Email Address" value="{{ old('email') }}" required>
                <i class="bi bi-envelope position-absolute" style="top:50%; left:15px; transform:translateY(-50%);"></i>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative">
                <input type="text" name="voter_id" class="form-control ps-5 @error('voter_id') is-invalid @enderror"
                    placeholder="Voter ID (6 digits)" value="{{ old('voter_id') }}" maxlength="6" required>
                <i class="bi bi-card-text position-absolute"
                    style="top:50%; left:15px; transform:translateY(-50%);"></i>
                @error('voter_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative">
                <input id="registerPassword" type="password" name="password"
                    class="form-control ps-5 pe-5 @error('password') is-invalid @enderror"
                    placeholder="Create password" required>
                <i class="bi bi-lock position-absolute input-leading-icon"></i>
                <i class="bi bi-eye position-absolute input-trailing-icon"
                    onclick="togglePassword('registerPassword', this)"></i>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative">
                <input id="registerConfirmPassword" type="password" name="password_confirmation"
                    class="form-control ps-5 pe-5" placeholder="Confirm password" required>
                <i class="bi bi-lock position-absolute input-leading-icon"></i>
                <i class="bi bi-eye position-absolute input-trailing-icon"
                    onclick="togglePassword('registerConfirmPassword', this)"></i>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input @error('agree') is-invalid @enderror" id="agree"
                    name="agree" {{ old('agree') ? 'checked' : '' }} required>
                <label class="form-check-label text-white" for="agree">
                    I agree to the <a href="#" class="text-info" data-bs-toggle="modal"
                        data-bs-target="#termsModal">VoteMaster Voting Management System Terms &amp; Conditions</a>
                </label>
                @error('agree')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success w-100" id="registerBtn">
                <span id="registerBtnText">Create Account</span>
                <span id="registerBtnSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Creating account...
                </span>
            </button>
        </form>
    </div>

    <!-- Terms modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-info">
                <div class="modal-header border-bottom border-info">
                    <h5 class="modal-title" id="termsModalLabel">VoteMaster Voting Management System Terms</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>By using VoteMaster, you agree to follow the election integrity, accuracy, and privacy standards
                        we maintain throughout the voting lifecycle.</p>
                    <ul>
                        <li>Only registered voters may create an account and cast ballots.</li>
                        <li>Voting data is secured and treated in accordance with our privacy policies.</li>
                        <li>Abusive behavior or manipulation of votes will result in account suspension.</li>
                    </ul>
                    <p>Please reach out to the voting administration team if you need any clarifications.</p>
                </div>
                <div class="modal-footer border-top border-info">
                    <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error + Success handling -->
    <script>
        window.errors = {!! $errors->any() ? json_encode($errors->all()) : '[]' !!};
        window.sessionError = "{{ session('error') ?? '' }}";
        window.sessionSuccess = "{{ session('success') ?? '' }}";

        // Auto-switch to the correct tab based on which form had validation errors
        @if (
            $errors->has('name') ||
                $errors->has('email') ||
                $errors->has('voter_id') ||
                $errors->has('password') ||
                $errors->has('agree'))
            // Register form has errors - switch to register tab
            document.getElementById("register-tab").click();
        @elseif ($errors->has('login'))
            // Login form has errors - keep on login tab (default)
            document.getElementById("login-tab").click();
        @endif

        // Only show SweetAlert for session messages, not validation errors
        if (window.sessionError) {
            Swal.fire("Error", window.sessionError, "error");
        }

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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script>
        // Add loading state to login form
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            const btnSpinner = document.getElementById('loginBtnSpinner');
            btn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        });

        // Add loading state to register form
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('registerBtn');
            const btnText = document.getElementById('registerBtnText');
            const btnSpinner = document.getElementById('registerBtnSpinner');
            btn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        });
    </script>
</body>

</html>
