# Set up the client

The Notify PHP Client is based on a [PSR-7 HTTP model](https://www.php-fig.org/psr/psr-7/) [external link]. To install it, you must select your preferred HTTP client. You can follow these instructions to use [Guzzle v6 and v5](http://docs.guzzlephp.org/en/stable/) and [cURL](http://php.net/manual/en/book.curl.php) [external links].

## Guzzle v6

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/guzzle6-adapter alphagov/notifications-php-client
    ```

    You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey' => '{your api key}',
        'httpClient' => new \Http\Adapter\Guzzle6\Client
    ]);
    ```

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/) and go to the __API integration__ page. Refer to the [API keys](#api-keys) section of this documentation for more information.

## Guzzle v5

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/guzzle5-adapter php-http/message alphagov/notifications-php-client
    ```

    You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'serviceId'     => '{your service id}',
        'apiKey'        => '{your api key}',
        'httpClient'    => new \Http\Adapter\Guzzle5\Client(
            new \GuzzleHttp\Client,
            new \Http\Message\MessageFactory\GuzzleMessageFactory
        ),
    ]);
    ```

1. Run `$notifyClient` to access the GOV.UK Notify API.

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/) and go to the __API integration__ page. Refer to the [API keys](#api-keys) section of this documentation for more information.

## cURL

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/curl-client php-http/message alphagov/notifications-php-client
    ```

You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'serviceId'     => '{your service id}',
        'apiKey'        => '{your api key}',
        'httpClient'    => new \Http\Client\Curl\Client(
            new \Http\Message\MessageFactory\GuzzleMessageFactory,
            new \Http\Message\StreamFactory\GuzzleStreamFactory
        ),
    ]);
    ```

1. Run `$notifyClient` to access the GOV.UK Notify API.

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/) and go to the __API integration__ page. Refer to the [API keys](#api-keys) section of this documentation for more information.
