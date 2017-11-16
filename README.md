# Lightrail Stripe Integration Library

Lightrail is a modern platform for digital account credits, gift cards, promotions, and points —to learn more, visit [Lightrail](https://www.lightrail.com/). The Lightrail Stripe integration provides a client library for developers to easily use Lightrail alongside [Stripe](https://stripe.com/) in PHP.

If you are looking for specific use-cases or other languages, check out the *Integrations* section of the [Lightrail API documentation](https://www.lightrail.com/docs/).

## Features ##
- Simple order checkout supporting split-tender transactions with Lightrail redemption alongside a Stripe payment.

## Usage ##

For a sample project using this library, check out the [Lightrail Stripe Sample PHP Web App](https://github.com/Giftbit/stripe-integration-sample-php-webapp).

## Installation ##

### Composer
You can add this library as a dependency to your project using `composer`:
```json
"require": {
    "lightrail/lightrail-stripe": "dev-master"
  }
```
Alternatively, you can copy all the files and add `init.php` to your project:
```php
require_once 'lightrail-stripe/init.php';

```
## Requirements ## 
This library requires `PHP 5.5` or later.

## Dependencies ##

The only dependencies of this library are `Stripe` and `firebase/php-jwt`. 
```json
"require": {
    "stripe/stripe-php": "^5.3",
    "firebase/php-jwt": "^5.0"
  }
```

The following dependency is also necessary if you want to run the unit tests.
```json
"require-dev": {
    "phpunit/phpunit": "^6.2"
  }
```

## Tests ## 

Copy `~test-config.php` to `test-config.php` and fill in the blank fields. 

Tests can be run from `tests/` with `../vendor/bin/phpunit ./`
