<?php

namespace App\Services;

use App\Helpers\KhmerDateHelper;
use App\Models\InboundDocumentResponse;
use App\Models\ResponseOpinionPlacement;
use Illuminate\Support\Facades\View;

class DocumentRenderService
{
    /**
     * Render the document into full HTML.
     */
    public function renderHtml(InboundDocumentResponse $doc, array $options = []): string
    {
        // 1. Backward compatibility: if legacy monolithic v1, expand signed signatures and return content
        if (($doc->format_version ?? 1) === 1 && !empty($doc->content) && empty($doc->body_html)) {
            return FileUrlService::expandContentForRender($doc->content, $doc);
        }

        // 2. Structured document (format_version === 2)
        // Clean & sanitize body_html
        $expandedBodyHtml = FileUrlService::expandContentForRender($doc->body_html ?? '<p></p>', $doc);

        // Split into pages if page break markers exist
        $pages = $this->splitBodyIntoPages($expandedBodyHtml);

        // Get drafter signature
        $drafter = $doc->draftedByUser;
        $drafterSigUrl = '';
        if ($drafter && !empty($drafter->signature_image)) {
            $drafterSigUrl = FileUrlService::signed($drafter->signature_image);
        }

        // Get drafter role
        $drafterRole = $doc->sign_title ?? ($drafter && $drafter->position ? ($drafter->position->title_kh ?? 'មន្ត្រីទទួលបន្ទុក') : 'មន្ត្រីទទួលបន្ទុក');

        // Logo base64
        $logoBase64 = $this->getLogoBase64();

        // Collect opinions with their assigned slots
        $opinions = $this->getOpinionsData($doc);

        return View::make('documents.templates.report', [
            'doc' => $doc,
            'pages' => $pages,
            'opinions' => $opinions,
            'drafterSignatureUrl' => $drafterSigUrl,
            'drafterRole' => $drafterRole,
            'logoBase64' => $logoBase64,
        ])->render();
    }

    /**
     * Split body HTML into pages based on page break dividers.
     */
    public function splitBodyIntoPages(string $bodyHtml): array
    {
        if (empty($bodyHtml)) {
            return ['<p></p>'];
        }

        // Standard page break separators (TipTap / Word / CSS pagebreak)
        $pattern = '/<!--\s*pagebreak\s*-->|<div[^>]*class="[^"]*page-break[^"]*"[^>]*>.*?<\/div>|<hr[^>]*class="[^"]*page-break[^"]*"[^>]*\/?>/iu';
        $parts = preg_split($pattern, $bodyHtml);

        if (!$parts || count($parts) === 0) {
            return [$bodyHtml];
        }

        // Filter out empty parts or re-wrap
        $pages = array_map(function ($part) {
            $trimmed = trim($part);
            return empty($trimmed) ? '<p></p>' : $trimmed;
        }, $parts);

        return array_values($pages);
    }

    /**
     * Retrieve opinions for this document mapped to their slots.
     */
    public function getOpinionsData(InboundDocumentResponse $doc): array
    {
        $placements = ResponseOpinionPlacement::where('response_id', $doc->id)
            ->with(['approval', 'user'])
            ->orderBy('order_seq', 'asc')
            ->get()
            ->keyBy('approval_id');

        $opinions = [];
        $seq = 1;

        // Iterate through all approvals that have opinions/signatures
        $approvals = $doc->approvals()
            ->with('user')
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($approvals as $approval) {
            $user = $approval->user;
            if (!$user) {
                continue;
            }

            // Check if slot already exists in placements
            $placement = $placements->get($approval->id);
            $slot = $placement ? $placement->slot : "OPINION_LEFT_{$seq}";

            // Signature URL
            $sigPath = $approval->signature_snapshot_path ?: $user->signature_image;
            $sigUrl = $sigPath ? FileUrlService::signed($sigPath) : '';

            $opinions[] = [
                'approval_id' => $approval->id,
                'user_id' => $user->id,
                'signer_name_kh' => $user->name_kh ?: $user->name,
                'role_title' => $user->position->title_kh ?? 'ថ្នាក់ដឹកនាំ',
                'comment' => $approval->comment,
                'short_date_kh' => KhmerDateHelper::toKhmerShortDate($approval->created_at),
                'signature_url' => $sigUrl,
                'slot' => $slot,
                'can_move' => auth()->check() && auth()->id() === $user->id && $doc->status !== 'COMPLETED',
            ];

            $seq++;
        }

        return $opinions;
    }

    /**
     * Helper to get logo base64.
     */
    private function getLogoBase64(): string
    {
        $logoPath = public_path('logo.png');
        if (file_exists($logoPath)) {
            $data = file_get_contents($logoPath);
            return 'data:image/png;base64,' . base64_encode($data);
        }

        return '';
    }
}
