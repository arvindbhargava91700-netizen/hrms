@extends('emails.layouts.master')

@section('content')
    <h2 style="color: #dc3545;">Payment Overdue</h2>
    <p>Hi {{ $customer->name }},</p>
    <p>Your payment for <strong>{{ $package->listing->title }}</strong> ({{ $package->name }}) is currently overdue.</p>
    
    <div style="background-color: #f4f5f7; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p style="margin:0;"><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
        <p style="margin:5px 0 0 0;"><strong>Amount Due:</strong> ₹{{ number_format($invoice->total, 2) }}</p>
        <p style="margin:5px 0 0 0; color: #dc3545;"><strong>Due Date:</strong> {{ $invoice->due_date->format('d M, Y') }}</p>
    </div>

    <p>Please pay the outstanding amount to avoid suspension of your subscription.</p>
    
    <center>
        <a href="{{ config('app.url') }}/pay/{{ $invoice->id }}" class="btn">Pay Now</a>
    </center>

    <p>Best regards,<br>{{ config('app.name') }} Team</p>
@endsection
