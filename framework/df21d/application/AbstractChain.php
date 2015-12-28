<?php
namespace df21d\application;
use df21d\Application;

abstract class AbstractChain extends \df21d\chain\AbstractChain
{
    public function forward($action, $controller = null, $module = null) {
        if(!isset($this->_valueStack['__router'])) {
            $this->__router = array(
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
                'scope' => PRE_MODULE
            );
        } else {
            if($action != $this->__router['action']) {
                $this->_valueStack['__router']['action'] = $action;
                $this->_valueStack['__router']['scope'] = PRE_ACTION;
                $this->_manager->removeScope(ACTION);
                $this->_manager->removeScope(PRE_ACTION);
                $this->_manager->removeScope(AFTER_ACTION);
            }

            if($controller && $controller != $this->__router['controller']) {
                $this->_valueStack['__router']['controller'] = $controller;
                $this->_valueStack['__router']['scope'] = PRE_CONTROLLER;
                $this->_manager->removeScope(PRE_CONTROLLER);
                $this->_manager->removeScope(AFTER_CONTROLLER);
            }

            if($module && $module != $this->__router['module']) {
                $this->_valueStack['__router']['module'] = $module;
                $this->_valueStack['__router']['scope'] = PRE_MODULE;
                $this->_manager->removeScope(PRE_MODULE);
                $this->_manager->removeScope(AFTER_MODULE);
            }
        }
    }
    
    public function dispatch() {
        $config = $this->_env['config'];
        if(!isset($config['module'][$this->__router['module']]))
            return $this->error('can-not-load-module', $this->__router['module']);
        $config = $config['module'][$this->__router['module']];
        if($this->__router['scope'] == PRE_MODULE)
            Application::load($config);
            
        if(!isset($config['controller'][$this->__router['controller']]))
            return $this->error('can-not-load-controller', $this->__router['controller']);
        $config = $config['controller'][$this->__router['controller']];
        if($this->__router['scope'] <= PRE_CONTROLLER)
            Application::load($config);
            
        if(!isset($config['action'][$this->__router['action']]))
            return $this->error('can-not-load-action', $this->__router['action']);
        $config = $config['action'][$this->__router['action']];
        Application::load($config);
        $class = $config['class'];
        $method = isset($config['method']) ? $config['method'] : 'execute';
        $action = new $class();
        $chain = new ActionChain($this->__router['action'], ACTION);
        $this->_manager->add($chain);
        $chain->setAction($action)
            ->setMethod($method)
            ->setConfig($config);

        return $this->_manager->gotoScope($this->__router['scope']);
    }
    
    public function error() {
        $this->__result = $this->_env['config']['result'][ActionSupport::InternalError];
        $args = func_get_args();
        $args[0] = $this->_env['config']['lang'][$args[0]];
        $this->__error = call_user_func_array('sprintf', $args);
        $this->__backtrace = debug_backtrace(false);
        
        return $this->_manager->invoke();
    }
}