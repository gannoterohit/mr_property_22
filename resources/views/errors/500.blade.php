@extends('layouts.public')

@section('content')
@php $contactPageLive = \App\Models\CmsPage::published()->where('slug', 'contact-us')->exists(); @endphp
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4 py-8">
    <div class="relative mb-6">
        <h1 class="text-7xl sm:text-8xl md:text-9xl font-extrabold text-gray-100 tracking-widest select-none">500</h1>
        <div class="bg-red-600 text-white px-3 py-1 text-xs sm:text-sm rounded rotate-12 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 shadow-lg">
            Server Error
        </div>
    </div>

    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 mb-3">Something went wrong on our end.</h2>
    <p class="text-gray-600 mb-6 max-w-md text-sm sm:text-base">
        We're working to fix this. Please try again in a few minutes or contact support if the problem continues.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition duration-150 ease-in-out shadow-md min-h-[44px]">
            Go Back Home
        </a>
        @if($contactPageLive)
            <a href="{{ url('/contact-us') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 transition duration-150 ease-in-out shadow-sm min-h-[44px]">
                Contact Support
            </a>
        @endif
    </div>
</div>
@endsection
