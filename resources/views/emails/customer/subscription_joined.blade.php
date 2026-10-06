@extends('emails.layouts.master')

@section('content')
    <h2>Subscription Confirmed!</h2>
    <p>Hi {{ $customer->name }},</p>
    <p>You have successfully joined the subscription for <strong>{{ $package->listing->title }}</strong>.</p>
    
    <div style="background-color: #f4f5f7; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p style="margin:0;"><strong>Package:</strong> {{ $package->name }}</p>
        <p style="margin:5px 0 0 0;"><strong>Starts At:</strong> {{ $subscription->starts_at->format('d M, Y') }}</p>
        <p style="margin:5px 0 0 0;"><strong>Expires At:</strong> {{ $subscription->expires_at->format('d M, Y') }}</p>
    </div>

    <p>Thank you for booking with us. You can view your active subscriptions in the app.</p>
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
