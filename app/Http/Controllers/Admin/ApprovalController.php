<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->status, ['pending', 'approved', 'rejected', 'all'], true) ? $request->status : 'pending';

        $requests = ApprovalRequest::with('requester')
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab))
            ->orderByRaw("priority = 'urgent' desc")
            ->latest('id')
            ->get();

        return view('admin.approvals.index', [
            'requests' => $requests,
            'tab' => $tab,
            'counts' => ApprovalRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function decide(ApprovalRequest $approval, string $decision)
    {
        abort_unless($approval->status === 'pending', 409, 'This request was already decided.');

        $approval->update([
            'status' => $decision === 'approve' ? 'approved' : 'rejected',
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return redirect()->back()->with('status', "“{$approval->title}” was ".($decision === 'approve' ? 'approved' : 'rejected').'.');
    }
}
