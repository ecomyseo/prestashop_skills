<?php
/**
 * LOCAL PRESTASHOP VALIDATOR & FIXER
 *
 * Replicates the logic of validator.prestashop.com locally.
 * Compatible with PrestaShop 8 and 9.
 *
 * USAGE:
 * php validator.php --path="PATH_TO_MODULE" [--fix]
 */

if (php_sapi_name() !== 'cli') {
    echo 'This script must be run from the CLI.';
    exit(1);
}

// Configuration
$rules = [
    'required_files' => ['config.xml', 'logo.png'],
    'recommended_files' => ['composer.json'],
    'forbidden_functions' => [
        'eval', 'system', 'exec', 'shell_exec', 'passthru', 'proc_open', 'popen', 'pcntl_exec',
        'dump', 'var_dump', 'print_r',
    ],
    // die/exit son permitidos SOLO en index.php
    'forbidden_except_index' => ['die', 'exit'],
    'required_header_token' => 'if (!defined(\'_PS_VERSION_\'))',
    'license_header_keyword' => 'Academic Free License (AFL 3.0)',
];

// Arguments parsing
$options = getopt('', ['path:', 'fix::']);
if (empty($options['path'])) {
    echo "ERROR: Missing --path argument.\nUsage: php validator.php --path=\"z:/path/to/module\" [--fix]\n";
    exit(1);
}

$modulePath = rtrim($options['path'], '/\\');
$fixMode = isset($options['fix']);

if (!is_dir($modulePath)) {
    echo "ERROR: Directory not found: $modulePath\n";
    exit(1);
}

$moduleName = basename($modulePath);
echo "_________________________________________________________________\n";
echo "PRESTASHOP VALIDATOR LOCAL - TARGET: $moduleName\n";
echo "PATH: $modulePath\n";
echo "MODE: " . ($fixMode ? "FIX & VALIDATE" : "VALIDATE ONLY") . "\n";
echo "_________________________________________________________________\n\n";

$report = [
    'errors' => [],
    'warnings' => [],
    'success' => [],
    'fixes' => [],
];

// 1. STRUCTURE VALIDATION
echo "[+] Checking Structure...\n";
foreach ($rules['required_files'] as $file) {
    if (!file_exists("$modulePath/$file")) {
        $report['errors'][] = "Missing required file: $file";
    } else {
        $report['success'][] = "Found: $file";
        // Deep check for logo.png
        if ($file === 'logo.png') {
            $size = @getimagesize("$modulePath/$file");
            if ($size === false) {
                $report['warnings'][] = "logo.png is not a valid image file";
            } elseif ($size[0] != 32 || $size[1] != 32) {
                $report['warnings'][] = "logo.png should be exactly 32x32px (Found: {$size[0]}x{$size[1]})";
            }
        }
    }
}

foreach ($rules['recommended_files'] as $file) {
    if (!file_exists("$modulePath/$file")) {
        $report['warnings'][] = "Recommended file missing: $file (required for Addons marketplace)";
    } else {
        $report['success'][] = "Found: $file";
    }
}

// 2. CHECK RECURSIVE INDEX.PHP
echo "[+] Checking Recursive index.php security...\n";
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isDir()) {
        $dirPath = $file->getPathname();
        // Skip .git, .idea, vendor, node_modules
        if (strpos($dirPath, '.git') !== false || strpos($dirPath, 'vendor') !== false || strpos($dirPath, 'node_modules') !== false) {
            continue;
        }

        if (!file_exists("$dirPath/index.php")) {
            if ($fixMode) {
                $indexContent = "<?php\n/**\n * $moduleName\n *\n * @license https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)\n */\n\nheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');\nheader('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');\n\nheader('Cache-Control: no-store, no-cache, must-revalidate');\nheader('Cache-Control: post-check=0, pre-check=0', false);\nheader('Pragma: no-cache');\n\nheader('Location: ../');\nexit;\n";
                file_put_contents("$dirPath/index.php", $indexContent);
                $report['fixes'][] = "Created missing index.php in " . str_replace($modulePath, '', $dirPath);
            } else {
                $report['errors'][] = "Missing index.php in " . str_replace($modulePath, '', $dirPath);
            }
        }
    }
}

// 3. CODE ANALYSIS
echo "[+] Analyzing PHP Files...\n";
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulePath, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($phpFiles as $file) {
    if (!$file->isFile()) {
        continue;
    }
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $filePath = $file->getPathname();
    $fileName = $file->getFilename();
    $relativePath = str_replace($modulePath, '', $filePath);

    // Skip vendor/node_modules
    if (strpos($relativePath, 'vendor') !== false || strpos($relativePath, 'node_modules') !== false) {
        continue;
    }

    $content = file_get_contents($filePath);

    // 3.1 Syntax Check
    $lint = shell_exec("php -l \"$filePath\" 2>&1");
    if (strpos($lint, 'No syntax errors') === false) {
        $report['errors'][] = "SYNTAX ERROR in $relativePath: " . trim($lint);
    }

    // 3.2 Security Token Check (skip index.php)
    if ($fileName !== 'index.php') {
        if (strpos($content, '_PS_VERSION_') === false) {
            if ($fixMode) {
                // Insert after <?php and any declare(strict_types)
                if (preg_match('/^<\?php\s*(declare\(strict_types\s*=\s*1\)\s*;)?/s', $content, $matches)) {
                    $insertAfter = $matches[0];
                    $securityLine = "\nif (!defined('_PS_VERSION_')) {\n    exit;\n}\n";
                    $content = $insertAfter . $securityLine . substr($content, strlen($insertAfter));
                    file_put_contents($filePath, $content);
                    $report['fixes'][] = "Added security token to $relativePath";
                }
            } else {
                $report['errors'][] = "Security failure: Missing '_PS_VERSION_' check in $relativePath";
            }
        }
    }

    // 3.3 License Header
    if (strpos($content, 'Academic Free License (AFL 3.0)') === false && strpos($content, 'Open Software License (OSL 3.0)') === false) {
        $report['warnings'][] = "Missing or incorrect License Header in $relativePath";
    }

    // 3.4 Forbidden Functions (always forbidden)
    foreach ($rules['forbidden_functions'] as $func) {
        if (preg_match("/\b" . preg_quote($func, '/') . "\s*\(/", $content)) {
            $report['errors'][] = "Forbidden function '$func' found in $relativePath";
        }
    }

    // 3.5 Forbidden except in index.php (die/exit)
    if ($fileName !== 'index.php') {
        foreach ($rules['forbidden_except_index'] as $func) {
            // Allow exit/die in security token context
            if (preg_match("/\b" . preg_quote($func, '/') . "\s*[;(]/", $content)) {
                // Check if it's only used in the _PS_VERSION_ guard
                $contentWithoutGuard = preg_replace("/if\s*\(\s*!defined\s*\(\s*'_PS_VERSION_'\s*\)\s*\)\s*\{\s*exit\s*;\s*\}/", '', $content);
                $contentWithoutGuard = preg_replace("/if\s*\(\s*!defined\s*\(\s*'_PS_VERSION_'\s*\)\s*\)\s*exit\s*;/", '', $contentWithoutGuard);
                if (preg_match("/\b" . preg_quote($func, '/') . "\s*[;(]/", $contentWithoutGuard)) {
                    $report['warnings'][] = "Usage of '$func' outside security guard in $relativePath";
                }
            }
        }
    }

    // 3.6 Strict Types Check (ENCOURAGED, not forbidden)
    // declare(strict_types=1) is a GOOD practice in PS 8/9
    if (strpos($content, 'declare(strict_types=1)') !== false) {
        $report['success'][] = "Good: strict_types enabled in $relativePath";
    }

    // 3.7 Namespace Check (Main file only)
    if ($fileName === ($moduleName . '.php')) {
        if (preg_match('/^\s*namespace\s+[\w\\\\]+;/m', $content)) {
            $report['errors'][] = "Main class file $fileName MUST NOT have a namespace.";
        }
    }

    // 3.8 Legacy translation check
    if (preg_match('/\$this->l\s*\(/', $content) && $fileName !== 'index.php') {
        $report['warnings'][] = "Legacy translation \$this->l() found in $relativePath. Use \$this->trans() with domain for PS 8/9.";
    }
}

// 4. REPORT
echo "\n\n";
echo "=================================================================\n";
echo "VALIDATION REPORT\n";
echo "=================================================================\n";

if (!empty($report['fixes'])) {
    echo "\n[ FIXED ISSUES ]\n";
    foreach ($report['fixes'] as $fix) {
        echo "  FIXED: $fix\n";
    }
}

if (!empty($report['errors'])) {
    echo "\n[ ERRORS ] (Must Fix)\n";
    foreach ($report['errors'] as $error) {
        echo "  ERROR: $error\n";
    }
} else {
    echo "\n[ OK ] No critical errors found.\n";
}

if (!empty($report['warnings'])) {
    echo "\n[ WARNINGS ] (Should Fix)\n";
    foreach ($report['warnings'] as $warning) {
        echo "  WARN: $warning\n";
    }
}

if (!empty($report['success'])) {
    echo "\n[ PASSED ]\n";
    foreach ($report['success'] as $success) {
        echo "  OK: $success\n";
    }
}

echo "\nDone.\n";
