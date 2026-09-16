# Composer Package Template

[![DevTests](https://github.com/limegreentangerine/composer_package/actions/workflows/DevTest.yml/badge.svg?branch=develop)](https://github.com/limegreentangerine/composer_package/actions/workflows/DevTest.yml)

Do a find and replace on the following fields before you start, best to make the search case sensitive:

`composer_package` - the package handle
`composer_description` - the description of the package
`ComposerPackage` - the namespace of the package
`composer_name` - the package name

## Authors

Ensure to add your credit in the `authors` section of `composer.json` so issues can be assigned appropriately.

## Formatting

As you add more folders to the package you will want those to be formatted too, look in `.php-cs-fixer.dist.php` and add them to the already define folders.

### Default Settings

```php
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
    ->append([
        __DIR__ . '/controller.php'
    ])
    ->exclude([
        'vendor',
    ]);
```

### Example with addtional info

```php
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/attributes',
        __DIR__ . '/blocks',
        __DIR__ . '/controllers',
        __DIR__ . '/elements',
        __DIR__ . '/overrides',
        __DIR__ . '/single_pages',
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ])
    ->append([
        __DIR__ . '/another.php'
        __DIR__ . '/controller.php'
    ])
    ->exclude([
        'bin',
        'vendor'
    ]);
```

## GitHub Actions

`DevTests` - runs formatting and build tests on the `develop` branch.

`Tests` - runs formatting, build and workflow triggers on `main` branch.

## Deployment Tokens (`main` branch)

The github actions has a link to `packages.limegreentangerine.net` so it will build on new version publication.

You will need to go to **Settings > Secrets & Variables > Actions** and add a new **Repository Secret**

- Name: `CROSS_REPO_TOKEN`
- Secret: _Available in mSecure under lgtdevrow github details_

Once these details are entered your tests will pass for pull requests and for deployment.
