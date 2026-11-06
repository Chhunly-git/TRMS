<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DocumentNumberCounter;
use App\Models\InboundDocumentResponse;
use App\Models\User;
use App\Models\WorkingGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentNumberService
{
    /**
     * បំលែងលេខអារ៉ាប់ (0-9) ទៅជាលេខខ្មែរ (០-៩)
     */
    public static function toKhmerDigits(string|int $number): string
    {
        $khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
        $arabicDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($arabicDigits, $khmerDigits, (string) $number);
    }

    /**
     * រៀបចំទម្រង់លេខឯកសារផ្លូវការ: {seq}/{yy} ន.ប.ធ./{suffix} ជាលេខខ្មែរ
     * ឧទាហរណ៍: ០០១/២៦ ន.ប.ធ./ន.ក.ទ.
     */
    public static function formatNumber(int $seq, int $year, string $suffix): string
    {
        $paddedSeq = sprintf('%03d', $seq);
        $twoDigitYear = substr((string) $year, -2);

        $seqKh = self::toKhmerDigits($paddedSeq);
        $yearKh = self::toKhmerDigits($twoDigitYear);

        return "{$seqKh}/{$yearKh} ន.ប.ធ./{$suffix}";
    }

    /**
     * ផ្ទៀងផ្ទាត់ថាតើអ្នកប្រើប្រាស់ជាប្រធានអង្គភាពចេញលិខិត (Unit Head) ដែរឬទេ
     * - Department: មន្ត្រីក្នុងនាយកដ្ឋាននោះដែលមាន position level 3 (ប្រធាននាយកដ្ឋាន) ឬ 4 (អនុប្រធាននាយកដ្ឋាន)
     * - Working Group: សមាជិកសកម្មក្នុងក្រុមការងារដែលមាន role = 'LEADER'
     */
    public static function isUnitHead(User $user, InboundDocumentResponse $response): bool
    {
        $unitType = strtoupper($response->issuing_unit_type ?? 'DEPARTMENT');
        $unitId = (int) ($response->issuing_unit_id ?: $response->department_id);

        if ($unitType === 'DEPARTMENT') {
            if ((int) $user->department_id !== $unitId) {
                return false;
            }
            $level = $user->position ? (int) $user->position->level : 99;
            return in_array($level, [3, 4], true);
        }

        if ($unitType === 'WORKING_GROUP') {
            return DB::table('working_group_members')
                ->where('working_group_id', $unitId)
                ->where('user_id', $user->id)
                ->where('role', 'LEADER')
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->exists();
        }

        return false;
    }

    /**
     * ពិនិត្យថាតើការបញ្ជូននេះ គឺបញ្ជូនចេញផុតពីអង្គភាពចេញលិខិត (Leaving Issuing Unit) ដែរឬទេ
     * - Department: បញ្ជូនទៅ DG/Leadership (level <= 2) ឬទៅនាយកដ្ឋានផ្សេង (target dept != issuing dept)
     * - Working Group: បញ្ជូនទៅ DG/Leadership (level <= 2) ឬទៅអ្នកក្រៅក្រុមការងារ (target not active member of WG)
     */
    public static function isLeavingUnit(InboundDocumentResponse $response, ?User $targetReviewer): bool
    {
        if (!$targetReviewer) {
            return false;
        }

        $targetLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

        // 1. បញ្ជូនទៅថ្នាក់ដឹកនាំ (អគ្គនាយក ឬអគ្គនាយករង level <= 2) គឺចាត់ទុកថាចេញផុតពីអង្គភាព
        if ($targetLevel <= 2) {
            return true;
        }

        $unitType = strtoupper($response->issuing_unit_type ?? 'DEPARTMENT');
        $unitId = (int) ($response->issuing_unit_id ?: $response->department_id);

        if ($unitType === 'DEPARTMENT') {
            // បញ្ជូនទៅកាន់នាយកដ្ឋានផ្សេង (រួមទាំងការបញ្ជូនទៅពិគ្រោះយោបល់ - consultation)
            return (int) $targetReviewer->department_id !== $unitId;
        }

        if ($unitType === 'WORKING_GROUP') {
            // បញ្ជូនទៅកាន់អ្នកដែលមិនមែនជាសមាជិកក្រុមការងារនេះ
            $isTargetInWg = DB::table('working_group_members')
                ->where('working_group_id', $unitId)
                ->where('user_id', $targetReviewer->id)
                ->where('status', 'ACTIVE')
                ->whereNull('deleted_at')
                ->exists();

            return !$isTargetInWg;
        }

        return false;
    }

    /**
     * ចុះលេខឯកសារជាស្វ័យប្រវត្តិ (Single Trigger: Unit Head forwards outside issuing unit)
     * រត់ក្នុង Transaction ជាមួយ Lock ដើម្បីធានា No Gaps & No Duplicates
     */
    public static function assign(InboundDocumentResponse $response, User $signer, ?User $targetReviewer): ?string
    {
        // ប្រសិនបើឯកសារធ្លាប់ចុះលេខរួចហើយ មិនចុះឡើងវិញឡើយ (Immutable)
        if ($response->number_seq !== null) {
            return $response->response_number;
        }

        // លក្ខខណ្ឌកេះ (Trigger): ត្រូវតែជា Unit Head ចុះហត្ថលេខា/បញ្ជូន ហើយបញ្ជូនចេញក្រៅអង្គភាព
        if (!self::isUnitHead($signer, $response) || !self::isLeavingUnit($response, $targetReviewer)) {
            return null;
        }

        $unitType = strtoupper($response->issuing_unit_type ?? 'DEPARTMENT');
        $unitId = (int) ($response->issuing_unit_id ?: $response->department_id);

        // ទាញយកអក្សរកាត់ផ្លូវការ (Abbreviation)
        $abbr = null;
        if ($unitType === 'DEPARTMENT') {
            $dept = Department::find($unitId);
            $abbr = $dept?->abbreviation_kh;
        } elseif ($unitType === 'WORKING_GROUP') {
            $wg = WorkingGroup::find($unitId);
            $abbr = $wg?->abbreviation_kh;
        }

        if (empty($abbr)) {
            abort(422, "អង្គភាពចេញលិខិតមិនទាន់មានអក្សរកាត់ផ្លូវការ (Abbreviation) សម្រាប់ចុះលេខនៅឡើយទេ! សូមទាក់ទង Admin។");
        }

        // ឆ្នាំគិតតាមម៉ោងកម្ពុជា (Asia/Phnom_Penh)
        $year = Carbon::now('Asia/Phnom_Penh')->year;

        // Lazy create counter row if not exists
        DB::table('document_number_counters')->insertOrIgnore([
            'issuing_unit_type' => $unitType,
            'issuing_unit_id' => $unitId,
            'year' => $year,
            'last_number' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Lock counter row for update (Strict Concurrency Lock)
        $counter = DB::table('document_number_counters')
            ->where('issuing_unit_type', $unitType)
            ->where('issuing_unit_id', $unitId)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $nextSeq = (int) ($counter->last_number ?? 0) + 1;

        DB::table('document_number_counters')
            ->where('id', $counter->id)
            ->update([
                'last_number' => $nextSeq,
                'updated_at' => Carbon::now(),
            ]);

        $fullNumber = self::formatNumber($nextSeq, $year, $abbr);

        // Inject number into content HTML placeholder if exists
        $newContent = self::injectNumberIntoContent($response->content, $nextSeq);

        // Snapshot fields on response
        $response->issuing_unit_type = $unitType;
        $response->issuing_unit_id = $unitId;
        $response->number_seq = $nextSeq;
        $response->number_year = $year;
        $response->number_suffix = $abbr;
        $response->response_number = $fullNumber;
        if ($newContent !== $response->content) {
            $response->content = $newContent;
        }
        $response->save();

        Log::info("Assigned document number {$fullNumber} to response ID {$response->id} by user ID {$signer->id}");

        return $fullNumber;
    }

    /**
     * អាប់ដេតលេខរបាយការណ៍ចូលក្នុង Content HTML
     */
    public static function injectNumberIntoContent(?string $content, int|string $seqNumber): ?string
    {
        if (empty($content)) {
            return $content;
        }

        $paddedSeq = is_numeric($seqNumber) ? sprintf('%03d', (int) $seqNumber) : (string) $seqNumber;
        $khmerPadded = self::toKhmerDigits($paddedSeq);

        // Replace span placeholder <span class="doc-number-input"...>.......</span>
        $patternSpan = '/(<span class="doc-number-input"[^>]*>)([\s\S]*?)(<\/span>)/i';
        if (preg_match($patternSpan, $content)) {
            return preg_replace($patternSpan, '${1}' . $khmerPadded . '${3}', $content);
        }

        // Fallback: លេខ៖ ....... ន.ប.ធ.
        $patternFallback = '/(លេខ៖\s*)([\.\s]+)(\s*ន\.ប\.ធ\.)/u';
        if (preg_match($patternFallback, $content)) {
            return preg_replace($patternFallback, '${1}' . $khmerPadded . ' ${3}', $content);
        }

        return $content;
    }
}
