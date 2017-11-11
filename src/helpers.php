<?php
use \Shipu\SslWPayment\Payment;

function ssl_wireless_input($customerInfo, $amount) {
    $payment = new Payment(config('sslwpayment'));
    return $payment->customer($customerInfo)->amount($amount)->hiddenValue();
}

function ssl_wireless_payment_url() {
    $payment = new Payment(config('sslwpayment'));
    return $payment->paymentUrl();
}
