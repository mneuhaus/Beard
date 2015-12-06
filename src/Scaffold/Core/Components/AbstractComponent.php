<?php
namespace Famelo\Beard\Scaffold\Core\Components;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

abstract class AbstractComponent {
    /**
     * @var string
     */
    public $name;

    public function getId() {
        return sha1(spl_object_hash($this));
    }

    public function getPrefix() {
        $path = trim(
            str_replace(
                array(
                    'Famelo\Beard\Scaffold',
                    '\\'
                ),
                array(
                    '',
                    '-'
                ),
                get_class($this)
            ),
            '-'
        ) . '.' . $this->getId();
        return String::pathToformName($path);
    }
}
