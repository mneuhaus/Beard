<?php

namespace Famelo\Beard\Backup\Sources;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Finder\Finder;


/**
 */
class MySqlSource {
    /**
     * @var array
     */
    protected $configuration = array(
        'driver' => 'pdo_mysql'
    );

    public function __construct($configuration = NULL) {
        $this->configuration = array_merge(
            $this->configuration,
            $configuration
        );
    }

    public function gatherFiles($sourcePath) {
        $command = array('mysqldump');
        if (isset($this->configuration['user'])) {
            $command[] = '-u' . $this->configuration['user'];
        }
        if (isset($this->configuration['password'])) {
            $command[] = '-p' . $this->configuration['password'];
        }
        if (isset($this->configuration['host'])) {
            $command[] = '-h' . $this->configuration['host'];
        }

        $command[] = $this->configuration['dbname'];

        if (isset($this->configuration['skip'])) {
            $tableNames = $this->getTableNames();
            foreach (explode(',', $this->configuration['skip']) as $pattern) {
                foreach ($tableNames as $tableName) {
                    $result = preg_match('/' . $pattern . '/', $tableName);
                    if ($result > 0) {
                        $command[] = '--ignore-table=' . $this->configuration['dbname'] . '.' . $tableName;
                    }
                }
            }
        }

        $filename = path($sourcePath, $this->configuration['dbname'] . '.sql');

        $command[] = '> ' . $filename;

        $this->executeShellCommand(implode(' ', $command));

        return array($filename);
    }

    public function getTableNames() {
        $configuration = new \Doctrine\DBAL\Configuration();
        $db = DriverManager::getConnection($this->configuration, $configuration);
        $sql = "SHOW TABLES";
        $results = $db->query($sql);
        $tableNames = array();
        foreach ($results as $key => $value) {
            $tableNames[] = current($value);
        }
        return $tableNames;
    }

    public function executeShellCommand($command) {
        $output = '';
        $fp = popen($command, 'r');
        while (($line = fgets($fp)) !== FALSE) {
            $output .= $line;
        }
        pclose($fp);
        return trim($output);
    }
}

?>