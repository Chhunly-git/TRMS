<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    // GET: /manage/positions
    public function getPositions()
    {
        $positions = Position::orderBy('level', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $positions // វេចខ្ចប់ក្នុង key 'data' សម្រាប់ VueJS
        ]);
    }

    // POST: /manage/positions/create
    public function createPosition(Request $request)
    {
        $validated = $request->validate([
            'title_kh' => 'required|string',
            'title_en' => 'nullable|string',
            'level' => 'required|integer',
        ]);

        $position = Position::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'បង្កើតតួនាទីជោគជ័យ',
            'data' => $position
        ], 201);
    }
    
    // PUT: /manage/positions/update/{id}
    public function updatePosition(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $validated = $request->validate([
            'title_kh' => 'required|string',
            'title_en' => 'nullable|string',
            'level' => 'required|integer',
        ]);

        $position->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ធ្វើបច្ចុប្បន្នភាពតួនាទីជោគជ័យ',
            'data' => $position
        ]);
    }

    // DELETE: /manage/positions/delete/{id}
    public function deletePosition($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'លុបតួនាទីជោគជ័យ'
        ]);
    }
}