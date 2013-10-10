<?php

namespace Iolay\Loader;

class StandardAutoloader
{
    protected $namespaces = array();

    public function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function registerNamespace($namespace, $directory)
    {
        $this->namespaces[$namespace] = $directory;

        return $this;
    }

    public function registerNamespaces($namespaces)
    {
        foreach($namespaces as $namespace => $directory)
        {
            $this->registerNamespace($namespace, $directory);
        }

        return $this;
    }

    public function autoload($class)
    {
        $matches = array();
        $class = ltrim($class,'\\');
        preg_match('/(?P<namespace>.+\\\)?(?P<class>[^\\\]+$)/', $class, $matches);

        $class     = (isset($matches['class'])) ? $matches['class'] : '';
        $classNamespace = (isset($matches['namespace'])) ? $matches['namespace'] : '';
        $classDirectory = '';

        foreach($this->namespaces as $namespace => $directory)
        {
            if(preg_match('/^'.preg_quote($namespace).'/', $classNamespace))
            {
                $classNamespace = preg_replace('/^'.preg_quote($namespace).'/', $directory, $classNamespace);
                $classDirectory = str_replace('\\', '/', $classNamespace);
            }
        }

        if($classDirectory == '')
        {
            return false;
        }

        if(!preg_match('/(\/|\\\)$/', $classDirectory))
        {
            $classDirectory .= '/';
        }

        $classFile = $classDirectory . $class . '.php';

        if(file_exists($classFile))
        {
            require_once $classFile;
        }
        else
        {
            return false;
        }
    }
}