@extends('emails.layouts.master')

@section('content')
    <h2>KYC Submitted</h2>
    <p>Hi {{ $partner->name }},</p>
    <p>We have successfully received your KYC documents.</p>
    <p>Our team is currently reviewing your submission. This process usually takes 24-48 hours. We will notify you via email as soon as your account is approved.</p>
    
    <p>Thank you for your patience.</p>
    <p>Best regards,<br>{{ config('app.name') }} Compliance Team</p>
@endsection
