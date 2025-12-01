<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Notifications\LeaveApplicationDecision;
use Illuminate\Notifications\DatabaseNotification;

class AdminLeaveApplicationController extends Controller
{
    public function index()
    {
        $pendingApplications = LeaveApplication::where('admin_status', 'pending')
                                                ->where('hr_status', 'approved')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'asc')
                                                ->get();

        return view('admin.leave_applications.index', compact('pendingApplications'));
    }

    public function review(Request $request, LeaveApplication $leaveApplication)
    {
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);
        return view('admin.leave_applications.review', compact('leaveApplication'));
    }

    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'decision' => ['required', 'in:approved_with_pay,approved_without_pay,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $request->input('decision');
        $remarks = $request->input('remarks');
        $approvedBy = Auth::user()->employee->name;

        if ($leaveApplication->admin_status !== 'pending') {
            return redirect()->back()->with('error', 'This leave application has already been processed by Admin.');
        }

        $leaveApplication->admin_status = $decision;
        $leaveApplication->admin_approved_at = Carbon::now();
        $leaveApplication->admin_approved_by = Auth::user()->employee->id;
        $leaveApplication->admin_remarks = $remarks;
        $leaveApplication->approval_status = $decision;
        $leaveApplication->save();

        // Mark notification as read
        $notification = Auth::user()->unreadNotifications()
                            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForAdmin')
                            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
                            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, $decision, $approvedBy, $remarks));

        return redirect()->route('admin.leave_applications.index')->with('success', "Leave application {$decision} successfully.");
    }

    /**
     * Cancel an already approved leave application.
     */
    public function cancel(Request $request, LeaveApplication $leaveApplication)
    {
        // Allow cancellation if it was approved (with or without pay) or just approved generic
        $allowableStatuses = ['approved_with_pay', 'approved_without_pay', 'approved'];

        if (!in_array($leaveApplication->admin_status, $allowableStatuses) && !in_array($leaveApplication->approval_status, $allowableStatuses)) {
            return redirect()->back()->with('error', 'Only approved applications can be cancelled.');
        }

        // Update Status to Cancelled
        $leaveApplication->admin_status = 'cancelled';
        $leaveApplication->approval_status = 'cancelled'; // Cancels the application as a whole
        $leaveApplication->save();

        // Optional: Notify the employee that their approved leave was cancelled
        // $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, 'cancelled', Auth::user()->employee->name, 'Cancelled by Admin'));

        return redirect()->back()->with('success', 'Leave application has been cancelled successfully.');
    }

    public function allLeaveApplications()
    {
        $leaveApplications = LeaveApplication::with(['employee', 'leaveType'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);

        return view('admin.leave_applications.all_leave_applications', compact('leaveApplications'));
    }
}