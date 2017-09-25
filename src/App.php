<?php

namespace Pokettomonstaa\Database;

use Doctrine\DBAL;
use GuzzleHttp\Client;
use League\CLImate\CLImate;
use PDO;
use Stringy\Stringy;

class App extends \ArrayObject
{
    /**
     * @var array
     */
    public $settings = [];
    /**
     * @var string
     */
    public $apiUrl;
    /**
     * @var string
     */
    public $rootPath;
    /**
     * @var string
     */
    public $buildPath;
    /**
     * @var string
     */
    public $distPath;
    /**
     * @var string
     */
    public $srcPath;
    /**
     * @var string
     */
    public $templatesPath;
    /**
     * @var string
     */
    public $vendorPath;
    /**
     * @var string
     */
    public $dbFile;
    /**
     * @var string
     */
    public $migrationsTable = 'db_migrations';
    /**
     * @var DBAL\Connection
     */
    private $db;
    /**
     * @var CLImate
     */
    private $cli;

    public function __construct()
    {
        $apiHost = isset($_ENV['INTERNAL_API_HOST']) ? $_ENV['INTERNAL_API_HOST'] : $_SERVER['HTTP_HOST'];
        $this->apiUrl = "http://{$apiHost}";
        $this->rootPath = isset($_ENV['PROJECT_PATH']) ? $_ENV['PROJECT_PATH'] : realpath(__DIR__ . '/../');
        $this->buildPath = isset($_ENV['BUILD_PATH']) ? $_ENV['BUILD_PATH'] : ($this->rootPath . '/build');
        $this->distPath = isset($_ENV['DIST_PATH']) ? $_ENV['DIST_PATH'] : ($this->rootPath . '/dist');
        $this->srcPath = isset($_ENV['SOURCE_PATH']) ? $_ENV['SOURCE_PATH'] : ($this->rootPath . '/src');
        $this->dbFile = isset($_ENV['DB_FILE']) ? $_ENV['DB_FILE'] : ($this->buildPath . '/pokedex.sqlite');
        $this->vendorPath = $this->rootPath . '/vendor';
        $this->templatesPath = $this->srcPath . '/templates';
        $this->migrationsPath = $this->srcPath . '/migrations';
    }

    public function __destruct()
    {
        $this->getDb()->close();
    }

    /**
     * Creates the directory if it does not exist
     *
     * @param string $dir
     *
     * @return string The dir argument
     */
    public function assureDir($dir)
    {
        if (empty($dir)) {
            throw new \LogicException('$dir is empty!');
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Renders a twig template
     *
     * @param string $name Template name, without the .twig suffix/extension
     * @param array  $vars
     *
     * @return string
     */
    public function renderTemplate($name, array $vars = [])
    {
        if (!$this->twig) {
            $loader = new \Twig_Loader_Filesystem($this->templatesPath);
            $this->twig = new \Twig_Environment($loader);
        }

        return $this->twig->render($name . '.twig', $vars);
    }

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
    public function createProtoBufEnumFromQuery(
        $enumName,
        $sql,
        $usePrefix = false,
        $nameColumn = 'name',
        $idColumn = 'id'
    ) {
        $records = $this->getDb()->query($sql);
        $prefix = Stringy::create($enumName)->underscored()->toUpperCase();
        $defaultItem = ['name' => '_DEFAULT', 'id' => 0];
        $templateData = ['enumName' => $enumName, 'items' => [(object)$defaultItem]];

        while ($record = $records->fetch(\PDO::FETCH_OBJ)) {
            $id = null;
            $name = null;

            if ($nameColumn) {
                $name = ($usePrefix ? ($prefix . '_') : '') .
                    Stringy::create($record->{$nameColumn})->slugify('_')->toUpperCase();

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
        $proto = $this->renderTemplate('enum.proto ', $templateData);

        return $proto;
    }

    /**
     * Executes SQL using transactions. It commits or rolls back automatically if something went wrong.
     *
     * @param string $statement
     *
     * @return int Number of affected rows
     * @throws \Exception
     * @throws \Throwable
     */
    public function dbExecTransactional($statement)
    {
        $db = $this->getDb();
        try {
            $db->beginTransaction();
            $affected_rows = $db->exec($statement);
            $db->commit();

            return $affected_rows;
        } catch (\Exception $exception) {
            $this->getCli()->red()->out(' FAILED. Rolling back.');
            $db->rollBack();
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->getCli()->red()->out(' FAILED. Rolling back.');
            $db->rollBack();
            throw $throwable;
        } finally {
            $errors = $db->errorInfo();
            if (is_array($errors) && ($errors[0] != PDO::ERR_NONE)) {
                $this->getCli()->error('SQL Error:');
                $this->getCli()->out(print_r($errors, true));
            }
        }
    }

    /**
     * @return Client
     */
    public function getApi()
    {
        if (!$this->api) {
            $this->api = new Client([
                // Base URI is used with relative requests
                'base_uri' => $this->apiUrl,
                'timeout'  => 15.0,
            ]);
        }

        return $this->api;
    }

    /**
     * Sends a GET request to the data API and returns the decoded response
     *
     * @param string $path
     * @param array  $query Query string parameters (like: include, filter, page, columns, order, satisfy, transform,
     *                      etc.) Documentation can be found at https://github.com/mevdschee/php-crud-api
     *
     * @return array The decoded JSON response if successful
     */
    public function sendApiRequest($path, array $query = [])
    {
        // Params that should be comma separated if defined as array
        foreach (['include', 'columns'] as $param) {
            if (isset($query[$param]) && is_array($query[$param])) {
                $query[$param] = implode(',', $query[$param]);
            }
        }

        $response = $this->getApi()->get('/api/' . ltrim($path, '/'), ['query' => $query]);

        return \GuzzleHttp\json_decode($response->getBody()->__toString(), true);
    }

    /**
     * @return CLImate
     */
    public function getCli()
    {
        if (!$this->cli) {
            $this->cli = new CLImate();
        }

        return $this->cli;
    }

    /**
     * @return DBAL\Connection
     */
    public function getDb()
    {
        if (!$this->db) {
            $config = [
                'url' => 'sqlite:///' . $this->dbFile,
            ];
            $this->db = DBAL\DriverManager::getConnection($config, new DBAL\Configuration());
            $platform = $this->db->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping(null, 'string');
            $platform->registerDoctrineTypeMapping('', 'string');
            $platform->registerDoctrineTypeMapping('num', 'integer');
        }

        return $this->db;
    }

    /**
     * @param string      $relative_path
     * @param string|null $root_element Root element name
     *
     * @return array
     */
    public function loadShowdownJson($relative_path, $root_element = null)
    {
        $filename = $this->buildPath . "/pokemon-showdown/${relative_path}.json";

        $data = (array)json_decode(file_get_contents($filename), true);

        if (!is_null($root_element)) {
            return isset($data[$root_element]) ? $data[$root_element] : [];
        }

        return $data;
    }

    /**
     * @param string   $migration_name
     * @param callable $callable
     * @param array    $args
     *
     * @return mixed|null
     */
    public function runMigration($migration_name, $callable, array $args = [])
    {
        $found_migration = $this->getDb()
            ->query("SELECT * FROM `$this->migrationsTable` WHERE name='${migration_name}'")
            ->fetch(PDO::FETCH_ASSOC);

        if (is_array($found_migration) && ($found_migration['name'] == $migration_name)) {
            $this->getCli()->whisper()->out("Skipping already executed '${migration_name}' migration...");

            return null;
        }

        $this->getCli()->green()->inline("Running '${migration_name}' migration...");

        $result = call_user_func_array($callable, $args);
        $this->dbExecTransactional("INSERT INTO `$this->migrationsTable` (name) VALUES ('$migration_name')");

        return $result;
    }

    /**
     * @return \Closure[]
     */
    public function getMigrations()
    {
        $raw_sql_migration = function ($sql) {
            return $this->dbExecTransactional($sql);
        };

        $migrations_path = $this->migrationsPath;

        $dir_iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($migrations_path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $migration_callables = [];

        foreach ($dir_iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
                $migration_name = strtolower(str_replace('.php', '', pathinfo($file, PATHINFO_FILENAME)));

                $migration_callables[$migration_name] = [include $file, []];
            }
        }

        foreach ($dir_iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "sql") {
                $migration_name = strtolower(str_replace('.sql', '', pathinfo($file, PATHINFO_FILENAME)));

                $sql = file_get_contents($file);

                if (isset($migration_files[$migration_name])) {
                    $migration_callables[$migration_name][] = [$sql];
                } else {
                    $migration_callables[$migration_name] = [$raw_sql_migration, [$sql]];
                }
            }
        }

        ksort($migration_callables, SORT_ASC);

        return $migration_callables;
    }

    public function exportDbToCsv()
    {
        $db = $this->getDb();

        $export_path = $this->assureDir($this->distPath . '/csv');

        $tables = $db->getSchemaManager()->listTables();

        $this->getCli()->lightBlue("Creating CSV data files...");

        foreach ($tables as $table) {
            $tableName = $table->getName();

            if (
                in_array($tableName, ['db_migrations'])
                # Ignore Conquest game data (is not main series) and other data specific to special gen features.
                | preg_match('/^(conquest|pokeathlon|pal_park).*/', $tableName)
            ) {
                continue;
            }

            $export_file = $export_path . "/{$tableName}.csv";
            # @unlink($export_file);

            $output = '';
            $this->getCli()->out(" > " . str_replace($this->distPath, 'dist', $export_file));

            if ($table->hasPrimaryKey()) {
                $orderBy = "ORDER BY 1 ASC";
            } else {
                $orderBy = "ORDER BY 1 ASC, 2 ASC";
            }

            exec(
                'sql2csv' .
                ' --db "sqlite:///' . $this->dbFile . '"' .
                ' --query "SELECT * FROM \\`' . $tableName . '\\` ' . $orderBy . '"' .
                " > ${export_file}",
                $output
            );

            $output = (array)$output;
            foreach ($output as $line) {
                $this->getCli()->out($line);
            }
        }
    }
}