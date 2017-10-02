<?php

require_once __DIR__ . '/../../bootstrap.php';

/** @var \Pokettomonstaa\Database\App $app */
$db = $app->getDb();

$originPath = getenv('REPO_PATH');

if (!file_exists($originPath)) {
    throw new \RuntimeException('REPO_PATH does not exist: ' . $originPath);
}

$destPath = $app->buildPath . '/pokesprite';

$app->assureDir($destPath . '/other/shapes');

$app->getCli()->whisper('Exporting images from pokesprite');

$app->execCmd('cp -r ' . $originPath . '/icons/pokemon ' . $destPath);
$app->execCmd('cp -r ' . $originPath . '/icons/body-style/*.png ' . $destPath . '/other/shapes');
$app->execCmd('cp -r ' . $originPath . '/icons/etc/*.png ' . $destPath . '/other');
$app->execCmd('cp -r ' . $originPath . '/icons/wonder-launcher ' . $destPath . '/other');

$folderSuffixes = [
    'apricorn'      => '-apricorn',
    'battle-item'   => '',
    'berry'         => '-berry',
    'ev-item'       => '',
    'evo-item'      => '',
    'flute'         => '-flute',
    'fossil'        => '-fossil',
    'gem'           => '-gem',
    'hm'            => '-hm',
    'hold-item'     => '',
    'incense'       => '-incense',
    'key-item'      => '',
    'mail'          => '-mail',
    'medicine'      => '',
    'mega-stone'    => '',
    'memory'        => '-memory',
    'mulch'         => '-mulch',
    'other-item'    => '',
    'plate'         => '-plate',
    'pokeball'      => '-ball',
    'scarf'         => '-scarf',
    'shard'         => '-shard',
    'tm'            => '-tm',
    'valuable-item' => '',
    'z-crystals'    => '!-bag',
];

$app->assureDir($destPath . '/items');

foreach ($folderSuffixes as $folderName => $suffix) {
    $app->getCli()->whisper('Exporting images from pokesprite/icons/' . $folderName);
    if (!$suffix) {
        $app->execCmd('cp -r ' . $originPath . '/icons/' . $folderName . '/*.png ' . $destPath . '/items');
        continue;
    }
    $iconDirIterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($originPath . '/icons/' . $folderName,
            \RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iconDirIterator as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (pathinfo($file, PATHINFO_EXTENSION) == "png") {
            $oldFileName = $file;

            if (preg_match('/^\!/', $suffix)) {
                $newFileName = $destPath . '/items/' .
                    str_replace(ltrim($suffix, '!'), '', pathinfo($file, PATHINFO_FILENAME)) . '.png';
            } else {
                $newFileName = $destPath . '/items/' . pathinfo($file, PATHINFO_FILENAME) . $suffix . '.png';
            }

            //echo "$oldFileName --> $newFileName\n";

            copy($oldFileName, $newFileName);
        }
    }
}

$app->getCli()->green("DONE!");

// Stop locking DB file
$db->close();