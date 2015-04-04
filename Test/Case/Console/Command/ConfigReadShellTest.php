<?php
/**
 * Class to test the ConfigReadShell methods
 *
 * @package ConfigRead.Test.Case.Console.Command
 */
App::uses('ConfigReadShell', 'ConfigRead.Console/Command');

/**
 * ConfigReadShellTest
 */
class ConfigReadShellTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var	array
	 */
	public $fixtures = array(
	);

	/**
	 * setUp method
	 *
	 * @return	void
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * tearDown method
	 *
	 * @return	void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * mark tests as incomplete for the plugin
	 *
	 * @return void
	 */
	public function testsIncomplete() {
		$this->markTestIncomplete("No tests implemented for ConfigReadShell.");
	}

}
