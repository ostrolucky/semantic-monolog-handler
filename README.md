# Semantic Monolog Handler

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-build]][link-build]


This is a [FingersCrossed handler](https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/FingersCrossedHandler.php) that fills a missing gap in Monolog configuration:

It allows to have "primary" channels. If log is being written to one of these "primary" channels, it _skips fingers crossed behaviour_.
This is combined with support for a Log Level. So you can still apply fingers crossed behaviour to message from primary channel, if it has low severity.

Best real world scenario of this is to be able to rely on >= INFO "app" logs always being written, no matter if there was an error or not (and if there was an error, ensure these log entries are not written twice).

This is something that's [impossible to configure](https://github.com/symfony/monolog-bundle/issues/375) without writing custom handler, unless you are fine with duplicate log entries.


## Install

Via [Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require ostrolucky/semantic-monolog-handler
```

## Example configuration

Handler configured following way will write all the logs to standard error output if any of the logs reach "ERROR" level,
which is a standard FingersCrossed behaviour. 

However, on top of it, it will also immediately print logs from "app" channel, 
unless they are under "DEBUG" level - without triggering FingersCrossed handler (which would flush all the logs).

```php
use Monolog\Level;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\StreamHandler;
use Ostrolucky\SemanticMonologHandler;

$logger = new SemanticLogHandler(
    innerHandler: new StreamHandler(STDERR),
    primaryChannels: ['app' => Level::Info],
    activationStrategy: new ErrorLevelActivationStrategy(Level::Error), 
);
```

## Example Symfony Monolog configuration
Above PHP configuration can be reflected in Symfony like so:

monolog.yaml:
```yaml
monolog:
  handlers:
    main:
      type: service
      id: Ostrolucky\SemanticMonologHandler\SemanticLogHandler

services:
  Ostrolucky\SemanticMonologHandler\SemanticLogHandler:
    autoconfigure: true
    arguments:
      - !service
          class: Monolog\Handler\StreamHandler
          arguments:
            - !php/const STDERR
      - app: !php/enum Psr\Log\LogLevel\Level::Error
      - !service
          class: Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy
          arguments: [!php/enum Psr\Log\LogLevel\Level::Error]
```
## Licensing

MIT license. Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/ostrolucky/semantic-monolog-handler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-build]: https://github.com/ostrolucky/semantic-monolog-handler/actions/workflows/continuous-integration.yaml/badge.svg

[link-packagist]: https://packagist.org/packages/ostrolucky/semantic-monolog-handler
[link-build]: https://github.com/ostrolucky/semantic-monolog-handler/actions/workflows/continuous-integration.yaml