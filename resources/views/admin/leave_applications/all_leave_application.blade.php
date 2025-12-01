@extends('layouts.admin') {{-- Make sure this matches your Admin Layout name --}}

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('All Leave Applications (Admin View)') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">All Leave Applications</h3>

                    @if($leaveApplications->isEmpty())
                        <p class="text-gray-600">No leave applications found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($leaveApplications as $application)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $application->employee->user->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $application->leaveType->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $application->start_date->format('M d, Y') }} - {{ $application->end_date->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if(in_array($application->admin_status, ['approved', 'approved_with_pay', 'approved_without_pay'])) bg-green-100 text-green-800
                                                    @elseif($application->admin_status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $application->admin_status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                {{-- View Button --}}
                                                <a href="{{ URL::signedRoute('admin.leave_applications.review', ['leaveApplication' => $application->id]) }}" class="text-gray-500 hover:text-gray-700 mr-2">
                                                    View
                                                </a>

                                                {{-- CANCEL BUTTON: Only shows if Approved --}}
                                                @if(in_array($application->admin_status, ['approved', 'approved_with_pay', 'approved_without_pay']))
                                                    <form action="{{ route('admin.leave_applications.cancel', $application->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to CANCEL this approved leave?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 ml-2">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $leaveApplications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection