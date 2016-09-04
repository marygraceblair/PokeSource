<?php

namespace metaunicorn\Pokedata;

/**
 * Imports CSV files into a SQL table
 */
class TableCsvImporter extends TableCsvHelper
{
    /**
     * @var bool|false|string
     */
    private $sqlPath;

    /**
     * CsvDbImporter constructor.
     * @param string $csvPath
     * @param bool|false|string $sqlPath if FALSE the SQL schema cache will be disabled
     * @param \PDO $db
     */
    public function __construct($csvPath, $sqlPath = false, \PDO $db)
    {
        $this->sqlPath = $sqlPath;
        parent::__construct($csvPath, $db);
    }

    public function isCacheEnabled()
    {
        return ($this->sqlPath !== false);
    }

    public function import(array $files = null, $tablePrefix = '', array $columnRules, $maxBulkInserts = 200)
    {
        if (is_null($files)) {
            $files = $this->getFiles();
        }
        $db = $this->getDb();

        foreach ($files as $i => $filename) {
            $csvFile = $this->getPath() . DIRECTORY_SEPARATOR . $filename;
            $tableNameNoPrefix = basename($filename, '.csv');
            $tableName = $tablePrefix . $tableNameNoPrefix;
            $currentColumnRules = array_merge(
                (isset($columnRules['*']) ? $columnRules['*'] : []),
                (isset($columnRules[$tableNameNoPrefix]) ? $columnRules[$tableNameNoPrefix] : [])
            );
            $columns = $this->getCsvColumns($filename);
            $newColumns = (empty($currentColumnRules) ? $columns :
                array_keys($this->applyColumnRules(array_combine($columns, $columns), $currentColumnRules)));
            $inserts = [];
            $csvLine = 0;
            $numInserts = 0;
            $tableSql = false;

            if (empty($columns) || empty($newColumns)) {
                $this->log('Something went wrong parsing "' . $filename . '" column names. ' .
                    'Skipping import for this file...');
                continue;
            }

            if (!$this->hasTable($tableName)) {
                // The table does not exist yet
                $this->log('Creating table "' . $tableName . '"...');
                $tableSql = $this->getTableCreationSql($tableNameNoPrefix, $tableName, $newColumns);
            } elseif (!$this->isTableEmpty($tableName)) {
                // The table is not empty
                $this->log('Skipping seeding of not empty table "' . $tableName . '"');
                continue;
            }

            if (($handle = fopen($csvFile, 'r')) !== false) {
                $db->beginTransaction();
                try {
                    if ($tableSql != false) {
                        $db->exec($tableSql);
                        $tableSql = false;
                    }

                    $this->log('Seeding table "' . $tableName . '"');
                    while (($row = fgetcsv($handle, null, ',')) !== false) {
                        if ($csvLine > 0) {
                            $rowAssoc = array_combine($columns, array_values($row));
                            $rowAssoc = (empty($currentColumnRules) ? $rowAssoc : $this->applyColumnRules($rowAssoc,
                                $currentColumnRules));

                            $inserts[] = $rowAssoc;
                            if (count($inserts) >= $maxBulkInserts) {
                                $numInserts += count($inserts);
                                $this->insert($tableName, $inserts);
                                $inserts = [];
                            }
                        }
                        $csvLine++;
                    }
                    fclose($handle);
                    if (count($inserts) > 0) { // if there is some bulk insert left
                        $numInserts += count($inserts);
                        $this->insert($tableName, $inserts);
                        $inserts = [];
                    }
                    $db->commit();
                } catch (\Exception $e) {
                    $this->log("WARNING: An SQL error occurred and the transaction will be rolled back.");
                    $db->rollBack();
                    throw $e;
                }
            }
            if ($numInserts > 0) {
                $this->log("$numInserts rows inserted in \"$tableName\"");
            } else {
                $this->log("No rows inserted in \"$tableName\"");
            }
        }
    }

    private function applyColumnRules(array $data, array $rules)
    {
        $newData = [];
        foreach ($data as $columnName => $value) {
            if (!array_key_exists($columnName, $rules)) {
                // No rules for this column, add it as it is
                $newData[$columnName] = $data[$columnName];
                continue;
            }
            $rule = $rules[$columnName];
            if (!$rule) {
                // Exclude column
                continue;
            } else {
                // Rename column
                $newData[$rule] = $data[$columnName];
            }
        }
        return $newData;
    }

    /**
     * Parses the column names of the CSV file (in the first line)
     *
     * @param string $filename CSV file
     *
     * @return \string[] The column names (not quoted)
     */
    public function getCsvColumns($filename)
    {
        $filename = $this->getPath() . DIRECTORY_SEPARATOR . $filename;
        $columns = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            $columns = fgetcsv($handle, null, ',');
            fclose($handle);
        }
        return $columns;
    }

    private function getTableCreationSql($tableName, $prefixedTableName, array $columnNames)
    {
        if ($this->hasTable($prefixedTableName)) {
            $this->log('Skipping table creation: Table "' . $prefixedTableName . '" already exists.');
            return false;
        }

        $this->log('Creating table "' . $prefixedTableName . '"...');
        $sqlFile = $this->getSqlPath() . DIRECTORY_SEPARATOR . $tableName . '.sql';

        if ($this->isCacheEnabled() && file_exists($sqlFile)) {
            // Load schema file if exists, and continue
            //return file_get_contents($sqlFile);
        }

        $definitions = [];
        $indexes = [];
        $hasPrimaryKey = false;
        foreach ($columnNames as $colName) {
            if (
                !$hasPrimaryKey
                && ($colName == 'id' || ($colName == $tableName . '_id'))
            ) {
                $definitions[] = "`{$colName}` INTEGER unsigned NOT NULL PRIMARY KEY";
                $hasPrimaryKey = true;
            } elseif (
                (preg_match('/^id_/', $colName))
                || (preg_match('/_id$/', $colName))
            ) {
                $definitions[] = "`{$colName}` INTEGER unsigned";
                $indexes[] = $colName;
            } elseif (
                (preg_match('/^is_/', $colName))
                || (in_array($colName, ['order']))
            ) {
                $definitions[] = "`{$colName}` INTEGER unsigned";
            } elseif (
                (preg_match('/^name_/', $colName))
                || (preg_match('/_name$/', $colName))
                || (in_array($colName, ['identifier', 'codename', 'name', 'slug', 'alias']))
            ) {
                $definitions[] = "`{$colName}` VARCHAR(255)";
            } else {
                $definitions[] = "`{$colName}` TEXT";
            }
        }
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $tableName
            . ' (' . PHP_EOL . '  ' . implode("," . PHP_EOL . '  ', $definitions) . PHP_EOL . ')' . ';' . PHP_EOL;

        foreach ($indexes as $colName) {
            $sql .= PHP_EOL . "CREATE INDEX IF NOT EXISTS `idx_${prefixedTableName}_${colName}`" .
                " ON `$prefixedTableName` (`${colName}`);" . PHP_EOL;
        }
        $sql = rtrim($sql, PHP_EOL . '; ') . ';' . PHP_EOL;

        if ($this->isCacheEnabled()) {
            if (!$this->getSqlPath() || !is_dir($this->getSqlPath())) {
                throw new \Exception('Cannot export table SQL because the path for storing SQL files
                does not exist. Table creation aborted.');
            }
            file_put_contents($sqlFile, $sql);
        }

        return $sql;
    }

    /**
     * @return bool|false|string
     */
    public function getSqlPath()
    {
        return $this->sqlPath;
    }

    /**
     * @param bool|false|string $sqlPath
     * @return $this
     */
    public function setSqlPath($sqlPath)
    {
        $this->sqlPath = $sqlPath;
        return $this;
    }
}