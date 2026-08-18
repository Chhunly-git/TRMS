<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Office;
use App\Models\Position;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. បង្កើត នាយកដ្ឋាន
        $dep1 = Department::create(['code' => 'GAD', 'name_kh' => 'នាយកដ្ឋានកិច្ចការទូទៅ', 'name_en' => 'Department of General Affairs']);
        $dep2 = Department::create(['code' => 'TRD', 'name_kh' => 'នាយកដ្ឋានចុះបញ្ជីបរធនបាលកិច្ច', 'name_en' => 'Department of Trust Registration']);
        $dep3 = Department::create(['code' => 'LID', 'name_kh' => 'នាយកដ្ឋានកិច្ចការគតិយុតនិងអធិការកិច្ច', 'name_en' => 'Department of Legal Affairs and Inspection']);
        $dep4 = Department::create(['code' => 'RTCD', 'name_kh' => 'នាយកដ្ឋានស្រាវជ្រាវ បណ្តុះបណ្តាល និងសហប្រតិបត្តិការ', 'name_en' => 'Department of Research, Training and Cooperation']);

        // 2. បង្កើត ការិយាល័យ
        Office::create(['department_id' => $dep1->id, 'code' => 'DAP', 'name_kh' => 'ការិយាល័យរដ្ឋបាលនិងបុគ្គលិក', 'name_en' => 'Division of Administration and Personnel']);
        Office::create(['department_id' => $dep1->id, 'code' => 'DAF', 'name_kh' => 'ការិយាល័យគណនេយ្យនិងហិរញ្ញវត្ថុ', 'name_en' => 'Division of Accounting and Finance']);
        Office::create(['department_id' => $dep1->id, 'code' => 'DMIT', 'name_kh' => 'ការិយាល័យគ្រប់គ្រងព័ត៌មានវិទ្យា', 'name_en' => 'Division of Management of Information Technology']);

        // 3. បង្កើត តួនាទី
        Position::create(['title_kh' => 'អគ្គនាយក', 'title_en' => 'Director General', 'level' => 1]);
        Position::create(['title_kh' => 'អគ្គនាយករង', 'title_en' => 'Deputy Director General', 'level' => 2]);
        Position::create(['title_kh' => 'ប្រធាននាយកដ្ឋាន', 'title_en' => 'Director of Department', 'level' => 3]);
        Position::create(['title_kh' => 'អនុប្រធានាយកដ្ឋាន', 'title_en' => 'Deputy Director of Department', 'level' => 4]);
    }
}