# Web App

Add installable, standalone web-app support to a Concrete CMS site.

This package adds a **Web App** configuration page to the Concrete CMS dashboard. Administrators can configure the site's web app manifest, icons, display mode, colours, and iOS launch screens. When the feature is enabled, the package:

- Generates `site.webmanifest` in the site's document root.
- Adds a manifest link to rendered pages.
- Adds mobile web-app meta tags.
- Adds configured iOS startup-image links.

## Requirements

- Concrete CMS 9.5.0 or later
- PHP 8.4 or later
- Composer
- The PHP `mbstring` extension

## Installation

### Install as a Concrete CMS package

The package is also configured as a Composer package (`limegreentangerine/web_app`) and can be included in a project's Composer dependencies:

```bash
composer require limegreentangerine/web_app
```

After copying or installing the package, log in to Concrete CMS and go to **Dashboard → Extend Concrete → Install**. Select **Web App** and install it.

The installer registers the dashboard page at:

```text
/dashboard/web_app
```

The package uses the `web_app` handle and can be upgraded from the Concrete CMS package manager after updating its source files.

## Configuration

Open **Dashboard → Web App** after installation.

1. Enable **Make this site web app capable**.
2. Enter the required **Name**.
3. Optionally provide:
    - Short name
    - A comma-separated list of categories
    - Description
    - Display mode
    - Theme colour
    - Background colour
4. Select the required iPhone thumbnail (PNG, 57 × 57 pixels).
5. Select any additional application icons:
    - 48 × 48
    - 72 × 72
    - 96 × 96
    - 144 × 144
    - 168 × 168
    - 192 × 192
    - 256 × 256
    - 512 × 512
6. Optionally select iOS launch-screen images for the supported device sizes.
7. Save the settings.

Saving the settings writes `site.webmanifest` to the Concrete CMS document root. The manifest contains the configured name and optional metadata, colours, display mode, and selected icon files.

Available display modes are:

- `fullscreen`
- `standalone`
- `minimal-ui`
- `browser`

If the feature is disabled, the package does not add the manifest or web-app headers to rendered pages. Uninstalling the package removes the generated `site.webmanifest`; uploaded files selected in the dashboard are not deleted.

## Development

Clone the repository and install its development dependencies:

```bash
git clone https://github.com/limegreentangerine/web_app.git
cd web_app
composer install
```

Composer's post-install script installs the Node.js development dependencies from `package.json`. To install them separately:

```bash
npm install
```

### Tests

Run the PHPUnit test suite:

```bash
composer test
```

Generate a text coverage report:

```bash
composer test-coverage
```

### Formatting

Format PHP and JavaScript files:

```bash
composer format
```

Check formatting without changing files:

```bash
composer format:check
```

Individual formatters can also be run with:

```bash
composer format:php
composer format:php:check
composer format:js
composer format:js:check
```

## Project structure

```text
controller.php                              Package lifecycle and front-end hooks
controllers/single_page/dashboard/web_app.php  Dashboard settings controller
single_pages/dashboard/web_app.php          Dashboard settings form
src/Package/PageTrait.php                    Concrete CMS page helpers
tests/ControllerTest.php                     PHPUnit tests
.github/workflows/                           Main and develop branch CI workflows
```

## Continuous integration

GitHub Actions runs `composer install` and `composer test` on PHP 8.4 for pushes to `main` and `develop`. The `main` workflow also dispatches a downstream package test event after the test job succeeds.

## License

This project is licensed under the MIT License.
