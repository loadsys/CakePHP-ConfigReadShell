<?php
/**
 * Custom test suite to execute all ConfigRead Plugin tests.
 *
 * @package ConfigRead.Test.Case
 */

/**
 * AllConfigReadTest
 */
class AllConfigReadTest extends PHPUnit_Framework_TestSuite {

	/**
	 * the suites to load
	 *
	 * @var array
	 */
	public static $suites = array(
		// Console Folder
		'AllConfigReadConsoleTest.php',
		'AllConfigReadCommandTest.php',
	);

	/**
	 * load the suites
	 *
	 * @return CakeTestSuite
	 */
	public static function suite() {
		$path = dirname(__FILE__) . '/';
		$suite = new CakeTestSuite('All Tests');

		foreach (self::$suites as $file) {
			if (is_readable($path . $file)) {
				$suite->addTestFile($path . $file);
			}
		}
		return $suite;
	}

}
