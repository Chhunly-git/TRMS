<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnicodeCorrection
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->all();

        $data = $this->replaceUnicodeRecursive($data);

        $request->replace($data);

        return $next($request);
    }

    private function replaceUnicodeRecursive($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceUnicodeRecursive($value);
            }
        } else {
            // Only process strings that are NOT base64/encoded
            if (is_string($data) && !$this->isSpecialString($data)) {
                $data = $this->replaceUnicode($data);
            }
        }
        return $data;
    }

    private function isSpecialString($string)
    {
        $trimmed = trim($string);

        // Skip empty strings
        if (empty($trimmed)) {
            return false;
        }

        // Skip JSON strings
        if ($trimmed[0] === '{' || $trimmed[0] === '[') {
            return true;
        }

        // Check for base64 pattern (mostly alphanumeric + / + = for padding)
        if (!preg_match('/^[a-zA-Z0-9+\/]*={0,2}$/', $string)) {
            return false;
        }

        // Check if it's valid base64 by decoding and re-encoding
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return false;
        }

        // If re-encoded matches original, it's likely base64
        if (base64_encode($decoded) === $string) {
            return true;
        }

        return false;
    }

    private function replaceUnicode($text)
    {
        $text = trim($text);

        // ✅ លុប literal string '\x{200B}' ឬ '\u200b' ដែលធ្លាប់ច្រឡំបង្កើតពីមុន
        $text = str_ireplace(['\x{200b}', '\x{200B}', '\u200b', '\u200B'], '', $text);

        // ✅ លុប Zero-Width Space (U+200B) និង Zero-Width No-Break Space (U+FEFF) ដែលបង្កបញ្ហាក្នុងអក្សរខ្មែរ
        $text = preg_replace('/[\x{200B}\x{FEFF}]+/u', '', $text);

        // ✅ បង្រួម Space ច្រើនជាន់ឱ្យនៅត្រឹម Space ' ' មួយធម្មតា
        $text = preg_replace('/\s+/u', ' ', $text);

        $salabpi = ['ង', 'ញ', 'ប', 'ម', 'យ', 'រ', 'វ'];
        $treysab = ['ស', 'ហ', 'អ'];
        $chars = array_merge($salabpi, $treysab);
        $vowels = ['ិ', 'ី', 'ឹ', 'ឺ', 'ើ'];

        // Direct replacements (Khmer Unicode Normalization)
        $text = str_replace('្' . 'ដ', '្' . 'ត', $text);
        $text = str_replace('ា' . 'ំ', 'ាំ', $text);
        $text = str_replace('េ' . 'ី', 'ើ', $text);
        $text = str_replace('េ' . 'ា', 'ោ', $text);
        $text = str_replace('េ' . 'ះ', 'េះ', $text);
        $text = str_replace('ោ' . 'ះ', 'ោះ', $text);
        $text = str_replace('េ' . 'ុ' . 'ី', 'ុ' . 'ើ', $text);

        foreach ($chars as $char) {
            foreach ($vowels as $vowel) {
                if (in_array($char, $salabpi)) {
                    $replacementSign = '៉';
                } elseif (in_array($char, $treysab)) {
                    $replacementSign = '៊';
                } else {
                    continue;
                }
                $word = $char . 'ុ' . $vowel;
                $replacement = $char . $replacementSign . $vowel;
                $text = str_replace($word, $replacement, $text);
            }
        }
        return $text;
    }
}