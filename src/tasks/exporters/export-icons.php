<?php

/** @var \Pokettomonstaa\App\App $app */
$app = $this;
$cli = $app->getCli();

$origPath = $app->buildPath . '/pokesprite';
$destPath = $app->distPath . '/assets/img';
$extraOrigPath = $app->srcPath . '/resources/assets/img';

$app->execCmd('rm -rf ' . $destPath);
$app->assureDir($destPath . '/icons/items');
$app->assureDir($destPath . '/icons/shapes');
$app->assureDir($destPath . '/icons/marks');
$app->assureDir($destPath . '/icons/pokemon/regular/female');
$app->assureDir($destPath . '/icons/pokemon/regular/right');
$app->assureDir($destPath . '/icons/pokemon/shiny/female');
$app->assureDir($destPath . '/icons/pokemon/shiny/right');

$app->getCli()->lightBlue('Moving exported images...');

$app->execCmd('cp -r ' . $extraOrigPath . '/glyphs ' . $destPath);
$app->execCmd('cp -r ' . $extraOrigPath . '/sprites ' . $destPath);
$app->execCmd('cp -r ' . $extraOrigPath . '/icons/marks ' . $destPath . '/icons');
$app->execCmd('cp -r ' . $extraOrigPath . '/icons/*.png ' . $destPath . '/icons');
$app->execCmd('cp -r ' . $extraOrigPath . '/icons/items/*.png ' . $destPath . '/icons/items');
$app->execCmd('cp -r ' . $extraOrigPath . '/icons/pokemon/*.png ' . $destPath . '/icons/pokemon');

$app->execCmd('mv ' . $origPath . '/icons/pokemon/*.png ' . $destPath . '/icons/pokemon');
$app->execCmd('mv ' . $origPath . '/icons/shapes/*.png ' . $destPath . '/icons/shapes');

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
            $origFile = $origPath . $folder . "/{$icon}.png";
            $destFile = $destPath . $folder . "/{$icon}.png";
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
        $origFile = $origPath . $folder . "/{$icon}.png";
        $destFile = $destPath . "/icons/items/{$icon}.png";
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
    new \RecursiveDirectoryIterator($origPath . '/icons/items',
        \RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($itemDirIterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $oldFilePath = $file;
    $newFilePath = $destPath . '/icons/items/' . $filename . '.' . $ext;

    if (!file_exists($newFilePath)) {
        $app->execCmd('mv ' . $oldFilePath . ' ' . $newFilePath);
    } else {
        $cli->yellow("Cannot move ${$oldFilePath}, file already exists in ${$newFilePath}");
    }
}

$itemDirIterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($origPath . '/icons/wonder-launcher',
        \RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($itemDirIterator as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $oldFilePath = $file;
    $newFilePath = $destPath . '/icons/items/' . $filename . '.' . $ext;

    if (file_exists($newFilePath)) {
        $newFilePath = $destPath . '/icons/items/' . $filename . '--wonder-launcher.' . $ext;
    }

    if (!file_exists($newFilePath)) {
        $app->execCmd('mv ' . $oldFilePath . ' ' . $newFilePath);
    } else {
        $cli->yellow("Cannot move ${$oldFilePath}, file already exists in ${$newFilePath}");
    }
}

$app->getCli()->green("DONE!");
