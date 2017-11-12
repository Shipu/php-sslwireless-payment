# PHP SSL-Wirless Payment client

php-sslwireless-payment is a PHP client for SSL Wirless Payment API. This package is also support Laravel.

## Installation

Go to terminal and run this command

```shell
composer require shipu/php-sslwireless-payment
```

Wait for few minutes. Composer will automatically install this package for your project.

### For Laravel

Below **Laravel 5.5** open `config/app` and add this line in `providers` section

```php
Shipu\SslWPayment\SslWPaymentServiceProvider::class,
```

For Facade support you have add this line in `aliases` section.

```php
'Payment'   =>  Shipu\SslWPayment\Facades\Payment::class,
```

Then run this command

```shell
php artisan vendor:publish --provider="Shipu\SslWPayment\SslWPaymentServiceProvider"
```

## Configuration

This package is required three configurations.

1. store_id = your store id in SSL-Wirless Payment Gateway.
2. store_password = your store password in SSL-Wirless Payment Gateway
3. sandbox = `true` for sandbox and `false` for live
4. redirect_url = your application redirect url after `success`, `fail` and `cancel`.

php-sslwireless-payment is take an array as config file. Lets services

```php
use Shipu\SslWPayment\Payment;

$config = [
    'store_id' => 'Your store id',
    'store_password' => 'Your store password',
    'sandbox' => true,
    'redirect_url' => [
        'fail' => [
            'route' => 'payment.failed'
        ],
        'success' => [
            'route' => 'payment.success'
        ],
        'cancel' => [
            'route' => 'payment.cancel' 
        ]
    ]
];

$payment = new Payment($config);
```
### For Laravel

This package is also support Laravel. For laravel you have to configure it as laravel style.

Go to `app\sslwpayment.php` and configure it with your credentials.

```php
return [
    'store_id' => 'Your store id',
    'store_password' => 'Your store password',
    'sandbox' => true,
    'redirect_url' => [
        'fail' => [
            'route' => 'payment.failed'
        ],
        'success' => [
            'route' => 'payment.success'
        ],
        'cancel' => [
            'route' => 'payment.cancel' 
        ]
    ]
];
```

## Usages

Its very easy to use. This packages has a lot of functionality and features.



That's it.

Thank you :)
