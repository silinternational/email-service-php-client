<?php return [
    'operations' => [
        'getSiteStatusInternal' => [
            'httpMethod' => 'GET',
            'uri' => '/site/status',
            'responseModel' => 'Result',
        ],
        'emailInternal' => [
            'httpMethod' => 'POST',
            'uri' => '/email',
            'responseModel' => 'Result',
            'parameters' => [
                'to_address' => [
                    'required' => true,
                    'type' => 'string',
                    'location' => 'json',
                ],
                'cc_address' => [
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ],
                'bcc_address' => [
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ],
                'subject' => [
                    'required' => true,
                    'type' => 'string',
                    'location' => 'json',
                ],
                'text_body' => [
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ],
                'html_body' => [
                    'required' => false,
                    'type' => 'string',
                    'location' => 'json',
                ],
            ],
        ],
    ],
    'models' => [
        'Result' => [
            'type' => 'object',
            'properties' => [
                'statusCode' => ['location' => 'statusCode'],
            ],
            'additionalProperties' => [
                'location' => 'json'
            ],
        ],
    ]
];
