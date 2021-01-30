# bitexpert/captainhook-infection

This package provides an action for [Captain Hook](https://github.com/CaptainHookPhp/captainhook) 
which will invoke [InfectionPHP](https://infection.github.io) for all changed files of a commit. Running Infection only 
against the changed files will result in a faster execution of Infection which is ideal when running it as a pre-commit hook.

## Installation

The preferred way of installing `bitexpert/captainhook-infection` is through Composer.
You can add `bitexpert/captainhook-infection` as a dev dependency, as follows:

```
composer.phar require --dev bitexpert/captainhook-infection
```

## Usage

Add the following code to your `captainhook.json` configuration file:

```
{
  "pre-commit": {
    "enabled": true,
    "actions": [
      {
        "action": "\\bitExpert\\CaptainHook\\Infection\\InfectionAction"
      }
    ]
  }
}
```
By default, the action will invoke `./vendor/bin/infection` as a command. If you need to customize the path, e.g. because
you installed the .phar distribution you can do so by passing a `infection` configuration option to the action.

```
{
  "pre-commit": {
    "enabled": true,
    "actions": [
      {
        "action": "\\bitExpert\\CaptainHook\\Infection\\InfectionAction",
        "options": {
            "infection": "php infection.phar"
        }
      }
    ]
  }
}
```

To pass additional parameters to Infection, e.g. to define the number of threads used by Infection, supply an args array
option like this:

```
{
  "pre-commit": {
    "enabled": true,
    "actions": [
      {
        "action": "\\bitExpert\\CaptainHook\\Infection\\InfectionAction",
        "options": {
            "args": [
                "-j 4"
            ]
        }
      }
    ]
  }
}
```

## Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes! To establish a consistent code quality, please provide unit tests for all your changes and adapt the documentation.

## Want To Contribute?

If you feel that you have something to share, then we’d love to have you.
Check out [the contributing guide](CONTRIBUTING.md) to find out how, as well as what we expect from you.

## License

Captain Hook Infection Action is released under the Apache 2.0 license.
