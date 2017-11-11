<?php

return [
    'store_id' => '',
    'store_password' => '',
    'sandbox' => true,
    'redirect_url' => [
        'fail' => [
            'route' => '' // payment.failed
        ],
        'success' => [
            'url' => '' //payment.success
        ],
        'cancel' => [
            'url' => '' // payment/cancel or you can use route also
        ]
    ]
];
