# Changelog

![keep a changelog](https://img.shields.io/badge/Keep%20a%20Changelog-v1.1.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog] for information about writing changes to this log.

## [Unreleased]

## [5.0.0] - 2026-11-19

### Added

* Added the contrib module `drupal/action`, because Drupal 11 removed Action UI
  from core.

### Changed

* Updated Drupal core from 10.6.22 to 11.4.13.
* Updated contrib modules to new major versions with Drupal 11 support:
  config_filter 2, config_ignore 3, elasticsearch_connector 9, field_group 4,
  flag 5, flood_control 3, image_widget_crop 3, maxlength 3, metatag 2,
  reroute_email 2.3, scheduler 2, video_embed_field 3 and xmlsitemap 2.
* Changed the search server configuration to the elasticsearch_connector 9
  format. Elasticsearch 9 runs with `xpack.security.enabled=false`.
  Index all content again after deployment.
* Set `core_version_requirement: ^10 || ^11` on all custom modules and the
  theme.

### Removed

* Removed contrib modules that were not enabled: advagg, email_registration,
  group, mailchimp, mailsystem, purge, purge_users, s3fs,
  user_registrationpassword and varnish_purge.
* Removed the tag fields from the full and search result node displays, so
  the pages show the same content as before the upgrade.

### Fixed

* Fixed deprecated API calls and implicit nullable parameters in custom code.
* Fixed the header search. The search blocks now put the form attributes on
  the block wrapper, because Drupal 11 does not do this for forms.

## [4.4.2] - 2026-11-18

### Security

* Drupal security update

## [4.4.1] - 2026-10-14

### Security

* Drupal security update

## [4.4.0] - 2026-05-20

### Changed

* Updated Drupal core from 10.4 to 10.6.9.
* Pinned core constraints to `~10.6.0` to keep the project on the 10.6.x series.

## [4.3.1] - 2026-04-20

### Changed

* Drupal core updates.

## [4.3.0] - 2025-03-24

### Changed

* Drupal core updates.

## [4.2.3] - 2024-06-21

### Fixed

* Fixed background-image paths.

## [4.2.2] - 2024-06-21

### Changed

* [PR-66](https://github.com/litteratursiden/litteratursiden/pull/66)
  Moved the last update banner styling to a CSS file.

## [4.2.1] - 2024-06-19

### Removed

* Removed the user picture with link.

## [4.2.0] - 2024-06-19

### Added

* [PR-65](https://github.com/litteratursiden/litteratursiden/pull/65)
  Added the last update banner to the site.

### Changed

* Disabled search, forms and login.
* Disabled contact forms.
* Updated configuration from production.

## [4.1.4] - 2024-01-30

### Changed

* Added a comma to the taxonomy term list.

## [4.1.3] - 2023-12-15

### Added

* Allow adding youthreview to latest reviews.

## [4.1.2] - 2023-11-20

### Changed

* Renamed flag actions.

## [4.1.1] - 2023-11-20

### Changed

* Updated new content type Ungeanmeldelse/youthreview.

## [4.1.0] - 2023-11-15

### Added

* Added new content type Ungeanmeldelse/youthreview.

## [4.0.5] - 2023-11-01

### Added

* Added Siteimprove script to the footer.

### Changed

* Updated drupal/xmlsitemap from ^1.4 to ^1.5.

### Fixed

* Fixed system action flag naming conventions, causing errors during setup.
* Fixed layout overflow styling issue.
* Fixed an issue with viewsreference interfering with infinitescroll.

## [4.0.4] - 2023-10-25

### Changed

* Changed action IDs.

## [4.0.3] - 2023-10-25

### Changed

* Updated config of sitemap scope, to only include content types: temaer,
  boglister, artikler.

## [4.0.2] - 2023-10-09

### Fixed

* Fixed "Add one more" for book ref. fields to the open platform.

## [4.0.1] - 2023-10-05

### Added

* Added pull request template.

### Changed

* Updated change log format.

### Fixed

* Fixed open platform widget - hook_field_widget_form_alter() is deprecated
  and removed in Drupal 10.

## [4.0.0] - 2023-09-25

### Added

* Added GitHub actions to check code.
* Added this changelog.
* Added linting for markdown.
* Added PHP code sniffer.
* Added PHPStan code analyzing.

### Changed

* Upgraded contrib modules to be Drupal 10 compatible.
* Upgraded custom modules to be Drupal 10 compatible.

### Removed

* Disabled several modules.
* Disabled videos in WYSIWYG (not D10 compatible yet).

[keep a changelog]: https://keepachangelog.com/en/1.1.0/
[unreleased]: https://github.com/litteratursiden/litteratursiden/compare/5.0.0...HEAD
[5.0.0]: https://github.com/litteratursiden/litteratursiden/compare/4.4.2...5.0.0
[4.4.2]: https://github.com/litteratursiden/litteratursiden/compare/4.4.1...4.4.2
[4.4.1]: https://github.com/litteratursiden/litteratursiden/compare/4.4.0...4.4.1
[4.4.0]: https://github.com/litteratursiden/litteratursiden/compare/4.3.1...4.4.0
[4.3.1]: https://github.com/litteratursiden/litteratursiden/compare/4.3.0...4.3.1
[4.3.0]: https://github.com/litteratursiden/litteratursiden/compare/4.2.3...4.3.0
[4.2.3]: https://github.com/litteratursiden/litteratursiden/compare/4.2.2...4.2.3
[4.2.2]: https://github.com/litteratursiden/litteratursiden/compare/4.2.1...4.2.2
[4.2.1]: https://github.com/litteratursiden/litteratursiden/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/litteratursiden/litteratursiden/compare/4.1.4...4.2.0
[4.1.4]: https://github.com/litteratursiden/litteratursiden/compare/4.1.3...4.1.4
[4.1.3]: https://github.com/litteratursiden/litteratursiden/compare/4.1.2...4.1.3
[4.1.2]: https://github.com/litteratursiden/litteratursiden/compare/4.1.1...4.1.2
[4.1.1]: https://github.com/litteratursiden/litteratursiden/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/litteratursiden/litteratursiden/compare/4.0.5...4.1.0
[4.0.5]: https://github.com/litteratursiden/litteratursiden/compare/4.0.4...4.0.5
[4.0.4]: https://github.com/litteratursiden/litteratursiden/compare/4.0.3...4.0.4
[4.0.3]: https://github.com/litteratursiden/litteratursiden/compare/4.0.2...4.0.3
[4.0.2]: https://github.com/litteratursiden/litteratursiden/compare/4.0.1...4.0.2
[4.0.1]: https://github.com/litteratursiden/litteratursiden/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/litteratursiden/litteratursiden/compare/3.0.7...4.0.0
