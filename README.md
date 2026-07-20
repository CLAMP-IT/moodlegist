Moodle Packagist
================

This is an experimental repository which allows Moodle plugins to be managed using [Composer](https://getcomposer.org). It is forked from the [wpackagist.org](https://wpackagist.org) project and builds on the work done by [Michael Aherne](https://github.com/micaherne/moodle-plugin-repo).

## Usage

Example composer.json:

```json
{
    "name": "myschool/my-moodle-site",
    "description": "My Moodle site",
    "repositories":[
        {
            "type":"composer",
            "url":"https://my-moodlegist-site"
        }
    ],
    "require": {
        "moodle/moodle":"3.0.0",
        "moodle-plugin-db/mod_attendance":"*",
        "moodle-plugin-db/theme_essential":"*",
    }
}
```

## Moodle core

This does not provide Moodle itself. You may specify a version of moodle to lock packages at that supported release level.

## Running Moodle Packagist

### Installing

1. Make sure you have PDO with sqlite support enabled.
2. Make sure [`data`](data/) is writable.
3. Run `composer install`.
4. Run `php bin/console doctrine:migrations:migrate` to bootstrap the database at `data/packages.sqlite`.
5. Point your Web server to [`public`](public/). A [`.htaccess`](web/.htaccess) is provided for Apache.

### Updating the database

1. `php bin/console refresh`: Refresh the list of plugins from the Moodle plugins repository.
2. `php bin/console build`: Rebuild all `.json` files in `public/`.
