<?php
namespace Famelo\Beard\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\AbstractSettingsCommand;
use cogpowered\FineDiff\Diff;

/**
 *
 */
class Compare extends AbstractSettingsCommand {

	/**
	 * @var array
	 */
	protected $uuidWordList = array('able','about','above','abuse','accept','accuse','across','act','attack','attempt','attend','battle','be','carry','case','cat','catch','cause','climb','clock','close','confirm','conflict','congratulate','Congress','connect','conservative','consider','constitution','contact','contain','container','continent','continue','direct','direction','dirt','disappear','disarm','disaster','discover','discrimination','discuss','disease','dismiss','dispute','dissident','distance','dive','divide','do','early','earn','earth','earthquake','ease','east','easy','eat','ecology','economy','edge','education','effect','effort','egg','evidence','evil','exact','examine','example','excellent','except','exchange','excuse','execute','famous','fan','far','farm','fast','fat','father','favorite','fear','federal','feed','feel','female','fence','fertile','few','field','fierce','flee','float','flood','floor','flow','flower','fluid','fly','fog','follow','food','fool','foot','for','force','foreign','forest','forget','forgive','form','former','forward','free','freedom','freeze','fresh','he','head','headquarters','heal','health','hear','heat','heavy','helicopter','help','hospital','hostage','hostile','hot','ignore','illegal','imagine','immediate','immigrant','import','interest','interfere','line','link','liquid','mass','mate','material','mathematics','matter','may','mayor','meal','mean','measure','meat','media','medicine','meet','melt','member','nation','native','natural','near','necessary','need','negotiate','nowhere','nuclear','number','parachute','parade','pardon','parent','parliament','part','partner','party','pass','peace','people','postpone','pour','poverty','power','praise','private','prize','probably','public','publication','publish','pull','pump','punish','purchase','pure','purpose','push','put','quality','question','quick','rain','raise','rare','rate','slide','slow','small','smash','smell','smoke','smooth','snow','so','suspend','swallow','swear in','sweet','swim','sympathy','system','take','talk','tall','tank','target','taste','tax','tea','teach','trial','tribe','trick','trip','troops','trouble','truce','truck','true','vaccine','valley','value','write','wrong','year','yellow','yes','yesterday','yet','you','young','zero','zoo');

	/**
	 * @var array
	 */
	protected $runtimeCache = array();

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:compare');
		$this->setDescription('Compare database states');
		$this->addOption('baseline', null, InputOption::VALUE_NONE, 'create/update baseline');
		$this->addOption('translate-uuids', null, InputOption::VALUE_NONE, 'show 3 words instead of uuid');
		$this->addOption('columns', null, InputOption::VALUE_OPTIONAL, 'columns to always include');
		$this->addOption('orderby', null, InputOption::VALUE_OPTIONAL, 'order by');

		$this->addArgument(
			'tableName',
			InputArgument::OPTIONAL,
			'name or part of the name of a table'
		);
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;
		$this->settings = $this->getSettings($input, $output);

		if ($this->settings === NULL) {
			$output->writeln('could not find any project settings');
			return;
		}

		$this->connection = new \mysqli(
			$this->settings->getHost(),
			$this->settings->getUsername(),
			$this->settings->getPassword(),
			$this->settings->getDatabase()
		);

		if (!file_exists('.beard-tmp')) {
			mkdir('.beard-tmp');
		}

		if ($input->getOption('baseline') === TRUE) {
			$this->createBaseline();
		} else {
			$this->compare();
		}
	}

	public function compare() {
		$this->output->getFormatter()->setStyle('ins', new OutputFormatterStyle('green', 'black'));
		$this->output->getFormatter()->setStyle('del', new OutputFormatterStyle('red', 'black'));

		$result = $this->connection->query('SHOW TABLES');
		$tables = $result->fetch_all();
		$tableNameArgument = $this->input->getArgument('tableName');
		foreach ($tables as $table) {
			$tableName = $table[0];

			if (!empty($tableNameArgument)) {
				if (preg_match('/(' . $tableNameArgument . ')/', $tableName, $matches) == 0) {
					continue;
				}
			}

			if ($this->settings->isTemporaryTable($tableName) === TRUE) {
				continue;
			}

			$primaryKeys = $this->getPrimaryKeys($tableName);
			$query = 'SELECT * FROM ' . $tableName;
			if (!empty($this->input->getOption('orderby'))) {
				$query .= ' ORDER BY ' . $this->input->getOption('orderby');
			}
			$result = $this->connection->query($query);
			$diffs = $this->generateDiffs(
				$result->fetch_all(MYSQLI_ASSOC),
				$this->getCsv($tableName),
				$tableName
			);

			if (count($diffs) == 0) {
				continue;
			}

			$this->output->writeln('found changes in:  ' . $tableName);

			$columnsUsed = array_keys(current($diffs));

			$columns = $primaryKeys;
			$table = new Table($this->output);
			$table
				->setHeaders($columnsUsed)
				->setRows($diffs);
			$table->render();

			unset($currentRows, $baselineRows);
		}
	}

	public function createBaseline() {
		$result = $this->connection->query('SHOW TABLES');
		$tables = $result->fetch_all();
		$tableNameArgument = $this->input->getArgument('tableName');
		foreach ($tables as $table) {
			$start = microtime(TRUE);
			$tableName = $table[0];

			if (!empty($tableNameArgument)) {
				if (preg_match('/(' . $tableNameArgument . ')/', $tableName, $matches) == 0) {
					continue;
				}
			}
			if ($this->settings->isTemporaryTable($tableName) === TRUE) {
				continue;
			}

			$this->output->writeln('processing ' . $tableName, OutputInterface::VERBOSITY_VERBOSE);
			$result = $this->connection->query('SELECT * FROM ' . $tableName);
			$fileHandle = fopen('.beard-tmp/' . $tableName . '.csv', 'w');
			foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
				$primaryKeys = $this->getPrimaryKeys($tableName);
				$keys = array();
				foreach ($primaryKeys as $primaryKey) {
					$keys[] = $row[$primaryKey];
				}
				array_unshift($row, implode('', $keys));
				fputcsv($fileHandle, $row);
			}
			$this->output->writeln('done in ' . (microtime(TRUE) - $start) . 's', OutputInterface::VERBOSITY_VERBOSE);
		}
	}

	public function getCsv($tableName) {
		$columNames = $this->getColumnNames($tableName);
		$fileHandle = fopen('.beard-tmp/' . $tableName . '.csv', 'r');
		array_unshift($columNames, 'primaryKey');
		$rows = array();
		while ($data = fgetcsv($fileHandle, 99999999)) {
			$row = array();
			$row = array_combine($columNames, $data);
			$key = array_shift($data);
			if ($key == 6) {
			}
			$rows[$key] = $row;
		}
		return $rows;
	}

	public function createPrimaryKey($row, $tableName) {
		$primaryKeys = $this->getPrimaryKeys($tableName);
		return implode('', array_intersect_key($row, array_flip($primaryKeys)));
	}

	public function getPrimaryKeys($tableName) {
		if (!isset($this->runtimeCache['primaryKeys'][$tableName])) {
			$result = $this->connection->query('SHOW INDEX FROM ' . $tableName);
			$this->runtimeCache['primaryKeys'][$tableName] = array();
			foreach ($result->fetch_all() as $row) {
				if ($row[2] == 'PRIMARY') {
					$this->runtimeCache['primaryKeys'][$tableName][] = $row[4];
				}
			}
			if (empty($this->runtimeCache['primaryKeys'][$tableName])) {
				$candidates = array('id', 'uid', 'uid_local', 'uid_foreign', 'fieldname', 'hash');
				$result = $this->connection->query('SHOW INDEX FROM ' . $tableName);
				foreach ($result->fetch_all() as $row) {
					if (in_array($row[4], $candidates)) {
						$this->runtimeCache['primaryKeys'][$tableName][] = $row[4];
					}
				}
			}
		}
		return $this->runtimeCache['primaryKeys'][$tableName];
	}

	public function getColumnNames($tableName) {
		if (!isset($this->runtimeCache['colums'][$tableName])) {
			$result = $this->connection->query('SHOW columns FROM ' . $tableName);
			$this->runtimeCache['colums'][$tableName] = array();
			foreach ($result->fetch_all() as $row) {
				$this->runtimeCache['colums'][$tableName][] = $row[0];
			}
		}
		return $this->runtimeCache['colums'][$tableName];
	}

	public function diffText($left, $right) {
		$diffParser = new Diff();
		return $diffParser->render($left, $right);
	}

	public function generateDiffs($rows, $baselineRows, $tableName) {
		$columnsUsed = array_flip($this->getColumnNames($tableName));

		$diffs = array();
		foreach ($rows as $row) {
			$primaryKey = $this->createPrimaryKey($row, $tableName);
			$primaryKeys = $this->getPrimaryKeys($tableName);
			$diff = array();
			if (!isset($baselineRows[$primaryKey])) {
				foreach ($primaryKeys as $primaryKey) {
					$diff[$primaryKey] = '<info>' . $row[$primaryKey] . '</info>';
					$columnsUsed[$primaryKey] = TRUE;
				}
			} else {
				if (implode('', $row) == implode('', $baselineRows[$primaryKey])) {
					unset($baselineRows[$primaryKey]);
					continue;
				}
				foreach ($row as $column => $value) {
					if ($baselineRows[$primaryKey][$column] != $value) {
						if (preg_match('/([a-zA-Z0-9]{2})[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*([a-zA-Z0-9]{2})([a-zA-Z0-9]{2})/', $value) > 0) {
							$diff[$column] = '<ins>' . $value . '</ins>' . '<del>' . $baselineRows[$primaryKey][$column] . '</del>';
						} else {
							$diff[$column] = $this->diffText($baselineRows[$primaryKey][$column], $value);
						}
						$columnsUsed[$column] = TRUE;
					}
				}
				unset($baselineRows[$primaryKey]);
			}
			if (count($diff) > 0) {
				foreach ($primaryKeys as $primaryKey) {
					if (!isset($diff[$primaryKey])) {
						$diff[$primaryKey] = $row[$primaryKey];
						$columnsUsed[$primaryKey] = TRUE;
					}
				}

				if (!empty($this->input->getOption('columns'))) {
					foreach ($row as $key => $value) {
						if (preg_match('/(' . str_replace(',', '|', $this->input->getOption('columns')) . ')/', $key, $matches) > 0) {
							$diff[$key] = $value;
							$columnsUsed[$key] = TRUE;
						}
					}
				}
				$diffs[] = $diff;
			}
		}
		foreach ($baselineRows as $baselineRow) {
			$diff = array();
			foreach ($primaryKeys as $primaryKey) {
				$diff[$primaryKey] = '<del>' . $baselineRow[$primaryKey] . '</del>';
				$columnsUsed[$primaryKey] = TRUE;
				foreach ($baselineRow as $key => $value) {
					$diff[$key] = '<del>' . $value . '</del>';
					$columnsUsed[$primaryKey] = TRUE;
				}

				if (!empty($this->input->getOption('columns'))) {
					foreach ($row as $key => $value) {
						if (preg_match('/(' . str_replace(',', '|', $this->input->getOption('columns')) . ')/', $key, $matches) > 0) {
							$diff[$key] = $value;
							$columnsUsed[$key] = TRUE;
						}
					}
				}
			}
			$diffs[] = $diff;
		}

		if (count($diffs) === 0) {
			return $diffs;
		}

		foreach ($columnsUsed as $key => $value) {
			if ($value !== TRUE) {
				unset($columnsUsed[$key]);
			}
		}
		$columnsUsed = array_keys($columnsUsed);

		foreach ($diffs as $key => $diff) {
			$newDiff = array();
			foreach ($columnsUsed as $column) {
				if (isset($diff[$column])) {
					$newDiff[$column] = $diff[$column];
					if ($this->input->getOption('translate-uuids') === TRUE) {
						preg_match('/([a-zA-Z0-9]{2})[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*-[a-zA-Z0-9]*([a-zA-Z0-9]{2})([a-zA-Z0-9]{2})/', $diff[$column], $match);
						if (count($match) > 0) {
							$search = array_shift($match);
							foreach ($match as $index => $value) {
								$match[$index] = $this->uuidWordList[hexdec($value)];
							}
							$newDiff[$column] = str_replace($search, implode('-', $match) , $newDiff[$column]) . ' (' . $search . ')';
						}
					}
				} else {
					$newDiff[$column] = '';
				}
				$newDiff[$column] = htmlspecialchars_decode($newDiff[$column]);
			}
			$diffs[$key] = $newDiff;
		}

		return $diffs;
	}
}
