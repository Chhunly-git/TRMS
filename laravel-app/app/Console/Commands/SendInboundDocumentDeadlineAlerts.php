<?php

namespace App\Console\Commands;

use App\Models\InboundDocument;
use App\Models\User;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendInboundDocumentDeadlineAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbound-documents:send-deadline-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Telegram alerts for inbound documents with upcoming deadlines (<= 3 days) or overdue';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        $this->info('Checking for inbound document deadline alerts...');

        $today = Carbon::today();
        $threeDaysLater = Carbon::today()->addDays(3);

        // Fetch documents that have a deadline and are still active
        $documents = InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $threeDaysLater->toDateString())
            ->with(['targetUser', 'targetDepartment', 'targetOffice', 'assistant'])
            ->get();

        $count = 0;

        foreach ($documents as $doc) {
            $deadline = Carbon::parse($doc->deadline)->startOfDay();
            $daysRemaining = (int) $today->diffInDays($deadline, false);

            $recipients = collect();

            if ($doc->targetUser) {
                $recipients->push($doc->targetUser);
            } elseif ($doc->target_office_id) {
                $officeHeads = User::where('office_id', $doc->target_office_id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7]))
                    ->whereNotNull('telegram_chat_id')
                    ->get();
                $recipients = $recipients->merge($officeHeads);
            } elseif ($doc->target_department_id) {
                $deptHeads = User::where('department_id', $doc->target_department_id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->whereNotNull('telegram_chat_id')
                    ->get();
                $recipients = $recipients->merge($deptHeads);
            }

            // If still no recipients with chat id, fallback to assistant or registered user
            if ($recipients->isEmpty()) {
                if ($doc->assistant && $doc->assistant->telegram_chat_id) {
                    $recipients->push($doc->assistant);
                }
            }

            foreach ($recipients as $recipient) {
                $telegram->notifyDeadlineAlert($doc, $daysRemaining, $recipient);
                $count++;
            }
        }

        $this->info("Completed sending {$count} deadline alert(s).");
        return Command::SUCCESS;
    }
}
