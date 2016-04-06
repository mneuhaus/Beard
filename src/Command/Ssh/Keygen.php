<?php
namespace Famelo\Beard\Command\Ssh;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use phpseclib\Crypt\RSA;

/**
 *
 */
class Keygen extends Command {

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('ssh:keygen');
		$this->setDescription('Create an SSH Public/Private Keypair');
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Generating public/private rsa key pair.');
		$helper = $this->getHelper('question');
		$defaultPath = getenv("HOME") . '/.ssh/id_rsa';
		$path = $helper->ask($input, $output, new Question('Enter file in which to save the key [' . $defaultPath . ']: ', $defaultPath));

        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
        $rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $pair = $rsa->createKey(4096);

		if (file_exists($path)) {
			$output->writeln('<error>File already exists, abort!</error>');
			return;
		}
		mkdir(dirname($path), 0600, true);
		file_put_contents($path, $pair['privatekey']);
		file_put_contents($path . '.pub', $pair['publickey']);
		chmod($path, 0600);
		chmod($path . '.pub', 0600);

		$output->writeln('<info>Your identification has been saved in ' . $path . '</info>');
		$output->writeln('<info>Your public key has been saved in ' . $path . '.pub</info>');
	}
}
