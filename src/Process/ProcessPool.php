<?php
namespace Famelo\Beard\Process;

use Symfony\Component\Process\Process;

/**
 * Class ProcessPool
 * <code>
 * $pool = new ProcessPool(
 *     'php child.php', // command
 *     [ // arguments
 *         'one',
 *         'two',
 *         'three',
 *     ],
 *     6 // number processes (running at same time)
 * );
 *
 * $pool->run(function ($arg, $result) {
 *     echo "{$arg}: {$result}";
 * });
 * </code>
 * https://gist.github.com/nicklasos/1a6c010c4ce05245d801
 */
class ProcessPool
{
    /**
     * Number of processes that running at same time
     * @var int
     */
    protected $numProcesses;

    /**
     * Current processes
     * (Now running)
     *
     * [
     *    ['process' => Process, 'argument' => Argument that process running with],
     *    ...
     * ]
     *
     * @var array
     */
    protected $processes = [];

    /**
     * @var array
     */
    protected $queue = [];

    /**
     * milliseconds
     * sleep between ticks
     * @var int
     */
    protected $tick;

    /**
     * @param int $numProcesses number of running processes at same time
     * @param int $tick sleep between ticks
     */
    public function __construct($numProcesses = 3, $tick = 100)
    {
        $this->tick = $tick;
        $this->numProcesses = $numProcesses;
    }

    /**
     */
    public function run()
    {
        do {
            foreach ($this->processes as $key => $process) {
                if (!$process['process']->isRunning()) {
                    unset($this->processes[$key]);
                }
            }
            if (count($this->processes) < $this->numProcesses) {
                for ($i = 0; $i < ($this->numProcesses - count($this->processes)); $i++) {
                    $command = array_shift($this->queue);
                    $process = new Process($command);
                    $process->start(function($type, $buffer){
                        echo $buffer;
                    });
                    $this->processes[] = [
                        'command' => $command,
                        'process' => $process
                    ];
                }
            }
            usleep($this->tick);
        } while (count($this->processes) > 0);
    }

    /**
     * @param string $command
     */
    public function call($command)
    {
        $this->queue[] = $command;
    }
}