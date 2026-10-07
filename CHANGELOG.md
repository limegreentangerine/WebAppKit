# Changelog

Notable changes to WebAppKit are documented in this file.

## [Unreleased]

### Fixed

- Preserve push subscription JSON as supplied instead of encoding the JSON string a second time.
- Allow push response JSON decoding to return either an object or an associative array.

## [1.0.0] - 2026-10-07

### Added

- Added dashboard configuration for a standalone web app, including its manifest, icons, display mode, colours, and iOS launch screens.
- Added generation of `site.webmanifest` and web-app metadata on rendered pages.
- Added push-notification management for subscribers, custom notifications, scheduled notifications, and page-publish notifications.
- Added automatic service-worker installation through the Composer `install-service-worker` command.
- Added dashboard settings for push keys and notification publishing.
