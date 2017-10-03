<?php

require_once __DIR__ . '/../../bootstrap.php';

/** @var \Pokettomonstaa\App\App $app */
$db = $app->getDb();
$cli = $app->getCli();

$originPath = getenv('REPO_PATH');
$extraOriginPath = $app->srcPath . '/resources/assets/img';

if (!file_exists($originPath)) {
    throw new \RuntimeException('REPO_PATH does not exist: ' . $originPath);
}

$destBuildPath = $app->buildPath . '/pokesprite';
$destDistPath = $app->distPath . '/assets/img';

// Cleanup
$app->execCmd('rm -rf ' . $destBuildPath);
$app->assureDir($destBuildPath . '/icons/items');
$app->assureDir($destBuildPath . '/icons/shapes');
$app->assureDir($destBuildPath . '/icons/marks');

$app->execCmd('rm -rf ' . $destDistPath);
$app->assureDir($destDistPath . '/icons/items');
$app->assureDir($destDistPath . '/icons/shapes');
$app->assureDir($destDistPath . '/icons/marks');
$app->assureDir($destDistPath . '/icons/pokemon/regular/female');
$app->assureDir($destDistPath . '/icons/pokemon/regular/right');
$app->assureDir($destDistPath . '/icons/pokemon/shiny/female');
$app->assureDir($destDistPath . '/icons/pokemon/shiny/right');

// PokeSprite images
$app->getCli()->whisper('Importing images from pokesprite');
$app->execCmd('cp -r ' . $originPath . '/icons/pokemon ' . $destBuildPath . '/icons');
$app->execCmd('cp -r ' . $originPath . '/icons/body-style/*.png ' . $destBuildPath . '/icons/shapes');
$app->execCmd('cp -r ' . $originPath . '/icons/etc/*.png ' . $destBuildPath . '/icons');
$app->execCmd('cp -r ' . $originPath . '/icons/wonder-launcher ' . $destBuildPath . '/icons/wonder-launcher');

// Extra images
$app->execCmd('cp -r ' . $extraOriginPath . '/glyphs ' . $destDistPath);
$app->execCmd('cp -r ' . $extraOriginPath . '/sprites ' . $destDistPath);
$app->execCmd('cp -r ' . $extraOriginPath . '/icons/*.png ' . $destBuildPath . '/icons');
$app->execCmd('cp -r ' . $extraOriginPath . '/icons/items/*.png ' . $destBuildPath . '/icons/items');
$app->execCmd('cp -r ' . $extraOriginPath . '/icons/pokemon/*.png ' . $destBuildPath . '/icons/pokemon');
$app->execCmd('cp -r ' . $extraOriginPath . '/icons/marks/*.png ' . $destBuildPath . '/icons/marks');

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

// Move misc.
$app->getCli()->lightBlue('Moving exported images...');
$app->execCmd('mv ' . $destBuildPath . '/icons/pokemon/*.png ' . $destDistPath . '/icons/pokemon');
$app->execCmd('mv ' . $destBuildPath . '/icons/marks/*.png ' . $destDistPath . '/icons/marks');
$app->execCmd('mv ' . $destBuildPath . '/icons/shapes/*.png ' . $destDistPath . '/icons/shapes');

$app->getCli()->whisper('Moving only the Pokemon and Item icons that are found in the DB...');
$noIconFound = [
    'pokemon' => [],
    'items'   => [],
];
$pokemon = $app->getRepo()->getPokemon();
$pokemonFolders = [
    '/icons/pokemon/regular',
    '/icons/pokemon/regular/female',
    '/icons/pokemon/regular/right',
    '/icons/pokemon/shiny',
    '/icons/pokemon/shiny/female',
    '/icons/pokemon/shiny/right',
];

foreach ($pokemon as $id => $p) {
    $icons = [];
    if ($p->name) {
        $icons[] = $p->name;
    }
    if ($p->form_name) {
        $icons[] = $p->form_name;
    }
    foreach ((array)$p->forms as $form) {
        if ($form->name) {
            $icons[] = $form->name;
        }
    }
    foreach ($icons as $icon) {
        if (!$icon) {
            continue;
        }
        $iconExists = false;
        foreach ($pokemonFolders as $folder) {
            $origFile = $destBuildPath . $folder . "/{$icon}.png";
            $destFile = $destDistPath . $folder . "/{$icon}.png";
            if (file_exists($origFile)) {
                $iconExists = true;
                if (!file_exists($destFile)) {
                    $app->execCmd('mv ' . $origFile . ' ' . $destFile);
                } else {
                    $cli->yellow("Cannot move ${origFile}, file already exists in ${destFile}");
                }
            }
        }
        if (!$iconExists) {
            //$cli->yellow("Pokemon '${icon}' has no icons.");
            $noIconFound['pokemon'][$icon] = $icon;
        }
    }
}

$items = $app->getRepo()->getItems();
$itemFolders = [
    '/icons/items',
    '/icons/wonder-launcher',
];

foreach ($items as $id => $item) {
    $icon = $item->name;
    if (preg_match('/^[th]m[0-9]{2,3}$/', $icon) || preg_match('/^data-card-.*/', $icon)) {
        continue;
    }
    $iconExists = false;
    foreach ($itemFolders as $folder) {
        $origFile = $destBuildPath . $folder . "/{$icon}.png";
        $destFile = $destDistPath . "/icons/items/{$icon}.png";
        if (file_exists($origFile)) {
            $iconExists = true;
            if (!file_exists($destFile)) {
                $app->execCmd('mv ' . $origFile . ' ' . $destFile);
            } else {
                $cli->yellow("Cannot move ${origFile}, file already exists in ${destFile}");
            }
        }
    }
    if (!$iconExists) {
        //$cli->yellow("Item '${icon}' has no icons.");
        $noIconFound['items'][$icon] = $icon;
    }
}

if (count($noIconFound['pokemon']) || count($noIconFound['items'])) {
    $app->getCli()->yellow("Not found icons for: \n" . var_export($noIconFound, true));
}

$app->getCli()->whisper('Moving remaining item and wonder launcher icons...');
$itemDirIterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($destBuildPath . '/icons/items',
        \RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($itemDirIterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $oldFilePath = $file;
    $newFilePath = $destDistPath . '/icons/items/' . $filename . '.' . $ext;

    if (!file_exists($newFilePath)) {
        $app->execCmd('mv ' . $oldFilePath . ' ' . $newFilePath);
    } else {
        $cli->yellow("Cannot move ${$oldFilePath}, file already exists in ${$newFilePath}");
    }
}
$itemDirIterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($destBuildPath . '/icons/wonder-launcher',
        \RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($itemDirIterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $oldFilePath = $file;
    $newFilePath = $destDistPath . '/icons/items/' . $filename . '.' . $ext;

    if (file_exists($newFilePath)) {
        $newFilePath = $destDistPath . '/icons/items/' . $filename . '--wonder-launcher.' . $ext;
    }

    if (!file_exists($newFilePath)) {
        $app->execCmd('mv ' . $oldFilePath . ' ' . $newFilePath);
    } else {
        $cli->yellow("Cannot move ${$oldFilePath}, file already exists in ${$newFilePath}");
    }
}

$app->getCli()->green("DONE!");

// Stop locking DB file
$db->close();