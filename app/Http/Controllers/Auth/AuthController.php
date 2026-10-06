<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PartnerRegisteredMail;
use App\Mail\CustomerRegisteredMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function testMail()
    {
        Mail::raw('Laravel Mail Test', function ($message) {
            $message->to('lifeinfosandeep01@gmail.com')
                    ->subject('Test Email');
        });

        return 'Mail Sent';
    }

    // ── Show login ────────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::validate($credentials)) {
            $user = Auth::getProvider()->retrieveByCredentials($credentials);

            if ($user->role === 'customer') {
                return back()->withErrors(['email' => 'Customers must use the Mobile App to login.'])->onlyInput('email');
            }

            if ($user->isAdmin()) {
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended($this->dashboardRoute());
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended($this->dashboardRoute());
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showOtpForm(Request $request)
    {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('login');
        }
        
        $userId = session('otp_user_id');
        $user = User::find($userId);
        
        $generatedAt = session('otp_generated_at', now()->timestamp);
        $timePassed = now()->timestamp - $generatedAt;
        $remainingTime = max(0, 120 - $timePassed);
        
        return view('auth.otp-verify', compact('user', 'remainingTime'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);

        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('login_otp_' . $userId);

        if (!$cachedOtp) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if ($cachedOtp !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        $user = User::find($userId);
        Auth::login($user, session('otp_remember', false));
        
        \Illuminate\Support\Facades\Cache::forget('login_otp_' . $userId);
        session()->forget(['otp_user_id', 'otp_remember']);
        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRoute());
    }

    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        // $otp = (string) rand(100000, 999999);
        $otp = (string) 123456;
        \Illuminate\Support\Facades\Cache::put('login_otp_' . $user->id, $otp, now()->addMinutes(5));
        
        session(['otp_generated_at' => now()->timestamp]);
        
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function dashboardRoute(): string
    {
        return Auth::user()->dashboard_route;
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

    // ── Show Forgot Password Form ─────────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Handle Forgot Password Request ─────────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // ── Show Reset Password Form ──────────────────────
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    // ── Handle Reset Password Request ─────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'     => 'required',
            'email'     => 'required|email',
            'password'  => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')
                ->with('success', 'Password reset successfully.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
