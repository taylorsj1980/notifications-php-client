# Get message status

Message status depends on the type of message that you have sent.

You can only get the status of messages that are 7 days old or newer.

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Status - text and email

<div style="height:1px;font-size:1px;">&nbsp;</div>

|Status|Information|
|:---|:---|
|Created|The message is queued to be sent to the provider. The notification usually remains in this state for a few seconds.|
|Sending|The message is queued to be sent by the provider to the recipient, and GOV.UK Notify is waiting for delivery information.|
|Delivered|The message was successfully delivered.|
|Failed|This covers all failure statuses:<br>- `permanent-failure` - "The provider was unable to deliver message, email or phone number does not exist; remove this recipient from your list"<br>- `temporary-failure` - "The provider was unable to deliver message, email inbox was full or phone was turned off; you can try to send the message again"<br>- `technical-failure` - "Notify had a technical failure; you can try to send the message again"|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Status - text only

<div style="height:1px;font-size:1px;">&nbsp;</div>

|Status|Information|
|:---|:---|
|Pending|GOV.UK Notify received a callback from the provider but the device has not yet responded. Another callback from the provider determines the final status of the notification.|
|Sent|The text message was delivered internationally. This only applies to text messages sent to non-UK phone numbers. GOV.UK Notify may not receive additional status updates depending on the recipient's country and telecoms provider.|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Status - letter

<div style="height:1px;font-size:1px;">&nbsp;</div>

|Status|information|
|:---|:---|
|Failed|The only failure status that applies to letters is `technical-failure`. GOV.UK Notify had an unexpected error while sending to our printing provider.|
|Accepted|GOV.UK Notify is printing and posting the letter.|
|Received|The provider has received the letter to deliver.|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Status - precompiled letter

<div style="height:1px;font-size:1px;">&nbsp;</div>

|Status|information|
|:---|:---|
|pending-virus-check|GOV.UK Notify virus scan of the precompiled letter file is not yet complete.|
|virus-scan-failed|GOV.UK Notify virus scan has identified a potential virus in the precompiled letter file.|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Get the status of one message

You can only get the status of messages that are 7 days old or newer.

### Method

```php
getNotification( $notificationId )
```

For example:

```php
try {
    $response = $notifyClient->getNotification( 'c32e9c89-a423-42d2-85b7-a21cd4486a2a' );
} catch (NotifyException $e){}
```

### Arguments

#### notification_id (required)

The ID of the notification. You can find the notification ID in the response to the [original notification method call](/python.html#get-the-status-of-one-message-response).

You can also find it in your [GOV.UK Notify Dashboard](https://www.notifications.service.gov.uk).

1. Sign in to GOV.UK Notify and select __Dashboard__.
1. Select either __emails sent__, __text messages sent__, or __letters sent__.
1. Select the relevant notification.
1. Copy the notification ID from the end of the page URL, for example `https://www.notifications.service.gov.uk/services/af90d4cb-ae88-4a7c-a197-5c30c7db423b/notification/ID`.

### Response

If the request to the client is successful, the client returns an `array`:

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
    "sent_at" => "sent to provider at",
];
```

### Error codes

If the request is not successful, the client will return an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

<div style="height:1px;font-size:1px;">&nbsp;</div>

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|Check the notification ID.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the notification ID.|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Get the status of multiple messages

This API call returns one page of up to 250 messages and statuses. You can get either the most recent messages, or get older messages by specifying a particular notification ID in the `older_than` argument.

You can only get the status of messages that are 7 days old or newer.

### Method

```php
listNotifications( array $filters = array() )
```

```php
    $response = $notifyClient->listNotifications([
        'older_than' => 'c32e9c89-a423-42d2-85b7-a21cd4486a2a',
        'reference' => 'weekly-reminders',
        'status' => 'delivered',
        'template_type' => 'sms'
    ]);
```

You can leave out the `older_than` argument to get the 250 most recent messages.

To get older messages, pass the ID of an older notification into the `older_than` argument. This returns the next 250 oldest messages from the specified notification ID.

### Arguments

You can leave out any of these arguments to ignore these filters.

#### template_type (optional)

You can filter by:

* `email`
* `sms`
* `letter`

You can leave out this argument to ignore this filter.

#### status (optional)

<div style="height:1px;font-size:1px;">&nbsp;</div>

| status | description | text | email | letter |
|:--- |:--- |:--- |:--- |:--- |
|`created` |The message is queued to be sent to the provider|Yes|Yes||
|`sending` |The message is queued to be sent to the recipient by the provider|Yes|Yes||
|`delivered`|The message was successfully delivered|Yes|Yes||
|`failed`|This will return all failure statuses:<br>- `permanent-failure`<br>- `temporary-failure`<br>- `technical-failure`|Yes|Yes||
|`permanent-failure`|The provider was unable to deliver message, email or phone number does not exist; remove this recipient from your list|Yes|Yes||
|`temporary-failure`|The provider was unable to deliver message, email inbox was full or phone was turned off; you can try to send the message again|Yes|Yes||
|`technical-failure`|Email or text message: Notify had a technical failure; you can try to send the message again. <br><br>Letter: Notify had an unexpected error while sending to our printing provider. <br><br>You can omit this argument to ignore this filter.|Yes|Yes||
|`accepted`|Notify is printing and posting the letter|||Yes|
|`received`|The provider has received the letter to deliver|||Yes|

<div style="height:1px;font-size:1px;">&nbsp;</div>

#### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING',
```

You can leave out this argument to ignore this filter.

#### older_than (optional)

Input the ID of a notification into this argument. If you use this argument, the method returns the next 250 received notifications older than the specified ID.

```php
older_than='740e5834-3a29-46b4-9a6f-16142fde533a'
```

If you leave out this argument, the method returns the most recent 250 notifications.

The client only returns notifications that are 7 days old or newer. If the notification specified in this argument is older than 7 days, the client returns an empty response.

### Response

If the request to the client is successful, the client returns an `array`.

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
        "sent_at" => "sent to provider at",
        ],
        …
  ],
  "links" => [
     "current" => "/notifications?template_type=sms&status=delivered",
     "next" => "/notifications?older_than=last_id_in_list&template_type=sms&status=delivered"
  ]
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

<div style="height:1px;font-size:1px;">&nbsp;</div>

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "bad status is not one of [created, sending, delivered, pending, failed, technical-failure, temporary-failure, permanent-failure]"`<br>`}]`|Contact the GOV.UK Notify team.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Apple is not one of [sms, email, letter]"`<br>`}]`|Contact the GOV>UK Notify team.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|

<div style="height:1px;font-size:1px;">&nbsp;</div>
