Testing
=======

At the moment it's not quite easy to test ApiDataFetcher.

You need to have https://github.com/atk14/Atk14Skelet properly installed on your local system.

The ApiDataFetcher needs to be installed into the Atk14Skelet installation.

    cd /path/to/atk14skelet/
    composer require atk14/api-data-fetcher dev-master
    cd vendor/atk14/api-data-fetcher/test/
    run_unit_tests
