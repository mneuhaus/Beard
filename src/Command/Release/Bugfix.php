<?php
namespace Famelo\Beard\Command\Release;

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
class Bugfix extends AbstractSettingsCommand
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
        $this->setName('release:bugfix');
        $this->setDescription('trigger a release of a bugfix version');
    }

    /**
     * @override
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = $this->getSettings($input, $output);
        var_dump($settings);
    }
}
