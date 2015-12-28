<?php
namespace df21d\chain;

abstract class AbstractChain
{
    const NONE = 0;
    
    protected $_name;
    protected $_scope;
    protected $_config;
    protected $_next = self::NONE;
    protected $_pre = self::NONE;
    protected $_env;
    protected $_valueStack;
    protected $_manager;
    
    public function __construct($name = null, $scope = null, $config = null) {
        if($name) $this->_name = $name;
        if($scope) $this->_scope = $scope;
        if($config) $this->_config = $config;
    }
    
    public function setName($name) {
        $this->_name = $name;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function setScope($scope) {
        $this->_scope = $scope;
    }
    
    public function getScope() {
        return $this->_scope;
    }
    
    public function setConfig($config) {
        $this->_config = $config;
    }
    
    public function setNext($chain) {
        $this->_next = $chain;
    }
    
    public function getNext() {
        return $this->_next;
    }
    
    public function setPre($chain) {
        $this->_pre = $chain;
    }
    
    public function getPre() {
        return $this->_pre;
    }
    
    public function reset() {
        $this->_next = self::NONE;
        $this->_pre = self::NONE;
        return $this;
    }
    
    public function setManager($manager) {
        $this->_manager = $manager;
        return $this;
    }
    
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
    
    public abstract function invoke();
}