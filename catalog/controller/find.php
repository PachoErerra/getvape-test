<?php

$path = __DIR__;
$needle = 'м';

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
);

foreach ($rii as $file) {
    if (!$file->isFile()) continue;

    $handle = fopen($file->getPathname(), "r");
    if ($handle) {
        $lineNum = 0;
        while (($line = fgets($handle)) !== false) {
            $lineNum++;
            if (strpos($line, $needle) !== false) {
                echo "Совпадение: {$file->getPathname()} (строка $lineNum)\n";
            }
        }
        fclose($handle);
    }
}
