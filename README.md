# WebAppKit

Add standalone web-app support and push notifications to a Concrete CMS site.

WebAppKit adds web-app settings and push-notification management pages to the Concrete CMS dashboard. Administrators can configure the site's web app manifest, icons, display mode, colours, iOS launch screens, push keys, and notification publishing. The package:

- Generates `site.webmanifest` in the site's document root.
- Adds a manifest link to rendered pages.
- Adds mobile web-app meta tags.
- Adds configured iOS startup-image links.
- Provides push subscription management and custom and scheduled notifications.
- Sends notifications when configured pages are published.
- Installs the web push service worker automatically when the package is installed or upgraded.

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

After copying or installing the package, log in to Concrete CMS and go to **Dashboard → Extend Concrete → Install**. Select **WebAppKit** and install it.

The package registers the following dashboard pages:

```text
/dashboard/web_app
/dashboard/push_notifications
```

The package uses the `web_app` handle and can be upgraded from the Concrete CMS package manager after updating its source files. It requires the `class_kit` package.

## Configuration

Open **Dashboard → Web App** after installation to configure the manifest and app appearance.

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

If the web-app feature is disabled, the package does not add the manifest or web-app headers to rendered pages. Push notifications are configured separately in the Push Notifications dashboard. If automatic service-worker installation fails, the package logs a warning and shows a dashboard warning; install it manually with `./vendor/bin/install-service-worker` from the Concrete CMS project root. Uninstalling the package removes the generated `site.webmanifest`; uploaded files selected in the dashboard are not deleted.

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

Run the PHPUnit suite, including package metadata and service-worker installation checks, entity accessor and serialization tests, image size validation, command value tests, and push-response JSON decoding:

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
controller.php                              Package lifecycle, routes, and event hooks
controllers/single_page/dashboard/           Dashboard page controllers
single_pages/dashboard/                     Dashboard page templates
src/                                        Push, web-app, entity, and search functionality
bin/install-service-worker                  Composer service-worker installation command
tests/ControllerTest.php                    PHPUnit controller tests
.github/workflows/                           CI workflows
```

## Continuous integration

GitHub Actions runs `composer test` for pull requests. Pushes run `composer format:check` and `composer typecheck`. Both workflows use PHP 8.4.

## License

This project is licensed under the MIT License.
