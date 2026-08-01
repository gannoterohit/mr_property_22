@extends('layouts.admin')

@section('title', 'Register New Owner - Admin')

@section('admin-content')
<div class="flex min-h-0 bg-gray-100">
    <!-- Sidebar -->
    <!-- Main Content -->
    <div class="flex-1 min-w-0">
        <div class="container mx-auto px-6 py-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-user-plus admin-theme-text mr-2"></i>Register New Owner
                    </h1>
                    <p class="text-gray-600 mt-1">Create a new owner account manually</p>
                </div>
                <a href="{{ route('admin.owners') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Owners
                </a>
            </div>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <form action="{{ route('admin.owners.store') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2  @error('name') border-red-500 @enderror"
                                       placeholder="Enter owner's full name">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2  @error('email') border-red-500 @enderror"
                                       placeholder="email@example.com">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2  @error('phone') border-red-500 @enderror"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                                    <input type="password" name="password" id="password" required
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2  @error('password') border-red-500 @enderror"
                                           placeholder="min. 8 characters">
                                    @error('password')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" required
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 "
                                           placeholder="Repeat password">
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" 
                                        class="w-full admin-theme-bg font-bold py-3 rounded-lg transition shadow-lg">
                                    <i class="fas fa-user-plus mr-2"></i>Register Owner
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
