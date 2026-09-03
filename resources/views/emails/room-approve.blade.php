@extends('emails.layout', ['title' => 'Listing approved', 'preheader' => 'Your property listing is now live on ApnaNest.'])
@section('content')
<span class="badge badge-success">Approved & live</span><h2 style="margin-top:16px;">Great news, {{ $ownerName }}!</h2>
<p>Your listing <strong>{{ $roomTitle }}</strong> has passed our review and is now visible to people searching on ApnaNest.</p>
<table role="presentation" width="100%" style="margin:22px 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;"><tr><td style="padding:18px;"><strong style="color:#0f172a;">{{ $roomTitle }}</strong><br><span style="color:#64748b;">{{ $roomAddress }}</span><br><span style="color:#15803d;font-size:18px;font-weight:700;">&#8377;{{ number_format((float)$roomPrice) }} / month</span></td></tr></table>
<p>Keep the listing details and availability up to date so interested users receive accurate information.</p>
<a href="{{ url('/owner/rooms') }}" class="btn">Manage my listings</a>
@endsection
