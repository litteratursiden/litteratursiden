# Changelog

![keep a changelog](https://img.shields.io/badge/Keep%20a%20Changelog-v1.1.0-brightgreen.svg?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiNmMTVkMzAiIHZpZXdCb3g9IjAgMCAxODcgMTg1Ij48cGF0aCBkPSJNNjIgN2MtMTUgMy0yOCAxMC0zNyAyMmExMjIgMTIyIDAgMDAtMTggOTEgNzQgNzQgMCAwMDE2IDM4YzYgOSAxNCAxNSAyNCAxOGE4OSA4OSAwIDAwMjQgNCA0NSA0NSAwIDAwNiAwbDMtMSAxMy0xYTE1OCAxNTggMCAwMDU1LTE3IDYzIDYzIDAgMDAzNS01MiAzNCAzNCAwIDAwLTEtNWMtMy0xOC05LTMzLTE5LTQ3LTEyLTE3LTI0LTI4LTM4LTM3QTg1IDg1IDAgMDA2MiA3em0zMCA4YzIwIDQgMzggMTQgNTMgMzEgMTcgMTggMjYgMzcgMjkgNTh2MTJjLTMgMTctMTMgMzAtMjggMzhhMTU1IDE1NSAwIDAxLTUzIDE2bC0xMyAyaC0xYTUxIDUxIDAgMDEtMTItMWwtMTctMmMtMTMtNC0yMy0xMi0yOS0yNy01LTEyLTgtMjQtOC0zOWExMzMgMTMzIDAgMDE4LTUwYzUtMTMgMTEtMjYgMjYtMzMgMTQtNyAyOS05IDQ1LTV6TTQwIDQ1YTk0IDk0IDAgMDAtMTcgNTQgNzUgNzUgMCAwMDYgMzJjOCAxOSAyMiAzMSA0MiAzMiAyMSAyIDQxLTIgNjAtMTRhNjAgNjAgMCAwMDIxLTE5IDUzIDUzIDAgMDA5LTI5YzAtMTYtOC0zMy0yMy01MWE0NyA0NyAwIDAwLTUtNWMtMjMtMjAtNDUtMjYtNjctMTgtMTIgNC0yMCA5LTI2IDE4em0xMDggNzZhNTAgNTAgMCAwMS0yMSAyMmMtMTcgOS0zMiAxMy00OCAxMy0xMSAwLTIxLTMtMzAtOS01LTMtOS05LTEzLTE2YTgxIDgxIDAgMDEtNi0zMiA5NCA5NCAwIDAxOC0zNSA5MCA5MCAwIDAxNi0xMmwxLTJjNS05IDEzLTEzIDIzLTE2IDE2LTUgMzItMyA1MCA5IDEzIDggMjMgMjAgMzAgMzYgNyAxNSA3IDI5IDAgNDJ6bS00My03M2MtMTctOC0zMy02LTQ2IDUtMTAgOC0xNiAyMC0xOSAzN2E1NCA1NCAwIDAwNSAzNGM3IDE1IDIwIDIzIDM3IDIyIDIyLTEgMzgtOSA0OC0yNGE0MSA0MSAwIDAwOC0yNCA0MyA0MyAwIDAwLTEtMTJjLTYtMTgtMTYtMzEtMzItMzh6bS0yMyA5MWgtMWMtNyAwLTE0LTItMjEtN2EyNyAyNyAwIDAxLTEwLTEzIDU3IDU3IDAgMDEtNC0yMCA2MyA2MyAwIDAxNi0yNWM1LTEyIDEyLTE5IDI0LTIxIDktMyAxOC0yIDI3IDIgMTQgNiAyMyAxOCAyNyAzM3MtMiAzMS0xNiA0MGMtMTEgOC0yMSAxMS0zMiAxMXptMS0zNHYxNGgtOFY2OGg4djI4bDEwLTEwaDExbC0xNCAxNSAxNyAxOEg5NnoiLz48L3N2Zz4K)

All notable changes to this project will be documented in this file.

See [keep a changelog] for information about writing changes to this log.

## [Unreleased]

## [4.1.4] 2024-01-30

* Add comma to taxonomy term list

## [4.1.3] 2023-12-15

* Allow adding youthreview to latest reviews.

## [4.1.2] 2023-11-20

* Renamed flag actions.

## [4.1.1] 2023-11-20

* Updated new contenttype Ungeanmeldelse/youthreview

## [4.1.0] 2023-11-15

* Added new contenttype Ungeanmeldelse/youthreview

## [4.0.2] 2023-10-09

* Fixed "Add one more" for book ref. fields to the open platform.
* Fixed system action flag naming conventions, causing errors during setup.
* Updated drupal/xmlsitemap from ^1.4 -> ^1.5.
* Update config of sitemap scope, to only include content types: temaer, boglister, artiker.
* Fixed layout overflow styling issue.
* Fixed an issue with viewsreference interfering with infinitescroll.
* Added siteimprove
* Added new contenttype Ungeanmeldelse/youthreview

## [4.0.1] 2023-10-05

* Added pull request template
* Update change log format
* Fixed open platform widget - hook_field_widget_form_alter() is deprecated and removed in Drupal 10

## [4.0.0] 2023-09-25

* Upgrade contrib modules to be drupal-10 compatible
* Upgrade custom modules to be drupal-10 compatible
* Add GitHub actions to check code
* Add this changelog
* Add linting for markdown
* Add php code sniffer
* Add PHPStan code analyzing
* Disable several modules
* Disable videos in wysiwyg (Not D10 compatible yet)

[keep a changelog]: https://keepachangelog.com/en/1.1.0/
[unreleased]: https://github.com/itk-dev/event-database-imports/compare/4.0.2...HEAD
[4.0.2]: https://github.com/itk-dev/event-database-imports/compare/compare/4.0.1...4.0.2
[4.0.1]: https://github.com/itk-dev/event-database-imports/compare/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/itk-dev/event-database-imports/compare/compare/3.0.7...4.0.0
