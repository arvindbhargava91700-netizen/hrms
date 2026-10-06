@extends('emails.layouts.master')

@section('content')
    <h2>Upcoming Autopay Reminder</h2>
    <p>Hi {{ $customer->name }},</p>
    <p>This is a reminder that your next automated payment for <strong>{{ $package->listing->title }}</strong> is coming up soon.</p>
    
    <div style="background-color: #f4f5f7; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p style="margin:0;"><strong>Amount to be charged:</strong> ₹{{ number_format($invoice->total, 2) }}</p>
        <p style="margin:5px 0 0 0;"><strong>Charge Date:</strong> {{ $invoice->due_date->format('d M, Y') }}</p>
    </div>

    <p>Since you are enrolled in Autopay, no action is required on your end. We will automatically deduct the amount on the charge date.</p>

    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
