<?php

defined('TYPO3') or die();

use Quicko\Clubmanager\Utils\PluginRegisterFacade;

call_user_func(function () {
  PluginRegisterFacade::registerAllPlugins();
});
