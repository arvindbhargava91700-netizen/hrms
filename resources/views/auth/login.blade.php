<x-layouts.auth title="Login">
    <h5 class="fw-700 mb-1 text-center">Sign In</h5>
    <p class="text-muted text-center mb-4" style="font-size:13px;">Welcome back! Please enter your details</p>

    <form method="POST" action="{{ route('login.post') }}" id="loginForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="search-box">
                <i class="bi bi-envelope search-icon"></i>
                <input type="email" name="email" class="form-control ps-5" placeholder="Enter your email..."
                    value="{{ old('email') }}" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label d-flex justify-content-between">
                Password
                <a href="{{ route('password.request') }}" class="text-primary" style="font-size:12px;">Forgot password?</a>
            </label>
            <div class="search-box">
                <i class="bi bi-lock search-icon"></i>
                <input type="password" name="password" id="password" class="form-control ps-5" placeholder="Enter your password..."
                    required>
                <i class="bi bi-eye-slash position-absolute" id="togglePassword"
                    style="right:15px; top:50%; transform:translateY(-50%); cursor:pointer;">
                </i>
            </div>
        </div>
        <div class="mb-4 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember" style="font-size:13px;">Remember me</label>
        </div>
        <button type="submit" id="loginBtn" class="btn btn-primary w-100 py-2 fw-600">
            <span class="btn-text"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</span>
            <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing In...</span>
        </button>
    </form>

    <div class="text-center mt-4" style="font-size:13px; color:#64748B;">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-primary fw-600">Create Account</a>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');

            if (password.type === 'password') {
                password.type = 'text';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            } else {
                password.type = 'password';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.querySelector('.btn-text').classList.add('d-none');
            btn.querySelector('.btn-loader').classList.remove('d-none');
        });
    </script>
</x-layouts.auth>
