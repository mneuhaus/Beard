<?php

namespace Famelo\Beard\Backup\Sources;

use Symfony\Component\Finder\Finder;


/**
 */
class FilesSource extends Finder {
	public function __construct($configuration = NULL) {
		parent::__construct();
		$this->files()->in(getcwd());

		if (is_array($configuration)) {
			foreach ($configuration as $key => $value) {
				if (method_exists($this, $key)) {
					$this->$key($value);
				}
			}
		}
	}

	public function gatherFiles($sourcePath) {
		$files = array();
		foreach ($this as $file) {
			$files[] = $file->getRelativePathname();
            $tmpFile = path($sourcePath, $file->getRelativePathname());
            if (!is_dir(dirname($tmpFile))) {
             mkdir(dirname($tmpFile), 0777, true);
            }
            copy($file, $tmpFile);
		}
		return $files;
	}

	/**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     * $finder->notPath('some/special/dir')
     * $finder->notPath('/some\/special\/dir/') // same as above
     *
     * Use only / as dirname separator.
     *
     * @param string $pattern A pattern (a regexp or a string)
     *
     * @return Finder The current Finder instance
     *
     * @see Symfony\Component\Finder\Iterator\FilenameFilterIterator
     */
    public function notPaths($patterns) {
    	foreach ((array) $patterns as $pattern) {
        	$this->notPath($pattern);
    	}

        return $this;
    }
}

?>