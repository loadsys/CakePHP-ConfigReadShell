# CakePHP-ConfigReadShell

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-ConfigReadShell.svg?style=flat-square)](https://github.com/loadsys/CakePHP-ConfigReadShell/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/config-read.svg?style=flat-square)](https://packagist.org/packages/loadsys/config-read)

A CakePHP plugin that provides a Shell to read an app's Configure vars from the command line.

* This is the Cake 2.x version of the plugin, which exists on the `cake-2.x` branch and is tracked by the `2.*` semver.
* For the Cake 3.x version of this plugin, please use the repo's `master` branch. (semver `3.*`)
* For the Cake 1.3 version, use the `cake-1.3` branch. (semver `1.*`) **Note:** we don't expect to actively maintain the 1.3 version. It's here because the project started life as a 1.3 Shell.


## Requirements

* CakePHP 2.5.0+
* PHP 5.4.19+


## Installation

```bash
$ composer require loadsys/config-read:2.*
```

or

`git submodule add -b cake-2.x https://github.com/loadsys/CakePHP-ConfigReadShell.git Plugin/ConfigRead`


## Usage

```shell
$ cd path/to/app/
$ ./lib/Console/cake config_read.config_read Key.Name
'foo'
```

### Format as a bash variable definition

```shell
$ ./lib/Console/cake config_read.config_read Key.Name Second.Key
KEY_NAME='foo'
SECOND_KEY_FIRST='bar'
SECOND_KEY_SECOND='baz'
SECOND_KEY_THIRD='42'
```

Note that this format is automatically used whenever more than one key is returned. For example, if you request a key that contains an array, all values in the array will be returned sequentially. Alternatively, if you pass multiple keys on the command line, they will be returned. The format can also be forced using the `-b` or `--bash` command line switch:

```shell
$ ./lib/Console/cake config_read.config_read -b Key.Name
KEY_NAME='foo'
```


## Contributing ##

### Reporting Issues ###

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-ConfigReadShell/issues) for listing any known defects or issues.

### Development ###

When developing this plugin, please fork and issue a PR for any new development.

## License ##

[MIT](https://github.com/loadsys/CakePHP-ConfigReadShell/blob/master/LICENSE.md)


## Copyright ##

[Loadsys Web Strategies](http://www.loadsys.com) 2015
