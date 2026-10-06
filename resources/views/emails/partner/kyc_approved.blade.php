@extends('emails.layouts.master')

@section('content')
    <h2 style="color: #28a745;">KYC Approved!</h2>
    <p>Congratulations {{ $partner->name }},</p>
    <p>Your KYC documents have been successfully verified and approved.</p>
    <p>You now have full access to your Partner Dashboard. You can start creating your property or gym listings right away.</p>
    
    <center>
        <a href="{{ config('app.url') }}/partner/dashboard" class="btn">Go to Dashboard</a>
    </center>
    
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
