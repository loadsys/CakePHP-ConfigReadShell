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
use Cake\Utility\Hash;

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
	 * Make the shell aware of "unique" key requests.
	 *
	 * Cake 3 uses Configure::consume() on a number of Configure keys to
	 * prime different Cake modules in `config/boostrap.php`.
	 *
	 * Cache::config(Configure::consume('Cache'));
	 * ConnectionManager::config(Configure::consume('Datasources'));
	 * Email::configTransport(Configure::consume('EmailTransport'));
	 * Email::config(Configure::consume('Email'));
	 * Log::config(Configure::consume('Log'));
	 * Security::salt(Configure::consume('Security.salt'));
	 *
	 * This removes the configs from Configure, making them inaccessible
	 * through standard means in this Shell.
	 *
	 * This property tracks specific key names and the class::method they
	 * map to so we can perform the correct logic to fetch the values. For
	 * example, a request for `Cache._cake_core_.className` would result
	 * in a call like `\Cake\Cache\Cache::config('_cake_core_')['className']`.
	 *
	 * @var array
	 */
	public $specialKeys = [
		'Cache' => '\Cake\Cache\Cache::config',
		'Datasources' => '\Cake\Datasource\ConnectionManager::config',
		'EmailTransport' => '\Cake\Network\Email\Email::configTransport',
		'Email' => '\Cake\Network\Email\Email::config',
		'Log' => '\Cake\Log\Log::config',
		'Security.salt' => 'self::securitySaltHelper',
	];

	/**
	 * Overrides the default welcome function in order to suppress the
	 * normal "Welcome to CakePHP" banner.
	 *
	 * @return void
	 */
	public function _welcome() {
		// Do nothing.
	}

	/**
	 * Sets internal state, validate options/arguments.
	 *
	 * @return void
	 */
	public function startup() {
		parent::startup();

		$this->formatBash = ($this->params['bash'] || count($this->args) > 1);
		$this->formatSerialize = $this->params['serialize'];

		if (empty($this->args)) {
			$this->_displayHelp('');
			$this->error(__('No Configure keys provided.'));
		}

		// All other output should not be processed by the Shell.
		$this->_io->outputAs(ConsoleOutput::RAW);
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
	 * Value fetch dispatcher.
	 *
	 * Checks if the requested key is from a "special" class (that
	 * normally has its configs `Configure::consume()`d) and routes the
	 * request to the correct fetcher.
	 *
	 * @param string $key The string name of the key to fetch.
	 * @return mixed The value as obtained from proper fetcher method.
	 */
	protected function fetchVal($key) {
		$special = $this->specialKey($key);
		if ($special) {
			return $this->fetchSpecial($special);
		} else {
			return $this->configRead($key);
		}
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
	protected function configRead($key) {
		return Configure::read($key);
	}

	/**
	 * Determine if the provided key name matches one of our known "special" keys.
	 *
	 * If so, return an array of attributes including the callable method
	 * to fetch values from the class, the config key name to pass (if any)
	 * and the subkey to extract from the results (if any).
	 *
	 * Examples:
	 *
	 *   - 'Cache' -->
	 *     [
	 *         'callable' => '\Cake\Cache\Cache::config',
	 *     ]
	 *     // Will return all available Cache configs.
	 *
	 *   - 'Cache.default' -->
	 *     [
	 *         'callable' => '\Cake\Cache\Cache::config',
	 *         'arg' => 'default',
	 *     ]
	 *     // Will return  all settings for the `default` Cache.
	 *
	 *   - 'Cache.default.className' -->
	 *     [
	 *         'callable' => '\Cake\Cache\Cache::config',
	 *         'arg' => 'default',
	 *         'subkey' => 'className',
	 *     ]
	 *     // Will return only the `className` value for the `default` Cache.
	 *
	 * Matching results are fed into ::fetchSpecial().
	 *
	 * @param string $search The dotted key name to check against our special keys.
	 * @return array|false An array containing at least a [callable] key, and possibly [arg] and [subkey] keys. False on no match.
	 * @see ::fetchSpecial()
	 */
	protected function specialKey($search) {
		$callable = false;
		foreach ($this->specialKeys as $key => $call) {
			if (strpos("{$search}.", "{$key}.") === 0) {
				$callable = $call;
			}
		}

		if (!$callable) {
			return false;
		}

		$special = [
			'callable' => $callable,
		];

		$keyParts = explode('.', $search, 3);
		if (isset($keyParts[1])) {
			$special['arg'] = $keyParts[1];
		}

		if (isset($keyParts[2])) {
			$special['subkey'] = $keyParts[2];
		}

		return $special;
	}

	/**
	 * Performs necessary gymnastics to fetch "special" configs.
	 *
	 * There are three cases to handle:
	 *
	 *   1. A "deep" subkey like `Cache.default.className`. In this case,
	 *      we need to fetch `Cache::config('default')` and then return
	 *      the ['className'] from the result.
	 *
	 *   2. A single config array like `Cache.default. We need to fetch
	 *      `Cache::config('default')` and return the whole thing.
	 *
	 *   3. All configs in a module like `Cache`. In this case we need to
	 *      try calling `Cache::configured()` (if it exists), then looping
	 *      over the results using each as a key name for a separate call
	 *      to `Cache::config($name)` and accumulating all of the results
	 *      together to return.
	 *
	 * @param array $special An array containing at least a [callable] key and possibly [arg] and [subkey]s.
	 * @return mixed A single scalar value, or an associative array of sub-values.
	 */
	protected function fetchSpecial($special) {
		if (isset($special['arg'])) {
			$set = call_user_func($special['callable'], $special['arg']);
		} else {
			$allConfigCallable = str_replace(
				'::config',
				'::configured',
				$special['callable'],
				$replaceCount
			);

			$set = null;
			if ($replaceCount && is_callable($allConfigCallable)) {
				foreach (call_user_func($allConfigCallable) as $configName) {
					$set[$configName] = call_user_func($special['callable'], $configName);
				}
			}
		}

		if (isset($special['subkey']) && is_array($set)) {
			return Hash::get($set, $special['subkey']);
		} else {
			return $set;
		}
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
	 * Provides a custom helper for fetching the App's Security.salt value.
	 *
	 * The call to Security::salt() method requires no arguments to get the
	 * current value but we will have split the command line request for
	 * `Security.salt` into
	 * `call_user_func('\Cake\Utility\Security::salt', 'salt'). That will
	 * **set** the salt to `salt` and return "salt" as the new value, which
	 * isn't what we want. This method exists to be the callable function,
	 * which itself passes the proper `null` value as the argument, which
	 * in turn will return the _actual_ salt value.
	 *
	 * @param string $salt Because of how this is implemented, this will always be the literal string "salt" and will be ignored.
	 @return string The result of calling `Security::salt(null);`
	 */
	private function securitySaltHelper($salt) {
		return call_user_func('\Cake\Utility\Security::salt', null);
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
			->addOption('bash', [
				'short' => 'b',
				'boolean' => true,
				'default' => false,
				'help' => __('Always use bash variable deinfition formatting. When enabled, output will be formatted as `KEY_NAME=\'value\'`. This option is auto-enabled if multiple keys are provided on the command line, or if the value for the requested key is itself an array. When multiple values are returned, each will be output on its own line.'),
			])
			->addOption('serialize', [
				'short' => 's',
				'boolean' => true,
				'default' => false,
				'help' => __('Encode all output using PHP\'s `serialize()` method. Makes the Shell\'s output suitable for consumption by other PHP console scripts. Always overrides the --bash option. A single requested key will be serialized directly. Multiple requested keys will be combined into an associative array with the provided arguments as key names and then serialized.'),
			])
			->description(
				__('Provides CLI access to variables defined in the Configure class of the host CakePHP application. Will output the value of any keys passed as arguments. Equivelant to `Configure::read(\'Key.Name\')`. Unrecognized keys will produce empty string or `null` output.')
			)
			->epilog(
				__('Provide the Key.name(s) to fetch from Configure::read() as arguments. Multiple keys may be specified, separated by spaces.')
			);
		return $parser;
	}
}
