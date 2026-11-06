<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InboundDocumentResponse;
use App\Models\User;
use App\Services\HtmlSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_sanitizer_removes_scripts_and_event_handlers(): void
    {
        $dirty = '<p>Safe Text</p><script>alert("XSS")</script><img src="x" onerror="alert(1)">';
        $clean = HtmlSanitizerService::clean($dirty);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('alert', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('Safe Text', $clean);
    }

    public function test_html_sanitizer_removes_data_uris_and_external_images(): void
    {
        $dirty = '<p>Image test</p><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" alt="data"><img src="http://evil-tracker.com/pixel.png" alt="external"><img src="/api/files?path=users/signatures/test.png" alt="local">';
        $clean = HtmlSanitizerService::clean($dirty);

        // data: URI is stripped
        $this->assertStringNotContainsString('data:image', $clean);
        // external image URL is stripped
        $this->assertStringNotContainsString('evil-tracker.com', $clean);
        // local relative URL is preserved
        $this->assertStringContainsString('/api/files?path=users/signatures/test.png', $clean);
    }

    public function test_html_sanitizer_restricts_css_properties(): void
    {
        $dirty = '<div style="color: red; text-align: center; font-size: 14px; background-image: url(http://attacker.com/leak); behavior: url(test.htc);">Content</div>';
        $clean = HtmlSanitizerService::clean($dirty);

        $this->assertMatchesRegularExpression('/color:\s*(#FF0000|red)/i', $clean);
        $this->assertStringContainsString('text-align:center', str_replace(' ', '', $clean));
        // forbidden CSS properties stripped
        $this->assertStringNotContainsString('background-image', $clean);
        $this->assertStringNotContainsString('behavior', $clean);
        $this->assertStringNotContainsString('attacker.com', $clean);
    }

    public function test_html_sanitizer_preserves_opinion_box_position_and_offsets(): void
    {
        $dirty = '<div class="draggable-opinion-box" data-draggable="true" data-owner-id="5" data-offset-x="45" data-offset-y="-15" style="width: 240px;">Opinion Box</div>';
        $clean = HtmlSanitizerService::clean($dirty);

        $this->assertStringContainsString('data-offset-x="45"', $clean);
        $this->assertStringContainsString('data-offset-y="-15"', $clean);
        $this->assertStringContainsString('data-draggable="true"', $clean);
    }

    public function test_html_sanitizer_preserves_composer_document_markers(): void
    {
        $dirty = '<div class="lc-doc" data-doc-v="2" data-doc-type="REPORT">'
            . '<div class="lc-header" data-region="header">'
            . '<span data-sys="doc-number">.......</span>'
            . '<img src="/logo.png" alt="Logo" class="lc-logo">'
            . '</div>'
            . '<table class="lc-subj" data-region="subject">'
            . '<tr><td class="lc-lbl">កម្មវត្ថុ</td><td data-field="subject">របាយការណ៍ស្តីពីការងារ</td></tr>'
            . '</table>'
            . '<div class="lc-body" data-region="body"><p>ខ្លឹមសាររបាយការណ៍</p></div>'
            . '</div>';

        $clean = HtmlSanitizerService::clean($dirty);

        $this->assertStringContainsString('data-doc-v="2"', $clean);
        $this->assertStringContainsString('data-doc-type="REPORT"', $clean);
        $this->assertStringContainsString('data-region="header"', $clean);
        $this->assertStringContainsString('data-sys="doc-number"', $clean);
        $this->assertStringContainsString('data-region="subject"', $clean);
        $this->assertStringContainsString('data-field="subject"', $clean);
        $this->assertStringContainsString('data-region="body"', $clean);
        $this->assertStringContainsString('src="/logo.png"', $clean);
    }

    public function test_content_save_via_api_automatically_sanitizes_html(): void
    {
        $dept = Department::create(['code' => 'D1', 'name_kh' => 'នាយកដ្ឋាន ១', 'name_en' => 'Dept 1']);
        $drafter = User::factory()->create([
            'department_id' => $dept->id,
            'level' => 'OFFICER',
            'status' => 'ACTIVE',
        ]);

        $responseDoc = InboundDocumentResponse::create([
            'response_type' => 'REPORT',
            'title' => 'Report with XSS attempt',
            'drafted_by' => $drafter->id,
            'department_id' => $dept->id,
            'content' => '<p>Initial</p>',
            'status' => 'DRAFT',
        ]);

        Sanctum::actingAs($drafter);

        $dirtyPayload = '<p>Valid text</p><script>alert("hacked")</script><img src="data:image/jpeg;base64,123"><img src="http://external.com/pic.jpg"><img src="/api/files?path=users/signatures/local.png">';

        $res = $this->patchJson("/api/outbound-documents/{$responseDoc->id}/content", [
            'content' => $dirtyPayload,
        ]);
        $res->assertStatus(200);

        // Fetch directly from DB to verify raw storage is sanitized
        $rawContent = \DB::table('inbound_document_responses')->where('id', $responseDoc->id)->value('content');
        $this->assertStringNotContainsString('<script>', $rawContent);
        $this->assertStringNotContainsString('hacked', $rawContent);
        $this->assertStringNotContainsString('data:image', $rawContent);
        $this->assertStringNotContainsString('external.com', $rawContent);
        $this->assertStringContainsString('Valid text', $rawContent);
        $this->assertStringContainsString('data-file-path="users/signatures/local.png"', $rawContent);
        $this->assertStringNotContainsString('/api/files', $rawContent);
    }
}
