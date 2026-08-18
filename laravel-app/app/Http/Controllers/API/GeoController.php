<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\District;
use App\Models\Commune;
use App\Models\Village;

class GeoController extends Controller
{
    public function getProvinces()
    {
        return response()->json([
            'provinces' => Province::select('id', 'name_kh', 'name_en')->orderBy('name_kh', 'asc')->get()
        ]);
    }

    public function getDistrictsByProvince($id)
    {
        return response()->json([
            'districts' => District::where('province_id', $id)->select('id', 'province_id', 'name_kh', 'name_en')->orderBy('name_kh', 'asc')->get()
        ]);
    }

    public function getCommunesByDistrict($id)
    {
        return response()->json([
            'communes' => Commune::where('district_id', $id)->select('id', 'district_id', 'name_kh', 'name_en')->orderBy('name_kh', 'asc')->get()
        ]);
    }

    public function getVillagesByCommune($id)
    {
        return response()->json([
            'villages' => Village::where('commune_id', $id)->select('id', 'commune_id', 'name_kh', 'name_en')->orderBy('name_kh', 'asc')->get()
        ]);
    }
}