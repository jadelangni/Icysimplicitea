<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors duration-200">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Employee') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <!-- Header with Employee Info -->
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-simplicitea-500 to-simplicitea-600">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-black font-bold text-xl">
                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-black">{{ $employee->name }}</h3>
                            <p class="text-simplicitea-100 text-sm">{{ $employee->email }}</p>
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('employees.update', $employee) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name', $employee->name) }}" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 @error('name') border-red-500 @enderror"
                                placeholder="Enter full name">
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 @error('email') border-red-500 @enderror"
                                placeholder="employee@simplicitea.com">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Password (Optional) -->
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Change Password (Optional)
                        </h4>
                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-3">Leave blank to keep the current password.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">New Password</label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 text-sm @error('password') border-red-500 @enderror"
                                    placeholder="••••••••">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 text-sm"
                                    placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance PIN -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            Attendance PIN {{ $employee->pin ? '(Set ✓)' : '(Not Set)' }}
                        </h4>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mb-3">Set a 4-6 digit PIN for clock-in/clock-out. Leave blank to keep the current PIN.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="pin" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">New PIN</label>
                                <input type="password" name="pin" id="pin" maxlength="6" minlength="4"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 text-sm @error('pin') border-red-500 @enderror"
                                    placeholder="4-6 digits">
                                @error('pin')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="pin_confirmation" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Confirm PIN</label>
                                <input type="password" name="pin_confirmation" id="pin_confirmation" maxlength="6" minlength="4"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-black placeholder-gray-400 focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 text-sm"
                                    placeholder="Confirm PIN">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Role & Branch -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <select name="role" id="role" required
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 appearance-none @error('role') border-red-500 @enderror">
                                    <option value="cashier" {{ old('role', $employee->role) == 'cashier' ? 'selected' : '' }}>Cashier</option>
                                    <option value="admin" {{ old('role', $employee->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('role')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="branch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Assign to Branch <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <select name="branch_id" id="branch_id" required
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-black focus:ring-2 focus:ring-simplicitea-500 focus:border-simplicitea-500 appearance-none @error('branch_id') border-red-500 @enderror">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('branch_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Inactive accounts cannot log in to the system.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-simplicitea-300 dark:peer-focus:ring-simplicitea-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-simplicitea-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('employees.index') }}" 
                            class="px-6 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-3 bg-simplicitea-600 hover:bg-simplicitea-700 text-black text-sm font-medium rounded-xl transition-colors duration-200 shadow-lg shadow-simplicitea-500/30 inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
                
                <!-- Delete Button (Separate Form - Outside main form to avoid nesting) -->
                @if($employee->id !== auth()->id())
                <div class="px-6 pb-6">
                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" 
                        onsubmit="return confirm('Are you sure you want to delete this employee? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                            class="w-full px-4 py-3 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 text-sm font-medium rounded-xl transition-colors duration-200 inline-flex items-center justify-center border border-red-200 dark:border-red-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete This Employee
                        </button>
                    </form>
                </div>
                @endif
            </div>
            
            <!-- Activity Info -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-black mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Account Information
                </h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Created</p>
                        <p class="text-gray-900 dark:text-black font-medium">{{ $employee->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Last Updated</p>
                        <p class="text-gray-900 dark:text-black font-medium">{{ $employee->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
