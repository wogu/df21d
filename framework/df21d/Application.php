<?php
namespace df21d;
use df21d\chain\Manager;

class Application
{
    private static $_env;
    private static $_manager;
    
    public static function init($config) {
        self::$_env['config'] = $config;
        self::$_manager = new Manager(self::$_env);
        self::load($config);
    }

    public static function bootstrap() {
        self::$_manager->invoke();
    }
    
    public static function load($config) {
        foreach($config as $k => $v) {
            switch($k) {
            case 'lang':
            case 'setting':
                foreach($v as $name => $value)
                    self::$_env[$k][$name] = $value;
                break;
            case 'const':
                foreach($v as $name => $value)
                    if(!defined($name))
                        define($name, $value);
                break;
            case 'chain':
                foreach($v as $name => $value)
                    self::addChain($name, $value);
                break;
            }
        }
    }
    
    public static function addChain($name, $config) {
        $scope = constant($config['scope']);
        $class = $config['class'];
        $chain = new $class($name, $scope, $config);
        return self::$_manager->add($chain);
    }
    
    public static function getManager() {
        return self::$_manager;
    }
}