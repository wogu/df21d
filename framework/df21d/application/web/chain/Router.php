<?php
namespace df21d\application\web\chain;
use df21d\application\AbstractChain;

class Router extends AbstractChain
{
    public function invoke() {
        if(!isset($this->_valueStack['__router'])) {
            $settings = $this->_env['setting'];
            $this->forward(
                empty($_GET['a']) ? $settings['default-action'] : $_GET['a'],
                empty($_GET['c']) ? $settings['default-controller'] : $_GET['c'],
                empty($_GET['m']) ? $settings['default-module'] : $_GET['m']
            );
            
            return $this->dispatch();
        }

        return $this->_manager->invoke();
    }
}