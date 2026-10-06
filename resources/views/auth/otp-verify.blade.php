<x-layouts.auth title="OTP Verification">
    <h5 class="fw-700 mb-1 text-center">Verify OTP</h5>
    <p class="text-muted text-center mb-4" style="font-size:13px;">Please enter the 6-digit OTP sent to {{ $user->email }}</p>

    <form method="POST" action="{{ route('login.otp.verify') }}" id="verifyForm">
        @csrf
        <div class="mb-3">
            <label class="form-label">OTP</label>
            <div class="search-box">
                <i class="bi bi-shield-lock search-icon"></i>
                <input type="text" name="otp" class="form-control ps-5" placeholder="Enter 6-digit OTP"
                    required autofocus autocomplete="off" maxlength="6" pattern="\d{6}">
            </div>
        </div>
        
        <button type="submit" id="verifyBtn" class="btn btn-primary w-100 py-2 fw-600">
            <span class="btn-text"><i class="bi bi-check-circle me-2"></i>Verify & Login</span>
            <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...</span>
        </button>
    </form>

    <div class="text-center mt-4" style="font-size:13px; color:#64748B;">
        Didn't receive the OTP? 
        <span id="timerDisplay" class="fw-600 text-muted ms-1">02:00</span>
        <form method="POST" action="{{ route('login.otp.resend') }}" class="d-inline" id="resendForm">
            @csrf
            <button type="submit" id="resendBtn" class="btn btn-link p-0 m-0 align-baseline text-primary fw-600 d-none" style="font-size:13px; text-decoration: none;">
                <span class="btn-text">Resend OTP</span>
                <span class="btn-loader d-none"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 1rem; height: 1rem; margin-right: 0.2rem;"></span></span>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let timer = {{ $remainingTime }};
            const timerDisplay = document.getElementById('timerDisplay');
            const resendBtn = document.getElementById('resendBtn');

            const interval = setInterval(function() {
                let minutes = Math.floor(timer / 60);
                let seconds = timer % 60;

                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;

                timerDisplay.textContent = minutes + ':' + seconds;

                if (timer <= 0) {
                    clearInterval(interval);
                    timerDisplay.classList.add('d-none');
                    resendBtn.classList.remove('d-none');
                }

                timer--;
            }, 1000);

            document.getElementById('verifyForm').addEventListener('submit', function() {
                const btn = document.getElementById('verifyBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-loader').classList.remove('d-none');
            });

            document.getElementById('resendForm').addEventListener('submit', function() {
                const btn = document.getElementById('resendBtn');
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-loader').classList.remove('d-none');
            });
        });
    </script>
</x-layouts.auth>
