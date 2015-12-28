<?php
namespace df21d\application;

class ActionChain extends AbstractChain
{
    protected $_action;
    protected $_method;
    protected $_config;
    
    public function setAction($action) {
        $this->_action = $action;
        if($action instanceof ActionSupport) {
            $action->setEnv($this->_env)
                ->setValueStack($this->_valueStack);
        }
        return $this;
    }
    
    public function setMethod($method) {
        $this->_method = $method;
        return $this;
    }
    
    public function setConfig($config) {
        $this->_config = $config;
        return $this;
    }
    
    public function invoke() {
        $result = call_user_func(array($this->_action, $this->_method));
        switch($result) {
        case ActionSupport::FORWARD:
            $this->forward(
                $this->__forwardAction,
                $this->__forwardController,
                $this->__forwardModule
            );
            return $this->dispatch();
        default:
            if(null === $result)
                return $this->error('action-return-no-result', $this->_name);
            if(!isset($this->_config['result'][$result]))
                return $this->error('action-has-no-such-result', $this->_name, $result);
                
            $this->__result = $this->_config['result'][$result];
            return $this->_manager->invoke();
        }
    }
}