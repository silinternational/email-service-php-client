# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [2.2.0] - 2019-05-02
- Add delayed-send options: `delay_seconds` and `send_after`

## [2.0.2] - 2017-12-07
### Fixed 
- Stop trying to interpret site-status response content as JSON. This should
  resolve the `json_decode` syntax error messages.

## [2.0.1] - 2017-08-23
### Changed
- Change error codes to be unique (not duplicates of those in `IdBrokerClient`).

## [2.0.0] - 2017-08-11
### Changed
- Only throw `EmailServiceClientException`s (for easier excluding from Yii log targets).

## [1.0.0] - 2017-07-24

[Unreleased]: https://github.com/silinternational/email-service-php-client/compare/2.2.0...develop
[2.2.0]: https://github.com/silinternational/email-service-php-client/compare/2.0.1...2.2.0
[2.0.2]: https://github.com/silinternational/email-service-php-client/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/silinternational/email-service-php-client/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/silinternational/email-service-php-client/compare/1.0.0...2.0.0
