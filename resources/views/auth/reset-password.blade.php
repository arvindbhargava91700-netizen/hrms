<x-layouts.auth title="Reset Password">
    <h5 class="fw-700 mb-1 text-center">Reset Password</h5>
    <p class="text-muted text-center mb-4" style="font-size:13px;">Enter your new password below</p>

    <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email"
                name="email"
                value="{{ $email }}"
                class="form-control"
                readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>

            <div class="position-relative">
                <input type="password"
                    name="password"
                    id="password"
                    class="form-control pe-5"
                    placeholder="New Password"
                    required>

                <i class="bi bi-eye-slash position-absolute"
                    id="togglePassword"
                    style="right:15px; top:50%; transform:translateY(-50%); cursor:pointer;">
                </i>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>

            <div class="position-relative">
                <input type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-control pe-5"
                    placeholder="Confirm Password"
                    required>

                <i class="bi bi-eye-slash position-absolute"
                    id="toggleConfirmPassword"
                    style="right:15px; top:50%; transform:translateY(-50%); cursor:pointer;">
                </i>
            </div>
        </div>

        <button type="submit"
            id="submitBtn"
            class="btn btn-success w-100">

            <span class="btn-text">
                Reset Password
            </span>

            <span class="btn-loader d-none">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Processing...
            </span>
        </button>
    </form>

    <div class="text-center mt-4" style="font-size:13px; color:#64748B;">
        Remember your password?
        <a href="{{ route('login') }}" class="text-primary fw-600">Sign In</a>
    </div>

    <script>
        // Toggle New Password
        document.getElementById('togglePassword').addEventListener('click', function() {
            let password = document.getElementById('password');

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

        // Toggle Confirm Password
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            let password = document.getElementById('password_confirmation');

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

        // Button Loader
        document.getElementById('resetPasswordForm').addEventListener('submit', function() {

            let btn = document.getElementById('submitBtn');

            btn.disabled = true;
            btn.querySelector('.btn-text').classList.add('d-none');
            btn.querySelector('.btn-loader').classList.remove('d-none');
        });
    </script>
</x-layouts.auth>