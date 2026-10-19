<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->status, ['pending', 'approved', 'rejected'], true) ? $request->status : null;

        return view('finance.budgets', [
            'requests' => ApprovalRequest::where('type', 'budget')->when($status, fn ($q, $s) => $q->where('status', $s))->latest('id')->get(),
            'status' => $status,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'amount' => 'required|numeric|min:1|max:10000000',
            'priority' => 'required|in:normal,urgent',
        ]);

        ApprovalRequest::create($data + ['type' => 'budget', 'department' => 'Finance team', 'requested_by' => auth()->id()]);

        return redirect()->route('finance.budgets')->with('status', 'Budget request sent to the head of school for approval.');
    }
}
