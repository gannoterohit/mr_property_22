@extends('emails.layout', ['title' => $mailSubject, 'preheader' => $bodyText])

@section('content')
    @if($eyebrow)<div style="margin-bottom:16px;"><span class="badge badge-{{ $tone }}">{{ $eyebrow }}</span></div>@endif
    <h2>{{ $heading }}</h2>
    @if(!empty($imageUrl))
        <div style="margin: 16px 0; text-align: center;">
            <img src="{{ $imageUrl }}" alt="{{ $heading }}" style="max-width: 100%; height: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
        </div>
    @endif
    <p>{!! nl2br(e($bodyText)) !!}</p>
    @if(count($details))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
            @foreach($details as $label => $value)<tr><td style="padding:12px 16px;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}color:#64748b;font-size:13px;width:34%;">{{ $label }}</td><td style="padding:12px 16px;{{ !$loop->last ? 'border-bottom:1px solid #e2e8f0;' : '' }}color:#0f172a;font-size:14px;font-weight:600;">{{ $value }}</td></tr>@endforeach
        </table>
    @endif
    @if($notice)<div class="notice notice-{{ $tone }}">{{ $notice }}</div>@endif
    @if($actionText && $actionUrl)
        <div style="margin-top:26px;"><a href="{{ $actionUrl }}" class="btn">{{ $actionText }}</a></div>
        <p class="fallback-link">If the button does not work, copy and paste this link:<br><a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>
    @endif
@endsection
