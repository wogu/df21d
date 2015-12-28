<?php
namespace df21d\application;

class ActionSupport
{
    const SUCCESS = 'success';
    const FORWARD = 'forward';
    const InternalError = 'internal-error';
    
    protected $_env;
    protected $_valueStack;
    
    public function setEnv($env) {
        $this->_env = $env;
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
}