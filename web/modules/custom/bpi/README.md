# Installation

1. In your Drupal8 project, run:
```
composer require wikimedia/composer-merge-plugin
```
2. Make sure the following section exists in your Drupal8 project `composer.json` file:
```
"extra": {
        ...
        "merge-plugin": {
            "include": [
                "web/modules/custom/*/composer.json"
            ]
        }
    }
```

This will allow drupal to discover any classes that are dependencies defined in custom modules `composer.json` file.

3. Run `composer update bpi/sdk`.
