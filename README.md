DpiDataFetcher
==============

A client library for communication with a ATK14 restful API

Basic usage
-----------

    
    $adf = Tracy\Debugger::getBar();
    $bar->addPanel(new DbMolePanel($dbmole));

Usage in an ATK14 application (built upon Atk14Skelet)
------------------------------------------------------

Use the Composer to install the panel.

    cd path/to/your/atk14/project/
    composer.phar require atk14/dbmole-panel dev-master

Load autoloader from the Composer and enable the Tracy Debugger.

    // file: lib/load.php
    require(__DIR__."/../vendor/autoload.php");

    if(
      !TEST &&
      !$HTTP_REQUEST->xhr() &&
      php_sapi_name()!="cli" // we do not want Tracy in cli
    ){
      Tracy\Debugger::enable(PRODUCTION, __DIR__ . '/../log/');
    }

Enable collecting of the DbMole statistics in DEVELOPMENT.

    // file: config/settings.php
    define("DBMOLE_COLLECT_STATICTICS",DEVELOPMENT);

Add the DbMole panel to the Tracy in \_application_after_filter().

    // file: app/controllers/application_base.php
    function _application_after_filter(){
      if(DBMOLE_COLLECT_STATICTICS){
        $bar = Tracy\Debugger::getBar();
        $bar->addPanel(new DbMolePanel($this->dbmole));
      }
    }

Licence
-------

DbMolePanel is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
