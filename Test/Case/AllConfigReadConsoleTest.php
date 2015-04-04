<?php
/**
 * Custom test suite to execute all ConfigRead Plugin Console tests.
 *
 * @package ConfigRead.Test.Case
 */

/**
 * AllConfigReadConsoleTest
 */
class AllConfigReadConsoleTest extends PHPUnit_Framework_TestSuite {

	/**
	 * load the suites
	 *
	 * @return CakeTestSuite
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All ConfigRead Plugin Console Tests');
		$suite->addTestDirectoryRecursive(dirname(__FILE__) . '/Console/');
		return $suite;
	}
}
