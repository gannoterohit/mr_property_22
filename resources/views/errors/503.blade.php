@extends('layouts.public')

@section('content')
@php $contactPageLive = \App\Models\CmsPage::published()->where('slug', 'contact-us')->exists(); @endphp
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
    <div class="relative mb-8">
        <h1 class="text-9xl font-extrabold text-gray-100 tracking-widest select-none">503</h1>
        <div class="bg-indigo-600 text-white px-4 py-1 text-sm rounded rotate-12 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 shadow-lg">
            Maintenance
        </div>
    </div>

    <h2 class="text-3xl font-bold text-gray-800 mb-4">We'll be back shortly.</h2>
    <p class="text-gray-600 mb-8 max-w-md">
        RoomRental is undergoing scheduled maintenance to serve you better. Please check back in a few minutes.
    </p>

    <div class="flex flex-col sm:flex-row gap-4">
        <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md">
            Try Again
        </button>
        @if($contactPageLive)
            <a href="{{ url('/contact-us') }}" class="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 ease-in-out shadow-sm">
                Contact Support
            </a>
        @endif
    </div>
</div>
@endsection
