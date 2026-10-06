<x-layouts.auth title="Forgot Password">
    <h5 class="fw-700 mb-1 text-center">Forgot Password</h5>
    <p class="text-muted text-center mb-4" style="font-size:13px;">Enter your email address and we'll send you a link to reset your password.</p>
    
    <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <div class="search-box">
                <i class="bi bi-envelope search-icon"></i>
                <input type="email"
                    name="email"
                    class="form-control ps-5"
                    placeholder="Enter your email..."
                    value="{{ old('email') }}"
                    required
                    autofocus>
            </div>
        </div>

        <button type="submit"
            id="submitBtn"
            class="btn btn-primary w-100 py-2 fw-600">

            <span class="btn-text">
                <i class="bi bi-envelope me-2"></i>
                Send Password Reset Link
            </span>

            <span class="btn-loader d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Sending...
            </span>
        </button>

    </form>

    <div class="text-center mt-4" style="font-size:13px; color:#64748B;">
        Remember your password?
        <a href="{{ route('login') }}" class="text-primary fw-600">Sign In</a>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function() {
            let btn = document.getElementById('submitBtn');

            btn.disabled = true;
            btn.querySelector('.btn-text').classList.add('d-none');
            btn.querySelector('.btn-loader').classList.remove('d-none');
        });
    </script>
</x-layouts.auth>