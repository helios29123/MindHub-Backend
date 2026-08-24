<?php

namespace App\Services\Moderation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModeratorService
{
    /**
     * Danh sách từ khóa thô tục, xúc phạm, lăng mạ tiếng Việt & tiếng Anh
     */
    private array $profanityList = [
        // Chửi bới, xúc phạm nặng
        'địt', 'đụ', 'đụ má', 'địt mẹ', 'đm', 'dcm', 'đcm', 'vcl', 'vkl', 'vcc',
        'cặc', 'lồn', 'buồi', 'chó đẻ', 'thằng chó', 'con điếm', 'đĩ', 'mẹ mày',
        'bố mày', 'óc chó', 'não tàn', 'ngu lol', 'ngu lồn', 'mất dạy', 'khốn nạn',
        'chó ngu', 'đồ ngu', 'thằng ngu', 'con ngu', 'óc heo', 'đồ chó', 'con chó', 'thằng chó',
        'đồ rác rưởi', 'chết tiệt', 'súc vật', 'đéo', 'đek', 'đéo hiểu', 'fuck',
        'bitch', 'asshole', 'dick', 'pussy', 'bastard', 'motherfucker',
        // Viết tắt, teencode lách luật & tiếng lóng
        'clmm', 'dkm', 'đkm', 'đmm', 'cc', 'clgt', 'vl', 'v~l', 'v.l', 'đ.m', 'd.m',
        'cak', 'cax', 'kac', 'kặc', 'cac', 'đb', 'đầu buồi', 'dau buoi', 'con cặc', 'con cac',
        'như cak', 'nhu cak', 'như cc', 'nhu cc', 'như cặc', 'nhu cac', 'như cl', 'nhu cl',
        'như loz', 'nhu loz', 'như lol', 'nhu lol', 'như lồn', 'nhu lon', 'như qq', 'nhu qq',
        'loz', 'l0n', 'l*n', 'c*k', 'c.a.k',
        // Phân biệt, thù ghét
        'bắc kỳ', 'nam kỳ', '3 que', 'ba que', 'bò đỏ', 'bọn mọi'
    ];

    /**
     * Danh sách biểu thức chính quy nhận diện từ ngữ tục tĩu có che dấu sao (*), ký tự đặc biệt hoặc teencode
     */
    private array $maskedProfanityPatterns = [
        // English Wildcard / Asterisk / Leet masking
        '/\b(f[\W_0-9*]*[u*o0]?[\W_0-9*]*[c*k][\W_0-9*]*[k*c]?)\b/ui' => 'Từ ngữ tục tĩu (f*ck/f*c/fuck)',
        '/\b(b[\W_0-9*]*[i*1!e][\W_0-9*]*[t*][\W_0-9*]*[c*][\W_0-9*]*[h*])\b/ui' => 'Từ ngữ xúc phạm (b*tch/bitch)',
        '/\b(d[\W_0-9*]*[i*1!][\W_0-9*]*[c*][\W_0-9*]*[k*])\b/ui' => 'Từ ngữ thô tục (d*ck)',
        '/\b(p[\W_0-9*]*[u*][\W_0-9*]*[s*5$][\W_0-9*]*[s*5$][\W_0-9*]*[y*])\b/ui' => 'Từ ngữ thô tục (pussy)',
        '/\b(s[\W_0-9*]*[h*][\W_0-9*]*[i*1!][\W_0-9*]*[t*])\b/ui' => 'Từ ngữ thô tục (sh*t)',

        // Vietnamese Wildcard / Asterisk / Teencode masking & Insults
        '/(^|\s|[.,!?;:()~#@\-_])(chó|cho)[\W_0-9*]*(ngu|dốt|lợn|heo|đẻ)($|\s|[.,!?;:()~#@\-_])/ui' => 'Xúc phạm, lăng mạ (chó ngu/chó đẻ)',
        '/(^|\s|[.,!?;:()~#@\-_])(thằng|con|đồ|bọn)[\W_0-9*]*(chó|ngu|điếm|rác|súc vật|bần)($|\s|[.,!?;:()~#@\-_])/ui' => 'Xúc phạm, lăng mạ (thằng chó/đồ ngu/con chó)',
        '/(^|\s|[.,!?;:()~#@\-_])([dđ][\W_0-9*]*[i*1!][\W_0-9*]*[t*])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ thô tục (đ*t/địt)',
        '/(^|\s|[.,!?;:()~#@\-_])([dđ][\W_0-9*]*[u*ụ][\W_0-9*]*[m*má]?)/ui' => 'Từ ngữ thô tục (đ*u/đụ má)',
        '/(^|\s|[.,!?;:()~#@\-_])([dđ][\W_0-9*]*[m*])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ tục tĩu (đm/đ.m/d.m/d*m)',
        '/(^|\s|[.,!?;:()~#@\-_])([c*k][\W_0-9*]*[a*4@ăặâ]?[\W_0-9*]*[c*k|x])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ thô tục (cak/c*k/c*c/cặc/kac)',
        '/(^|\s|[.,!?;:()~#@\-_])([l*][\W_0-9*]*[o*0ôồõ]?[\W_0-9*]*[n*z])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ thô tục (l*n/l0n/lồn/loz)',
        '/(^|\s|[.,!?;:()~#@\-_])([c*k][\W_0-9*]*[c*k])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ thô tục (cc/c*c/k*k)',
        '/(^|\s|[.,!?;:()~#@\-_])([v*][\W_0-9*]*[c*k]?[\W_0-9*]*[l*])($|\s|[.,!?;:()~#@\-_])/ui' => 'Từ ngữ thô tục (vl/vcl/vkl/v*l)',
    ];

    /**
     * Danh sách từ khóa cờ bạc, lừa đảo, mua bán tài khoản lậu
     */
    private array $scamAndSpamList = [
        'kéo tài xỉu', 'tài xỉu', 'kubet', 'thabet', 'w88', 'fun88', '88bet', 'nhà cái',
        'nổ hũ', 'bắn cá đổi thưởng', 'hack game', 'kéo rank',
        'share acc', 'học lậu', 'tải lậu', 'bán tài khoản', 'mua nick', 'share tài khoản',
        'nhận làm hộ đồ án', 'chạy điểm', 'tiền ảo', 'đầu tư sinh lời', 'việc nhẹ lương cao'
    ];

    /**
     * Quét tự động toàn diện nội dung (Hybrid: Rule-based NLP + Gemini AI nếu có)
     *
     * @param string $content Nội dung bình luận / đánh giá
     * @return array Kết quả kiểm duyệt chi tiết
     */
    public function inspect(string $content): array
    {
        $text = trim($content);
        if (empty($text)) {
            return [
                'is_violating' => false,
                'risk_score' => 0.0,
                'categories' => [],
                'reason' => 'Nội dung rỗng',
                'suggested_status' => 'visible',
            ];
        }

        // TẦNG 1: Quét nhanh bằng Rule-Based & Regex Tiếng Việt chuẩn hóa (0ms, 100% Free)
        $ruleResult = $this->scanWithRules($text);

        // Nếu tầng 1 đã phát hiện vi phạm nghiêm trọng (risk_score >= 0.8), kết luận và ẩn ngay lập tức
        if ($ruleResult['risk_score'] >= 0.8) {
            $ruleResult['suggested_status'] = 'hidden';
            return $ruleResult;
        }

        // TẦNG 2: Gọi AI Gemini phân tích ngữ nghĩa sâu cho mọi câu bình luận
        $geminiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (!empty($geminiKey) && mb_strlen($text, 'UTF-8') >= 2) {
            $aiResult = $this->scanWithGemini($text, $geminiKey);
            if ($aiResult !== null) {
                // Kết hợp điểm số giữa Tầng 1 và Tầng 2
                $finalScore = max($ruleResult['risk_score'], $aiResult['risk_score']);
                $finalCategories = array_unique(array_merge($ruleResult['categories'], $aiResult['categories']));
                $isViolating = $finalScore >= 0.7;

                return [
                    'is_violating' => $isViolating,
                    'risk_score' => $finalScore,
                    'categories' => $finalCategories,
                    'reason' => $aiResult['reason'] ?: ($ruleResult['reason'] ?: 'Hợp lệ'),
                    'suggested_status' => $isViolating ? 'hidden' : 'visible',
                    'moderated_by' => 'ai_gemini',
                ];
            }
        }

        // Mặc định trả về kết quả Tầng 1
        $ruleResult['suggested_status'] = $ruleResult['is_violating'] ? 'hidden' : 'visible';
        $ruleResult['moderated_by'] = 'rule_based_engine';
        return $ruleResult;
    }

    /**
     * Quét phân tích bằng luật, regex và từ điển tiếng Việt
     */
    private function scanWithRules(string $text): array
    {
        $normalized = mb_strtolower($text, 'UTF-8');
        // Chuẩn hóa xóa ký tự xen kẽ lách luật (đ.ụ.m -> đụm, d-m -> dm)
        $cleaned = preg_replace('/[._\-*#@\s]+/', '', $normalized);

        $categories = [];
        $reasons = [];
        $riskScore = 0.0;

        // 1. Kiểm tra link lạ / mạng xã hội / mời chào ngoài nền tảng
        if (preg_match('/(https?:\/\/|t\.me\/|telegram\.me|zalo\.me\/|chat\.zalo\.me|bit\.ly\/|tinyurl\.com)/i', $normalized)) {
            $categories[] = 'spam_link';
            $reasons[] = 'Chứa liên kết web ngoài hoặc link nhóm chat lạ';
            $riskScore = max($riskScore, 0.85);
        }

        // 2. Kiểm tra để lại số điện thoại nhạy cảm
        if (preg_match('/(0[35789][0-9]{8}|\+84[35789][0-9]{8})/', $cleaned)) {
            $categories[] = 'pii_phone';
            $reasons[] = 'Để lại số điện thoại cá nhân';
            $riskScore = max($riskScore, 0.75);
        }

        // 3. Kiểm tra từ cờ bạc, lừa đảo, share lậu
        foreach ($this->scamAndSpamList as $scamWord) {
            if (str_contains($normalized, $scamWord) || str_contains($cleaned, str_replace(' ', '', $scamWord))) {
                $categories[] = 'scam_and_spam';
                $reasons[] = "Phát hiện nội dung quảng cáo rác/lừa đảo liên quan đến: '{$scamWord}'";
                $riskScore = max($riskScore, 0.90);
                break;
            }
        }

        // 4. Kiểm tra từ ngữ thô tục có dấu sao (*), ký tự đặc biệt lách luật hoặc teencode
        foreach ($this->maskedProfanityPatterns as $pattern => $label) {
            if (preg_match($pattern, $text)) {
                $categories[] = 'toxicity_and_profanity';
                $reasons[] = $label;
                $riskScore = max($riskScore, 0.95);
                break;
            }
        }

        // 5. Kiểm tra từ ngữ thô tục, xúc phạm chuẩn
        if ($riskScore < 0.8) {
            foreach ($this->profanityList as $badWord) {
                $len = mb_strlen($badWord, 'UTF-8');
                if ($len <= 3) {
                    // Bắt từ độc lập để không bị nhầm lẫn với các từ tiếng Anh (như click, block, access...)
                    if (preg_match('/(^|\s|[.,!?;:()~#@\-_])' . preg_quote($badWord, '/') . '($|\s|[.,!?;:()~#@\-_])/ui', $normalized)) {
                        $categories[] = 'toxicity_and_profanity';
                        $reasons[] = "Chứa từ ngữ xúc phạm, thiếu văn minh";
                        $riskScore = max($riskScore, 0.95);
                        break;
                    }
                } else {
                    if (str_contains($normalized, $badWord) || str_contains($cleaned, str_replace(' ', '', $badWord))) {
                        $categories[] = 'toxicity_and_profanity';
                        $reasons[] = "Chứa từ ngữ xúc phạm, thiếu văn minh";
                        $riskScore = max($riskScore, 0.95);
                        break;
                    }
                }
            }
        }

        // 6. Kiểm tra spam ký tự vô nghĩa (ví dụ: aaaaaaaaa, ????????)
        if (preg_match('/(.)\1{7,}/u', $normalized)) {
            $categories[] = 'nonsense_spam';
            $reasons[] = 'Spam ký tự lặp lại vô nghĩa';
            $riskScore = max($riskScore, 0.70);
        }

        return [
            'is_violating' => $riskScore >= 0.7,
            'risk_score' => $riskScore,
            'categories' => $categories,
            'reason' => !empty($reasons) ? implode('; ', $reasons) : 'Nội dung bình thường',
        ];
    }

    /**
     * Gọi Gemini API (Gói Free Tier) phân tích ngữ cảnh nâng cao
     */
    private function scanWithGemini(string $text, string $apiKey): ?array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . $apiKey;

        $prompt = <<<PROMPT
Bạn là bộ lọc kiểm duyệt bình luận tự động của hệ thống E-learning MindHub.
Hãy phân tích xem bình luận sau có vi phạm các tiêu chuẩn: chửi bới xúc phạm, spam link lừa đảo/cờ bạc, để lại SĐT, rủ share tài khoản lậu hay không.
Trả về định dạng JSON:
{
  "is_violating": true/false,
  "risk_score": 0.0 đến 1.0,
  "categories": ["toxicity" | "spam_ad" | "pii_leak" | "piracy_share"],
  "reason": "Giải thích ngắn bằng tiếng Việt"
}
PROMPT;

        try {
            $response = Http::timeout(6.0)->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt . "\n\nBình luận: \"{$text}\""]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.0,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $rawJson = $response->json('candidates.0.content.parts.0.text');
                $parsed = json_decode($rawJson, true);
                if (is_array($parsed)) {
                    return [
                        'is_violating' => (bool) ($parsed['is_violating'] ?? false),
                        'risk_score' => (float) ($parsed['risk_score'] ?? 0.0),
                        'categories' => (array) ($parsed['categories'] ?? []),
                        'reason' => (string) ($parsed['reason'] ?? ''),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info('Gemini Moderation skipped: ' . $e->getMessage());
        }

        return null;
    }
}
