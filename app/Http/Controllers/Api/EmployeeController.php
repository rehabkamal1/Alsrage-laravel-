<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('position')) {
            $query->where('position', $request->string('position'));
        }

        $allowedSortBy = ['id', 'name', 'username', 'phone', 'position', 'created_at'];
        $sortBy = in_array($request->input('sort_by'), $allowedSortBy, true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $employees = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate((int) $request->integer('per_page', 10))
            ->withQueryString();

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'username' => 'required|string|max:255|unique:employees,username',
            'password' => 'required|string|min:6',
            'position' => 'nullable|string|max:255',
            'office_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        $employee = Employee::create($validated);
        return response()->json($employee, 201);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'username' => 'required|string|max:255|unique:employees,username,' . $employee->id,
            'password' => 'nullable|string|min:6',
            'position' => 'nullable|string|max:255',
            'office_name' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        // Only update password if provided
        if (!$validated['password']) {
            unset($validated['password']);
        }

        $employee->update($validated);
        return response()->json($employee);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}

