@extends('layouts.public')

@section('content')
@php $contactPageLive = \App\Models\CmsPage::published()->where('slug', 'contact-us')->exists(); @endphp
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
    <div class="relative mb-8">
        <h1 class="text-9xl font-extrabold text-gray-100 tracking-widest select-none">404</h1>
        <div class="bg-blue-600 text-white px-4 py-1 text-sm rounded rotate-12 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 shadow-lg">
            Page Not Found
        </div>
    </div>
    
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Whoops! You're lost in space.</h2>
    <p class="text-gray-600 mb-8 max-w-md">
        The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
    </p>

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md">
            Go Back Home
        </a>
        @if($contactPageLive)
            <a href="{{ route('pages.contact') }}" class="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 ease-in-out shadow-sm">
                Contact Support
            </a>
        @endif
    </div>

</div>
@endsection
