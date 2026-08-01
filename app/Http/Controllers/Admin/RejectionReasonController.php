<?php

namespace App\Http\Controllers\Admin;

use App\Models\RejectionReason;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RejectionReasonController extends Controller
{
    public function index()
    {
        $reasons = RejectionReason::orderByDesc('is_active')->orderBy('reason')->get();
        return view('admin.rejection-reasons.index', compact('reasons'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);
        
        RejectionReason::create([
            'reason' => $request->reason,
            'is_active' => true
        ]);
        
        return redirect()->back()->with('success', 'Rejection reason added successfully');
    }
    
    public function update(Request $request, RejectionReason $rejectionReason)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);
        
        $rejectionReason->update([
            'reason' => $request->reason
        ]);
        
        return redirect()->back()->with('success', 'Rejection reason updated successfully');
    }
    
    public function destroy(RejectionReason $rejectionReason)
    {
        // Soft delete by setting is_active to false
        $rejectionReason->update([
            'is_active' => false
        ]);
        
        return redirect()->back()->with('success', 'Rejection reason deleted successfully');
    }

    public function toggleStatus(RejectionReason $rejectionReason)
    {
        $rejectionReason->update(['is_active' => !$rejectionReason->is_active]);

        return redirect()->back()->with(
            'success',
            $rejectionReason->is_active ? 'Rejection reason activated successfully.' : 'Rejection reason deactivated successfully.'
        );
    }
}
