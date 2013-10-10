<?php

namespace Iolay\Mvc;

use \Zend\ServiceManager\ServiceManager;
use \Zend\Config\Config;
use \Zend\EventManager\EventManager;

use \Iolay\ModuleManager\ModuleManager;
use \Iolay\Loader\StandardAutoloader;

class Application
{
    public $serviceManager;

    public $eventManager;

    public function bootstrap(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->eventManager = new EventManager;

        $this->config = $this->serviceManager->get('Config');

        return $this->serviceManager->get('Application');
    }

    public function run()
    {
        $this->moduleManager = new ModuleManager($this->config->modules, $this);

        $this->moduleManager->loadModules();

        $this->autoloader = new StandardAutoloader;

        if(!is_null($this->serviceManager->get('Config')->namespaces))
        {
            $this->autoloader->registerNamespaces($this->config->namespaces);
        }

        $controller = new \User\Controller\IndexController;
        $controller->indexAction();
    }

    public static function init(Array $config)
    {
        $serviceManager = new ServiceManager();

        $config = new Config($config, true);
        $serviceManager->setService('Config', $config);

        $applicaton = new self;
        $serviceManager->setService('Application', $applicaton);

        return $serviceManager->get('Application')->bootstrap($serviceManager);
    }
}