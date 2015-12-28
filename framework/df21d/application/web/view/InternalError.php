<?php
namespace df21d\application\web\view;

class InternalError extends AbstractView
{
    public function invoke() {
        print_r($this->__error);
    }
}