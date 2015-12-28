<?php
namespace df21d\chain;

class Manager
{
    const NONE = 0;
    const STATUS_OK = 0;
    const STATUS_FAILED = 1;
    
    protected $_chains;
    protected $_chainMap;
    protected $_currChain;
    protected $_preChain;
    protected $_env;
    
    public function __construct(&$env) {
        $this->_env = &$env;
    }

    public function add($chain) {
        $scope = $chain->getScope();
        $name = $chain->getName();
        if($this->find($name)) return false;
        
        $chain->reset()
            ->setManager($this)
            ->setEnv($this->_env)
            ->setValueStack($this->_env['valueStack']);
            
        if(null === $this->_chains) {
            $this->_chains = $chain;
            $this->_currChain = $this->_chains;
            $this->_chainMap[$scope][$name] = $chain;
            return true;
        }
        
        for($i = $scope; $i >= 0; $i--) {
            if(!empty($this->_chainMap[$i])) {
                $prechain = end($this->_chainMap[$i]);
                $afterchain = $prechain->getNext();
                $prechain->setNext($chain);
                $chain->setPre($prechain);
                if($afterchain)
                    $chain->setNext($afterchain);
                $this->_chainMap[$scope][$name] = $chain;
                if($this->_preChain)
                    $this->_currChain = $this->_preChain->getNext();
                return true;
            }
        }
        
        $chain->setNext($this->_chains);
        $this->_chains = $chain;
        $this->_chainMap[$scope][$name] = $chain;
        if($this->_preChain)
            $this->_currChain = $this->_preChain->getNext();
        return true;
    }
    
    public function remove($name) {
        $chain = $this->find($name);
        if($chain) {
            $prechain = $chain->getPre();
            $afterchain = $chain->getNext();
            if($prechain)
                $prechain->setNext($afterchain);
            if($afterchain)
                $afterchain->setPre($prechain);
            if(!$prechain)
                $this->_chains = $afterchain;
            $scope = $chain->getScope();
            unset($this->_chainMap[$scope][$name]);
            // if current chain is removed then reset current chain
            if($this->_currChain->getName() == $name) {
                $this->_currChain = $afterchain;
            }
            return $chain;
        }
        
        return false;
    }

    public function removeScope($scope) {
        if(empty($this->_chainMap[$scope]))
            return false;

        reset($this->_chainMap[$scope]);
        $startChain = current($this->_chainMap[$scope]);
        $endChain = end($this->_chainMap[$scope]);
        $prechain = $startChain->getPre();
        $afterchain = $endChain->getNext();
        if($prechain)
            $prechain->setNext($afterchain);
        if($afterchain)
            $afterchain->setPre($prechain);
        if(!$prechain)
            $this->_chains = $afterchain;
        $removedChains = $this->_chainMap[$scope];
        $this->_chainMap[$scope] = null;
        unset($this->_chainMap[$scope]);
        // if current chain is removed then reset current chain
        if($this->find($this->_currChain->getName())) {
            $this->_currChain = $afterchain;
        }
        return $removedChains;
    }

    public function invoke() {
        if(null === $this->_chains)
            return self::STATUS_FAILED;
        
        if(self::NONE === $this->_currChain)
            return self::STATUS_OK;

        $this->_preChain = $this->_currChain;
        $this->_currChain = $this->_currChain->getNext();
        return $this->_preChain->invoke();
    }
    
    public function forward($name) {
        $chain = $this->find($name);
        if(!$chain) return self::STATUS_FAILED;
        $this->_currChain = $chain;
        return $this->invoke();
    }
    
    public function gotoScope($scope) {
        for($i = $scope; $i >= 0; $i--) {
            if(!empty($this->_chainMap[$i])) {
                reset($this->_chainMap[$i]);
                $this->_currChain = current($this->_chainMap[$i]);
                return $this->invoke();
            }
        }

        return false;
    }
    
    public function find($name) {
        $chain = $this->_chains;
        if($chain) {
            while($chain) {
                if($chain->getName() == $name)
                    return $chain;
                $chain = $chain->getNext();
            }
        }
        
        return false;
    }
    
    public function toString() {
        $chain = $this->_chains;
        while($chain) {
            $names[] = $chain->getName();
            $chain = $chain->getNext();
        }
        
        if($names)
            return implode('->', $names);
    }
    
    public function getEnv() {
        return $this->_env;
    }
    
    public function &getValueStack() {
        return $this->_env['valueStack'];
    }
}