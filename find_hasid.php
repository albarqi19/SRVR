<?php

// ملف للبحث عن جميع الملفات التي تحتوي على كلمة HasId
function searchForString($dir, $string) {
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, $string) !== false) {
                $results[] = [
                    'file' => $file->getPathname(),
                    'line' => findLineNumber($content, $string)
                ];
            }
        }
    }

    return $results;
}

function findLineNumber($content, $string) {
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, $string) !== false) {
            return $i + 1;
        }
    }
    return -1;
}

$searchStrings = ['HasId', 'Checkable', 'implements'];

echo "البحث في مجلد app...\n";
foreach ($searchStrings as $string) {
    echo "البحث عن: $string\n";
    $results = searchForString(__DIR__ . '/app', $string);
    foreach ($results as $result) {
        echo $result['file'] . " (السطر: " . $result['line'] . ")\n";
    }
    echo "\n";
}

echo "البحث في مجلد app/Models...\n";
foreach ($searchStrings as $string) {
    echo "البحث عن: $string\n";
    $results = searchForString(__DIR__ . '/app/Models', $string);
    foreach ($results as $result) {
        echo $result['file'] . " (السطر: " . $result['line'] . ")\n";
    }
    echo "\n";
}

echo "تم الانتهاء من البحث.\n";