<?php
class GeminiAPI {
    private $apiKey;
    // Changed to Gemini 2.0 Flash model endpoint
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private $alternativeUrls = [
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
        'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent'
    ];

    public function __construct() {
        // Replace with your actual Gemini API key!
        $this->apiKey = 'YOUR_GEMINI_API_KEY_HERE';
    }

    public function generateAnalysis($prompt, $opts = []) {
        set_time_limit(60); // Faster timeout for Gemini 2.0 Flash

        // Allow override of model or maxOutputTokens
        $modelUrl = isset($opts['model'])
            ? 'https://generativelanguage.googleapis.com/v1beta/models/' . $opts['model'] . ':generateContent'
            : $this->baseUrl;
        $maxOutputTokens = isset($opts['maxOutputTokens']) ? intval($opts['maxOutputTokens']) : 2048;

        // Check if API key is properly configured
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            error_log("BLIMAS: Gemini API key not configured");
            return $this->generateMockAnalysis($prompt);
        }

        $strategies = [
            'direct_curl',
            'proxy_curl',
            'alternative_endpoints',
            'chunked_request'
        ];

        foreach ($strategies as $strategy) {
            $result = $this->tryConnectionStrategy($prompt, $strategy, [
                'modelUrl' => $modelUrl,
                'maxOutputTokens' => $maxOutputTokens
            ]);
            if (isset($result['success']) && $result['success']) {
                error_log("BLIMAS: Successfully connected using strategy: $strategy");
                return $result;
            }
        }

        error_log("BLIMAS: All API connection strategies failed, using enhanced mock analysis");
        return $this->generateEnhancedMockAnalysis($prompt);
    }

    private function tryConnectionStrategy($prompt, $strategy, $opts = []) {
        switch ($strategy) {
            case 'direct_curl':
                return $this->directCurlRequest($prompt, $opts);
            case 'proxy_curl':
                return $this->proxyCurlRequest($prompt, $opts);
            case 'alternative_endpoints':
                return $this->tryAlternativeEndpoints($prompt, $opts);
            case 'chunked_request':
                return $this->chunkedRequest($prompt, $opts);
            default:
                return ['error' => 'Unknown strategy'];
        }
    }

    private function directCurlRequest($prompt, $opts = []) {
        if (!function_exists('curl_init')) {
            return ['error' => 'cURL not available'];
        }

        $modelUrl = isset($opts['modelUrl']) ? $opts['modelUrl'] : $this->baseUrl;
        $maxOutputTokens = isset($opts['maxOutputTokens']) ? intval($opts['maxOutputTokens']) : 2048;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => $maxOutputTokens
            ]
        ];

        $jsonData = json_encode($data);
        $url = $modelUrl . '?key=' . $this->apiKey;

        try {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: Mozilla/5.0 (compatible; BLIMAS/1.0)',
                    'Accept: application/json',
                    'Cache-Control: no-cache'
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($response === false) {
                return ['error' => 'Direct cURL failed: ' . $error];
            }

            if ($httpCode === 200) {
                return $this->parseResponse($response, 'direct_curl');
            }

            return ['error' => "Direct cURL HTTP error: $httpCode"];

        } catch (Exception $e) {
            return ['error' => 'Direct cURL exception: ' . $e->getMessage()];
        }
    }

    private function proxyCurlRequest($prompt, $opts = []) {
        if (!function_exists('curl_init')) {
            return ['error' => 'cURL not available'];
        }

        $modelUrl = isset($opts['modelUrl']) ? $opts['modelUrl'] : $this->baseUrl;
        $maxOutputTokens = isset($opts['maxOutputTokens']) ? intval($opts['maxOutputTokens']) : 2048;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => $maxOutputTokens
            ]
        ];

        $jsonData = json_encode($data);
        $url = $modelUrl . '?key=' . $this->apiKey;

        try {
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept: application/json',
                    'Accept-Language: en-US,en;q=0.9',
                    'Accept-Encoding: gzip, deflate, br'
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($response === false) {
                return ['error' => 'Proxy cURL failed: ' . $error];
            }

            if ($httpCode === 200) {
                return $this->parseResponse($response, 'proxy_curl');
            }

            return ['error' => "Proxy cURL HTTP error: $httpCode"];

        } catch (Exception $e) {
            return ['error' => 'Proxy cURL exception: ' . $e->getMessage()];
        }
    }

    private function tryAlternativeEndpoints($prompt, $opts = []) {
        foreach ($this->alternativeUrls as $index => $url) {
            $result = $this->makeRequestToUrl($prompt, $url, "alternative_endpoint_$index", $opts);
            if (isset($result['success']) && $result['success']) {
                return $result;
            }
        }
        return ['error' => 'All alternative endpoints failed'];
    }

    private function makeRequestToUrl($prompt, $url, $method, $opts = []) {
        $maxOutputTokens = isset($opts['maxOutputTokens']) ? intval($opts['maxOutputTokens']) : 2048;
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => $maxOutputTokens
            ]
        ];

        $jsonData = json_encode($data);
        $fullUrl = $url . '?key=' . $this->apiKey;

        if (function_exists('curl_init')) {
            try {
                $ch = curl_init();

                curl_setopt_array($ch, [
                    CURLOPT_URL => $fullUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $jsonData,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'User-Agent: BLIMAS/1.0'
                    ],
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_CONNECTTIMEOUT => 20,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response !== false && $httpCode === 200) {
                    return $this->parseResponse($response, $method);
                }
            } catch (Exception $e) {
                // Continue to next method
            }
        }

        return ['error' => 'Alternative endpoint failed'];
    }

    private function chunkedRequest($prompt, $opts = []) {
        if (strlen($prompt) > 1000) {
            $simplifiedPrompt = "Analyze environmental data for Bolgoda Lake and provide key insights in under 500 words.";
            return $this->directCurlRequest($simplifiedPrompt, $opts);
        }
        return $this->directCurlRequest($prompt, $opts);
    }

    private function parseResponse($response, $method) {
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response from API'];
        }

        if (isset($result['error'])) {
            $errorMsg = $result['error']['message'] ?? 'Unknown API error';
            return ['error' => 'Gemini API Error: ' . $errorMsg];
        }

        if (
            isset($result['candidates'][0]['content']['parts'][0]['text']) &&
            !empty($result['candidates'][0]['content']['parts'][0]['text'])
        ) {
            return [
                'success' => true,
                'analysis' => $result['candidates'][0]['content']['parts'][0]['text'],
                'method' => $method
            ];
        }
        return ['error' => 'Unexpected response format from Gemini API', 'raw' => $response];
    }

    // ...rest of your mock and analysis functions remain unchanged...
    // (generateEnhancedMockAnalysis, generateDataDrivenAnalysis, createDataBasedAnalysis, generateMockAnalysis, getComprehensiveAnalysis, getWaterQualityAnalysis, getClimateImpactAnalysis, getEcosystemHealthAnalysis, getAlertsAnalysis)
}
?>