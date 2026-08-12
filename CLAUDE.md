# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`atk14/api-data-fetcher` — a PHP client library (PHP >= 5.6, no namespacing on the main class) for calling ATK14-style RESTful APIs and generic JSON APIs. It wraps `atk14/url-fetcher` (`UrlFetcher`) to add: automatic `lang`/`format=json` URL segments, JSON decoding, a typed exception hierarchy, file-based response caching, and a Tracy debug-bar panel.

## Commands

Install dependencies:

    composer update --dev

Run the full test suite (from `test/`, using the `atk14/tester` runner):

    cd test && ../vendor/bin/run_unit_tests

Run a single test file (accepts the filename with or without `.php`):

    cd test && ../vendor/bin/run_unit_tests tc_api_data_fetcher

Lint a single file:

    php -l src/api_data_fetcher.php

There is no separate build step; the library is autoloaded via Composer's `classmap` on `src/`.

### Test environment caveat

Most tests are **not** hermetic — they make live HTTP calls against `https://www.atk14.net/api/` and `https://skelet.atk14.net/api/` (a running Atk14Skelet instance), so they require network access to those hosts to pass. `test/tc_proxy.php` additionally needs a local `privoxy` on `127.0.0.1:8118` and skips itself (rather than failing) if it's not running — see `.github/workflows/tests.yml` for how CI provisions it across the PHP 5.6–8.5 matrix. `test/tc_file_upload.php` needs a local PupiqSrv instance. If you can't reach these, expect network-dependent tests to fail/skip and judge changes by reasoning through the code rather than a green run.

## Architecture

### Core request flow

Everything funnels through `ApiDataFetcher::_doRequest()` (`src/api_data_fetcher.php`), called by the public `get`/`post`/`put`/`delete`/`postFile`/`postJson`/`postRawData` methods, which just set `$options["method"]` (and file/raw-body options) and delegate. `_doRequest()`:

1. Builds the URL as `base_url/{lang}/{action}/`, merging in `default_params` (`format=json` by default) and any explicit params.
2. Checks the file cache (`cache_storage`, an injected `CacheFileStorage`) when `options["cache"] > 0`; a cache hit short-circuits everything below.
3. Delegates the actual HTTP call to `__doHttpRequest()`, timed with `atk14/stop-watch`.
4. Decodes JSON, records the call in the static `ApiDataFetcher::$QueriesExecuted` log (used by `getStatistics()`/`ApiDataFetcherPanel`), and either returns the decoded data or throws.

`__doHttpRequest()` constructs a `UrlFetcher` (or `UrlFetcherViaCommand` if `communicate_via_command` is set — a path to an external binary used instead of a real socket, mainly for testing/sandboxed environments) with all instance-level networking options: `proxy`, `verify_peer`, `verify_peer_name`, `socket_timeout`, `read_timeout`, `ip_address`. Any option added to `UrlFetcher` that should be user-configurable from `ApiDataFetcher` needs the same three touch points: (a) a `protected` property + constructor default in `ApiDataFetcher::__construct()`, (b) copying `$options[...]` into `$this->...`, and (c) forwarding `$this->...` into the `new UrlFetcher(...)` call in `__doHttpRequest()`.

### Exception hierarchy (`src/api_data_fetcher/*.php`, namespace `ApiDataFetcher`)

All extend `ApiDataFetcher\Exception`:
- `HttpException` — non-2xx HTTP status (carries status code, raw content, headers); *not* thrown if the status is listed in the `acceptable_error_codes` request option (then `_doRequest()` returns `null` instead).
- `NetworkException` — connection-level failure (DNS, refused connection, timeout); recognizable because `status_code` is `null`.
- `InvalidContentException` — response body isn't valid JSON.
- `NoContentException extends InvalidContentException` — empty body when status isn't 204.

### Caching

`_writeCache`/`_readCache` in `ApiDataFetcher` persist a small envelope (`url`, `status_code`, `data`, `raw_response`, `errors`, `created`, `version`) via the injected `cache_storage`. Cache entries are invalidated if `ApiDataFetcher::VERSION` differs from what's stored, so bumping `VERSION` implicitly busts all caches. `return_cached_content_on_error` lets a request fall back to a stale-but-present cache entry instead of throwing.

### Testing conventions (`test/`)

Uses the `atk14/tester` framework: one `Tc<Name>` class per `tc_*.php` file, extending `TcBase`, with `test*` methods as individual test cases. `test/initialize.php` is the shared bootstrap (loaded automatically by the runner) — it requires the library source directly (not via autoload) plus test helpers: `TestingApiDataFetcher` (exposes protected methods like `_hidePasswordInMessage`/`_serializeErrorMessages` for direct testing) and `TestingLogger` (a fake logger asserting on captured `messages`). Tests exercise real endpoints on the ATK14 skeleton API (e.g. `login_availabilities/detail`, `http_requests/detail`, `echoes/detail`) rather than mocking HTTP.
