<?php
namespace Famelo\Beard\Command;

use Dotenv\Dotenv;
use Famelo\Beard\Command\AbstractSettingsCommand;
use Famelo\Beard\Command\Backup\Database;
use Famelo\Beard\Command\Backup\Userdata;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Mia3\Koseki\ClassRegister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 *
 */
class Backup extends AbstractSettingsCommand
{

    /**
     * The output handler.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @override
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('backup');
        $this->setAliases(array(
            'backup:all',
        ));
        $this->setDescription('Backup database and userdata of the project in this directory');

        $this->addArgument(
            'file',
            InputArgument::OPTIONAL,
            'filename to safe the backups to'
        );

        $this->addOption('send-to-remote', null, InputOption::VALUE_NONE,
            'send this backup to the configured offsite location');
    }

    /**
     * @override
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getArgument('file'))) {
            $input->setArgument('file', 'backup-'.date('d.m.Y-H.i.s'));
        }

        $databaseCommand = new Database();
        $databaseCommand->execute($input, $output);

        $userdata = new Userdata();
        $userdata->execute($input, $output);

        if ($input->getOption('send-to-remote') === true) {
            $this->sendToRemote($input, $output);
        }
    }

    protected function sendToRemote($input, $output)
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

        $file = $input->getArgument('file');
        $stream = fopen($file.'.tar.gz', 'r+');
        $filesystem->writeStream(rtrim($directory, '/').'/'.$file.'.tar.gz', $stream);

        unlink($file . '.sql');
        unlink($file . '.tar.gz');

        $this->cleanup($filesystem);
    }

    public function getMaxSize() {
        $size = getenv('beard_backup_max_size');
        preg_match('/([0-9]*)(kb|mb|gb|tb)/', $size, $match);
        $units = array(
            'kb' => 1024,
            'mb' => pow(1024, 2),
            'gb' => pow(1024, 3),
            'tb' => pow(1024, 4)
        );
        return $match[1] * $units[$match[2]];
    }

    public function cleanup($filesystem) {
        $directory = getenv('beard_backup_path');
        $existingBackups = $filesystem->listContents($directory, true);
        $maxSize = $this->getMaxSize();
        usort($existingBackups, function($a, $b){
            return $a['timestamp'] < $b['timestamp'];
        });
        $totalSize = 0;
        foreach ($existingBackups as $key => $existingBackup) {
            $totalSize+= $existingBackup['size'];
            if ($totalSize > $maxSize) {
                $filesystem->delete($existingBackup['path']);
            }
        }
    }
}
