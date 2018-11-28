# Get a template

## Get a template by ID

### Method

This returns the latest version of the template.

```php
$response = $notifyClient->getTemplate( 'templateId' );
```

### Arguments

#### templateId (required)

The ID of the template. Sign in to GOV.UK Notify and go to the __Templates__ page to find this.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "template_id",
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

<div style="height:1px;font-size:1px;">&nbsp;</div>

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-arguments-template-id-required).|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Get a template by ID and version

### Method

```php
$response = $notifyClient->getTemplateVersion( 'templateId', 1 );
```

### Arguments

#### templateId (required)

The ID of the template. Sign in to GOV.UK Notify and go to the __Templates__ page to find this.

#### version (required)

The version number of the template.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "template_id",
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

<div style="height:1px;font-size:1px;">&nbsp;</div>

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-and-version-arguments-template-id-required) and [version](#version-required).|

<div style="height:1px;font-size:1px;">&nbsp;</div>

## Get all templates

### Method

This returns the latest version of all templates.

```php
    $this->getAllTemplates(
      $template_type  // optional
    );
```

### Arguments

#### template_type (optional)

If omitted, the method returns all templates. Otherwise you can filter by:

- `email`
- `sms`
- `letter`

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "templates"  => [
        [
            "id" => "template_id",
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
];
```

If no templates exist for a template type or there no templates for a service, the client returns a `dict` with an empty `templates` list element:

```php
[
    "templates"  => []
];
```

## Generate a preview template

### Method

This generates a preview version of a template.

```php
    $personalisation = [ "foo" => "bar" ];
    $this->previewTemplate( $templateId, $personalisation );
```

The parameters in the personalisation argument must match the placeholder fields in the actual template. The API notification client will ignore any extra fields in the method.

### Arguments

#### template_id (required)

The ID of the template. Sign in to GOV.UK Notify and go to the __Templates__ page.

#### personalisation (required)

If a template has placeholder fields for personalised information such as name or reference number, you need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'first_name' => 'Amala',
    'reference_number' => '300241',
];
```

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "notify_id",
    "type" => "sms|email|letter",
    "version" => "version",
    "body" => "Hello bar" // with substitution values,
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

<div style="height:1px;font-size:1px;">&nbsp;</div>

|exc->getCode()|exc->getErrors()|Notes|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Missing personalisation: [PERSONALISATION FIELD]"`<br>`}]`|Check that the personalisation arguments in the method match the placeholder fields in the template.|
|`400`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the [template ID](#generate-a-preview-template-arguments-template-id-required).|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|

<div style="height:1px;font-size:1px;">&nbsp;</div>
