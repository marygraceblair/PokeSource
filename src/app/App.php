<?php

namespace Pokettomonstaa\App;

use Doctrine\DBAL;
use GuzzleHttp\Client;
use League\CLImate\CLImate;
use PDO;

class App
{
    /**
     * @var array
     */
    public $settings = [];
    /**
     * @var string Internal API URL
     */
    public $apiUrl;
    /**
     * @var string
     */
    public $baseUrl;
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
    public $viewsPath;
    /**
     * @var string
     */
    public $publicPath;
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
     * @var string
     */
    public $migrationsPath;
    /**
     * @var DBAL\Connection
     */
    private $db;
    /**
     * @var CLImate
     */
    private $cli;
    /**
     * @var Client
     */
    private $api;
    /**
     * @var Repo
     */
    private $repo;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct()
    {
        $srv = collect($_SERVER);
        $requestHost = isset($srv['API_HOST']) ? $srv['API_HOST'] :
            (isset($srv['HTTP_HOST']) ? $srv['HTTP_HOST'] : 'localhost:8151');
        $internalApiHost = isset($srv['INTERNAL_API_HOST']) ? $srv['INTERNAL_API_HOST'] : $requestHost;
        $this->apiUrl = "http://{$internalApiHost}";
        $this->baseUrl = "http://{$requestHost}";
        $this->rootPath = isset($srv['PROJECT_PATH']) ? $srv['PROJECT_PATH'] : realpath(__DIR__ . '/../');
        $this->buildPath = isset($srv['BUILD_PATH']) ? $srv['BUILD_PATH'] : ($this->rootPath . '/build');
        $this->distPath = isset($srv['DIST_PATH']) ? $srv['DIST_PATH'] : ($this->rootPath . '/dist');
        $this->srcPath = isset($srv['SOURCE_PATH']) ? $srv['SOURCE_PATH'] : ($this->rootPath . '/src');
        $this->dbFile = isset($srv['DB_FILE']) ? $srv['DB_FILE'] : ($this->buildPath . '/pokedex.sqlite');
        $this->publicPath = $this->srcPath . '/web/public';
        $this->vendorPath = $this->rootPath . '/vendor';
        $this->viewsPath = $this->srcPath . '/resources/views';
        $this->migrationsPath = $this->srcPath . '/tasks/migrations';
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
            $loader = new \Twig_Loader_Filesystem($this->viewsPath);
            $this->twig = new \Twig_Environment($loader);
        }

        return $this->twig->render($name . '.twig', $vars);
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
     * @return Repo
     */
    public function getRepo()
    {
        if (!$this->repo) {
            $this->repo = new Repo($this);
        }

        return $this->repo;
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
        $query = $this->formatApiQueryParams($query);

        $response = $this->getApi()->get('/api/' . ltrim($path, '/'), ['query' => $query]);

        return \GuzzleHttp\json_decode($response->getBody()->__toString(), true);
    }

    /**
     * Builds an API URL given a path and query string
     *
     * @param string $path
     * @param array  $query  Query string parameters (like: include, filter, page, columns, order, satisfy, transform,
     *                       etc.) Documentation can be found at https://github.com/mevdschee/php-crud-api
     *
     * @param bool   $public If true, the public API url will be returned, otherwise the internal one
     *
     * @return string
     */
    public function buildApiUrl($path, array $query = [], $public = true)
    {
        $query = $this->formatApiQueryParams($query);

        return ($public ? $this->baseUrl : $this->apiUrl) . '/api/' . ltrim($path,
                '/') . '?' . http_build_query($query);
    }

    /**
     * @param array $query
     *
     * @return array
     */
    private function formatApiQueryParams(array $query)
    {
        // Params that should be comma separated if defined as array
        foreach (['include', 'columns'] as $param) {
            if (isset($query[$param]) && is_array($query[$param])) {
                $query[$param] = implode(',', $query[$param]);
            }
        }

        return $query;
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
     * Executes a PHP script file
     *
     * @param string $file
     *
     * @return mixed The PHP script return value
     */
    public function execFile($file)
    {
        // This is safer as variables are scoped.
        $this->getCli()->whisper('Executing ' . $file . ' ...');

        return include $file;
    }

    /**
     * Executes a shell command
     *
     * @param string $command
     *
     * @param bool   $verbose if true, the output will be printed
     *
     * @return int|null Return status of the executed command
     */
    public function execCmd($command, $verbose = true)
    {
        $output = [];
        $result = null;

        exec($command . ' 2>&1', $output, $result);

        if (!$verbose) {
            return $result;
        }

        foreach ($output as $line) {
            echo $line . PHP_EOL;
        }

        return $result;
    }
}
