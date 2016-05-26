Moodle Packagist
================

This is an experimental repository which allows Moodle plugins to be managed using [Composer](https://getcomposer.org). It is forked from the [wpackagist.org](https://wpackagist.org) project and builds on the work done by [Michael Aherne](https://github.com/micaherne/moodle-plugin-repo).

## Usage

Example composer.json:

```json
{
    "name": "acme/brilliant-wordpress-site",
    "description": "My brilliant WordPress site",
    "repositories":[
        {
            "type":"composer",
            "url":"https://wpackagist.org"
        }
    ],
    "require": {
        "aws/aws-sdk-php":"*",
        "wpackagist-plugin/akismet":"dev-trunk",
        "wpackagist-plugin/captcha":">=3.9",
        "wpackagist-theme/hueman":"*"
    },
    "autoload": {
        "psr-0": {
            "Acme": "src/"
        }
    }
}
```

## Moodle core

This does not provide Moodle itself.

## Running Moodle Packagist

### Installing

1. Make sure you have PDO with sqlite support enabled.
2. Make sure [`data`](data/) is writable. Do NOT create `data/packages.sqlite`, it will be created automatically.
3. Run `composer install`.
4. Point your Web server to [`web`](web/). A [`.htaccess`](web/.htaccess) is provided for Apache.

### Updating the database

1. `bin/cmd refresh`: Refresh the list of plugins from the Moodle plugins repository.
2. `bin/cmd build`: Rebuild all `.json` files in `web/`.
