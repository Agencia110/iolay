<?php

namespace Iolay\ModuleManager;

use \Zend\Config\Config;
use \Iolay\Mvc\Application;

class ModuleManager
{
    protected $application;

    protected $isLoaded = false;

    protected $modulesPath;

    protected $modules = array();

    protected $modulesInstances = array();

    public function __construct($modules, Application $application)
    {
        $this->modules = $modules;
        $this->application = $application;
        $this->modulesPath = $this->application->serviceManager->get('Config')->modules_path;
    }

    public function loadModules()
    {
        if(true === $this->isLoaded)
        {
            return $this;
        }

        foreach($this->modules as $module)
        {
            $this->loadModuleByName($module);
        }

        $this->isLoaded = true;

        return $this;
    }

    protected function loadModuleByName($module)
    {
        if(isset($this->modulesInstances[$module]))
        {
            return;
        }

        $modulePath = "{$this->modulesPath}/{$module}"; 
        if(!is_dir($modulePath))
        {
            throw new \Exception("Module {$module} not found");
        }

        $moduleFile = "{$modulePath}/Module.php";
        if(!file_exists($moduleFile))
        {
            throw new Exception("Module file {$moduleFile} not fount");
        }

        require $moduleFile;

        $moduleClass = "{$module}\\Module";
        if(!class_exists($moduleClass))
        {
            throw new Exception("Module class {$moduleClass} not found");
        }

        $this->modulesInstances[$module] = new $moduleClass;

        return $this->loadModuleConfig($module);
    }

    protected function loadModuleConfig($module)
    {
        if(!isset($this->modulesInstances[$module]))
        {
            throw new Exception("Module {$module} not loaded");
        }

        $moduleInstance = $this->modulesInstances[$module];
        $config = $this->application->serviceManager->get('Config');

        $moduleConfig = new Config($moduleInstance->getConfig());
        $config->merge($moduleConfig);

        $autoloaderConfig = new Config($moduleInstance->getAutoloaderConfig());
        $config->merge($autoloaderConfig);
    }
}