Change Log
==========

All notable changes to this project will be documented in this file.

[1.5] - 2018-04-03
------------------

### Added
- Added methods ApiDataFetcher::put() and ApiDataFetcher::delete() in order to performing PUT and DELETE requests
- Added method ApiDataFetcher::postRawData()
- Method for easy JSON posting added: ApiDataFetcher::postJson()
- Added a new options to the constructor: default_params
- Better statistics

[1.4] - 2018-03-23
------------------

### Added
- Perfect User-Agent header added
- Adedd ApiDataFetcherPanel for integration into Tracy

[1.3.1] - 2018-03-04
--------------------

### Added
- ApiDataFetcher::VERSION

### Fixed
- Added proper User-Agent header

[1.3] - 2018-02-16
------------------

### Added
- Added methods ApiDataFetcher::getMethod() and  ApiDataFetcher::getDuration()

### Fixed
- ApiDataFetcher can operate without a logger

[1.2.1] - 2018-02-14
--------------------

### Fixed
- Test fixed

[1.2] - 2018-02-14
------------------

### Added
- Presence of the lang in URL can be suppressed in constructor

[1.1] - 2018-02-01
------------------

### Added
- lang can be changed in a post() or get() call

[1.0.2] - 2017-11-30
--------------------

### Fixed
- error_log() replaced with trigger_error()

[1.0.1] - 2017-04-24
--------------------

### Fixed
- Fix when response contains only string with zeros

[1.0] - 2016-09-15
------------------

- First version of the ApiDataFetcher
