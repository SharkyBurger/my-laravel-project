<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Notifications\LeaveApplicationDecision;
use App\Notifications\LeaveApplicationSubmittedForAdmin;
use Illuminate\Notifications\DatabaseNotification;

class HrLeaveApplicationController extends Controller
{
    /**
     * Show all pending leave applications for HR review.
     */
    public function index()
    {
        // Fetch Pending (for review)
        $pendingApplications = LeaveApplication::where('hr_status', 'pending')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'asc')
                                                ->get();

        // Fetch Approved (so you can cancel them)
        $approvedApplications = LeaveApplication::where('hr_status', 'approved')
                                                ->with(['employee', 'classesToMiss'])
                                                ->orderBy('created_at', 'desc')
                                                ->get();

        return view('hr.leave_applications.index', compact('pendingApplications', 'approvedApplications'));
    }

    /**
     * Show details of a specific leave application for review.
     */
    public function review(Request $request, LeaveApplication $leaveApplication)
    {
        $leaveApplication->load(['employee', 'classesToMiss.substituteTeacher']);
        return view('hr.leave_applications.review', compact('leaveApplication'));
    }

    /**
     * Process HR decision (approve/reject).
     */
    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $decision = $request->input('decision');
        $remarks = $request->input('remarks');
        
        // FIX: Pass 'HR' instead of trying to get a name that might be null
        $approvedBy = 'HR'; 

        if ($leaveApplication->hr_status !== 'pending') {
            return redirect()->back()->with('error', 'This leave application has already been processed by HR.');
        }

        $leaveApplication->hr_status = $decision;
        $leaveApplication->hr_approved_at = Carbon::now();
        $leaveApplication->hr_approved_by = Auth::user()->employee->id;
        $leaveApplication->hr_remarks = $remarks;
        $leaveApplication->save();

        // Mark notification as read
        $notification = Auth::user()->unreadNotifications()
                            ->where('type', 'App\Notifications\LeaveApplicationSubmittedForHR')
                            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
                            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        // Notify Admin if approved
        if ($decision === 'approved') {
            $adminUsers = User::whereHas('employee', function ($query) {
                $query->where('role', 'admin');
            })->get();
            
            foreach ($adminUsers as $adminUser) {
                $adminUser->notify(new LeaveApplicationSubmittedForAdmin($leaveApplication));
            }
        } 

        // Notify the employee
        // We pass $approvedBy which is now the string "HR"
        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, $decision, $approvedBy, $remarks));

        return redirect()->route('hr.leave_applications.index')->with('success', "Leave application {$decision} successfully.");
    }

    /**
     * Cancel an already approved leave application.
     */
    public function cancel(Request $request, LeaveApplication $leaveApplication)
    {
        if (!in_array($leaveApplication->hr_status, ['approved', 'approved_with_pay', 'approved_without_pay']) && 
            !in_array($leaveApplication->approval_status, ['approved_with_pay', 'approved_without_pay'])) {
            return redirect()->back()->with('error', 'Only approved applications can be cancelled.');
        }

        $leaveApplication->hr_status = 'cancelled';
        $leaveApplication->approval_status = 'cancelled'; 
        $leaveApplication->save();

        // FIX: Pass 'HR' here as well to prevent the same crash
        $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication, 'cancelled', 'HR', 'Cancelled by HR'));

        return redirect()->back()->with('success', 'Leave application has been cancelled successfully.');
    }
}