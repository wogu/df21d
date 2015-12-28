<?php
namespace df21d\application\web\chain;
use df21d\application\AbstractChain;

class View extends AbstractChain
{
    public function invoke() {
        $adapterName = isset($this->__result['adapter']) ? $this->__result['adapter'] : $this->_env['config']['setting']['default-view-adapter'];
        $type = isset($this->__result['type']) ? $this->__result['type'] : $this->_env['config']['setting']['default-content-type'];
        $config = $this->_env['config']['view-adapter'][$adapterName];
        $adapter = new $config['class']();
        $adapter->setEnv($this->_env)
            ->setConfig($config)
            ->setValueStack($this->_valueStack)
            ->invoke();
        return $this->_manager->invoke();
    }
}