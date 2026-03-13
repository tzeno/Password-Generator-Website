<?php
// Security: Disable error display in production
ini_set('display_errors', 0);
error_reporting(0);

// Secure session configuration
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,     // Requires HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Regenerate session ID to prevent fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Initialize password history
if (!isset($_SESSION['password_history'])) {
    $_SESSION['password_history'] = [];
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-ancestors 'none';");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>PassCraft | Secure Password Generator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container">
        <div class="generator-wrapper">
            <!-- Header -->
            <div class="generator-header">
                <h1>PassCraft<span class="accent">.</span></h1>
                <p class="subtitle">enterprise-grade password generator</p>
                <div class="security-badge">
                    <span class="badge">🔒 256-bit encryption</span>
                    <span class="badge">🔄 CSPRNG</span>
                    <span class="badge">📋 zero storage</span>
                </div>
            </div>
            
            <!-- Main Generator Card -->
            <div class="generator-card">
                <!-- Password Display -->
                <div class="password-display">
                    <input type="text" id="password" class="password-field" readonly placeholder="Click generate">
                    <div class="display-actions">
                        <button class="icon-btn" id="copyBtn" title="Copy to clipboard">
                            <span>📋</span>
                        </button>
                        <button class="icon-btn" id="refreshBtn" title="Generate new">
                            <span>🔄</span>
                        </button>
                    </div>
                </div>
                
                <!-- Strength Meter -->
                <div class="strength-meter">
                    <div class="meter-label">
                        <span>Password Strength</span>
                        <span id="strengthText" class="strength-value">None</span>
                    </div>
                    <div class="meter-bar">
                        <div id="strengthBar" class="bar" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="controls">
                    <!-- Length Slider -->
                    <div class="control-group">
                        <label for="length">
                            Length: <span id="lengthValue">16</span> characters
                        </label>
                        <input type="range" id="length" min="4" max="64" value="16" class="slider">
                    </div>
                    
                    <!-- Character Types -->
                    <div class="control-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="uppercase" checked>
                            <span>A-Z (Uppercase)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="lowercase" checked>
                            <span>a-z (Lowercase)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="numbers" checked>
                            <span>0-9 (Numbers)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="symbols" checked>
                            <span>!@# (Symbols)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="excludeSimilar">
                            <span>Avoid similar chars (i, l, 1, L, o, 0, O)</span>
                        </label>
                    </div>
                    
                    <!-- Advanced Options -->
                    <div class="advanced-options">
                        <button class="toggle-advanced" id="toggleAdvanced" type="button">
                            ⚡ Advanced Options
                        </button>
                        
                        <div class="advanced-panel hidden" id="advancedPanel">
                            <div class="control-group">
                                <label>
                                    <input type="radio" name="generatorType" value="random" checked>
                                    Random Password
                                </label>
                                <label>
                                    <input type="radio" name="generatorType" value="passphrase">
                                    Memorable Passphrase
                                </label>
                            </div>
                            
                            <div class="control-group" id="passphraseControls" style="display: none;">
                                <label for="wordCount">Number of words:</label>
                                <select id="wordCount">
                                    <option value="3">3 words</option>
                                    <option value="4" selected>4 words</option>
                                    <option value="5">5 words</option>
                                    <option value="6">6 words</option>
                                </select>
                                <label for="separator">
                                    Separator:
                                    <input type="text" id="separator" value="-" maxlength="2">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Generate Button -->
                    <button class="generate-btn" id="generateBtn" type="button">
                        <span>🔐 Generate Secure Password</span>
                    </button>
                </div>
            </div>
            
            <!-- History Section (PHP Session) -->
            <div class="history-section">
                <h3>Recent Passwords <span class="history-note">(session only)</span></h3>
                <div class="history-list" id="historyList">
                    <?php foreach(array_slice($_SESSION['password_history'], 0, 5) as $pwd): 
                        // Secure output: encode for HTML
                        $safe_pwd = htmlspecialchars($pwd, ENT_QUOTES, 'UTF-8');
                        // Also prepare for JavaScript onclick
                        $js_safe_pwd = json_encode($pwd);
                    ?>
                    <div class="history-item">
                        <code><?php echo $safe_pwd; ?></code>
                        <button class="small-copy" onclick="copyToClipboard(<?php echo $js_safe_pwd; ?>)" title="Copy">
                            📋
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Security Info -->
            <div class="security-info">
                <div class="info-card">
                    <h4>🔐 Why Trust This?</h4>
                    <ul>
                        <li>Passwords generated server-side using PHP's cryptographically secure random_int()</li>
                        <li>No passwords stored in database</li>
                        <li>SSL encryption in transit (when hosted with HTTPS)</li>
                        <li>History stored only in your session (cleared when browser closes)</li>
                        <li>All output is encoded to prevent XSS attacks</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    
    <script src="script.js"></script>
</body>
</html>