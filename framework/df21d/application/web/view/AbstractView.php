<?php
namespace df21d\application\web\view;

abstract class AbstractView
{
    protected $_env;
    protected $_config;
    protected $_valueStack;
    
    public function setEnv($env) {
        $this->_env = $env;
        return $this;
    }
    
    public function setConfig($config) {
        $this->_config = $config;
        return $this;
    }
    
    public function setValueStack(&$valueStack) {
        $this->_valueStack = &$valueStack;
        return $this;
    }
    
    public function __set($name, $value) {
        $this->_valueStack[$name] = $value;
    }
    
    public function __get($name) {
        return isset($this->_valueStack[$name]) ? $this->_valueStack[$name] : null;
    }
    
    public abstract function invoke();
}