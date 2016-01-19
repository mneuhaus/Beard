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
	protected $runtimeCache = array();


	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:compare');
		$this->setDescription('Compare database states');
		$this->addOption('baseline', null, InputOption::VALUE_NONE, 'create/update baseline');

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
		$settings = $this->getSettings($input, $output);

		if ($settings === NULL) {
			$output->writeln('could not find any project settings');
			return;
		}

		$this->connection = new \mysqli(
			$settings->getHost(),
			$settings->getUsername(),
			$settings->getPassword(),
			$settings->getDatabase()
		);

		if (!file_exists('.beard-tmp')) {
			mkdir('.beard-tmp');
		}
		$this->baselineConnection = new \SQLite3('.beard-tmp/baseline.sqlite');

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
			$this->output->writeln('processing ' . $tableName);
			$primaryKeys = $this->getPrimaryKeys($tableName);
			$result = $this->connection->query('SELECT * FROM ' . $tableName);
			$currentRows = $result->fetch_all(MYSQLI_ASSOC);
			$baselineRows = $this->getCsv($tableName);

			$diffs = array();
			$columnsUsed = array_flip($this->getColumnNames($tableName));
			foreach ($currentRows as $row) {
				$primaryKey = $this->createPrimaryKey($row, $tableName);
				if (!isset($baselineRows[$primaryKey])) {
					$diff = array();
					foreach ($primaryKeys as $primaryKey) {
						$diff[$primaryKey] = '<info>' . $row[$primaryKey] . '</info>';
						$columnsUsed[$primaryKey] = TRUE;
					}
					$diffs[] = $diff;
				} else {
					if (implode('', $row) == implode('', $baselineRows[$primaryKey])) {
						continue;
					}
					$diff = array();
					foreach ($row as $column => $value) {
						if ($baselineRows[$primaryKey][$column] != $value) {
							$diff[$column] = $this->diffText($baselineRows[$primaryKey][$column], $value);
							$columnsUsed[$column] = TRUE;
						}
					}
					if (count($diff) > 0) {
						$diffs[] = $diff;
					}
				}
			}

			if (count($diffs) === 0) {
				continue;
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
					} else {
						$newDiff[$column] = '';
					}
				}
				$diffs[$key] = $newDiff;
			}

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
		foreach ($tables as $table) {
			$tableName = $table[0];
			$this->baselineConnection->query('
				DROP TABLE IF EXISTS ' . $tableName . '
			');
			$this->baselineConnection->query('
				CREATE TABLE IF NOT EXISTS ' . $tableName . ' (id integer, primaryKey text, data text, PRIMARY KEY (id))
			');
			$this->output->writeln('processing ' . $tableName, OutputInterface::VERBOSITY_VERBOSE);
			$result = $this->connection->query('SELECT * FROM ' . $tableName);
			foreach ($result->fetch_all(MYSQLI_ASSOC) as $id => $row) {
				$statement = $this->baselineConnection->prepare('INSERT INTO ' . $tableName . ' (id, primaryKey, data) VALUES (:id, :primaryKey, :data)');
				$statement->bindValue(':id', $id);
				$statement->bindValue(':primaryKey', $this->createPrimaryKey($row, $tableName));
				$statement->bindValue(':data', serialize($row));
				$statement->execute();
			}
		}
	}

	public function getCsv($tableName) {
		$columNames = $this->getColumnNames($tableName);
		$fileHandle = fopen('.beard-tmp/' . $tableName . '.csv', 'r');
		$rows = array();
		while ($data = fgetcsv($fileHandle, 1000)) {
			$row = array_combine($columNames, $data);
			$rows[$this->createPrimaryKey($row, $tableName)] = $row;
		}
		return $rows;
	}

	public function createPrimaryKey($row, $tableName) {
		$primaryKeys = $this->getPrimaryKeys($tableName);
		$keys = array();
		foreach ($primaryKeys as $primaryKey) {
			$keys[] = $row[$primaryKey];
		}
		return implode(' | ', $keys);
	}

	public function getPrimaryKeys($tableName) {
		if (!isset($runtimeCache['primaryKeys'][$tableName])) {
			$result = $this->connection->query('SHOW INDEX FROM ' . $tableName);
			$runtimeCache['primaryKeys'][$tableName] = array();
			foreach ($result->fetch_all() as $row) {
				if ($row[2] == 'PRIMARY') {
					$runtimeCache['primaryKeys'][$tableName][] = $row[4];
				}
			}
			if (empty($runtimeCache['primaryKeys'][$tableName])) {
				$candidates = array('id', 'uid', 'uid_local', 'uid_foreign', 'fieldname', 'hash');
				$result = $this->connection->query('SHOW INDEX FROM ' . $tableName);
				foreach ($result->fetch_all() as $row) {
					if (in_array($row[4], $candidates)) {
						$runtimeCache['primaryKeys'][$tableName][] = $row[4];
					}
				}
			}
		}
		return $runtimeCache['primaryKeys'][$tableName];
	}

	public function getColumnNames($tableName) {
		if (!isset($runtimeCache['colums'][$tableName])) {
			$result = $this->connection->query('SHOW columns FROM ' . $tableName);
			$runtimeCache['colums'][$tableName] = array();
			foreach ($result->fetch_all() as $row) {
				$runtimeCache['colums'][$tableName][] = $row[0];
			}
		}
		return $runtimeCache['colums'][$tableName];
	}

	public function diffText($left, $right) {
		$diffParser = new Diff();
		return $diffParser->render($left, $right);
	}
}
