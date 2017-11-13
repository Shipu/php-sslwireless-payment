<?php
use \Shipu\SslWPayment\Payment;

function ssl_wireless_post_button($input, $amount , $buttonText = 'Payment', $class = '') {
    return \Form::open([
            'method' => 'POST',
            'url'    => ssl_wireless_payment_url(),
            'style'  => 'display:inline'
        ]) . ssl_wireless_hidden_input($input, $amount) .
        \Form::button($buttonText,
            [ 'type' => 'submit', 'class' => $class ]) .
        \Form::close();
}

function ssl_wireless_hidden_input($input, $amount) {
    $payment = new Payment(config('sslwpayment'));
    return $payment->customer($input)->amount($amount)->hiddenValue();
}

function ssl_wireless_payment_url() {
    $payment = new Payment(config('sslwpayment'));
    return $payment->paymentUrl();
}
