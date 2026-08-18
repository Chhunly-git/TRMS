<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // GET: /manage/departments
    public function getDepartments()
    {
        $departments = Department::withCount('offices')->latest()->get();
        return response()->json($departments);
    }

    // GET: /manage/departments/read/{id}
    public function readDepartment($id)
    {
        $department = Department::with('offices')->findOrFail($id);
        return response()->json($department);
    }

    // POST: /manage/departments/create
    public function createDepartment(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:departments,code',
            'name_kh' => 'required|string',
            'name_en' => 'nullable|string',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'message' => 'បង្កើតនាយកដ្ឋានជោគជ័យ',
            'data' => $department
        ], 201);
    }

    // PUT: /manage/departments/update/{id}
    public function updateDepartment(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:departments,code,' . $department->id,
            'name_kh' => 'required|string',
            'name_en' => 'nullable|string',
        ]);

        $department->update($validated);

        return response()->json([
            'message' => 'ធ្វើបច្ចុប្បន្នភាពនាយកដ្ឋានជោគជ័យ',
            'data' => $department
        ]);
    }

    // DELETE: /manage/departments/delete/{id}
    public function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json(['message' => 'លុបនាយកដ្ឋានជោគជ័យ']);
    }
}