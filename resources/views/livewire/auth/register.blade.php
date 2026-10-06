<div>
    @if($step == 1)
        <h5 class="fw-700 mb-1 text-center">Create Account</h5>
        <p class="text-muted text-center mb-4" style="font-size:13px;">Join as a partner or customer</p>

        <form wire:submit.prevent="submitDetails" id="registerForm">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" wire:model="name" class="form-control" placeholder="Enter your full name" required>
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="col-12 col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" wire:model="email" class="form-control" placeholder="Enter your email..." required>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" wire:model="mobile" class="form-control" placeholder="Enter your mobile number..." required>
                    @error('mobile') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Account Type</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" wire:model.live="role" id="partner" value="partner" required>
                            <label class="role-card w-100" for="partner">
                                <i class="bi bi-briefcase-fill"></i>
                                <span>Partner</span>
                                <small>Business</small>
                            </label>
                        </div>

                        <div class="col-6">
                            <input type="radio" class="btn-check" wire:model.live="role" id="customer" value="customer" required>
                            <label class="role-card w-100" for="customer">
                                <i class="bi bi-person-fill"></i>
                                <span>Customer</span>
                                <small>User</small>
                            </label>
                        </div>
                    </div>
                    @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                @if($role === 'partner')
                <div class="col-12" id="categorySelectionContainer">
                    <label class="form-label">Service Categories</label>
                    <div class="row g-2">
                        @foreach($categoryList as $category)
                        <div class="col-6 col-md-4">
                            <input type="checkbox" class="btn-check category-check" wire:model="categories" id="cat_{{ $category->id }}" value="{{ $category->id }}">
                            <label class="role-card w-100 py-2" for="cat_{{ $category->id }}" style="height: auto; min-height: 80px;">
                                @if($category->icon)
                                    <img src="{{ asset('storage/' . $category->icon) }}" style="width: 24px; height: 24px; object-fit: contain; margin-bottom: 8px;">
                                @else
                                    <i class="bi bi-tag-fill"></i>
                                @endif
                                <span style="font-size: 14px;">{{ $category->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('categories')
                        <div class="text-danger mt-1" style="font-size: 12px;">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <div class="col-12 col-md-6" x-data="{ show: false }">
                    <label class="form-label">Password</label>
                    <div class="position-relative">
                        <input x-bind:type="show ? 'text' : 'password'" wire:model="password" class="form-control pe-5" placeholder="Enter your password" required>
                        <i class="bi position-absolute" x-bind:class="show ? 'bi-eye' : 'bi-eye-slash'" @click="show = !show" style="right:15px;top:50%;transform:translateY(-50%);cursor:pointer;"></i>
                    </div>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 col-md-6" x-data="{ show: false }">
                    <label class="form-label">Confirm Password</label>
                    <div class="position-relative">
                        <input x-bind:type="show ? 'text' : 'password'" wire:model="password_confirmation" class="form-control pe-5" placeholder="Confirm your password" required>
                        <i class="bi position-absolute" x-bind:class="show ? 'bi-eye' : 'bi-eye-slash'" @click="show = !show" style="right:15px;top:50%;transform:translateY(-50%);cursor:pointer;"></i>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2 fw-600 mt-4" wire:loading.attr="disabled" wire:target="submitDetails">
                <span wire:loading.remove wire:target="submitDetails">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </span>
                <span wire:loading wire:target="submitDetails">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Processing...
                </span>
            </button>
        </form>

        <div class="text-center mt-3" style="font-size:13px; color:#64748B;">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary fw-600">Sign In</a>
        </div>

    @elseif($step == 2)
        <div class="text-center mb-4">
            <h5 class="fw-700 mb-1">Verify Your Account</h5>
            <p class="text-muted" style="font-size:13px;">We've sent OTPs to your email ({{ $email }}) and mobile ({{ $mobile }}).</p>
        </div>

        <form wire:submit.prevent="verifyOtps">
            @if(session()->has('error') || $errors->has('otp'))
                <div class="alert alert-danger p-2 text-center" style="font-size: 14px;">
                    {{ $errors->first('otp') ?? session('error') }}
                </div>
            @endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Email OTP</label>
                    <input type="text" wire:model="email_otp" class="form-control text-center fs-4 letter-spacing-2" placeholder="------" required maxlength="6">
                    @error('email_otp') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label">Mobile OTP (SMS/WhatsApp)</label>
                    <input type="text" wire:model="mobile_otp" class="form-control text-center fs-4 letter-spacing-2" placeholder="------" required maxlength="6">
                    @error('mobile_otp') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-600 mt-4" wire:loading.attr="disabled" wire:target="verifyOtps">
                <span wire:loading.remove wire:target="verifyOtps">
                    <i class="bi bi-check-circle me-2"></i>Verify & Register
                </span>
                <span wire:loading wire:target="verifyOtps">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Verifying...
                </span>
            </button>
        </form>

        <!-- Timer & Resend Section -->
        <div class="text-center mt-4" x-data="otpTimer()" x-init="startTimer()" @otp-sent.window="startTimer()" @otp-resent.window="startTimer()">
            <div x-show="timeLeft > 0" class="text-muted" style="font-size: 14px;">
                Resend OTP in <strong x-text="formatTime(timeLeft)" class="text-primary"></strong>
            </div>
            
            <div x-show="timeLeft <= 0" style="display: none;">
                <p class="text-muted mb-2" style="font-size: 13px;">Didn't receive the OTP?</p>
                <button type="button" wire:click="resendOtps" class="btn btn-outline-primary btn-sm px-4 rounded-pill" wire:loading.attr="disabled" wire:target="resendOtps">
                    <span wire:loading.remove wire:target="resendOtps">Resend OTPs</span>
                    <span wire:loading wire:target="resendOtps">Sending...</span>
                </button>
            </div>
        </div>

        <div class="text-center mt-3">
            <button type="button" wire:click="$set('step', 1)" class="btn btn-link text-muted" style="text-decoration: none; font-size: 13px;">
                <i class="bi bi-arrow-left me-1"></i> Back to registration
            </button>
        </div>
        
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('otpTimer', () => ({
                    timeLeft: 120, // 2 minutes in seconds
                    timer: null,
                    
                    startTimer() {
                        this.timeLeft = 120;
                        if(this.timer) clearInterval(this.timer);
                        
                        this.timer = setInterval(() => {
                            if (this.timeLeft > 0) {
                                this.timeLeft--;
                            } else {
                                clearInterval(this.timer);
                            }
                        }, 1000);
                    },
                    
                    formatTime(seconds) {
                        const m = Math.floor(seconds / 60);
                        const s = seconds % 60;
                        return `${m}:${s < 10 ? '0' : ''}${s}`;
                    }
                }));
            });
        </script>
    @endif

    <style>
        .letter-spacing-2 {
            letter-spacing: 0.5rem;
        }
        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all .3s ease;
            background: #fff;
            text-align: center;
        }

        .role-card i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #0d6efd;
        }

        .role-card span {
            font-weight: 600;
            color: #111827;
        }

        .role-card small {
            color: #6b7280;
        }

        .btn-check:checked+.role-card {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.08);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        .role-card:hover {
            border-color: #0d6efd;
        }
    </style>
</div>
