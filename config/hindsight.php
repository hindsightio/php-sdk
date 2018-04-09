<?php

return [
    'attach_request_id_to_response' => true,

    'blacklist' => [
        'fields' => [
            'password',
            'confirm_password',
            'cvv',
            'cvc',
            'cvv2',
            'card_number',
            'ssn',
            'ni_number',
        ],

        'headers' => ['Authorization']
    ],
];