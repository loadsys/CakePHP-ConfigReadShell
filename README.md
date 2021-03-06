# CakePHP-ConfigReadShell

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-ConfigReadShell.svg?style=flat-square)](https://github.com/loadsys/CakePHP-ConfigReadShell/releases)
[![Build Status](https://img.shields.io/travis/loadsys/CakePHP-ConfigReadShell/master.svg?style=flat-square)](https://travis-ci.org/loadsys/CakePHP-ConfigReadShell)
[![Coverage Status](https://img.shields.io/coveralls/loadsys/CakePHP-ConfigReadShell/master.svg?style=flat-square)](https://coveralls.io/r/loadsys/CakePHP-ConfigReadShell)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-config-read.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-config-read)

A CakePHP plugin that provides a Shell to read an app's Configure vars from the command line.

## Installation
Use the following as a guide for choosing your version based on the version of CakePHP installed.

| CakePHP | ConfigReadShell Plugin | Tag   | Notes |
| :-------------: | :------------------------: | :--:  | :---- |
| ^3.4            | [master](https://github.com/loadsys/CakePHP-ConfigReadShell/tree/master)            | 4.0.0 | stable   |
| 3.3             | [3.3](https://github.com/loadsys/CakePHP-ConfigReadShell/tree/3.0.0)                | 3.0.0 | stable   |
| 2.5             | [2.x](https://github.com/loadsys/CakePHP-ConfigReadShell/tree/2.0.1)                | 2.0.1 | stable   |
| 1.3             | [1.x](https://github.com/loadsys/CakePHP-ConfigReadShell/tree/1.0.1)                  | 1.0.1   | stable   |


### This will install the latest version.
```bash
$ composer require loadsys/cakephp-config-read:~4.0
```

**Note:** we don't expect to actively maintain or improve the 1.3 version. It's here because the project started life as a 1.3 Shell.

## Requirements

* PHP 7.0+
* CakePHP 3.6.0+



## Usage

Imagine the following defined in `config/app.php`:

```php
return [
	'Key' => [
		'Name' => 'foo',
	],
	'Second' => [
		'Key' => [
			'First' => 'bar',
			'Second' => 'baz',
			'Third' => 42,
		],
	],
];
```


To use this plugin, call it from the command line:

```shell
$ cd path/to/app/
$ ./bin/cake config_read Key.Name
'foo'
```

### Format as a bash variable definition

```shell
$ ./bin/cake ConfigRead.ConfigRead Key.Name Second.Key
KEY_NAME='foo'
SECOND_KEY_FIRST='bar'
SECOND_KEY_SECOND='baz'
SECOND_KEY_THIRD='42'
```

Note that this format is automatically used whenever more than one key is returned (unless the `--serialize` switch has been used). For example, if you request a key that contains an array, all values in the array will be returned sequentially. Alternatively, if you pass multiple keys on the command line, they will all be returned. The format can also be forced using the `-b` or `--bash` command line switch:

```shell
$ ./bin/cake config_read -b Key.Name
KEY_NAME='foo'
```

### Serializing output

It is possible serialize the output from ConfigReadShell so that it can be consumed by other PHP scripts more easily by using the `-s` or `--serialize` command line switch.

Requesting multiple keys on the command line will produce an array of those keys. Requesting a single scalar value will produce only that scalar value.

This switch always overrides both the `--bash` switch and the Shell's automatic bash formatting.

```shell
$ ./bin/cake config_read -s Key.Name Second.Key
a:2:{s:8:"Key.Name";s:3:"foo";s:10:"Second.Key";a:3:{s:5:"First";s:3:"bar";s:6:"Second";s:3:"baz";s:5:"Third";i:42;}}
# Check the result by piping into PHP and unserializing the result.
$ ./bin/cake ConfigRead.ConfigRead -s Key.Name Second.Key | php -r 'print_r(unserialize(file_get_contents("php://stdin")));'
Array
(
    [Key.Name] => foo

    [Second.Key] => Array
		(
			[First] => bar
			[Second] => baz
			[Third] => 42
		)

)
```


## Potential Gotchas

### "Consumed" Configure Vars

CakePHP 3 by default "consumes" some of its configs so as not to confuse developers. [`Configure::consume()`](http://book.cakephp.org/3.0/en/development/configuration.html#Cake\Core\Configure::consume) removes the configuration key from Configure, making it unavailable to the rest of the app. At the [time of this writing](https://github.com/cakephp/app/blob/a0f2c4/config/bootstrap.php#L136,L141), it does this for the following keys/classes:

| _`[Configure.Key]`_  | _`Class::configEnumerationMethod()`_  | _`Class::configFetchMethod()`_    |
|----------------------|---------------------------------------|-----------------------------------|
| `[Cache]`            | `Cache::configured()`                 | `Cache::getConfig()`              |
| `[Datasources]`      | `ConnectionManager::configured()`     | `ConnectionManager::getConfig()`  |
| `[EmailTransport]`   | `Email::configuredTransport()`        | `Email::getConfigTransport()`     |
| `[Email]`            | `Email::configured()`                 | `Email::getConfig()`              |
| `[Log]`              | `Log::configured()`                   | `Log::getConfig()`                |
| `[Security.salt]`    | _(none)_                              | `Security::getSalt()`             |


The ConfigReadShell devotes about half of its codebase dealing with this for you, allowing you to continue to fetch values using the Configure path (`Datasources.default.host` -> `localhost`) while in the background it is actually querying `ConnectionManager::getConfig('default')['host']`. (This is particularly helpful if you are using [Environment-Aware Configs](https://github.com/beporter/CakePHP-EnvAwareness/tree/master/slides).)

The "gotcha" here is that ConfigReadShell has to maintain a hard-coded list of Configure keys that are normally consumed, and how to access them in their new container. This is further complicated by the fact that not all consumed configs are loaded into or retrieved from their containers the same way, although the base assumption is that the container implements the [`StaticConfigTrait`](http://api.cakephp.org/3.0/class-Cake.Core.StaticConfigTrait.html) and so will have `::getConfig()` and `::configured()` available.

:warning: **If your app uses `Configure::consume()` on any non-standard Configure key during bootstrapping, you will not be able to obtain any child values of those keys from the ConfigReadShell.**


## Contributing

### Code of Conduct

This project has adopted the Contributor Covenant as its [code of conduct](CODE_OF_CONDUCT.md). All contributors are expected to adhere to this code. [Translations are available](http://contributor-covenant.org/).

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-ConfigReadShell/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

## License

[MIT](https://github.com/loadsys/CakePHP-ConfigReadShell/blob/master/LICENSE.md)


## Copyright

[Loadsys Web Strategies](http://www.loadsys.com) 2018
