<?php
/**
 * Custom test suite to execute all ConfigRead Plugin Command tests.
 *
 * @package ConfigRead.Test.Case
 */

/**
 * AllConfigReadCommandTest
 */
class AllConfigReadCommandTest extends PHPUnit_Framework_TestSuite {

	/**
	 * load the suites
	 *
	 * @return CakeTestSuite
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All ConfigRead Plugin Command Tests');
		$suite->addTestDirectoryRecursive(dirname(__FILE__) . '/Console/Command/');
		return $suite;
	}
}
