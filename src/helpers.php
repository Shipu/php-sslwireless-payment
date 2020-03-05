<?php
use \Shipu\SslWPayment\Payment;

if (! function_exists('ssl_wireless_post_button')) {
    function ssl_wireless_post_button( $input, $amount, $buttonText = 'Payment', $class = '' )
    {
        return '<form action="'.ssl_wireless_payment_url().'" method="POST" style="display:inline">' .
            ssl_wireless_hidden_input($input, $amount) .
            '<button type="submit" class="'.$class.'">'.$buttonText.'</button>' .
            '</form>';
    }
}

if (! function_exists('ssl_wireless_hidden_input')) {
    function ssl_wireless_hidden_input($input, $amount) {
        $payment = new Payment(config('sslwpayment'));
        return $payment->customer($input)->amount($amount)->hiddenValue();
    }
}

if (! function_exists('ssl_wireless_payment_url')) {
    function ssl_wireless_payment_url() {
        $payment = new Payment(config('sslwpayment'));
        return $payment->paymentUrl();
    }
}
