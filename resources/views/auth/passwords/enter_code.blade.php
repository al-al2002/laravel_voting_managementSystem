<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        {{-- Debug code displayed in logfile mode only. --}}
        @if (session('debug_code') && session('show_debug_code'))
            <div class="bg-yellow-100 text-black px-3 py-2 rounded mb-3">
                <strong>Debug code (local only):</strong>
                <div class="mt-1 text-xl font-mono">{{ session('debug_code') }}</div>
                <div class="text-sm text-gray-600 mt-1">If you didn't receive an email, check
                    <code>storage/logs/laravel.log</code> or set <code>MAIL_MAILER=log</code> in your .env for local
                    testing.
                </div>
            </div>
        @endif

        <div id="resendStatus" class="bg-blue-600 text-white px-3 py-2 rounded mb-3 d-none"></div>
        <div id="resendDebugDynamic" class="bg-yellow-100 text-black px-3 py-2 rounded mb-3 d-none">
            <strong>Debug code (local only):</strong>
            <div class="mt-1 text-xl font-mono" id="resendDebugCode"></div>
            <div class="text-sm text-gray-600 mt-1">The code above was logged because mailing is not configured locally.
            </div>
        </div>

        <form method="POST" action="{{ route('password.check_code') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label text-white">Email</label>
                <div class="position-relative">
                    <input type="email" name="email" value="{{ old('email', $email) }}" required
                        class="form-control ps-5" placeholder="you@example.com" readonly tabindex="-1">
                    <i class="bi bi-envelope position-absolute input-leading-icon"></i>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Verification Code</label>
                <div class="position-relative">
                    <input type="text" name="code" required class="form-control ps-5" placeholder="123456">
                    <i class="bi bi-shield-lock position-absolute input-leading-icon"></i>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div class="d-flex flex-column">
                    <a href="{{ route('login') }}" class="text-info">Back to login</a>
                    <button type="button" id="resendCodeBtn" class="btn btn-link text-info p-0 mt-2 text-start">Resend
                        code</button>
                    <span id="resendCountdown" class="small text-white mt-1"></span>
                </div>
                <button type="submit" class="btn btn-primary">Verify code</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resendBtn = document.getElementById('resendCodeBtn');
            const countdownEl = document.getElementById('resendCountdown');
            const statusEl = document.getElementById('resendStatus');
            const debugContainer = document.getElementById('resendDebugDynamic');
            const debugCodeEl = document.getElementById('resendDebugCode');
            const emailInput = document.querySelector('input[name="email"]');
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            let countdownInterval;

            if (!resendBtn || !statusEl || !countdownEl || !csrfTokenMeta) {
                return;
            }

            const csrfToken = csrfTokenMeta.getAttribute('content');
            const resendRoute = '{{ route('password.resend') }}';

            function startCountdown() {
                clearInterval(countdownInterval);
                let remaining = 60;
                resendBtn.disabled = true;
                countdownEl.textContent = `Resend available in ${remaining}s`;

                countdownInterval = setInterval(() => {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                        countdownEl.textContent = '';
                        resendBtn.disabled = false;
                    } else {
                        countdownEl.textContent = `Resend available in ${remaining}s`;
                    }
                }, 1000);
            }

            function showStatus(message, variant = 'info') {
                statusEl.textContent = message;
                statusEl.className = 'px-3 py-2 rounded mb-3';
                statusEl.classList.remove('d-none');
                if (variant === 'success') {
                    statusEl.classList.add('bg-green-600', 'text-white');
                } else if (variant === 'danger') {
                    statusEl.classList.add('bg-red-600', 'text-white');
                } else {
                    statusEl.classList.add('bg-blue-600', 'text-white');
                }
            }

            async function requestResend(email) {
                const response = await fetch(resendRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    const errMessage = data.error || data.status || 'Failed to resend code.';
                    throw new Error(errMessage);
                }

                return data;
            }

            resendBtn.addEventListener('click', async function(event) {
                event.preventDefault();

                if (!emailInput || !emailInput.value.trim()) {
                    showStatus('Enter your email address first.', 'danger');
                    return;
                }

                if (debugContainer) {
                    debugContainer.classList.add('d-none');
                }

                startCountdown();
                try {
                    const data = await requestResend(emailInput.value.trim());
                    showStatus(data.status || 'Code resent. Please check your email.', 'success');

                    if (data.debug_code && data.show_debug_code && debugContainer && debugCodeEl) {
                        debugCodeEl.textContent = data.debug_code;
                        debugContainer.classList.remove('d-none');
                    }
                } catch (error) {
                    showStatus(error.message, 'danger');
                }
            });
        });
    </script>
</body>

</html>
