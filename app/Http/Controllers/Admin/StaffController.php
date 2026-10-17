<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = StaffMember::query()
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', "%$t%")->orWhere('last_name', 'like', "%$t%")->orWhere('email', 'like', "%$t%")))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->withCount('classes')
            ->orderBy('first_name')->orderBy('last_name')
            ->paginate(15)->withQueryString();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.form', ['member' => new StaffMember]);
    }

    public function store(Request $request)
    {
        StaffMember::create($request->validate($this->rules()));

        return redirect()->route('admin.staff.index')->with('status', 'Staff member added.');
    }

    public function edit(StaffMember $staffMember)
    {
        return view('admin.staff.form', ['member' => $staffMember]);
    }

    public function update(Request $request, StaffMember $staffMember)
    {
        $staffMember->update($request->validate($this->rules($staffMember->id)));

        return redirect()->route('admin.staff.index')->with('status', 'Staff member updated.');
    }

    public function destroy(StaffMember $staffMember)
    {
        $staffMember->delete();

        return redirect()->route('admin.staff.index')->with('status', 'Staff member deleted.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'type' => 'required|in:teacher,support',
            'email' => ['nullable', 'email', 'max:120', Rule::unique('staff_members', 'email')->ignore($id)],
            'phone' => 'nullable|string|max:40',
            'hired_on' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ];
    }
}
