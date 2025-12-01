<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\User;
use App\Notifications\LeaveApplicationSubmittedForHR;
use App\Notifications\LeaveApplicationDecision;
use App\Notifications\LeaveApplicationSubmittedForAH;
use Illuminate\Support\Facades\Auth;

class AcademicHeadLeaveApplicationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasRole('academic_head')) {
            return redirect()->route('dashboard')->with('error', 'Access Denied.');
        }

        $notifications = $user->unreadNotifications;
        $pendingApplications = LeaveApplication::where('ah_status', 'pending')
                                ->orderBy('created_at', 'desc')
                                ->get();

        $dashboardData = [
            'notifications' => $notifications,
            'pendingApplications' => $pendingApplications,
            'totalStudents' => 0,
            'totalCourses' => 0,
            'totalEnrollments' => 0,
            'totalTeachers' => 0,
            'totalPrograms' => 0,
            'totalSections' => 0,
            'totalUsers' => 0,
            'recentStudents' => collect(),
            'recentCourses' => collect(),
        ];

        return view('academic_head.dashboard', $dashboardData);
    }

    public function review(LeaveApplication $leaveApplication)
    {
        $user = Auth::user();
        if (!$user->hasRole('academic_head') || $user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action. You can only review applications from your department.');
        }
        return view('academic_head.leave_applications.review', compact('leaveApplication'));
    }

    public function decide(Request $request, LeaveApplication $leaveApplication)
    {
        $user = Auth::user();
        if (!$user->hasRole('academic_head') || $user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ah_status' => 'required|in:approved,rejected',
            'ah_remarks' => 'nullable|string|max:1000',
        ]);

        $leaveApplication->ah_status = $request->ah_status;
        $leaveApplication->ah_remarks = $request->ah_remarks;
        $leaveApplication->ah_approved_at = now();
        $leaveApplication->ah_approved_by = Auth::user()->employee->id;
        $leaveApplication->save();

        $user->notifications()
            ->where('type', LeaveApplicationSubmittedForAH::class)
            ->whereJsonContains('data->leave_application_id', $leaveApplication->id)
            ->update(['read_at' => now()]);

        if ($request->ah_status === 'approved') {
            $hrUsers = User::whereHas('employee', function ($query) {
                $query->where('role', 'hr');
            })->get();

            foreach ($hrUsers as $hrUser) {
                $hrUser->notify(new LeaveApplicationSubmittedForHR($leaveApplication));
            }
        } elseif ($request->ah_status === 'rejected') {
            $leaveApplication->employee->user->notify(new LeaveApplicationDecision($leaveApplication));
        }

        return redirect()->route('ah.leave_applications.index')->with('success', 'Leave application decision recorded successfully.');
    }

    /**
     * Cancel an approved leave application.
     */
    public function cancel(Request $request, LeaveApplication $leaveApplication)
    {
        $user = Auth::user();

        // Authorization: Ensure AH can only cancel for their department
        if (!$user->hasRole('academic_head') || $user->employee->department_id !== ($leaveApplication->employee->department_id ?? null)) {
            abort(403, 'Unauthorized action.');
        }

        // Allow cancellation if status is currently approved
        // Note: This cancels the leave regardless of if HR/Admin has already seen it, 
        // effectively revoking the AH's previous approval.
        
        $leaveApplication->ah_status = 'cancelled';
        $leaveApplication->approval_status = 'cancelled'; // Cancel as a whole
        $leaveApplication->save();

        return redirect()->back()->with('success', 'Leave application has been cancelled successfully.');
    }

    public function allLeaveApplications()
    {
        $user = Auth::user();
        if (!$user->hasRole('academic_head')) {
            abort(403, 'Unauthorized action.');
        }

        $departmentId = $user->employee->department_id ?? null;
        if (!$departmentId) {
            return redirect()->route('ah.leave_applications.index')->with('error', 'Profile incomplete.');
        }

        $leaveApplications = LeaveApplication::whereHas('employee.department', function ($query) use ($departmentId) {
                                    $query->where('id', $departmentId);
                                })
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);

        return view('academic_head.all_leave_applications', compact('leaveApplications'));
    }
}