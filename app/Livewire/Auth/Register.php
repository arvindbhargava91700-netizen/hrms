<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Category;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use App\Mail\PartnerRegisteredMail;
use App\Mail\CustomerRegisteredMail;
use Illuminate\Support\Facades\Cache;

class Register extends Component
{
    public $step = 1;

    // Step 1 Properties
    public $name = '';
    public $email = '';
    public $mobile = '';
    public $role = 'customer';
    public $password = '';
    public $password_confirmation = '';
    public $categories = [];
    
    // Step 2 Properties
    public $email_otp = '';
    public $mobile_otp = '';
    public $canResend = false;

    public function mount()
    {
        $this->role = request()->query('role', 'customer');
    }

    public function updatedRole()
    {
        if ($this->role !== 'partner') {
            $this->categories = [];
        }
    }

    public function submitDetails()
    {
        $this->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|unique:users',
            'mobile'          => 'required|string|max:20|unique:users',
            'role'            => 'required|in:partner,customer',
            'password'        => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'categories'      => $this->role === 'partner' ? 'required|array' : 'nullable|array',
            'categories.*'    => 'exists:categories,id',
        ], [
            'categories.required' => 'Please select at least one service category.'
        ]);

        $this->sendOtps();
        
        $this->step = 2;
        // The frontend will start the 2-minute timer when step == 2
        $this->dispatch('otp-sent');
    }

    public function sendOtps()
    {
        $otpService = app(OtpService::class);
        
        $emailOtp = $otpService->generateOtp('email_' . $this->email);
        $mobileOtp = $otpService->generateOtp('mobile_' . $this->mobile);

        $otpService->sendEmailOtp($this->email, $emailOtp);
        $otpService->sendSmsOtp($this->mobile, $mobileOtp, $this->name);
        $otpService->sendWhatsappOtp($this->mobile, $mobileOtp, $this->name);
    }

    public function resendOtps()
    {
        // Add a simple throttle or just resend
        $this->sendOtps();
        $this->canResend = false;
        $this->dispatch('otp-resent');
    }

    public function verifyOtps()
    {
        $this->validate([
            'email_otp'  => 'required|string',
            'mobile_otp' => 'required|string',
        ]);

        $otpService = app(OtpService::class);

        $isEmailValid = $otpService->verifyOtp('email_' . $this->email, $this->email_otp);
        $isMobileValid = $otpService->verifyOtp('mobile_' . $this->mobile, $this->mobile_otp);

        if (!$isEmailValid || !$isMobileValid) {
            $this->addError('otp', 'Invalid or expired OTP(s). Please try again.');
            return;
        }

        $this->registerUser();
    }

    private function registerUser()
    {
        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'mobile'   => $this->mobile,
            'role'     => $this->role,
            'status'   => 'active',
            'password' => Hash::make($this->password),
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $this->role, 'guard_name' => 'web']);
        $user->assignRole($this->role);

        if ($this->role === 'partner' && !empty($this->categories)) {
            $user->categories()->sync($this->categories);
        }

        try {
            if ($this->role === 'partner') {
                $this->assignFreePackage($user);
                Mail::to($user->email)->queue(new PartnerRegisteredMail($user));

                // Notify Super Admin
                $admins = User::role('super_admin')->get();
                foreach ($admins as $admin) {
                    \App\Models\AppNotification::create([
                        'user_id' => $admin->id,
                        'title' => 'New Partner Registered',
                        'message' => "Partner {$user->name} has just registered."
                    ]);
                }
                
                // Notify Partner
                \App\Models\AppNotification::create([
                    'user_id' => $user->id,
                    'title' => 'Welcome to Feetrack!',
                    'message' => 'Thank you for registering. Please complete your KYC to get started.'
                ]);
            } else {
                Mail::to($user->email)->queue(new CustomerRegisteredMail($user));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send registration welcome email: ' . $e->getMessage());
        }

        if ($this->role === 'customer') {
            session()->flash('success', 'Account created successfully! Please download our Mobile App to login.');
            return redirect()->route('login');
        }

        Auth::login($user);

        return redirect()->to($user->dashboard_route)
            ->with('success', 'Account created successfully! Next steps: Please complete your KYC and wait for admin approval before you can start listing your services.')
            ->with('info', 'Your account is currently pending approval. You will receive an email once it has been reviewed by our team.');
    }

    private function assignFreePackage(User $partner)
    {
        $freePackage = \App\Models\PartnerPackage::where('is_free_trial', true)->where('is_active', true)->latest()->first();
        
        if ($freePackage) {
            \App\Models\PartnerSubscription::create([
                'partner_id' => $partner->id,
                'partner_package_id' => $freePackage->id,
                'starts_at' => now(),
                'expires_at' => $freePackage->duration_days > 0 ? now()->addDays($freePackage->duration_days) : null,
                'status' => 'active',
            ]);
        }
    }

    public function render()
    {
        $categoryList = Category::where('is_active', true)->get();
        return view('livewire.auth.register', [
            'categoryList' => $categoryList
        ])->layout('components.layouts.auth', ['maxWidth' => '800px']);
    }
}
