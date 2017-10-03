<?php

require_once __DIR__ . '/../../bootstrap.php';

/** @var \Pokettomonstaa\App\App $app */
$cli = $app->getCli();

$originPath = getenv('REPO_PATH');

if (!file_exists($originPath)) {
    throw new \RuntimeException('REPO_PATH does not exist: ' . $originPath);
}

$destBuildPath = $app->buildPath . '/pokesprite';

// Cleanup
$app->execCmd('rm -rf ' . $destBuildPath);
$app->assureDir($destBuildPath . '/icons/items');
$app->assureDir($destBuildPath . '/icons/shapes');

// PokeSprite images
$app->getCli()->whisper('Importing images from pokesprite');
$app->execCmd('cp -r ' . $originPath . '/icons/pokemon ' . $destBuildPath . '/icons');
$app->execCmd('cp -r ' . $originPath . '/icons/body-style/*.png ' . $destBuildPath . '/icons/shapes');
$app->execCmd('cp -r ' . $originPath . '/icons/etc/*.png ' . $destBuildPath . '/icons');
$app->execCmd('cp -r ' . $originPath . '/icons/wonder-launcher ' . $destBuildPath . '/icons/wonder-launcher');

$itemFolderSuffixes = [
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
    'z-crystals'    => '!-bag', // ! = remove the suffix
];

$itemRenames = [
    // Items that have wrong or changed names:  pokesprite_name -> db_name
    'glalite'              => 'glalitite',
    'contest-costume-cool' => 'contest-costume--jacket',
    'contest-costume-cute' => 'contest-costume--dress',
    'meteorite-stage-1'    => 'meteorite',
    'meteorite-stage-2'    => 'meteorite2',
    'meteorite-stage-3'    => 'meteorite3',
    'meteorite-stage-4'    => 'meteorite4',
    'travel-trunk-gold'    => 'travel-trunk',
    'bridge-s'             => 'bridge-mail-s',
    'bridge-d'             => 'bridge-mail-d',
    'bridge-t'             => 'bridge-mail-t',
    'bridge-v'             => 'bridge-mail-v',
    'bridge-m'             => 'bridge-mail-m',
];

// Copy item icons to a single folder
foreach ($itemFolderSuffixes as $folderName => $suffix) {
    $app->getCli()->whisper('Importing images from pokesprite/icons/' . $folderName);
    $iconDirIterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($originPath . '/icons/' . $folderName,
            \RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iconDirIterator as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $oldFilePath = $file;
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $fileSuffix = $suffix;

        if (array_key_exists($filename, $itemRenames)) {
            $renaming = $itemRenames[$filename];
            $fileSuffix = '';
            $cli->yellow("Renaming ${filename} -> ${renaming} ");
            $filename = $renaming;
        }

        // Z Crystals rename
        if (preg_match('/-z-held$/', $filename)) {
            $filename = str_replace('-z-held', '-z--held', $filename);
        }

        if (pathinfo($file, PATHINFO_EXTENSION) == "png") {
            if (preg_match('/^\!/', $fileSuffix)) {
                $newFilePath = $destBuildPath . '/icons/items/' .
                    str_replace(ltrim($fileSuffix, '!'), '', $filename) . '.png';
            } else {
                $newFilePath = $destBuildPath . '/icons/items/' . $filename . $fileSuffix . '.png';
            }

            copy($oldFilePath, $newFilePath);
        }
    }
}

$pokemonRenames = [
    'genesect-douse'    => 'genesect-water',
    'genesect-shock'    => 'genesect-normal',
    'genesect-burn'     => 'genesect-fire',
    'genesect-chill'    => 'genesect-ice',
    'minior-red'        => 'minior-core-red',
    'minior-orange'     => 'minior-core-orange',
    'minior-yellow'     => 'minior-core-yellow',
    'minior-green'      => 'minior-core-green',
    'minior-blue'       => 'minior-core-blue',
    'minior-indigo'     => 'minior-core-indigo',
    'minior-violet'     => 'minior-core-violet',
    'oricorio-pau'      => 'oricorio-pa-u',
    'pikachu-rock-star' => 'pikachu-cool',
    'pikachu-belle'     => 'pikachu-beautiful',
    'pikachu-pop-star'  => 'pikachu-cute',
    'pikachu-phd'       => 'pikachu-clever',
    'pikachu-libre'     => 'pikachu-tough',
    'zygarde-10'        => 'zygarde-10-percent',
];

$pokemonRenames = array_combine(array_values($pokemonRenames), array_keys($pokemonRenames));

$pokeDirIterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($destBuildPath . '/icons/pokemon',
        \RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($pokeDirIterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $oldFilePath = $file;
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $filedir = pathinfo($file, PATHINFO_DIRNAME);

    if (array_key_exists($filename, $pokemonRenames)) {
        $newFile = "${filedir}/${pokemonRenames[$filename]}.${ext}";
        $cli->yellow("Renaming ${filename} -> ${pokemonRenames[$filename]} ");
        $app->execCmd('mv ' . $file . ' ' . $newFile);
    }
}

$app->getCli()->green("DONE!");