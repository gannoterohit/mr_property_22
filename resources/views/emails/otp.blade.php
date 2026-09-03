@extends('emails.layout', ['title' => 'Verification Code'])

@section('content')
    <h2>Hello,</h2>
    <p>To verify your identity and secure your session, please use the following one-time password (OTP):</p>
    
    <div style="background-color: #f3f4f6; border: 2px dashed #d1d5db; border-radius: 12px; padding: 25px; text-align: center; margin: 30px 0;">
        <span style="font-size: 36px; font-weight: 800; color: #1e3a8a; letter-spacing: 8px; font-family: monospace;">{{ $otp }}</span>
    </div>
    
    <p>This code is uniquely generated for you and will expire in <strong>10 minutes</strong>. For your safety, do not share this code with anyone.</p>
    
    <p style="font-size: 14px; color: #718096; margin-top: 40px; border-top: 1px solid #edf2f7; padding-top: 20px;">
        If you did not request this code, someone may be trying to access your account. You can safely ignore this email or change your security settings.
    </p>
@endsection
