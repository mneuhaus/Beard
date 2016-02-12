<?php

namespace Famelo\Beard\Command\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Setup command
 *
 */
class Hash extends Command {

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('site:hash');
		$this->setDescription('Add commit hooks and gerrit push remotes to all repositories');

		$this->addArgument(
			'site',
			InputArgument::OPTIONAL,
			'url of the site'
		);
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$baseUri = $this->input->getArgument('site');
		$fileHandle = fopen('hashes.txt', 'w');
		$this->scrapeSite($baseUri, $baseUri, $fileHandle);
	}

	protected function scrapeSite($uri, $baseUri, $fileHandle, $scraped = array()) {
		if (in_array($uri, $scraped)) {
			return;
		}
		if (stristr($uri, 'tx_template_cart')) {
			return;
		}
		$scraped[] = $uri;
		$request = \Requests::get($uri, array('connect_timeout' => 30, 'timeout' => 30));
		$filename = trim(str_replace($baseUri, '', $uri), '/') . '.html';
		if ($filename == '.html') {
			$filename = 'index.html';
		}
		// if (!file_exists(dirname($filename))) {
		// 	mkdir(dirname($filename), 0777, TRUE);
		// }
		// file_put_contents($filename, $request->body);
		fputcsv($fileHandle, array($uri, md5($request->body)));
		echo $filename . chr(10);
		preg_match_all('/href="(\/.*?)"/s', $request->body, $matches);
		foreach ($matches[1] as $match) {
			$pathinfo = pathinfo($match);
			if (isset($pathinfo['extension']) && $pathinfo['extension'] !== 'html') {
				continue;
			}
			$childUri = rtrim($baseUri, '/') . '/' . ltrim($match, '/');
			$this->scrapeSite($childUri, $baseUri, $fileHandle, $scraped);
		}
	}

}
