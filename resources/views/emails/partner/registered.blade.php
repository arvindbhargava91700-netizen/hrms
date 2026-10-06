@extends('emails.layouts.master')

@section('content')
    <h2>Welcome to {{ config('app.name') }}, {{ $partner->name }}!</h2>
    <p>Thank you for registering as a partner on our platform.</p>
    <p>Your account has been successfully created. The next step is to submit your KYC documents so you can start adding your listings.</p>
    
    <center>
        <a href="{{ route('partner.kyc') }}" class="btn">Complete KYC Now</a>
    </center>
    
    <p>If you need any help, please contact our partner support team.</p>
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
