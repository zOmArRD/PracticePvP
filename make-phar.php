<?php
$file_phar = 'PracticePvP.phar';
if (file_exists($file_phar)) {
    echo "Phar file already exists, overwriting...";
    echo PHP_EOL;
    Phar::unlinkArchive($file_phar);
}

$files = [];
$dir = getcwd() . DIRECTORY_SEPARATOR;

$exclusions = ["vendor", ".target", "make-phar.php", "github", ".gitignore", "composer.json", "composer.lock", "build", ".git", ".lib", ".poggit.yml", ".idea", "README.md"];

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $path => $file) {
    $bool = true;
    foreach ($exclusions as $exclusion) {
        if (str_contains($path, $exclusion)) {
            $bool = false;
        }
    }

    if (!$bool) {
        continue;
    }

    if ($file->isFile() === false) {
        continue;
    }
    $files[str_replace($dir, "", $path)] = $path;
}
echo "Compressing..." . PHP_EOL;

$phar = new Phar($file_phar);
$phar->startBuffering();
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->buildFromIterator(new ArrayIterator($files));
$phar->setStub('<?php __HALT_COMPILER();');
$phar->compressFiles(Phar::GZ);

$phar->stopBuffering();
echo "end." . PHP_EOL;