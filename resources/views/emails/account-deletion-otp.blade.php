@extends('emails.layout', ['title' => 'Confirm account deletion', 'preheader' => 'Use this security code only if you requested account deletion.'])
@section('content')
<span class="badge badge-danger">Security confirmation</span><h2 style="margin-top:16px;">Confirm account deletion</h2><p>Enter this one-time code to confirm that you want to permanently delete your ApnaNest account:</p>
<div style="margin:24px 0;padding:22px;text-align:center;background:#fef2f2;border:2px dashed #fca5a5;border-radius:12px;color:#991b1b;font-size:34px;font-weight:800;letter-spacing:8px;">{{ $otp }}</div>
<div class="notice notice-danger"><strong>Important:</strong> Account deletion is permanent. If you did not request it, do not use this code and secure your account.</div><p>This code expires in <strong>10 minutes</strong>. Never share it with anyone.</p>
@endsection
