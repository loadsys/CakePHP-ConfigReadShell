<?php
/**
 * ConfigReadShell
 *
 * @package ConfigRead\Shell
 */
namespace ConfigRead\Shell;

use Cake\Console\ConsoleOutput;
use Cake\Console\Shell;
use Cake\Core\Configure;

/**
 * ConfigReadShell class.
 *
 * Provide a command line interface for fetching the values of keys from
 * the `Configure` utility class. Called via
 * `./vendor/bin/cake config_read.config_read Key.Name`.
 */
class ConfigReadShell extends Shell {

	/**
	 * Stores command line switch value for whether to use bash variable
	 * formatting or just echo the raw value. Automatically enabled in
	 * two cases:
	 *
	 *   1. Multiple arguments are provided on the command line.
	 *   2. The key requested is an array that will be iterated over.
	 *
	 * @var bool
	 */
	public $formatBash = false;

	/**
	 * Stores command line switch value for whether to serialize all output.
	 *
	 * When present, it always overrides the --bash option.
	 *
	 * @var bool
	 */
	public $formatSerialize = false;

	/**
	 * Overrides the defaul welcome function in order to supporess the
	 * normal "Welcome to CakePHP" Cake banner.
	 *
	 * @return void
	 */
	public function _welcome() {
		// Do nothing.
	}

	/**
	 * Sets internal state.
	 *
	 * @return void
	 */
	public function startup() {
		parent::startup();

		$this->_io->outputAs(ConsoleOutput::RAW);

		if (isset($this->params['h'])) {
			return $this->help();
		}

		if (isset($this->params['b'])) {
			$this->formatBash = true;
			// Make up for Cake snagging the next arg as the value for `-b`.
			array_unshift($this->args, $this->params['b']);
		}

		if (count($this->args) > 1) {
			$this->formatBash = true;
		}

		if (isset($this->params['s'])) {
			$this->formatSerialize = true;
			// Make up for Cake snagging the next arg as the value for `-s`.
			array_unshift($this->args, $this->params['s']);
		}
	}

	/**
	 * Main shell execution method.
	 *
	 * Coordinates command line arguments and kicks off the output routine.
	 *
	 * @return void
	 */
	public function main() {
		if ($this->formatSerialize) {
			$this->serializedFetchAndPrint();
		} else {
			$this->simpleFetchAndPrint();
		}
	}

	/**
	 * Iterate over provided args, printing them to the console as we go.
	 *
	 * Used to handle single scalar values and all --bash formatted output.
	 *
	 * @return void
	 */
	protected function simpleFetchAndPrint() {
		foreach ($this->args as $key) {
			$val = $this->fetchVal($key);

			// One-way switch. Will enable bash variable formatting if the
			// value is an array, and it will "stick on" after that.
			$this->formatBash = ($this->formatBash || is_array($val));

			$this->iterateOnKey($key, $val);
		}
	}

	/**
	 * Iterate over provided args, collecting them for serialization.
	 *
	 * Used to --serialize formatted output. Returns a single requested
	 * value as a directly-serialized string. If multiple keys were
	 * provided on the command line, they are collected into an
	 * associative array, which is serialized and echoed.
	 *
	 * @return void
	 */
	protected function serializedFetchAndPrint() {
		$unserialized = [];
		foreach ($this->args as $key) {
			$unserialized[$key] = $this->fetchVal($key);
		}

		if (count($unserialized) === 1) {
			$unserialized = array_shift($unserialized);
		}

		$this->out(serialize($unserialized), 0, Shell::QUIET);
	}

	/**
	 * Value fetcher.
	 *
	 * Fetches the requested $key from the Configure class. Also serves as
	 * a handy way to isolate the static method call to `Configure`.
	 *
	 * @param string $key The string name of the key to fetch.
	 * @return mixed The value as obtained from `Configure::read($key)`.
	 */
	protected function fetchVal($key) {
		return Configure::read($key);
	}

	/**
	 * Recursive output handler.
	 *
	 * Handles keys that are themselves associative arrays by recursively
	 * calling itself for child values. If the provided $val is not an array,
	 * output it. Otherwise, loop over the array of values and call itself
	 * for each element (in case they are _also_ arrays.)
	 *
	 * The string key names are "built up" as the depth increases, allowing
	 * for the output key names to be the compound path through the entire
	 * depth of the array up to that key.
	 *
	 * Side effect: Calls out to ::printVal(), which will (typically) echo
	 * values to stdout.
	 *
	 * @param string $key The string name of the key that was used to fetch $val.
	 * @param mixed $val The value as obtained from `Configure::read($key)`.
	 * @return void
	 */
	protected function iterateOnKey($key, $val) {
		// Base case.
		if (!is_array($val)) {
			$this->printVal($key, $val);
			return;
		}

		// Recursive case.
		foreach ($val as $k => $v) {
			$this->iterateOnKey("{$key}.{$k}", $v);
		}
	}

	/**
	 * Output wrapper.
	 *
	 * Designed to handle printing a single value to the console. Takes the
	 * requested output format into account to determine how the data is
	 * formatted for display.
	 *
	 * @param string $key The string name of the key that was used to fetch $val.
	 * @param mixed $val The value as obtained from `Configure::read($key)`.
	 * @return void
	 */
	protected function printVal($key, $val) {
		$val = escapeshellarg($val);
		$format = '%2$s';

		if ($this->formatBash) {
			$key = strtoupper(str_replace('.', '_', $key));
			$format = '%1$s=%2$s';
		}

		$this->out(sprintf($format, $key, $val), 1, Shell::QUIET);
	}

	/**
	 * getOptionParser
	 *
	 * Processing command line options.
	 *
	 * @access public
	 * @return CosnsoleOptionParser
	 * @codeCoverageIgnore
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser
			->addArgument('key', array(
				'help' => 'The Key.name to fetch from Configure::read().',
				'required' => true,
			))
			->addArgument('key2', array(
				'help' => 'Multiple keys may be specified, separated by spaces.',
				'required' => false,
			))
			->addOption('bash', array(
				'short' => 'b',
				'boolean' => true,
				'default' => false,
				'help' => __('Always use bash variable deinfition formatting. When enabled, output will be formatted as `KEY_NAME=\'value\'`. This option is auto-enabled if multiple keys are provided on the command line, or if the value for the requested key is itself an array. When multiple values are returned, each will be output on its own line.')
			))
			->addOption('serialize', array(
				'short' => 's',
				'boolean' => true,
				'default' => false,
				'help' => __('Encode all output using PHP\'s `serialize()` method. Makes the Shell\'s output suitable for consumption by other PHP console scripts. Always overrides the --bash option. A single requested key will be serialized directly. Multiple requested keys will be combined into an associative array and then serialized.')
			))
			->description(__('Provides CLI access to variables defined in the Configure class of the host
CakePHP application. Will output the value of any keys passed as arguments.
Equivelant to `Configure::read(\'Key.Name\')`.'));
		return $parser;
	}
}
