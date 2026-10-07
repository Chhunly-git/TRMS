<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InboundSenderOrganization;

class InboundSenderOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = [
            ['name_kh' => 'ទីស្តីការគណៈរដ្ឋមន្ត្រី', 'name_en' => 'Office of the Council of Ministers', 'code' => 'OCM', 'category' => 'GOVERNMENT', 'order_index' => 1],
            ['name_kh' => 'ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ', 'name_en' => 'Ministry of Economy and Finance', 'code' => 'MEF', 'category' => 'GOVERNMENT', 'order_index' => 2],
            ['name_kh' => 'ក្រសួងមហាផ្ទៃ', 'name_en' => 'Ministry of Interior', 'code' => 'MoI', 'category' => 'GOVERNMENT', 'order_index' => 3],
            ['name_kh' => 'ក្រសួងយុត្តិធម៌', 'name_en' => 'Ministry of Justice', 'code' => 'MoJ', 'category' => 'GOVERNMENT', 'order_index' => 4],
            ['name_kh' => 'ក្រសួងពាណិជ្ជកម្ម', 'name_en' => 'Ministry of Commerce', 'code' => 'MoC', 'category' => 'GOVERNMENT', 'order_index' => 5],
            ['name_kh' => 'ក្រសួងប្រៃសណីយ៍ និងទូរគមនាគមន៍', 'name_en' => 'Ministry of Post and Telecommunications', 'code' => 'MPTC', 'category' => 'GOVERNMENT', 'order_index' => 6],
            ['name_kh' => 'ធនាគារជាតិនៃកម្ពុជា', 'name_en' => 'National Bank of Cambodia', 'code' => 'NBC', 'category' => 'BANK', 'order_index' => 7],
            ['name_kh' => 'អាជ្ញាធរសេវាហិរញ្ញវត្ថុមិនមែនធនាគារ', 'name_en' => 'Non-Bank Financial Services Authority', 'code' => 'FSA', 'category' => 'REGULATOR', 'order_index' => 8],
            ['name_kh' => 'និយ័តករអាណាព្យាបាល', 'name_en' => 'Trust Regulator', 'code' => 'TR', 'category' => 'REGULATOR', 'order_index' => 9],
            ['name_kh' => 'និយ័តករមូលបត្រកម្ពុជា', 'name_en' => 'Securities and Exchange Regulator of Cambodia', 'code' => 'SERC', 'category' => 'REGULATOR', 'order_index' => 10],
            ['name_kh' => 'និយ័តករធានារ៉ាប់រងកម្ពុជា', 'name_en' => 'Insurance Regulator of Cambodia', 'code' => 'IRC', 'category' => 'REGULATOR', 'order_index' => 11],
            ['name_kh' => 'និយ័តករគណនេយ្យនិងសវនកម្ម', 'name_en' => 'Accounting and Auditing Regulator', 'code' => 'ACAR', 'category' => 'REGULATOR', 'order_index' => 12],
            ['name_kh' => 'និយ័តករបច្ចេកវិទ្យាហិរញ្ញវត្ថុ', 'name_en' => 'Financial Technology Regulator', 'code' => 'FinTech', 'category' => 'REGULATOR', 'order_index' => 13],
            ['name_kh' => 'អគ្គនាយកដ្ឋានពន្ធដារ', 'name_en' => 'General Department of Taxation', 'code' => 'GDT', 'category' => 'GOVERNMENT', 'order_index' => 14],
            ['name_kh' => 'អគ្គនាយកដ្ឋានគយ និងរដ្ឋាករកម្ពុជា', 'name_en' => 'General Department of Customs and Excise', 'code' => 'GDCE', 'category' => 'GOVERNMENT', 'order_index' => 15],
            ['name_kh' => 'គណៈកម្មាធិការជាតិប្រឆាំងការសម្អាតប្រាក់', 'name_en' => 'Cambodia Financial Intelligence Unit', 'code' => 'CAFIU', 'category' => 'GOVERNMENT', 'order_index' => 16],
            ['name_kh' => 'សាលារាជធានីភ្នំពេញ / រដ្ឋបាលរាជធានី-ខេត្ត', 'name_en' => 'Phnom Penh Capital Hall / Provincial Administration', 'code' => 'ADMIN', 'category' => 'GOVERNMENT', 'order_index' => 17],
            ['name_kh' => 'ក្រុមហ៊ុន / ស្ថាប័នឯកជន', 'name_en' => 'Private Companies / Institutions', 'code' => 'PRIVATE', 'category' => 'COMPANY', 'order_index' => 18],
        ];

        foreach ($orgs as $org) {
            InboundSenderOrganization::firstOrCreate(
                ['name_kh' => $org['name_kh']],
                $org
            );
        }
    }
}
