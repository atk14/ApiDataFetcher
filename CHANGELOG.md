Change Log
==========

All notable changes to this project will be documented in this file.

[1.10.1] - 2022-05-13
---------------------

* d05d170 - PHP 8.1 compatibility


[1.10] - 2021-12-03
-------------------

* 8f5e289 - Added possibility to communicate via command instead of network socket (e.g. scripts/simulate_http_request in an ATK14 project)

[1.9.4] - 2021-11-11
--------------------

- 2b79bc7 - Version is being written into cache files
- ec22211 - Added method ApiDataFetcher::getStatusMessage()

[1.9.3] - 2021-09-07
--------------------

- HTML Markup in Tracy Panel fixed

[1.9.2] - 2021-07-13
--------------------

- Better error message on an unknown hostname

[1.9.1] - 2021-02-07
--------------------

- Dependency updated

[1.9] - 2021-02-05
------------------

- A file to be posted to a URL is passed to UrlFetcher as StringBuffer and not as a string
- Using fixed version of UrlFetcher (>=1.5)

[1.8.1] - 2019-10-25
--------------------

- Using fixed version of UrlFetcher (>=1.4.1)

[1.8] - 2019-10-22
------------------

- Added option "return_raw_content" (by default false) to ApiDataFetcher::getContent()

[1.7] - 2019-01-10
------------------

- Added option return_cached_content_on_error

[1.6] - 2018-11-09
------------------

- Added method ApiDataFetcher::getApiUrl()

[1.5.1] - 2018-06-08
--------------------

### Fixed
- Empty answer with HTTP status 204 is handled properly

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
