@extends('emails.layouts.master')

@section('content')
    <h2>Welcome to {{ config('app.name') }}, {{ $customer->name }}!</h2>
    <p>Your account has been successfully created.</p>
    <p>You can now browse and book listings like Gyms, PGs, and Hostels near you directly from the mobile app.</p>
    
    <p>We are excited to have you on board!</p>
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
