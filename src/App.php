<?php

namespace metaunicorn\Pokedata;

use metaunicorn\Pokedata\Orm\Db;

class App extends CliAware
{
    /**
     * @var array
     */
    private $paths = [];

    /**
     * @var Db
     */
    private $db;

    /**
     * @var array
     */
    private $config = [];

    /**
     * App constructor.
     *
     * @param string $rootPath
     * @param array $config
     * @param Db|null $db
     */
    public function __construct($rootPath, array $config, Db $db = null)
    {
        $realRootPath = realpath($rootPath);
        if ( ! is_dir($realRootPath)) {
            throw new \InvalidArgumentException("Invalid directory given: " . $rootPath);
        }

        $this->config = $config;

        $this->initDefaultPaths($realRootPath);

        if ($db) {
            $this->setDb($db);
        }
    }

    /**
     * @param Db $db
     *
     * @return static
     */
    public function setDb(Db $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * @return Db
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * @param string $name
     * Predefined config: table_prefix, csv_import_rules
     *
     * @param mixed|null $default
     *
     * @return null|string
     */
    public function getConfig($name, $default = null)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setPath($name, $value)
    {
        $this->paths[$name] = $value;
    }

    /**
     * @param string $name Path name.
     * Predefined paths: root, src, data, csv, db, sql, migrations, tmp, tmp_csv, tmp_db, tmp_sql
     *
     * @return string|null
     */
    public function getPath($name)
    {
        return isset($this->paths[$name]) ? $this->paths[$name] : null;
    }

    /**
     * @param string $rootPath
     */
    private function initDefaultPaths($rootPath)
    {
        $ds = DIRECTORY_SEPARATOR;

        $this->paths = [
            'root'    => $rootPath,
            'src'     => $rootPath . $ds . 'src',
            'tmp'     => $rootPath . $ds . 'tmp',
            'tmp_csv' => $rootPath . $ds . 'tmp' . $ds . 'csv',
            'tmp_sql' => false,
            'tmp_db'  => $rootPath . $ds . 'tmp' . $ds . 'database.sqlite',

            'data' => $rootPath . $ds . 'data',
            'csv'  => $rootPath . $ds . 'data' . $ds . 'csv',
            'sql'  => $rootPath . $ds . 'data' . $ds . 'sql',
            'db'   => $rootPath . $ds . 'data' . $ds . 'database.sqlite',

            'migrations' => $rootPath . $ds . 'migrations',
        ];

        // Paths can also be overridden by config
        $this->paths = array_merge($this->paths, $this->getConfig('paths', []));

        // Directories to be checked and created
        $checkDirs = [
            'tmp',
            'tmp_csv',
            'tmp_sql',
            'data',
            'csv',
            'sql',
        ];

        // Create dirs
        foreach ($checkDirs as $dirAlias) {
            $dir = $this->paths[$dirAlias];
            if ($dir && ! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

}