# Contributing

Pull requests are welcome.

## Tests

There are unit and integration tests that can be run to test functionality of the client.

To run the unit tests:

```sh
vendor/bin/phpspec run spec/unit/ --format=pretty --verbose
```

To run the integration tests:

```sh
vendor/bin/phpspec run spec/integration/ --format=pretty --verbose
```

To run both sets of tests:

```sh
vendor/bin/phpspec run --format=pretty
```
