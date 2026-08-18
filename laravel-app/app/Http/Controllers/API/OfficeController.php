<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    // GET: /manage/offices
    public function getOffices()
    {
        $offices = Office::with('department')->latest()->get();
        return response()->json($offices);
    }

    // GET: /manage/offices/by-department/{department_id} (សម្រាប់ Cascading Dropdown)
    public function getOfficesByDepartment($department_id)
    {
        $offices = Office::where('department_id', $department_id)->get();
        return response()->json($offices);
    }

    // POST: /manage/offices/create
    public function createOffice(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'required|string|unique:offices,code',
            'name_kh' => 'required|string',
            'name_en' => 'nullable|string',
        ]);

        $office = Office::create($validated);

        return response()->json([
            'message' => 'បង្កើតការិយាល័យជោគជ័យ',
            'data' => $office->load('department')
        ], 201);
    }

    // PUT: /manage/offices/update/{id}
    public function updateOffice(Request $request, $id)
    {
        $office = Office::findOrFail($id);

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'required|string|unique:offices,code,' . $office->id,
            'name_kh' => 'required|string',
            'name_en' => 'nullable|string',
        ]);

        $office->update($validated);

        return response()->json([
            'message' => 'ធ្វើបច្ចុប្បន្នភាពការិយាល័យជោគជ័យ',
            'data' => $office->load('department')
        ]);
    }

    // DELETE: /manage/offices/delete/{id}
    public function deleteOffice($id)
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return response()->json(['message' => 'លុបការិយាល័យជោគជ័យ']);
    }
}