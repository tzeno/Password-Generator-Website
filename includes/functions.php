<?php

/**
 * Generate cryptographically secure random password
 */
function generateSecurePassword($length, $useUppercase, $useLowercase, $useNumbers, $useSymbols, $excludeSimilar) {
    $chars = '';
    
    // Character sets
    $uppercase = $excludeSimilar ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = $excludeSimilar ? 'abcdefghijkmnopqrstuvwxyz' : 'abcdefghijklmnopqrstuvwxyz';
    $numbers = $excludeSimilar ? '23456789' : '0123456789';
    $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    if ($useUppercase) $chars .= $uppercase;
    if ($useLowercase) $chars .= $lowercase;
    if ($useNumbers) $chars .= $numbers;
    if ($useSymbols) $chars .= $symbols;
    
    // Convert to array of characters
    $chars = mb_str_split($chars);
    $charCount = count($chars) - 1;
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charCount)];
    }
    
    return $password;
}

/**
 * Generate memorable passphrase
 */
function generatePassphrase($wordCount, $separator) {
    $wordLists = [
        'animals' => ['tiger', 'eagle', 'shark', 'wolf', 'bear', 'lion', 'hawk', 'deer'],
        'colors' => ['red', 'blue', 'green', 'black', 'white', 'gold', 'silver'],
        'actions' => ['jumping', 'running', 'flying', 'swimming', 'climbing'],
        'objects' => ['mountain', 'river', 'forest', 'ocean', 'star', 'moon', 'cloud']
    ];
    
    $words = [];
    for ($i = 0; $i < $wordCount; $i++) {
        $category = array_rand($wordLists);
        $words[] = $wordLists[$category][array_rand($wordLists[$category])];
    }
    
    // Add a number for extra security
    $words[] = random_int(10, 99);
    
    shuffle($words);
    return implode($separator, $words);
}

/**
 * Calculate password strength
 */
function calculatePasswordStrength($password) {
    $score = 0;
    $length = strlen($password);
    
    if ($length >= 8) $score += 20;
    if ($length >= 12) $score += 15;
    if ($length >= 16) $score += 15;
    
    if (preg_match('/[A-Z]/', $password)) $score += 10;
    if (preg_match('/[a-z]/', $password)) $score += 10;
    if (preg_match('/[0-9]/', $password)) $score += 10;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 20;
    
    $score = min(100, $score);
    
    if ($score < 40) return ['score' => $score, 'level' => 'Weak', 'class' => 'weak'];
    if ($score < 70) return ['score' => $score, 'level' => 'Moderate', 'class' => 'moderate'];
    return ['score' => $score, 'level' => 'Strong', 'class' => 'strong'];
}

/**
 * Calculate password entropy (bits)
 */
function calculateEntropy($password) {
    $charset = 0;
    
    if (preg_match('/[A-Z]/', $password)) $charset += 26;
    if (preg_match('/[a-z]/', $password)) $charset += 26;
    if (preg_match('/[0-9]/', $password)) $charset += 10;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $charset += 32;
    
    $entropy = log(pow($charset, strlen($password)), 2);
    return round($entropy, 1) . ' bits';
}