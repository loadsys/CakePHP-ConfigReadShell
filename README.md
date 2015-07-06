# CakePHP-ConfigReadShell

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-ConfigReadShell.svg?style=flat-square)](https://github.com/loadsys/CakePHP-ConfigReadShell/releases)
[![Build Status](https://travis-ci.org/loadsys/CakePHP-ConfigReadShell.svg?branch=master)](https://travis-ci.org/loadsys/CakePHP-ConfigReadShell)
[![Coverage Status](https://coveralls.io/repos/loadsys/CakePHP-ConfigReadShell/badge.svg?branch=master)](https://coveralls.io/r/loadsys/CakePHP-ConfigReadShell?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-config-read.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-config-read)

A CakePHP plugin that provides a Shell to read an app's Configure vars from the command line.

* This is the Cake 3.x version of the plugin, which exists on the `master` branch and is tracked by the `~3.0` semver.
* For the Cake 2.x version of this plugin, please use the repo's `cake-2.x` branch. (semver `~2.0`)
* For the Cake 1.3 version, use the `cake-1.3` branch. (semver `~1.0`) **Note:** we don't expect to actively maintain the 1.3 version. It's here because the project started life as a 1.3 Shell.


## Requirements

* CakePHP 3.0.0+
* PHP 5.6+


## Installation

```bash
$ composer require loadsys/cakephp-config-read:~3.0
```


## Usage

```shell
$ cd path/to/app/
$ ./bin/cake ConfigRead Key.Name
'foo'
```

### Format as a bash variable definition

```shell
$ ./bin/cake ConfigRead Key.Name Second.Key
KEY_NAME='foo'
SECOND_KEY_FIRST='bar'
SECOND_KEY_SECOND='baz'
SECOND_KEY_THIRD='42'
```

Note that this format is automatically used whenever more than one key is returned. For example, if you request a key that contains an array, all values in the array will be returned sequentially. Alternatively, if you pass multiple keys on the command line, they will be returned. The format can also be forced using the `-b` or `--bash` command line switch:

```shell
$ ./bin/cake ConfigRead -b Key.Name
KEY_NAME='foo'
```

## Gotchas

### "Consumed" Configure Vars

CakePHP 3 by default "consumes" some of its configs so as not to confused developers. [`Configure::consume()`](http://book.cakephp.org/3.0/en/development/configuration.html#Cake\Core\Configure::consume) removes the configuration key from Configure, making it unavailable to the rest of the app. At the [time of this writing](https://github.com/cakephp/app/blob/a0f2c4/config/bootstrap.php#L136,L141), it does this for the following keys/classes:

* Cache/Cache
* Datasources/ConnectionManager
* EmailTransport/Email
* Email/Email
* Log/Log
* Security.salt/Security::salt()

The effect is that you can not use the ConfigReadShell to obtain Configure values for any of these keys since they no longer exist in Configure's store. (This is particularly troublesome if you are using [Environment-Aware Configs](https://github.com/beporter/CakePHP-EnvAwareness/tree/master/slides).)

There are two possible workarounds:

1. Use the `ConsoleShell` instead. For example:

	```shell
	$ echo 'use Cake\Datasource\ConnectionManager; foreach(ConnectionManager::config("default") as $k => $v) { echo "$k=" . escapeshellarg($v) . PHP_EOL; } exit;' | bin/cake Console -q
	className='Cake\Database\Connection'
	driver='Cake\Database\Driver\Mysql'
	persistent=''
	host='localhost'
	username='my_app'
	password='secret'
	database='my_app'
	encoding='utf8'
	timezone='UTC'
	cacheMetadata='1'
	quoteIdentifiers=''
	name='default'
	```

	This command is wrapped up in our [loadsys/cakephp-shell-scripts](https://github.com/loadsys/CakePHP-Shell-Scripts) repo as the [`db-credentials`](https://github.com/loadsys/CakePHP-Shell-Scripts/blob/76a24/db-credentials) script.

2. Edit your `config/bootstrap.php` to use `Configure::read()` instead of `Configure::consume()`.

	```diff
	-Cache::config(Configure::consume('Cache'));
	-ConnectionManager::config(Configure::consume('Datasources'));
	-Email::configTransport(Configure::consume('EmailTransport'));
	-Email::config(Configure::consume('Email'));
	-Log::config(Configure::consume('Log'));
	-Security::salt(Configure::consume('Security.salt'));
	+Cache::config(Configure::read('Cache'));
	+ConnectionManager::config(Configure::read('Datasources'));
	+Email::configTransport(Configure::read('EmailTransport'));
	+Email::config(Configure::read('Email'));
	+Log::config(Configure::read('Log'));
	+Security::salt(Configure::read('Security.salt'));
	```

	This will leave the Configure vars in place and allow commands like `bin/cake ConfigRead Datasources.default` to work as expected, but be warned that the values in Configure might not reflect the values actually being used by the various Cake modules.


## Contributing

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-ConfigReadShell/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

## License

[MIT](https://github.com/loadsys/CakePHP-ConfigReadShell/blob/master/LICENSE.md)


## Copyright

[Loadsys Web Strategies](http://www.loadsys.com) 2015
