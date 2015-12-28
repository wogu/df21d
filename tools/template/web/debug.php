<?php
define('FRAMEWORK_ROOT', '#FRAMEWORK_ROOT#');
define('PROJECT_ROOT', '#PROJECT_ROOT#');

function __autoload($class) {
    $class = str_replace('\\', '/', $class);
    if(file_exists(PROJECT_ROOT . 'src/' . $class . '.php'))
        include PROJECT_ROOT . 'src/' . $class . '.php';
    elseif(file_exists(FRAMEWORK_ROOT . '/' . $class . '.php'))
        include FRAMEWORK_ROOT . '/' . $class . '.php';
}

function load_xml_as_string($xml) {
    $xmlstr = file_get_contents($xml);
    do {
        $newxml = preg_replace_callback(
            '/<#include file="(.+)"\/>/',
            function($matches) {
                $path = PROJECT_ROOT . ltrim($matches[1], '/\\');
                if(!file_exists($path))
                    throw new Exception($path . ' not exist!');
                return file_get_contents($path);
            },
            $xmlstr
        );
        if($xmlstr == $newxml) return $newxml;
        $xmlstr = $newxml;
    } while(1);
}

function load_config($xmlobj) {
    $config = array();
    foreach($xmlobj as $k => $v) {
        switch($k) {
        case 'setting':
        case 'lang':
        case 'const':
            $config[$k][(string) $v['name']] = (string) $v['value'];
            break;
        case 'chain':
            $config['chain'][(string) $v['name']] = array(
                'scope' => (string) $v['scope'],
                'class' => (string) $v['class'],
            );
            break;
        default:
            $config[$k][(string) $v['name']] = load_config($v);
            foreach($v->attributes() as $name => $value) {
                $config[$k][(string) $v['name']]['@'.$name] = (string) $value;
            }
            break;
        }
    }
    return $config;
}

$xml = load_xml_as_string(PROJECT_ROOT. 'app.xml');
$xmlobj = simplexml_load_string($xml);
if(!$xmlobj)
    throw new Exception('error xml format!');

$config = load_config($xmlobj);
df21d\application::init($config);
df21d\application::bootstrap();