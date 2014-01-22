<?php

namespace Famelo\Beard\Backup\Sources;

use Symfony\Component\Finder\Finder;


/**
 */
class WordpressSource extends Finder {

    /**
     * @var string
     */
    protected $sources = array(
        'Files'=> array(
            'type'=> "Files",
            'ignoreVCS' =>  true,
            'notPaths' => array(
                'wp-content/cache',
                'wp-content/upgrade'
            )
        ),
        'Database'=> array(
            'type'=> "MySql"
        )
    );

    public function __construct($configuration = NULL) {
        include_once('wp-load.php');

        $this->sources['Database'] = array_merge($this->sources['Database'], array(
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => DB_HOST
        ));
    }

    public function gatherFiles() {
        return array();
    }

    public function getSources() {
        return $this->sources;
    }
}

?>