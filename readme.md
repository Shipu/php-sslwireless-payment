# PHP SSL-Wirless Payment client

php-sslwireless-payment is a PHP client for SSL Wirless Payment Gateway API. This package is also support Laravel.

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

- Mandatory input field name
    - tran_id
    - cus_name
    - cus_email
    - cus_phone

#### Getting Payment Post Url

In PHP:
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment($config);
return $payment->paymentUrl();
```
In Laravel:
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->paymentUrl();
```

#### Getting Hidden Input Field
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->customer([
    'cus_name'  => 'Shipu Ahamed', // Customer name
    'cus_email' => 'shipuahamed01@gmail.com', // Customer email
    'cus_phone' => '01616022669' // Customer Phone
])->transactionId('21005455540')->amount(3500)->hiddenValue();
```
Where Transaction id is random value. you can generate by yourself or follow bellow steps:
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->customer([
    'cus_name'  => 'Shipu Ahamed', // Customer name
    'cus_phone' => '01616022669' // Customer Phone
    'cus_email' => 'shipuahamed01@gmail.com', // Customer email
])->transactionId()->amount(3500)->hiddenValue();

or 

return $payment->customer([
    'cus_name'  => 'Shipu Ahamed', // Customer name
    'cus_phone' => '01616022669' // Customer Phone
    'cus_email' => 'shipuahamed01@gmail.com', // Customer email
])->amount(3500)->hiddenValue();
```
#### Generate Transaction Id
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->generateTransaction();
```

#### Checking Valid Response
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->valid($request);
```
Checking valid response with amount:
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->valid($request, '3500'); 
```

Checking valid response with amount and transaction id:
```php
use \Shipu\SslWPayment\Payment;

...

$payment = new Payment(config('sslwpayment'));
return $payment->valid($request, '3500', '21005455540');
```
Where `$request` will appear after post response.

## In Blade

#### Getting Payment Post Url
```php
{{ ssl_wireless_payment_url() }}
```

#### Getting Hidden Input Field
```php
{!!
    ssl_wireless_hidden_input([
        'tran_id'   => '21005455540', // random number
        'cus_name'  => 'Shipu Ahamed', // Customer name
        'cus_email' => 'shipuahamed01@gmail.com', // Customer email
        'cus_phone' => '01616022669' // Customer Phone
    ], 3500) 
!!}
```

#### Complete Post Button View 
```php
{!! 
ssl_wireless_post_button([
    'tran_id'   => '21005455540', // random number
    'cus_name'  => 'Shipu Ahamed', // Customer name
    'cus_email' => 'shipuahamed01@gmail.com', // Customer email
    'cus_phone' => '01616022669' // Customer Phone
], 2000, '<i class="fa fa-money"></i>', 'btn btn-sm btn-success') 
!!}
```
## Example 

##### Route
```php
Route::post('payment/success', 'YourMakePaymentsController@paymentSuccess')->name('payment.success');
Route::post('payment/failed', 'YourMakePaymentsController@paymentFailed')->name('payment.failed');
Route::post('payment/cancel', 'YourMakePaymentsController@paymentCancel')->name('payment.cancel');
```

or 

```php
Route::post('payment/success', 'YourMakePaymentsController@paymentSuccessOrFailed')->name('payment.success');
Route::post('payment/failed', 'YourMakePaymentsController@paymentSuccessOrFailed')->name('payment.failed');
Route::post('payment/cancel', 'YourMakePaymentsController@paymentSuccessOrFailed')->name('payment.cancel');

```
##### Controller Method
```php
use Shipu\SslWPayment\Facades\Payment;

...

public function paymentSuccessOrFailed(Request $request)
{
    if($request->get('status') == 'CANCELLED') {
        return redirect()->back();
    }
    
    $transactionId = $request->get('tran_id');
    $valid = Payment::valid($request, 3500, $transactionId);
    
    if($valid) {
        // Successfully Paid.
    } else {
       // Something went wrong. 
    }
    
    return redirect()->back();
}
```

## To Disable CSRF token
Open `app/Http/Middleware/VerifyCsrfToken.php` and adding :
```php
protected $except = [
    ...
    'payment/*',
    ...
];
```

## Credits

- [Shipu Ahamed](https://github.com/shipu)
- [All Contributors](../../contributors)

Special Thanks to [Tawsif ul Karim](https://github.com/tawsifkarim).

## Support on Beerpay
Hey dude! Help me out for a couple of :beers:!

[![Beerpay](https://beerpay.io/Shipu/php-sslwireless-payment/badge.svg?style=beer-square)](https://beerpay.io/Shipu/php-sslwireless-payment)  [![Beerpay](https://beerpay.io/Shipu/php-sslwireless-payment/make-wish.svg?style=flat-square)](https://beerpay.io/Shipu/php-sslwireless-payment?focus=wish)