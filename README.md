# Laravel Model Auditlog

[![Latest Version on Packagist](https://img.shields.io/packagist/v/orisintel/laravel-model-auditlog.svg?style=flat-square)](https://packagist.org/packages/orisintel/laravel-model-auditlog)
[![Build Status](https://img.shields.io/travis/orisintel/laravel-model-auditlog/master.svg?style=flat-square)](https://travis-ci.org/orisintel/laravel-model-auditlog)
[![Total Downloads](https://img.shields.io/packagist/dt/orisintel/laravel-model-auditlog.svg?style=flat-square)](https://packagist.org/packages/orisintel/laravel-model-auditlog)

When modifying a model record, it is nice to have a log of the changes made and who made those changes. There are many packages around this already, but this one is different in that it logs those changes to individual tables for performance and supports real foreign keys.

## Installation

You can install the package via composer:

```bash
composer require orisintel/laravel-model-auditlog
```

## Configuration

``` php
php artisan vendor:publish --provider="\OrisIntel\AuditLog\AuditLogServiceProvider"
```

Running the above command will publish the config file.

## Usage

After adding the proper fields to your table, add the trait to your model.

``` php
// User model
class User extends Model
{
    use \OrisIntel\AuditLog\AuditLog;

```

### Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email [security@orisintel.com](mailto:security@orisintel.com) instead of using the issue tracker.

## Credits

- [Tom Schlick](https://github.com/tomschlick)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
