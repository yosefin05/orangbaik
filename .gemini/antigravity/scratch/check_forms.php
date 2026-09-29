<?php

$viewsDir = __DIR__ . '/../../../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

$files = [];

foreach ($iterator as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }
    
    $path = $file->getPathname();
    $content = file_get_contents($path);
    
    if (stripos($content, '<form') === false) {
        continue;
    }
    
    $relativePath = str_replace(realpath($viewsDir) . DIRECTORY_SEPARATOR, '', realpath($path));
    
    // Check form open vs close
    $openCount = preg_match_all('/<form\b/i', $content, $m1);
    $closeCount = preg_match_all('/<\/form>/i', $content, $m2);
    
    // Parse individual forms
    preg_match_all('/<form\b([^>]*)>(.*?)<\/form>/is', $content, $formMatches, PREG_SET_ORDER);
    
    $formDetails = [];
    foreach ($formMatches as $idx => $fm) {
        $attrs = $fm[1];
        $body = $fm[2];
        
        $hasCsrf = (stripos($body, '@csrf') !== false) || (stripos($attrs, 'method="get"') !== false) || (stripos($attrs, "method='get'") !== false);
        $hasMethod = preg_match('/method=[\'"]([^\'"]+)[\'"]/i', $attrs, $methodMatch) ? strtoupper($methodMatch[1]) : 'GET';
        $hasAction = preg_match('/action=[\'"]([^\'"]*)[\'"]/i', $attrs, $actionMatch) ? $actionMatch[1] : '';
        $hasEnctype = preg_match('/enctype=[\'"]([^\'"]+)[\'"]/i', $attrs, $encMatch) ? $encMatch[1] : '';
        $hasFileInput = preg_match('/type=[\'"]file[\'"]/i', $body);
        
        // Find inputs
        preg_match_all('/name=[\'"]([^\'"]+)[\'"]/i', $body, $nameMatches);
        $inputNames = $nameMatches[1] ?? [];
        
        $formDetails[] = [
            'method' => $hasMethod,
            'action' => $hasAction,
            'enctype' => $hasEnctype,
            'has_csrf' => $hasCsrf,
            'has_file_input' => (bool)$hasFileInput,
            'needs_multipart' => $hasFileInput && (stripos($hasEnctype, 'multipart/form-data') === false),
            'inputs' => array_unique($inputNames),
        ];
    }
    
    $files[] = [
        'file' => $relativePath,
        'open_count' => $openCount,
        'close_count' => $closeCount,
        'forms' => $formDetails,
    ];
}

echo "Total view files with forms: " . count($files) . "\n\n";

$issues = [];
$totalForms = 0;

foreach ($files as $f) {
    if ($f['open_count'] !== $f['close_count']) {
        $issues[] = "UNMATCHED TAGS in {$f['file']}: opened {$f['open_count']} vs closed {$f['close_count']}";
    }
    
    foreach ($f['forms'] as $i => $form) {
        $totalForms++;
        if ($form['method'] === 'POST' && !$form['has_csrf']) {
            $issues[] = "MISSING @csrf in {$f['file']} (form #".($i+1).")";
        }
        if ($form['needs_multipart']) {
            $issues[] = "MISSING enctype=\"multipart/form-data\" in {$f['file']} (form #".($i+1).") with file inputs";
        }
    }
}

echo "Total forms parsed: {$totalForms}\n";
if (empty($issues)) {
    echo "No syntax issues found in forms!\n";
} else {
    echo "Issues found:\n" . implode("\n", $issues) . "\n";
}

echo "\n--- FORM SUMMARY BY FILE ---\n";
foreach ($files as $f) {
    echo "- {$f['file']} ({$f['open_count']} forms):\n";
    foreach ($f['forms'] as $i => $form) {
        echo "  Form #" . ($i+1) . ": [{$form['method']}] action='{$form['action']}' enctype='{$form['enctype']}' inputs=" . implode(', ', array_slice($form['inputs'], 0, 8)) . (count($form['inputs']) > 8 ? '...' : '') . "\n";
    }
}
