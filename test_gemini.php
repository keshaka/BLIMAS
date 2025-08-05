<?php
// Test Gemini API connection
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gemini API Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Gemini API Connection Test</h1>
    
    <?php
    include_once 'config/gemini.php';
    
    echo "<h2>Testing API Connection</h2>";
    
    $gemini = new GeminiAPI();
    $testPrompt = "Provide a brief test response about environmental monitoring.";
    
    echo "<p><strong>Test Prompt:</strong> " . htmlspecialchars($testPrompt) . "</p>";
    
    $start = microtime(true);
    $result = $gemini->generateAnalysis($testPrompt);
    $duration = round((microtime(true) - $start) * 1000);
    
    echo "<p><strong>Response Time:</strong> {$duration}ms</p>";
    
    if (isset($result['error'])) {
        echo "<p class='error'><strong>Error:</strong> " . htmlspecialchars($result['error']) . "</p>";
        
        // Test basic connectivity
        echo "<h3>Connectivity Tests</h3>";
        
        // Test if we can reach Google
        $google_test = @file_get_contents('https://www.google.com', false, stream_context_create([
            'http' => ['timeout' => 5]
        ]));
        
        if ($google_test !== false) {
            echo "<p class='success'>✓ Internet connectivity: OK</p>";
        } else {
            echo "<p class='error'>✗ Internet connectivity: FAILED</p>";
        }
        
        // Test if cURL is available
        if (function_exists('curl_init')) {
            echo "<p class='success'>✓ cURL support: Available</p>";
        } else {
            echo "<p class='error'>✗ cURL support: NOT AVAILABLE</p>";
        }
        
        // Test if allow_url_fopen is enabled
        if (ini_get('allow_url_fopen')) {
            echo "<p class='success'>✓ allow_url_fopen: Enabled</p>";
        } else {
            echo "<p class='error'>✗ allow_url_fopen: Disabled</p>";
        }
        
    } else {
        echo "<p class='success'><strong>Success!</strong> API connection working</p>";
        echo "<p><strong>Method used:</strong> " . ($result['method'] ?? 'unknown') . "</p>";
        
        if (isset($result['note'])) {
            echo "<p class='info'><strong>Note:</strong> " . htmlspecialchars($result['note']) . "</p>";
        }
        
        echo "<h3>Response:</h3>";
        echo "<pre>" . htmlspecialchars($result['analysis']) . "</pre>";
    }
    
    // Show server info
    echo "<h3>Server Information</h3>";
    echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
    echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
    echo "<p><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "</p>";
    
    // Show OpenSSL info if available
    if (extension_loaded('openssl')) {
        echo "<p class='success'>✓ OpenSSL: Available</p>";
    } else {
        echo "<p class='error'>✗ OpenSSL: Not available (required for HTTPS)</p>";
    }
    ?>
    
    <h3>Instructions</h3>
    <ol>
        <li>Get your Gemini API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
        <li>Replace 'YOUR_GEMINI_API_KEY_HERE' in config/gemini.php with your actual API key</li>
        <li>Make sure your server allows outbound HTTPS connections</li>
        <li>If using a shared hosting provider, contact them about API access</li>
    </ol>
    
</body>
</html>