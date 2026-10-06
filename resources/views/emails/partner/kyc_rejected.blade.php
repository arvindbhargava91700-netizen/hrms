@extends('emails.layouts.master')

@section('content')
    <h2 style="color: #dc3545;">KYC Update Required</h2>
    <p>Hi {{ $partner->name }},</p>
    <p>We have reviewed your KYC submission but unfortunately, it was rejected for the following reason:</p>
    
    <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-left: 4px solid #ffeeba; margin: 20px 0;">
        <strong>Reason:</strong> {{ $reason ?? 'Documents unclear or mismatched.' }}
    </div>

    <p>Please log in to your dashboard to re-upload the correct documents.</p>
    
    <center>
        <a href="{{ config('app.url') }}/partner/kyc" class="btn">Update KYC Documents</a>
    </center>
    
    <p>Best regards,<br>{{ config('app.name') }} Compliance Team</p>
@endsection
