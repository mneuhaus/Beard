<?php
namespace Famelo\Beard\Command\Backup;

use Famelo\Beard\Command\AbstractSettingsCommand;
use Mia3\Koseki\ClassRegister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Dotenv\Dotenv;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

/**
 *
 */
class Test extends AbstractSettingsCommand
{

    /**
     * @override
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('backup:test');
        $this->setDescription('Test remote connection for backup storage');
    }

    /**
     * @override
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = new Dotenv(getcwd());
        $dotenv->load();

        $config = [
            'host' => getenv('beard_backup_host'),
            'username' => getenv('beard_backup_username'),
            'port' => getenv('beard_backup_port') ? getenv('beard_backup_port') : 22,
            'privateKey' => getenv('beard_backup_private_key'),
            'timeout' => 60,
        ];

        $adapter = new SftpAdapter($config);
        $filesystem = new Filesystem($adapter);

        $directory = getenv('beard_backup_path');
        if ($filesystem->has($directory) === false) {
            $filesystem->createDir($directory);
        }

        if ($adapter->isConnected() !== TRUE) {
            $output->writeln('<fg=red;bg=white>Connection failed</>
(forgot to accept host fingerprint?)
try connection through ssh manually: ssh ' . $config['username'] . '@' . $config['host'] . ' -p' . $config['port'] . '
            ');
            return;
        }

        $output->writeln('<fg=green;bg=black>Connection Successful</>');
    }

}
