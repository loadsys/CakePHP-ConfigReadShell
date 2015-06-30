<?php
/**
 * 
 */
namespace ConfigReadShell\Test\TestCase\Shell;

use ConfigRead\Shell\ConfigReadShell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
// use Cake\Core\App;
// use Cake\Core\Configure;
// use Cake\Core\Plugin;
// use Cake\Filesystem\Folder;
// use Cake\Log\Log;
use Cake\TestSuite\TestCase;
// use Cake\Utility\Hash;

/**
 * TestConfigReadShell class
 *
 * Exposes protected methods for direct testing.
 */
class TestConfigReadShell extends ConfigReadShell
{
	public function fetchVal($key) {
		return parent::fetchVal($key);
	}

	public function iterateOnKey($key, $val) {
		return parent::iterateOnKey($key, $val);
	}

	public function printVal($key, $val) {
		return parent::printVal($key, $val);
	}
}

/**
 * ConfigReadShellTest class
 *
 */
class ConfigReadShellTest extends TestCase
{

    /**
     * Fixtures used in this test case
     *
     * @var array
     */
    public $fixtures = [
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Shell = $this->initSUT();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->io, $this->Shell);

        parent::tearDown();
    }

	/**
	 * Helper for setting up an instance of the target Shell with proper
	 * mocked methods.
	 *
	 * The Shell that will be mocked is taken from the test class name
	 * automatically. Example: `SomeShellTest extends CakeTestCase` will
	 * create a mocked copy of `SomeShell`. Will check for a subclassed
	 * `TestSomeShell` and instantiate that instead, if available, to
	 * allow for overriding protected methods.
	 *
	 * All of the fixtures defined in the test class will be "installed"
	 * into the mocked Shell.
	 *
	 * Typically called in ::setUp() or at the beginning
	 * of a test method (if additional mocked methods are necessary.)
	 *
	 * @return mixed	A partially mocked copy of the Shell matching the test class's name.
	 */
	protected function initSUT($additionalMocks = []) {
		$defaultMocks = array(
			'in', 'out', 'hr', 'help', 'error', 'err', '_stop', 'initialize', '_run', 'clear',
		);
        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

		$class = preg_replace('/(.*)Test$/', '\1', get_class($this));
		$testClass = preg_replace('/(.*)\\\([^\\\]+)$/', '\1\\\Test\2', $class);
		$class = (class_exists($testClass) ? $testClass : $class);

		$shell = $this->getMock(
			$class,
			array_merge($defaultMocks, $additionalMocks),
			[$this->io]
		);

		$shell->OptionParser = $this->getMock('Cake\Console\ConsoleOptionParser', [], [null, false]);

		// Load and attach all fixtures defined in this test case.
// 		foreach ($this->fixtures as $fixture) {
// 			$modelName = str_replace('App.', '', implode('.', array_map('Inflector::classify', explode('.', $fixture))));
// 			$propName = str_replace('.', '', $modelName);
// 			$shell->{$propName} = ClassRegistry::init($modelName);
// 		}
		return $shell;
	}

    /**
     * Confirm that startup() engages help output when flag is present.
     *
     * @return void
     */
    public function testStartupHelp()
    {
		$this->Shell->params = ['h' => true];
        $this->Shell->expects($this->once())
        	->method('help')
        	->will($this->returnValue('canary'));
        $this->assertEquals(
        	'canary',
        	$this->Shell->startup(),
        	'Shell should return help() when -h is passed.'
        );
    }

/*
	public function startup() {
		parent::startup();

		//Configure::write('debug', 0);

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
	}

*/

    /**
     * testOut method
     *
     * @return void
     */
    public function testOut()
    {
        $this->io->expects($this->once())
            ->method('out')
            ->with('Just a test', 1);

        $this->Shell->out('Just a test');
    }

    /**
     * test main.
     *
     * @return void
     */
    public function testMain()
    {
        $io = $this->getMock('Cake\Console\ConsoleIo');
        $shell = $this->getMock('Cake\Console\Shell', ['main', 'startup'], [$io]);

        $shell->expects($this->once())->method('startup');
        $shell->expects($this->once())->method('main')
            ->with('debug')
            ->will($this->returnValue('canary'));

        $result = $shell->runCommand(['debug', '--verbose']);
        $this->assertEquals('canary', $result);
    }

    /**
     * Test reading params
     *
     * @dataProvider paramReadingDataProvider
     */
    public function testParamReading($toRead, $expected)
    {
        $this->Shell->params = [
            'key' => 'value',
            'help' => false,
            'emptykey' => '',
            'truthy' => true
        ];
        $this->assertSame($expected, $this->Shell->param($toRead));
    }

    /**
     * Data provider for testing reading values with Shell::param()
     *
     * @return array
     */
    public function paramReadingDataProvider()
    {
        return [
            [
                'key',
                'value',
            ],
            [
                'help',
                false,
            ],
            [
                'emptykey',
                '',
            ],
            [
                'truthy',
                true,
            ],
            [
                'does_not_exist',
                null,
            ]
        ];
    }

    /**
     * Tests __debugInfo
     *
     * @return void
     */
    public function testDebugInfo()
    {
        $expected = [
            'plugin' => null,
            'command' => null,
            'tasks' => [],
            'params' => [],
            'args' => [],
            'interactive' => true
        ];
        $result = $this->Shell->__debugInfo();
        $this->assertEquals($expected, $result);
    }
}
