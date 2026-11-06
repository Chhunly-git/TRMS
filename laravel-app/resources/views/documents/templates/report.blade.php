<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<title>{{ $doc->title ?? 'របាយការណ៍' }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Kantumruy+Pro:ital,wght@0,400;0,600;0,700;1,400&family=Moul&display=swap');

@page {
    size: A4 portrait;
    margin: 0;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Kantumruy Pro', 'Battambang', sans-serif;
    color: #000;
    background: #eef2f5;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.a4-page {
    width: 210mm;
    min-height: 297mm;
    margin: 15px auto;
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    padding: 16mm 20mm 20mm 25mm; /* Official Cambodian Margins: Top 16mm, Right 20mm, Bottom 20mm, Left 25mm */
    position: relative;
    overflow: hidden;
    page-break-after: always;
    break-after: page;
}

@media print {
    body {
        background: transparent;
    }
    .a4-page {
        margin: 0;
        box-shadow: none;
        width: 100%;
        height: 100%;
    }
}

/* ==================== ZONE 1: HEADER ==================== */
.header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.header-left-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    width: 220px;
}
.header-logo-img {
    width: 72px;
    height: 72px;
    object-fit: contain;
    margin-bottom: 4px;
}
.header-org-name {
    font-family: 'Moul', cursive;
    font-size: 12px;
    color: #1b5e20;
    line-height: 1.35;
    white-space: nowrap;
}
.header-doc-number {
    font-size: 12.5px;
    font-weight: 600;
    margin-top: 3px;
    white-space: nowrap;
}
.header-right-col {
    text-align: center;
    width: 260px;
    padding-top: 5px;
}
.kingdom-title {
    font-family: 'Moul', cursive;
    font-size: 14.5px;
    color: #1b5e20;
    margin-bottom: 3px;
}
.kingdom-motto {
    font-family: 'Moul', cursive;
    font-size: 13px;
    color: #1b5e20;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.kingdom-stars {
    font-size: 11px;
    color: #1b5e20;
    letter-spacing: 3px;
}
.recipient-center-block {
    text-align: center;
    margin: 8px 0 16px 0;
}
.recipient-honorific {
    font-family: 'Moul', cursive;
    font-size: 13px;
    margin-bottom: 2px;
}
.recipient-title {
    font-family: 'Moul', cursive;
    font-size: 13px;
    line-height: 1.4;
}

/* ==================== ZONE 2: SUBJECT & REFERENCES ==================== */
.meta-block {
    margin-bottom: 14px;
}
.meta-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 5px;
    font-size: 13.5px;
    line-height: 1.55;
}
.meta-label-fixed {
    width: 1.5cm;
    min-width: 1.5cm;
    font-weight: 700;
    white-space: nowrap;
}
.meta-content-aligned {
    flex: 1;
}
.meta-content-aligned.title-bold {
    font-weight: 700;
}
.ref-list {
    list-style: none;
}
.ref-list li {
    margin-bottom: 2px;
}

/* ==================== ZONE 3: BODY ==================== */
.doc-body {
    font-size: 13.5px;
    line-height: 1.6;
    text-align: justify;
    margin-bottom: 12px;
}
.doc-body p {
    text-indent: 1.5cm;
    margin-bottom: 8px;
}

/* ==================== ZONE 4: KEEP TOGETHER BLOCK ==================== */
.keep-together {
    page-break-inside: avoid;
    break-inside: avoid;
    margin-top: 10px;
}
.closing-paras p {
    text-indent: 1.5cm;
    font-size: 13.5px;
    line-height: 1.6;
    margin-bottom: 6px;
    text-align: justify;
}

/* STRICT TWO-COLUMN SIGNATURE ZONE */
.signatures-main-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-top: 10px;
    gap: 15px;
}
.leaders-opinion-area {
    width: 50%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.opinion-boxes-subgrid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.current-opinion-box {
    flex: 1 1 calc(50% - 10px);
    min-width: 130px;
    text-align: center;
    padding: 2px 4px;
    font-size: 11px;
    line-height: 1.4;
}
.current-opinion-box.full-width {
    flex: 1 1 100%;
}
.opinion-text {
    font-size: 10.5px;
    color: #000;
    margin-bottom: 3px;
    white-space: pre-line;
    word-break: break-word;
}
.opinion-sig-area {
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 2px 0;
}
.opinion-sig-area img {
    height: 42px;
    max-width: 150px;
    object-fit: contain;
}
.opinion-short-date {
    font-size: 10px;
    color: #111;
    margin-top: 1px;
}
.opinion-signer-name {
    font-family: 'Moul', cursive;
    font-size: 11px;
    color: #000;
    margin-top: 2px;
}

/* RIGHT 50%: Drafter Block ONLY (Space above lunar date is 100% blank) */
.drafter-official-block {
    width: 50%;
    text-align: center;
    padding-left: 10px;
}
.lunar-date-text {
    font-size: 11px;
    font-style: italic;
    color: #222;
}
.solar-date-text {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 3px;
}
.drafter-dept-text {
    font-family: 'Moul', cursive;
    font-size: 11px;
    color: #1b5e20;
}
.drafter-office-text {
    font-family: 'Moul', cursive;
    font-size: 10px;
    color: #1b5e20;
    margin-bottom: 2px;
}
.drafter-role-text {
    font-size: 11.5px;
    font-weight: 600;
}
.drafter-sig-img {
    height: 48px;
    object-fit: contain;
    margin: 3px 0;
}
.drafter-name-text {
    font-family: 'Moul', cursive;
    font-size: 12px;
}

/* ==================== ZONE 5: FOOTER (Page 1 Only - HTML/CSS) ==================== */
.page-1-footer {
    position: absolute;
    bottom: 8mm;
    left: 25mm;
    right: 20mm;
}
.footer-divider-line {
    width: 100%;
    height: 1.5px;
    background: #1b5e20;
    margin-bottom: 5px;
}
.footer-content-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    font-size: 8px;
    line-height: 1.35;
    color: #1b5e20;
}
.footer-address-col {
    flex: 1.25;
}
.footer-addr-kh {
    font-size: 8px;
    font-weight: 600;
    color: #1b5e20;
    margin-bottom: 2px;
}
.footer-addr-en {
    font-size: 7px;
    color: #555;
}
.footer-qr-col {
    display: flex;
    align-items: center;
    gap: 6px;
}
.footer-qr-box {
    width: 36px;
    height: 36px;
    border: 1px dashed #1b5e20;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
}
.footer-contact-col {
    flex: 0.95;
    text-align: right;
    font-size: 7.5px;
    color: #1b5e20;
}
.footer-contact-row {
    margin-bottom: 1.5px;
    white-space: nowrap;
}

/* ==================== ZONE 6: PAGE NUMBERS ==================== */
/* Page 1 has NO page number. Starts on Page 2+ */
.page-num-other {
    position: absolute;
    bottom: 12mm;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 12.5px;
    font-weight: 600;
}
</style>
</head>
<body>

@php
    $totalPages = count($pages);
@endphp

@foreach($pages as $index => $pageHtml)
    <div class="a4-page page-{{ $index + 1 }}">
        @if($index === 0)
            {{-- ZONE 1: HEADER (Page 1 Only) --}}
            <div class="header-row">
                <div class="header-left-col">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="header-logo-img" alt="Logo">
                    @endif
                    <div class="header-org-name">{{ $doc->header_organization ?? 'និយ័តករបរធនបាលកិច្ច' }}</div>
                    <div class="header-doc-number">
                        លេខ ៖ {{ $doc->response_number ?? '......... ន.ប.ធ./' . ($doc->number_suffix ?? '') }}
                    </div>
                </div>

                <div class="header-right-col">
                    <div class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</div>
                    <div class="kingdom-motto">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
                    <div class="kingdom-stars">៚ ៚ ៚</div>
                </div>
            </div>

            <div class="recipient-center-block">
                <div class="recipient-honorific">សូមគោរពជូន</div>
                <div class="recipient-title">{{ $doc->recipient_text ?? 'ឯកឧត្តមអគ្គនាយក នៃនិយ័តករបរធនបាលកិច្ច' }}</div>
            </div>

            {{-- ZONE 2: SUBJECT & REFERENCES --}}
            <div class="meta-block">
                <div class="meta-row">
                    <div class="meta-label-fixed">កម្មវត្ថុ ៖</div>
                    <div class="meta-content-aligned title-bold">{{ $doc->title }}</div>
                </div>
                @if(!empty($doc->references) && is_array($doc->references) && count($doc->references) > 0)
                    <div class="meta-row">
                        <div class="meta-label-fixed">យោង ៖</div>
                        <div class="meta-content-aligned">
                            <ul class="ref-list">
                                @foreach($doc->references as $ref)
                                    <li>- {{ $ref }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ZONE 3: BODY FOR THIS PAGE --}}
        <div class="doc-body">
            {!! $pageHtml !!}
        </div>

        {{-- ZONE 4: KEEP-TOGETHER BLOCK (Rendered on the final page) --}}
        @if($index === $totalPages - 1)
            <div class="keep-together">
                <div class="closing-paras">
                    <p>{{ $doc->closing_phrase_1 ?? 'សេចក្តីដូចបានគោរពរាយការណ៍ជូនខាងលើ សូម ឯកឧត្តមអគ្គនាយក មេត្តាពិនិត្យ និងផ្តល់ការណែនាំដ៏ខ្ពង់ខ្ពស់។' }}</p>
                    <p>{{ $doc->closing_phrase_2 ?? 'សូម ឯកឧត្តមអគ្គនាយក មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់អំពីខ្ញុំ។' }}</p>
                </div>

                <div class="signatures-main-row">
                    {{-- Left Half: Leaders' Opinions Grid --}}
                    <div class="leaders-opinion-area">
                        <div class="opinion-boxes-subgrid">
                            @foreach($opinions ?? [] as $box)
                                <div class="current-opinion-box slot-{{ $box['slot'] ?? '' }}">
                                    <div class="opinion-text">{{ $box['comment'] ?? '' }}</div>
                                    <div class="opinion-sig-area">
                                        @if(!empty($box['signature_url']))
                                            <img src="{{ $box['signature_url'] }}" alt="ហត្ថលេខា">
                                        @endif
                                    </div>
                                    <div class="opinion-short-date">{{ $box['short_date_kh'] ?? '' }}</div>
                                    <div class="opinion-signer-name">{{ $box['signer_name_kh'] ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Right Half: Drafter Block ONLY --}}
                    <div class="drafter-official-block">
                        <div class="lunar-date-text">{{ $doc->lunar_date_kh ?? 'ថ្ងៃ................... ខែ............. ឆ្នាំ........ ព.ស.២៥៦៨' }}</div>
                        <div class="solar-date-text">{{ $doc->solar_date_kh ?? App\Helpers\KhmerDateHelper::toKhmerSolarDate($doc->created_at) }}</div>
                        <div class="drafter-dept-text">{{ $doc->department->name_kh ?? '' }}</div>
                        @if(!empty($doc->office->name_kh))
                            <div class="drafter-office-text">{{ $doc->office->name_kh }}</div>
                        @endif
                        <div class="drafter-role-text">{{ $drafterRole ?? 'មន្ត្រីទទួលបន្ទុក' }}</div>
                        @if(!empty($drafterSignatureUrl))
                            <img src="{{ $drafterSignatureUrl }}" class="drafter-sig-img" alt="ហត្ថលេខាអ្នកបង្កើត">
                        @endif
                        <div class="drafter-name-text">{{ $doc->draftedByUser->name_kh ?? $doc->draftedByUser->name ?? '' }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ZONE 5: FOOTER (Page 1 Only - Pure HTML/CSS) --}}
        @if($index === 0)
            <div class="page-1-footer">
                <div class="footer-divider-line"></div>
                <div class="footer-content-row">
                    <div class="footer-address-col">
                        <div class="footer-addr-kh">អគារ ១៦៨F (ជាន់ទី៨) ផ្លូវ ៥៩៨ សង្កាត់ច្រាំងចំរេះទី១ ខណ្ឌឫស្សីកែវ រាជធានីភ្នំពេញ ព្រះរាជាណាចក្រកម្ពុជា</div>
                        <div class="footer-addr-en">Building 168F (8th Floor), Street 598, Sangkat Chrang Chamres 1, Khan Russey Keo, Phnom Penh, Kingdom of Cambodia</div>
                    </div>
                    <div class="footer-qr-col">
                        <div class="footer-qr-box">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="#1b5e20">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v4h-4v-4zm-4 4h4v4h-4v-4zm4 4h4v4h-4v-4zm-4-8h4v4h-4v-4z"/>
                            </svg>
                        </div>
                        <div class="footer-qr-box">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="#1b5e20">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v4h-4v-4zm-4 4h4v4h-4v-4zm4 4h4v4h-4v-4zm-4-8h4v4h-4v-4z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="footer-contact-col">
                        <div class="footer-contact-row"><strong>ទូរសព្ទ ៖</strong> (+៨៥៥) ២៣-៦៣៧ ៨៨៨</div>
                        <div class="footer-contact-row"><strong>សារអេឡិចត្រូនិច ៖</strong> info@trustregulator.gov.kh</div>
                        <div class="footer-contact-row"><strong>គេហទំព័រ ៖</strong> www.trustregulator.gov.kh</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ZONE 6: PAGE NUMBERS (Page 1 has NO page number. Starts on Page 2+) --}}
        @if($index > 0)
            <div class="page-num-other">- {{ App\Helpers\KhmerDateHelper::toKhmerDigits($index + 1) }} -</div>
        @endif
    </div>
@endforeach

</body>
</html>
