<?php

/** @var \Pokettomonstaa\App\App $app */
$app = $this;

// Create Protocol Buffer files
$proto_path = $app->assureDir($app->distPath . '/code/protobuf/Pokemon/Enums');

$proto_enums = [
    'Species'    => 'SELECT id, name FROM zz_pokemon WHERE form_category IS NULL ORDER BY id',
    'Form'       => 'SELECT id, form_name AS `name` FROM zz_pokemon WHERE id >= 10000 AND form_name IS NOT NULL ORDER BY id',
    'Type'       => 'SELECT id, identifier AS `name` FROM types WHERE id < 10000 ORDER BY id',
    'Ability'    => 'SELECT id, identifier AS `name` FROM abilities WHERE id < 10000 ORDER BY id',
    'EggGroup'   => 'SELECT id, identifier AS `name` FROM egg_groups WHERE id < 10000 ORDER BY id',
    'Gender'     => 'SELECT id, identifier AS `name` FROM genders WHERE id < 10000 ORDER BY id',
    'Nature'     => 'SELECT id, identifier AS `name` FROM natures WHERE id < 10000 ORDER BY id',
    'Move'       => 'SELECT id, identifier AS `name` FROM moves WHERE id < 10000 ORDER BY id',
    'Item'       => 'SELECT id, identifier AS `name` FROM items WHERE id < 10000 ORDER BY id',
    'Stat'       => 'SELECT id, identifier AS `name` FROM stats WHERE id < 10000 ORDER BY id',
    'GrowthRate' => 'SELECT id, identifier AS `name` FROM growth_rates WHERE id < 10000 ORDER BY id',
    'Shape'      => 'SELECT id, identifier AS `name` FROM pokemon_shapes WHERE id < 10000 ORDER BY id',
    'Color'      => 'SELECT id, identifier AS `name` FROM pokemon_colors WHERE id < 10000 ORDER BY id',
    'Region'     => 'SELECT id, identifier AS `name` FROM regions WHERE id < 10000 ORDER BY id',
    'Generation' => 'SELECT id, identifier AS `name` FROM generations WHERE id < 10000 ORDER BY id',
    'GameGroup'  => 'SELECT id, identifier AS `name` FROM version_groups WHERE id < 10000 ORDER BY id',
    'Game'       => 'SELECT id, identifier AS `name` FROM versions WHERE id < 10000 ORDER BY id',
];

$app->getCli()->lightBlue("Creating Protocol Buffer enum files...");

/**
 * Generates a Protocol Buffer enum out of an SQL query
 *
 * @param string $enumName
 * @param string $sql
 * @param bool   $usePrefix
 * @param string $nameColumn
 * @param string $idColumn
 *
 * @return string The generated protobuf code
 */
$createProtoEnumFromQuery = function (
    $enumName,
    $sql,
    $usePrefix = false,
    $nameColumn = 'name',
    $idColumn = 'id'
) use ($app) {
    $records = $app->getDb()->query($sql);
    $prefix = \Stringy\Stringy::create($enumName)->underscored()->toUpperCase();
    $defaultItem = ['name' => '_DEFAULT', 'id' => 0];
    $templateData = ['enumName' => $enumName, 'items' => [(object)$defaultItem]];

    while ($record = $records->fetch(\PDO::FETCH_OBJ)) {
        $id = null;
        $name = null;

        if ($nameColumn) {
            $name = ($usePrefix ? ($prefix . '_') : '') .
                \Stringy\Stringy::create($record->{$nameColumn})->slugify('_')->toUpperCase();

            if (!preg_match('/^[A-Z_]/', $name)) {
                $name = "_" . $name; // safer enum name
            }

            if (!preg_match('/^[A-Z_]([A-Z0-9_])?.*/', $name)) {
                throw new \RuntimeException("Cannot use '{$name}' as a ProtoBuf constant name for '{$enumName}'.");
            }
        }

        $record->id = $idColumn ? $record->{$idColumn} : null;
        $record->name = $name;

        $templateData['items'][] = $record;
    }
    $proto = $app->renderTemplate('enum.proto', $templateData);

    return $proto;
};

foreach ($proto_enums as $proto_enum => $proto_sql) {
    $proto_code = $createProtoEnumFromQuery($proto_enum, $proto_sql);
    $proto_file = $proto_path . DIRECTORY_SEPARATOR . $proto_enum . '.proto';
    $app->getCli()->out(" > " . str_replace($app->distPath, 'dist', $proto_file));
    file_put_contents($proto_file, $proto_code);
}

$app->getCli()->green("DONE!");
