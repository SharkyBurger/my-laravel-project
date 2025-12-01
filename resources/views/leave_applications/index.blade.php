@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('HR Leave Applications Dashboard') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- SECTION 1: PENDING APPLICATIONS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200 bg-yellow-50">
                    <h3 class="font-semibold text-lg text-yellow-800">{{ __('Pending Leave Applications') }}</h3>
                </div>
                <div class="p-6">
                    @if($pendingApplications->isNotEmpty())
                        <ul class="space-y-4">
                            @foreach($pendingApplications as $application)
                                <li class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-lg text-gray-800">{{ $application->employee->first_name.' '.$application->employee->last_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $application->leaveType->name }} • {{ $application->start_date->format('M d') }} - {{ $application->end_date->format('M d, Y') }}</p>
                                        <p class="text-xs text-yellow-600 mt-1 font-bold uppercase">Status: {{ $application->hr_status }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ URL::signedRoute('hr.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Review') }}
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-gray-500 italic">{{ __('No pending leave applications.') }}</p>
                    @endif
                </div>
            </div>

            {{-- SECTION 2: APPROVED APPLICATIONS (Can be Cancelled) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200 bg-green-50">
                    <h3 class="font-semibold text-lg text-green-800">{{ __('Approved Applications') }}</h3>
                </div>
                <div class="p-6">
                    @if(isset($approvedApplications) && $approvedApplications->isNotEmpty())
                        <ul class="space-y-4">
                            @foreach($approvedApplications as $application)
                                <li class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-lg text-gray-800">{{ $application->employee->first_name.' '.$application->employee->last_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $application->leaveType->name }} • {{ $application->start_date->format('M d') }} - {{ $application->end_date->format('M d, Y') }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-green-600 font-bold uppercase">HR: {{ $application->hr_status }}</span>
                                            @if($application->approval_status === 'cancelled')
                                                <span class="text-xs text-red-600 font-bold uppercase border-l pl-2 border-gray-300">Final: Cancelled</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        {{-- CANCEL BUTTON --}}
                                        @if($application->approval_status !== 'cancelled')
                                            <form action="{{ route('hr.leave_applications.cancel', $application->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this approved leave?');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Cancel') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm italic">Cancelled</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-gray-500 italic">{{ __('No approved applications found.') }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection