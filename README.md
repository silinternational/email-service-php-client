# email-service-php-client
PHP client to interact with our [Email Service](https://github.com/silinternational/email-service)'s API.

This client is built on top of 
[Guzzle](http://docs.guzzlephp.org/en/stable/), the PHP HTTP Client. 
Guzzle has a simple way to create API clients by describing the API in a 
Swagger-like format without the need to implement every method yourself. So 
adding support for more APIs is relatively simple.


## Install ##
Installation is simple with [Composer](https://getcomposer.org/):

    $ composer require silinternational/email-service-php-client


## Usage ##

Example:

```php
<?php

use Sil\EmailService\Client\EmailServiceClient;

$emailServiceClient = new EmailServiceClient(
    'https://api.example.com/', // The base URI for the API.
    'DummyAccessToken', // Your HTTP header authorization bearer token.
    [
        'http_client_options' => [
            'timeout' => 10, // An (optional) custom HTTP timeout, in seconds.
        ],
    ]
);

$email = $emailServiceClient->email([
    "to_address" => "test@domain.com",
    "cc_address" => "other@domain.com",   // optional
    "bcc_address" => "bcc@domain.com",    // optional
    "subject" => "Test Subject",
    "text_body" => "this is text",        // either text_body or html_body is required, but both can be provided
    "html_body" => "<b>this is html</b>",
]);
```

To send an email with a delay of a fixed amount of time:`

```php
$email = $emailServiceClient->email([
    "to_address" => "test@domain.com",
    "subject" => "Test Subject",
    "html_body" => "<b>this is html</b>",
    "delay_seconds" => "3600",
]);
```

Or to scheduled an email, use `send_after` with a Unix timestamp:

```php
$email = $emailServiceClient->email([
    "to_address" => "test@domain.com",
    "subject" => "Test Subject",
    "html_body" => "<b>this is html</b>",
    "send_after" => "1556825944",
]);
```

## Tests ##

To run the unit tests for this, run `make test`.


## Guzzle Service Client Notes ##
- Tutorial on developing an API client with Guzzle Web Services:  
  <http://www.phillipshipley.com/2015/04/creating-a-php-nexmo-api-client-using-guzzle-web-service-client-part-1/>
- Presentation by Jeremy Lindblom:  
  <https://speakerdeck.com/jeremeamia/building-web-service-clients-with-guzzle-1>
- Example by Jeremy Lindblom:  
  <https://github.com/jeremeamia/sunshinephp-guzzle-examples>
- Parameter docs in source comments:  
  <https://github.com/guzzle/guzzle-services/blob/master/src/Parameter.php>
- Guzzle 3 Service Descriptions documentation (at least mostly still relevant):  
  <https://guzzle3.readthedocs.org/webservice-client/guzzle-service-descriptions.html>
