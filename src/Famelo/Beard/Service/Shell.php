<?php

namespace Famelo\Beard\Service;


/**
 * Sets up the application.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Shell {

	public function __construct() {
		# code...
	}

	/**
	 * Execute a shell command via SSH
	 *
	 * @param mixed $command
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param boolean $logOutput TRUE if the output of the command should be logged
	 * @return array
	 */
	protected function executeRemoteCommand($command, Node $node, Deployment $deployment, $logOutput = TRUE) {
		$command = $this->prepareCommand($command);
		$deployment->getLogger()->log('$' . $node->getName() . ': "' . $command . '"', LOG_DEBUG);

		if ($node->hasOption('remoteCommandExecutionHandler')) {
			$remoteCommandExecutionHandler = $node->getOption('remoteCommandExecutionHandler');
			/** @var $remoteCommandExecutionHandler callable */
			return $remoteCommandExecutionHandler($this, $command, $node, $deployment, $logOutput);
		}

		$username = $node->hasOption('username') ? $node->getOption('username') : NULL;
		if (!empty($username)) {
			$username = $username . '@';
		}
		$hostname = $node->getHostname();

			// TODO Get SSH options from node or deployment
		$sshOptions = array('-A');
		if ($node->hasOption('port')) {
			$sshOptions[] = '-p ' . escapeshellarg($node->getOption('port'));
		}
		if ($node->hasOption('password')) {
			$sshOptions[] = '-o PubkeyAuthentication=no';
		}

		$sshCommand = 'ssh ' . implode(' ', $sshOptions) . ' ' . escapeshellarg($username . $hostname) . ' ' . escapeshellarg($command) . ' 2>&1';

		if ($node->hasOption('password')) {
			$surfPackage = $this->packageManager->getPackage('TYPO3.Surf');
			$passwordSshLoginScriptPathAndFilename = \TYPO3\Flow\Utility\Files::concatenatePaths(array($surfPackage->getResourcesPath(), 'Private/Scripts/PasswordSshLogin.expect'));
			$sshCommand = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')), $sshCommand);
		}

		return $this->executeProcess($deployment, $sshCommand, $logOutput, '    > ');
	}

	/**
	 * @param mixed $command
	 * @return array The exit code of the command and the returned output
	 */
	public function executeProcess($command) {
		$command = $this->prepareCommand($command);
		$returnedOutput = '';
		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$returnedOutput .= $line;
		}
		$exitCode = pclose($fp);
		return array($exitCode, trim($returnedOutput));
	}

	/**
	 * Prepare a command
	 *
	 * @param mixed $command
	 * @return string
	 */
	protected function prepareCommand($command) {
		if (is_string($command)) {
			return trim($command);
		} elseif (is_array($command)) {
			return implode(' && ', $command);
		}
	}
}

?>