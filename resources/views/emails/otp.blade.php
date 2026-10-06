@extends('emails.layouts.master')

@section('content')
    <h2 style="text-align: center;">Verify Your Email</h2>
    <p>Hello,</p>
    <p>Please use the verification code below to complete your authentication. This code is valid for 10 minutes.</p>
    
    <div style="background-color: #f4f5f7; padding: 20px; text-align: center; border-radius: 6px; margin: 20px 0;">
        <h1 style="letter-spacing: 5px; color: #0044cc; margin: 0;">{{ $otp }}</h1>
    </div>

    <p>If you didn't request this code, you can safely ignore this email.</p>
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
