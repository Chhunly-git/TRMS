<?php

namespace App\Services;

use App\Models\InboundDocument;
use App\Models\InboundDocumentResponse;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $token;
    protected ?string $defaultChatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        $this->defaultChatId = config('services.telegram.default_chat_id') ?: env('TELEGRAM_DEFAULT_CHAT_ID');
    }

    /**
     * Send generic HTML message to Telegram Chat ID
     */
    public function sendMessage(?string $chatId, string $htmlMessage, ?array $replyMarkup = null): bool
    {
        $targetChatId = $chatId ?: $this->defaultChatId;

        if (empty($this->token)) {
            Log::warning('TelegramService: Telegram bot token is not configured.');
            return false;
        }

        if (empty($targetChatId)) {
            Log::info('TelegramService: No recipient chat_id provided and no default chat_id configured.');
            return false;
        }

        try {
            $payload = [
                'chat_id' => $targetChatId,
                'text' => $htmlMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $response = Http::timeout(6)
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

            if (!$response->successful()) {
                Log::warning('TelegramService: API returned error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('TelegramService: Exception during sendMessage', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test connection by sending a welcome test message
     */
    public function testConnection(?string $chatId): array
    {
        $targetChatId = $chatId ?: $this->defaultChatId;

        if (empty($this->token)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token មិនទាន់ត្រូវបានកំណត់ក្នុងប្រព័ន្ធ (.env) ឡើយ!'
            ];
        }

        if (empty($targetChatId)) {
            return [
                'success' => false,
                'message' => 'សូមបញ្ចូល Telegram Chat ID ជាមុនសិន!'
            ];
        }

        $now = now()->setTimezone('Asia/Phnom_Penh')->format('d/m/Y H:i:s');
        $text = "🔔 <b>ការតភ្ជាប់ Telegram Bot ទទួលបានជោគជ័យ!</b>\n\n"
            . "🏛 <b>ប្រព័ន្ធគ្រប់គ្រងឯកសារ និយ័តករអាណាព្យាបាល (TRMS)</b>\n"
            . "📅 កាលបរិច្ឆេទតេស្ត៖ <code>{$now}</code>\n"
            . "✅ លោកអ្នកនឹងទទួលបានការជូនដំណឹងពីលំហូរឯកសារចូលតាមរយៈគណនីនេះ។";

        $success = $this->sendMessage($targetChatId, $text);

        if ($success) {
            return [
                'success' => true,
                'message' => 'សារសាកល្បងត្រូវបានផ្ញើទៅកាន់ Telegram ដោយជោគជ័យ!'
            ];
        }

        return [
            'success' => false,
            'message' => 'មិនអាចផ្ញើសារបានឡើយ! សូមពិនិត្យមើលថាអ្នកបាន Start Bot ក្នុង Telegram រួចរាល់ហើយ ឬនៅ?'
        ];
    }

    /**
     * 1. ជូនដំណឹងពេលចុះបញ្ជីឯកសារចូលថ្មី (New Inbound Document)
     */
    public function notifyNewInbound(InboundDocument $doc): void
    {
        $doc->loadMissing(['registeredByUser']);
        $creator = $doc->registeredByUser ? ($doc->registeredByUser->name_kh ?? $doc->registeredByUser->name) : 'មន្ត្រីចុះបញ្ជី';
        $deadlineText = $doc->deadline ? date('d/m/Y', strtotime($doc->deadline)) : 'គ្មាន';
        $urgencyKh = match ($doc->urgency) {
            'URGENT' => '⚠️ បន្ទាន់',
            'VERY_URGENT' => '🚨 បន្ទាន់បំផុត',
            default => 'ធម្មតា',
        };

        $msg = "📥 <b>ឯកសារចូលថ្មី (ចុះបញ្ជីទូទៅ)</b>\n\n"
            . "🔢 <b>លេខចូលទូទៅ៖</b> <code>{$doc->general_inbound_number}</code>\n"
            . "🏢 <b>ស្ថាប័នបញ្ជូន៖</b> " . htmlspecialchars($doc->sender_organization, ENT_QUOTES, 'UTF-8') . "\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "⚡ <b>កម្រិតបន្ទាន់៖</b> {$urgencyKh}\n"
            . "⏰ <b>កាលកំណត់ (Deadline)៖</b> <code>{$deadlineText}</code>\n"
            . "👤 <b>ចុះបញ្ជីដោយ៖</b> {$creator}\n"
            . "📊 <b>ស្ថានភាព៖</b> " . ($doc->status === 'SUBMITTED_TO_ASSISTANT' ? 'បានបញ្ជូនទៅជំនួយការអគ្គនាយក' : 'ព្រាងនៅកន្លែងទទួល');

        // Send to general channel/group if configured
        $this->sendMessage($this->defaultChatId, $msg);

        // Also notify assistants if any have telegram_chat_id
        $assistants = User::whereHas('permissions', function ($q) {
            $q->whereJsonContains('permissions', 'inbound-documents-assistant');
        })->whereNotNull('telegram_chat_id')->get();

        foreach ($assistants as $assistant) {
            $this->sendMessage($assistant->telegram_chat_id, $msg);
        }
    }

    /**
     * 2. ជំនួយការដាក់ជូនឯកឧត្តមអគ្គនាយក (Submitted to DG)
     */
    public function notifySubmittedToDg(InboundDocument $doc): void
    {
        $doc->loadMissing(['assistant']);
        $assistantName = $doc->assistant ? ($doc->assistant->name_kh ?? $doc->assistant->name) : 'ជំនួយការ';

        $msg = "📝 <b>ឯកសារចូលដាក់ជូនឯកឧត្តមអគ្គនាយកពិនិត្យ</b>\n\n"
            . "👑 <b>លេខចូលអគ្គនាយក៖</b> <code>{$doc->dg_inbound_number}</code>\n"
            . "🔢 <b>លេខចូលទូទៅ៖</b> <code>{$doc->general_inbound_number}</code>\n"
            . "🏢 <b>ស្ថាប័នបញ្ជូន៖</b> " . htmlspecialchars($doc->sender_organization, ENT_QUOTES, 'UTF-8') . "\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "📅 <b>កាលបរិច្ឆេទទទួល៖</b> " . date('d/m/Y', strtotime($doc->dg_received_date ?? now())) . "\n"
            . "👤 <b>ជំនួយការដាក់ជូន៖</b> {$assistantName}";

        // Send to DG users (level = 1) who have telegram_chat_id
        $dgUsers = User::whereHas('position', function ($q) {
            $q->where('level', 1);
        })->whereNotNull('telegram_chat_id')->get();

        foreach ($dgUsers as $dg) {
            $this->sendMessage($dg->telegram_chat_id, $msg);
        }

        // Also broadcast to general group
        $this->sendMessage($this->defaultChatId, $msg);
    }

    /**
     * 3. ជំនួយការ Scan ចំណារ និងចែកចាយ (Dispatched after DG annotation)
     */
    public function notifyDispatched(InboundDocument $doc): void
    {
        $doc->loadMissing(['targetDepartment', 'targetOffice', 'targetUser', 'dispatchedByUser']);

        $targetDesc = 'មិនទាន់បញ្ជាក់';
        if ($doc->target_type === 'OFFICER' && $doc->targetUser) {
            $targetDesc = 'មន្ត្រី៖ ' . ($doc->targetUser->name_kh ?? $doc->targetUser->name);
        } elseif ($doc->target_type === 'OFFICE' && $doc->targetOffice) {
            $targetDesc = 'ការិយាល័យ៖ ' . $doc->targetOffice->name_kh;
        } elseif ($doc->target_type === 'DEPARTMENT' && $doc->targetDepartment) {
            $targetDesc = 'នាយកដ្ឋាន៖ ' . $doc->targetDepartment->name_kh;
        }

        $responseReqKh = $doc->is_response_required ? '🔴 <b>តម្រូវឱ្យឆ្លើយតប</b>' : '🟢 <b>សម្រាប់ជ្រាប</b>';
        $deadlineText = $doc->deadline ? date('d/m/Y', strtotime($doc->deadline)) : 'គ្មាន';
        $annotation = $doc->dg_annotation ? htmlspecialchars($doc->dg_annotation, ENT_QUOTES, 'UTF-8') : 'គ្មាន';

        $msg = "📋 <b>ឯកសារមានចំណារអគ្គនាយកត្រូវបានចែកចាយ</b>\n\n"
            . "👑 <b>លេខចូលអគ្គនាយក៖</b> <code>{$doc->dg_inbound_number}</code>\n"
            . "🏢 <b>ប្រភព៖</b> " . htmlspecialchars($doc->sender_organization, ENT_QUOTES, 'UTF-8') . "\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "✍️ <b>ចំណារអគ្គនាយក៖</b> <i>«{$annotation}»</i>\n"
            . "📌 <b>ប្រភេទចំណារ៖</b> {$responseReqKh}\n"
            . "⏰ <b>កាលកំណត់ (Deadline)៖</b> <code>{$deadlineText}</code>\n"
            . "🎯 <b>ចែកចាយទៅកាន់៖</b> <b>{$targetDesc}</b>";

        // Notify specific target user if assigned
        if ($doc->targetUser && !empty($doc->targetUser->telegram_chat_id)) {
            $this->sendMessage($doc->targetUser->telegram_chat_id, $msg);
        }

        // Also notify department or office heads if target is department/office
        if ($doc->target_type === 'DEPARTMENT' && $doc->target_department_id) {
            $deptHeads = User::where('department_id', $doc->target_department_id)
                ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($deptHeads as $head) {
                $this->sendMessage($head->telegram_chat_id, $msg);
            }
        } elseif ($doc->target_type === 'OFFICE' && $doc->target_office_id) {
            $officeHeads = User::where('office_id', $doc->target_office_id)
                ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7]))
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($officeHeads as $head) {
                $this->sendMessage($head->telegram_chat_id, $msg);
            }
        }

        // Notify general channel
        $this->sendMessage($this->defaultChatId, $msg);
    }

    /**
     * 4. ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម (Forwarded Downward)
     */
    public function notifyForwarded(InboundDocument $doc, User $fromUser, string $targetDesc, ?string $notes = null, ?User $targetUser = null): void
    {
        $fromName = $fromUser->name_kh ?? $fromUser->name;
        $docNumber = $doc->dg_inbound_number ?: $doc->general_inbound_number;
        $notesText = $notes ? htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') : 'គ្មាន';

        $msg = "🔄 <b>ឯកសារត្រូវបានចាត់ចែងបន្ត</b>\n\n"
            . "🔢 <b>លេខឯកសារ៖</b> <code>{$docNumber}</code>\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "👤 <b>ចាត់ចែងដោយ៖</b> {$fromName}\n"
            . "🎯 <b>បញ្ជូនបន្តទៅកាន់៖</b> <b>{$targetDesc}</b>\n"
            . "💬 <b>ការណែនាំ/ចំណាំ៖</b> <i>«{$notesText}»</i>";

        if ($targetUser && !empty($targetUser->telegram_chat_id)) {
            $this->sendMessage($targetUser->telegram_chat_id, $msg);
        }

        // Also if target is department or office, notify their leadership
        if ($doc->target_type === 'DEPARTMENT' && $doc->target_department_id) {
            $deptHeads = User::where('department_id', $doc->target_department_id)
                ->where('id', '!=', $fromUser->id)
                ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($deptHeads as $head) {
                $this->sendMessage($head->telegram_chat_id, $msg);
            }
        } elseif ($doc->target_type === 'OFFICE' && $doc->target_office_id) {
            $officeHeads = User::where('office_id', $doc->target_office_id)
                ->where('id', '!=', $fromUser->id)
                ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7]))
                ->whereNotNull('telegram_chat_id')
                ->get();
            foreach ($officeHeads as $head) {
                $this->sendMessage($head->telegram_chat_id, $msg);
            }
        }
    }

    /**
     * 5. ដាក់ស្នើព្រាងលិខិតឆ្លើយតប (Response Draft Submitted)
     */
    public function notifyResponseSubmitted(InboundDocumentResponse $response, User $drafter, User $targetApprover): void
    {
        $response->loadMissing(['document']);
        $doc = $response->document;
        $docNumber = $doc ? ($doc->dg_inbound_number ?: $doc->general_inbound_number) : 'N/A';
        $drafterName = $drafter->name_kh ?? $drafter->name;

        $msg = "📑 <b>លិខិតឆ្លើយតបថ្មី - ដាក់ស្នើពិនិត្យ</b>\n\n"
            . "🔢 <b>ឯកសារយោង៖</b> <code>{$docNumber}</code>\n"
            . "📝 <b>ចំណងជើងព្រាង៖</b> " . htmlspecialchars($response->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "👤 <b>អ្នករៀបចំ៖</b> {$drafterName}\n"
            . "🎯 <b>ដាក់ជូនលោក/លោកស្រីពិនិត្យ៖</b> " . ($targetApprover->name_kh ?? $targetApprover->name) . "\n"
            . "📊 <b>ដំណាក់កាល៖</b> {$response->current_stage}";

        if (!empty($targetApprover->telegram_chat_id)) {
            $this->sendMessage($targetApprover->telegram_chat_id, $msg);
        }
    }

    /**
     * 6. បញ្ជូនបន្តលិខិតឆ្លើយតបឡើងលើ (Response Forwarded to Next Reviewer)
     */
    public function notifyResponseForwarded(InboundDocumentResponse $response, User $sender, User $nextApprover, ?string $comment = null): void
    {
        $response->loadMissing(['document']);
        $doc = $response->document;
        $docNumber = $doc ? ($doc->dg_inbound_number ?: $doc->general_inbound_number) : 'N/A';
        $senderName = $sender->name_kh ?? $sender->name;
        $commentText = $comment ? htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') : 'គ្មាន';

        $msg = "📑 <b>លិខិតឆ្លើយតប - បញ្ជូនបន្តពិនិត្យ</b>\n\n"
            . "🔢 <b>ឯកសារយោង៖</b> <code>{$docNumber}</code>\n"
            . "📝 <b>ចំណងជើងព្រាង៖</b> " . htmlspecialchars($response->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "👤 <b>បញ្ជូនបន្តដោយ៖</b> {$senderName}\n"
            . "🎯 <b>ដាក់ជូនលោក/លោកស្រីពិនិត្យ៖</b> " . ($nextApprover->name_kh ?? $nextApprover->name) . "\n"
            . "💬 <b>មតិយោបល់៖</b> <i>«{$commentText}»</i>\n"
            . "📊 <b>ដំណាក់កាល៖</b> {$response->current_stage}";

        if (!empty($nextApprover->telegram_chat_id)) {
            $this->sendMessage($nextApprover->telegram_chat_id, $msg);
        }
    }

    /**
     * 7. បញ្ជូនលិខិតឆ្លើយតបត្រឡប់ទៅកែសម្រួល (Response Returned to Drafter)
     */
    public function notifyResponseReturned(InboundDocumentResponse $response, User $reviewer, User $drafter, ?string $comment = null): void
    {
        $response->loadMissing(['document']);
        $doc = $response->document;
        $docNumber = $doc ? ($doc->dg_inbound_number ?: $doc->general_inbound_number) : 'N/A';
        $reviewerName = $reviewer->name_kh ?? $reviewer->name;
        $commentText = $comment ? htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') : 'ត្រូវកែសម្រួលបន្ថែម';

        $msg = "⚠️ <b>លិខិតឆ្លើយតបត្រូវបានបញ្ជូនត្រឡប់ដើម្បីកែសម្រួល</b>\n\n"
            . "🔢 <b>ឯកសារយោង៖</b> <code>{$docNumber}</code>\n"
            . "📝 <b>ចំណងជើងព្រាង៖</b> " . htmlspecialchars($response->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "👤 <b>ពិនិត្យដោយ៖</b> {$reviewerName}\n"
            . "💬 <b>មូលហេតុ/ចំណុចកែសម្រួល៖</b> <i>«{$commentText}»</i>\n"
            . "📌 <b>សូមចូលទៅកែសម្រួល និងដាក់ស្នើឡើងវិញ។</b>";

        if (!empty($drafter->telegram_chat_id)) {
            $this->sendMessage($drafter->telegram_chat_id, $msg);
        }
    }

    /**
     * 8. អគ្គនាយកឯកភាពលើលិខិតឆ្លើយតប (DG Approved Response)
     */
    public function notifyResponseApproved(InboundDocumentResponse $response, User $approver, InboundDocument $doc): void
    {
        $response->loadMissing(['draftedByUser']);
        $drafter = $response->draftedByUser;
        $docNumber = $doc->dg_inbound_number ?: $doc->general_inbound_number;
        $respNum = $response->response_number ? " (លេខ៖ {$response->response_number})" : "";

        $msg = "🎉 <b>ឯកឧត្តមអគ្គនាយកបានឯកភាពលើលិខិតឆ្លើយតប{$respNum}</b>\n\n"
            . "🔢 <b>ឯកសារយោង៖</b> <code>{$docNumber}</code>\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "📝 <b>លិខិតឆ្លើយតប៖</b> " . htmlspecialchars($response->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "✅ <b>ស្ថានភាព៖</b> បានបញ្ចប់ដំណើរការពេញលេញ (COMPLETED)\n"
            . "📅 <b>កាលបរិច្ឆេទឯកភាព៖</b> " . now()->setTimezone('Asia/Phnom_Penh')->format('d/m/Y H:i');

        if ($drafter && !empty($drafter->telegram_chat_id)) {
            $this->sendMessage($drafter->telegram_chat_id, $msg);
        }

        $this->sendMessage($this->defaultChatId, $msg);
    }

    /**
     * 9. ការដាស់តឿនផុតកំណត់ឯកសារចូល (Deadline Alert)
     */
    public function notifyDeadlineAlert(InboundDocument $doc, int $daysRemaining, User $recipient): void
    {
        $docNumber = $doc->dg_inbound_number ?: $doc->general_inbound_number;
        $deadlineText = $doc->deadline ? date('d/m/Y', strtotime($doc->deadline)) : 'N/A';

        if ($daysRemaining < 0) {
            $absDays = abs($daysRemaining);
            $headline = "🚨 <b>ហួសកាលកំណត់ចំនួន {$absDays} ថ្ងៃ! (Overdue Alert)</b>";
        } elseif ($daysRemaining === 0) {
            $headline = "⚠️ <b>ឯកសារផុតកំណត់នៅថ្ងៃនេះ! (Due Today)</b>";
        } else {
            $headline = "⏳ <b>ឯកសារជិតដល់កាលកំណត់ (នៅសល់ {$daysRemaining} ថ្ងៃ)</b>";
        }

        $msg = "{$headline}\n\n"
            . "🔢 <b>លេខឯកសារ៖</b> <code>{$docNumber}</code>\n"
            . "🏢 <b>ប្រភព៖</b> " . htmlspecialchars($doc->sender_organization, ENT_QUOTES, 'UTF-8') . "\n"
            . "📄 <b>កម្មវត្ថុ៖</b> " . htmlspecialchars($doc->title, ENT_QUOTES, 'UTF-8') . "\n"
            . "⏰ <b>កាលកំណត់ (Deadline)៖</b> <code>{$deadlineText}</code>\n"
            . "👤 <b>ទទួលបន្ទុក៖</b> " . ($recipient->name_kh ?? $recipient->name) . "\n"
            . "📌 <b>សូមមេត្តាពន្លឿនការអនុវត្ត ឬរៀបចំលិខិតឆ្លើយតបឱ្យបានទាន់ពេលវេលា!</b>";

        if (!empty($recipient->telegram_chat_id)) {
            $this->sendMessage($recipient->telegram_chat_id, $msg);
        }
    }
}
