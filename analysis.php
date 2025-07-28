use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\EndpointName;

<?php
require_once 'vendor/autoload.php';


class DataAnalysis {
    private $apiKey;
    private $client;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->initializeGeminiClient();
    }
    
    private function initializeGeminiClient() {
        // Initialize Gemini API client
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/v1beta/',
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }
    
    public function analyzeData($data, $analysisType = 'general') {
        $prompt = $this->buildAnalysisPrompt($data, $analysisType);
        
        try {
            $response = $this->client->post("models/gemini-pro:generateContent?key={$this->apiKey}", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody()->getContents(), true);
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No analysis generated';
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    private function buildAnalysisPrompt($data, $type) {
        $dataString = is_array($data) ? json_encode($data) : $data;
        
        $prompts = [
            'general' => "Analyze the following data and provide insights: {$dataString}",
            'trend' => "Identify trends and patterns in this data: {$dataString}",
            'summary' => "Provide a comprehensive summary of this data: {$dataString}",
            'recommendations' => "Based on this data, provide actionable recommendations: {$dataString}"
        ];
        
        return $prompts[$type] ?? $prompts['general'];
    }
}

// HTML Interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analysis with Gemini AI</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .container { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; }
        textarea { width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        select, button { padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; cursor: pointer; }
        .result { background: white; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Data Analysis Dashboard</h1>
    
    <form method="POST">
        <div class="container">
            <h3>Input Data</h3>
            <textarea name="data" placeholder="Enter your data (JSON, CSV, or plain text)..." required><?php echo htmlspecialchars($_POST['data'] ?? ''); ?></textarea>
            
            <h3>Analysis Type</h3>
            <select name="analysis_type">
                <option value="general">General Analysis</option>
                <option value="trend">Trend Analysis</option>
                <option value="summary">Data Summary</option>
                <option value="recommendations">Recommendations</option>
            </select>
            
            <br>
            <button type="submit">Analyze Data</button>
        </div>
    </form>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
        $apiKey = 'AIzaSyAu5tclMb7fjkuC57V_0zU2lkviCATNXc4';
        $analyzer = new DataAnalysis($apiKey);
        
        $data = $_POST['data'];
        $analysisType = $_POST['analysis_type'] ?? 'general';
        
        echo '<div class="container">';
        echo '<h3>Analysis Results</h3>';
        echo '<div class="result">';
        echo nl2br(htmlspecialchars($analyzer->analyzeData($data, $analysisType)));
        echo '</div>';
        echo '</div>';
    }
    ?>
</body>
</html>