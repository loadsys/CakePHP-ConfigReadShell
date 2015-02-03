# CakePHP-ConfigReadShell

A CakePHP plugin that provides a Shell to read an app's Configure vars from the command line.

* For the Cake 2.x version of this plugin, please use the repo's `master` branch.
* For the Cake 1.3 version, use the `1.3` branch. (Note: we don't expect to actively maintain the 1.3 version. It's here because it started life as a 1.3 Shell.)


## Requirements

* CakePHP 2.5.0+
* PHP 5.4.19+


## Installation

`composer require loadsys/config-read:2.*`

or

`git submodule add https://github.com/loadsys/CakePHP-ConfigReadShell.git Plugin/ConfigRead`


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

## TODO

* Unit tests.
* Travis-CI setup.


## Support

For bugs and feature requests, please use the [Issues](https://github.com/loadsys/CakePHP-ConfigReadShell/issues).


## Contributing

Please feel free to open a new Issue, or fork the repo and submit a PR.


## License

Copyright 2015 Loadsys Web Strategies. All rights reserved.

Licensed under the [MIT](http://www.opensource.org/licenses/mit-license.php) License. Redistributions of the source code included in this repository must retain the copyright notice found in each file.
