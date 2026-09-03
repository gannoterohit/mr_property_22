@extends('emails.layout', ['title' => 'Listing changes required', 'preheader' => 'Please update your listing and submit it for review again.'])
@section('content')
<span class="badge badge-danger">Action required</span><h2 style="margin-top:16px;">Hello {{ $ownerName }},</h2>
<p>Your listing <strong>{{ $roomTitle }}</strong> needs a few changes before it can go live.</p>
<div class="notice notice-danger"><strong>Review reasons</strong><ul style="margin:8px 0 0;padding-left:20px;">@forelse($reasons as $reason)<li>{{ $reason }}</li>@empty<li>Please review the listing details and contact support if you need help.</li>@endforelse</ul></div>
<p>Correct the items above, save your changes, and resubmit the listing. Our team will review it again.</p>
<a href="{{ url('/owner/rooms') }}" class="btn">Update listing</a>
@endsection
