# GOV.UK Notify PHP client

This documentation is for developers interested in using this PHP client to integrate their government service with GOV.UK Notify.

## Table of Contents

* [Installation](#installation)
* [Getting started](#getting-started)
* [Send messages](#send-messages)
* [Get the status of one message](#get-the-status-of-one-message)
* [Get the status of all messages](#get-the-status-of-all-messages)
* [Get a template by ID](#get-a-template-by-id)
* [Get a template by ID and version](#get-a-template-by-id-and-version)
* [Get all templates](#get-all-templates)
* [Generate a preview template](#generate-a-preview-template)
* [Get received text messages](#get-received-text-messages)
* [Development](#development)
* [License](#license)

## Installation

The Notify PHP Client can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require php-http/guzzle6-adapter alphagov/notifications-php-client
```

### PSR-7 HTTP

The Notify PHP Client is based on a PSR-7 HTTP model. You therefore need to pick your preferred HTTP Client library to use.

We will show examples here using the Guzzle v6 Adapter.

Setup instructions are also available for [Curl](docs/curl-client-setup.md) and [Guzzle v5](docs/guzzle5-client-setup.md).

## Getting started

Assuming you’ve installed the package via Composer, the Notify PHP Client will be available via the autoloader.

Create a (Guzzle v6 based) instance of the Client using:

```php
$notifyClient = new \Alphagov\Notifications\Client([
    'apiKey' => '{your api key}',
    'httpClient' => new \Http\Adapter\Guzzle6\Client
]);
```

Generate an API key by logging in to [GOV.UK Notify](https://www.notifications.service.gov.uk) and going to the **API integration** page.

## Send messages

### Text message

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

The method signature is:
```php
sendSms( $phoneNumber, $templateId, array $personalisation = array(), $reference = '', $smsSenderId = NULL  )
```

An example request would look like:

```php
try {

    $response = $notifyClient->sendSms(
        '+447777111222',
        'df10a23e-2c6d-4ea5-87fb-82e520cbf93a', [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968'
        ],
        'unique_ref123',
        '862bfaaf-9f89-43dd-aafa-2868ce2926a9'
    );

} catch (NotifyException $e){}
```

</details>

#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => None,
    "content" => [
        "body" => "Some words",
        "from_number" => "40604"
    ],
    "uri" => "https =>//api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
       "version" => 1,
       "uri" => "https://api.notifications.service.gov.uk/v2/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ]
]
```
Otherwise the client will raise a ``Alphagov\Notifications\Exception\NotifyException``:

|`exc->getCode()`|`exc->getErrors()`|
|:---|:---|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM of 10 requests per 10 seconds"`<br>`}]`|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (50) for today"`<br>`}]`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient using a team-only API key"`<br>`]}`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|

</details>

#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$phoneNumber`
The mobile number the SMS notification is sent to.

##### `$templateId`

Find by clicking **API info** for the template you want to send.

##### `$reference`
An optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single  notification or a batch of notifications.

##### `$personalisation`

If a template has placeholders, you need to provide their values, for example:

```php
personalisation = [
    'name' => 'Betty Smith',
    'dob'  => '12 July 1968'
]
```

Otherwise the parameter can be omitted.

##### `smsSenderId`

Optional. Specifies the identifier of the sms sender to set for the notification. The identifiers are found in your service Settings, when you 'Manage' your 'Text message sender'.

If you omit this argument your default sms sender will be set for the notification.

</details>


### Email

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

The method signature is:
```php
sendEmail( $emailAddress, $templateId, array $personalisation = array(), $reference = '', $emailReplyToId = NULL )
```

An example request would look like:

```php
try {

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c', [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968'
        ],
        'unique_ref123',
        '862bfaaf-9f89-43dd-aafa-2868ce2926a9'
        );

} catch (NotifyException $e){}
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => None,
    "content" => [
        "subject" => "Licence renewal",
        "body" => "Dear Bill, your licence is due for renewal on 3 January 2016.",
        "from_email" => "the_service@gov.uk"
    ],
    "uri" => "https://api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
        "version" => 1,
        "uri" => "https://api.notificaitons.service.gov.uk/service/your_service_id/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ]
]
```

Otherwise the client will raise a ``Alphagov\Notifications\Exception\NotifyException``:

|`exc->getCode()`|`exc->getErrors()`|
|:---|:---|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM of 10 requests per 10 seconds"`<br>`}]`|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (50) for today"`<br>`}]`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient using a team-only API key"`<br>`]}`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|


</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$emailAddress`
The email address the email notification is sent to.

##### `$templateId`

Find by clicking **API info** for the template you want to send.

##### `$personalisation`

If a template has placeholders you need to provide their values. For example:

```php
personalisation = [
    'name' => 'Betty Smith',
    'dob'  => '12 July 1968'
]
```

Otherwise the parameter can be omitted.

##### `$reference`

An optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single  notification or a batch of notifications.

##### `$emailReplyToId`

Optional. Specifies the identifier of the email reply-to address to set for the notification. The identifiers are found in your service Settings, when you 'Manage' your 'Email reply to addresses'.

If you omit this argument your default email reply-to address will be set for the notification.

</details>


### Send a document by email
Send files without the need for email attachments.

To send a document by email, add a placeholder field to the template then upload a file. The placeholder field will contain a secure link to download the document.

[Contact the GOV.UK Notify team](https://www.notifications.service.gov.uk/support) to enable this function for your service.

#### Add a placeholder field to the template

In Notify, use double brackets to add a placeholder field to the email template. For example:

"Download your document at: ((link_to_document))"


#### Upload your document
˜
The document you upload must be a PDF file smaller than 2MB.

Pass the file object as a value into the personalisation argument. For example:

```php
try {
    $file_data = file_get_contents('/path/to/my/file.pdf');

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968',
            'link_to_document' => $notifyClient->prepareUpload( $file_data )
        ]
    );

} catch (NotifyException $e){}
```

### Response

If the request to the client is successful, the client returns a response `object`, with a following `body` attribute:

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => None,
    "content" => [
        "subject" => "SUBJECT TEXT",
        "body" => "MESSAGE TEXT",
        "from_email" => "SENDER EMAIL
    ],
    "uri" => "https://api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
        "version" => 1,
        "uri" => "https://api.notificaitons.service.gov.uk/service/your_service_id/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ]
]
```

### Error codes

If the request is not successful, the client returns an error `error object`:

|error.status_code|error.message|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient using a team-only API key"`<br>`]}`|Use the correct type of [API key](#api-keys)|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode)|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Unsupported document type '{}'. Supported types are: {}"`<br>`}]`|The document you upload must be a PDF file|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Document didn't pass the virus scan"`<br>`}]`|The document you upload must be virus free|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct type of [API key](#api-keys)|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#api-rate-limits) for more information|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|

### Letter

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

The method signature is:
```php
sendLetter( $templateId, array $personalisation = array(), $reference = '' )
```

An example request would look like:

```php
try {

    $response = $notifyClient->sendLetter(
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name'=>'Fred',
            'address_line_1' => 'Foo',
            'address_line_2' => 'Bar',
            'postcode' => 'Baz'
        ],
        'unique_ref123'
    );

} catch (NotifyException $e){}
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => "unique_ref123",
    "content" => [
        "subject" => "Licence renewal",
        "body" => "Dear Bill, your licence is due for renewal on 3 January 2016.",
    ],
    "uri" => "https://api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
        "version" => 1,
        "uri" => "https://api.notificaitons.service.gov.uk/service/your_service_id/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ],
    "scheduled_for" => null
]
```

Otherwise the client will raise a ``Alphagov\Notifications\Exception\NotifyException``:

|`exc->getCode()`|`exc->getErrors()`|
|:---|:---|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM of 10 requests per 10 seconds"`<br>`}]`|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (50) for today"`<br>`}]`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient using a team-only API key"`<br>`]}`|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can"t send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|

</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `templateId`

Find by clicking **API info** for the template you want to send.

##### `personalisation`

If a template has placeholders you need to provide their values. For example:

```php
personalisation = [
    'name' => 'Betty Smith',
    'dob'  => '12 July 1968'
]
```

Otherwise the parameter can be omitted.

##### `reference`

An optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single  notification or a batch of notifications.


</details>

## Send a precompiled Letter

This is an invitation-only feature. Contact the GOV.UK Notify team on the [support page](https://www.notifications.service.gov.uk/support) or through the [Slack channel](https://ukgovernmentdigital.slack.com/messages/govuk-notify) for more information.

### Method

```php
response = notifications_client.send_precompiled_letter_notification(
    $reference,      # Reference to identify the notification
    $pdf_file        # PDF File object
)
```

### Arguments

##### `$reference` (required)

A unique identifier you create. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

#### `$pdf_data` (required)

The precompiled letter must be a PDF file.

```php
$file_contents = file_get_contents("path/to/pdf_file");
try {

    $response = $notifyClient->sendLetter(
        'unique_ref123',
        $file_contents
    );

} catch (NotifyException $e){}
```

### Response

If the request to the client is successful, the client returns a `dict`:

```php
[
  "id" => "740e5834-3a29-46b4-9a6f-16142fde533a",
  "reference" => "unique_ref123"
]
```

### Error codes

If the request is not successful, the client returns an HTTPError containing the relevant error code.

|error.status_code|error.message|How to fix|
|:---|:---|:---|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type live of 10 requests per 20 seconds"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (50) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters with a team api key"`<br>`]}`|Use the correct type of [API key](#api-keys)|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send precompiled letters"`<br>`]}`|This is an invitation-only feature. Contact the GOV.UK Notify team on the [support page](https://www.notifications.service.gov.uk/support) or through the [Slack channel](https://ukgovernmentdigital.slack.com/messages/govuk-notify) for more information|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Letter content is not a valid PDF"`<br>`]}`|PDF file format is required|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode)|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "reference is a required property"`<br>`}]`|Add a `reference` argument to the method call|


## Get the status of one message

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

The method signature is:
```php
getNotification( $notificationId )
```

An example request would look like:

```php
try {

    $response = $notifyClient->getNotification( 'c32e9c89-a423-42d2-85b7-a21cd4486a2a' );

} catch (NotifyException $e){}
```

</details>


#### Response

If the request is successful, `response` will be an `array `.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "id" => "notify_id",
    "body" => "Hello Foo",
    "subject" => "null|email_subject",
    "reference" => "client reference",
    "email_address" => "email address",
    "phone_number" => "phone number",
    "line_1" => "full name of a person or company",
    "line_2" => "123 The Street",
    "line_3" => "Some Area",
    "line_4" => "Some Town",
    "line_5" => "Some county",
    "line_6" => "Something else",
    "postcode" => "postcode",
    "type" => "sms|letter|email",
    "status" => "current status",
    "template" => [
        "version" => 1,
        "id" => 1,
        "uri" => "/template/{id}/{version}"
     ],
    "created_at" => "created at",
    "created_by_name" => "A name",  // name of the person who sent the notification if sent manually
    "sent_at" => "sent to provider at",
]
```

Otherwise the client will raise a ``Alphagov\Notifications\Exception\NotifyException``:

|`error["status_code"]`|`error["message"]`|
|:---|:---|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|

</details>

#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$notificationId`

The ID of the notification.

</details>

## Get the status of all messages

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

The method signature is:
```php
listNotifications( array $filters = array() )
```

An example request would look like:

```php
    $response = $notifyClient->listNotifications([
        'older_than' => 'c32e9c89-a423-42d2-85b7-a21cd4486a2a',
        'reference' => 'weekly-reminders',
        'status' => 'delivered',
        'template_type' => 'sms'
    ]);
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "notifications" => [
            "id" => "notify_id",
            "reference" => "client reference",
            "email_address" => "email address",
            "phone_number" => "phone number",
            "line_1" => "full name of a person or company",
            "line_2" => "123 The Street",
            "line_3" => "Some Area",
            "line_4" => "Some Town",
            "line_5" => "Some county",
            "line_6" => "Something else",
            "postcode" => "postcode",
            "type" => "sms | letter | email",
            "status" => sending | delivered | permanent-failure | temporary-failure | technical-failure
            "template" => [
            "version" => 1,
            "id" => 1,
            "uri" => "/template/{id}/{version}"
        ],
        "created_at" => "created at",
        "created_by_name" => "A name",  // name of the person who sent the notification if sent manually
        "sent_at" => "sent to provider at",
        ],
        …
  ],
  "links" => [
     "current" => "/notifications?template_type=sms&status=delivered",
     "next" => "/notifications?older_than=last_id_in_list&template_type=sms&status=delivered"
  ]
]
```

Otherwise the client will raise a ``Alphagov\Notifications\Exception\NotifyException``:

|`error["status_code"]`|`error["message"]`|
|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "bad status is not one of [created, sending, delivered, pending, failed, technical-failure, temporary-failure, permanent-failure]"`<br>`}]`|
|`400`|`[{`<br>`"error": "Apple is not one of [sms, email, letter]"`<br>`}]`|

</details>

#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `older_than`

If omitted 250 of the most recent messages are returned. Otherwise the next 250  messages older than the given notification id are returned.

##### `template_type`

If omitted all messages are returned. Otherwise you can filter by:

* `email`
* `sms`
* `letter`

##### `status`

__email__

You can filter by:

* `sending` - the message is queued to be sent by the provider.
* `delivered` - the message was successfully delivered.
* `failed` - this will return all failure statuses `permanent-failure`, `temporary-failure` and `technical-failure`.
* `permanent-failure` - the provider was unable to deliver message, email does not exist; remove this recipient from your list.
* `temporary-failure` - the provider was unable to deliver message, email box was full; you can try to send the message again.
* `technical-failure` - Notify had a technical failure; you can try to send the message again.

You can omit this argument to ignore this filter.

__text message__

You can filter by:

* `sending` - the message is queued to be sent by the provider.
* `delivered` - the message was successfully delivered.
* `failed` - this will return all failure statuses `permanent-failure`, `temporary-failure` and `technical-failure`.
* `permanent-failure` - the provider was unable to deliver message, phone number does not exist; remove this recipient from your list.
* `temporary-failure` - the provider was unable to deliver message, the phone was turned off; you can try to send the message again.
* `technical-failure` - Notify had a technical failure; you can try to send the message again.

You can omit this argument to ignore this filter.

__letter__

You can filter by:

* `accepted` - Notify is in the process of printing and posting the letter
* `technical-failure` - Notify had an unexpected error while sending to our printing provider

You can omit this argument to ignore this filter.

##### `reference`

This is the `reference` you gave at the time of sending the notification. This can be omitted to ignore the filter.

</details>

## Get a template by ID

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

```php
    $response = $notifyClient->getTemplate( 'templateId' );
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>


```php
{
    "id" => "template_id",
    "name" => "Template name"
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
}
```

|`error["status_code"]`|`error["errors"]`|
|:---|:---|
|`404`|`[{`<br>`"error" => "NoResultFound",`<br>`"message" => "No result found"`<br>`}]`|

</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `templateId`

Find by clicking **API info** for the template you want to send.

</details>

## Get a template by ID and version

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

```php
    $response = $notifyClient->getTemplateVersion( 'templateId', 1 );
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "id" => "template_id",
    "name" => "Template name"
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
]
```

|`error["status_code"]`|`error["errors"]`|
|:---|:---|
|`404`|`[{`<br>`"error" => "NoResultFound",`<br>`"message" => "No result found"`<br>`}]`|

</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `templateId`

Find by clicking **API info** for the template you want to send.

##### `version`

The version number of the template

</details>

## Get all templates

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

```php
    $this->getAllTemplates(
      $template_type  // optional
    );
```
This will return the latest version for each template

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>

```php
[
    "templates"  => [
        [
            "id" => "template_id",
            "name" => "Template name"
            "type" => "sms|email|letter",
            "created_at" => "created at",
            "updated_at" => "updated at",
            "version" => "version",
            "created_by" => "someone@example.com",
            "body" => "body",
            "subject" => "null|email_subject"
        ],
        [
            ... another template
        ]
    ]
]
```

If no templates exist for a template type or there no templates for a service, the `response` will be a Dictionary` with an empty `templates` list element:

```php
[
    "templates"  => []
]
```

</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$templateType`

If omitted all messages are returned. Otherwise you can filter by:

* `email`
* `sms`
* `letter`

</details>


## Generate a preview template

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

```php
    $personalisation = [ "foo" => "bar" ];
    $this->previewTemplate( $templateId, $personalisation );
```

</details>


#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>


```php
[
    "id" => "notify_id",
    "type" => "sms|email|letter",
    "version" => "version",
    "body" => "Hello bar" // with substitution values,
    "subject" => "null|email_subject"
]
```

|`error["status_code"]`|`error["errors"]`|
|:---|:---|
|`400`|`[{`<br>`"error" => "BadRequestError",`<br>`"message" => "Missing personalisation => [name]"`<br>`}]`|
|`404`|`[{`<br>`"error" => "NoResultFound",`<br>`"message" => "No result found"`<br>`}]`|


</details>


#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$templateId`

Find by clicking **API info** for the template you want to send.

##### `$personalisation`

If a template has placeholders you need to provide their values. For example:

```php
$personalisation = [
    'first_name' => 'Amala',
    'reference_number' => '300241',
];
```

Otherwise the parameter can be omitted or `null` can be passed in its place.

</details>

## Get received text messages

#### Method

<details>
<summary>
Click here to expand for more information.
</summary>

```php
    $this->listReceivedTexts(
      $older_than  // optional
    );
```

</details>

#### Response

If the request is successful, `response` will be an `array`.

<details>
<summary>
Click here to expand for more information.
</summary>


```php
[
    "received_text_messages" => [
        [
            "id" => "notify_id",
            "user_number" => "user number",
            "notify_number" => "notify number",
            "created_at" => "created at",
            "service_id" => "service id",
            "content" => "text content"
        ],
        [
            ... another received text message
        ]
    ]
  ],
  "links" => [
     "current" => "/received-text-messages",
     "next" => "/received-text-messages?older_than=last_id_in_list"
  ]
]
```

</details>

#### Arguments

<details>
<summary>
Click here to expand for more information.
</summary>

##### `$older_than`

If omitted 250 of the most recently received text messages are returned. Otherwise the next 250 received text messages older than the given id are returned.

</details>
