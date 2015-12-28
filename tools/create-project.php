<?php
ob_implicit_flush(true);
define('FRAMEWORK_ROOT', substr(__FILE__, 0, -24).'framework'.DIRECTORY_SEPARATOR);
$project_types = array('web');
$argmap = array(
    'name' => array('short' => 'n', 'required' => true),
    'path' => array('short' => 'p', 'required' => true),
    'type' => array('short' => 't', 'default' => 'web', 'required' => true),
    'charset' => array('short' => 'c', 'default' => 'utf-8', 'required' => true),
    'deploydir' => array(
        'short' => 'd',
        'default' => array('web' => './www'),
        'required' => true
    ),
);
$input_args = get_args();
$args = check_args($input_args, $argmap);
if(!is_dir($args['path']))
    throw new Exception(sprintf('%s isn\'t a dir', $args['path']));
$project_dir = trim($args['path'], '/\\') . DIRECTORY_SEPARATOR . $args['name'] . DIRECTORY_SEPARATOR;
if(is_dir($project_dir))
    throw new Exception(sprintf('%s is already existed', $project_dir));
if(!mkdir($project_dir))
    throw new Exception(sprintf('can\'t create dir %s', $project_dir));
if(!in_array($args['type'], $project_types))
    throw new Exception(sprintf('incognizant project type %s', $args['type']));
$deploydir = is_array($args['deploydir']) ? $args['deploydir'][$args['type']] : $args['deploydir'];
$deploydir = 0 === strpos($deploydir, './') ? ($project_dir . substr($deploydir, 2)) : $deploydir;
$deploydir = trim($deploydir, '/\\') . DIRECTORY_SEPARATOR;
if(is_dir($deploydir))
    throw new Exception(sprintf('%s is already existed', $deploydir));
if(!mkdir($deploydir))
    throw new Exception(sprintf('can\'t create deploy dir %s', $deploydir));

copy_dir("./template/{$args['type']}/project", $project_dir);

$debug_tpl = file_get_contents("./template/{$args['type']}/debug.php");
$debug_tpl = str_replace(array('#FRAMEWORK_ROOT#', '#PROJECT_ROOT#'), array(str_replace('\\', '/', FRAMEWORK_ROOT), str_replace('\\', '/', $project_dir)), $debug_tpl);
file_put_contents($deploydir . 'debug.php', $debug_tpl);
$app_tpl = file_get_contents("./template/{$args['type']}/app.xml");
$app_tpl = str_replace(array('#CHARSET#'), array($args['charset']), $app_tpl);
file_put_contents(str_replace('\\', '/', $project_dir) . 'app.xml', $app_tpl);
    
function check_args($input_args, $argmap) {
    $args = array();
    foreach($argmap as $key => $val) {
        if(isset($input_args[$key]))
            $args[$key] = $input_args[$key];
        elseif(isset($input_args[$val['short']]))
            $args[$key] = $input_args[$val['short']];
        
        if(empty($args[$key]) && isset($val['default']))
            $args[$key] = $val['default'];
        
        if(empty($args[$key]) && $val['required'])
            throw new Exception(isset($val['msg']) ? $val['msg'] : sprintf('argument %s is required!', $key));
    }
    return $args;
}

function get_args() {
    $args = array();
    
    if($_SERVER['argc'] > 1) {
        for($i = 1; $i < $_SERVER['argc']; $i++) {
            $arg = $_SERVER['argv'][$i];
            if(0 === strpos($arg, '--')) { // --key=val
                $arr = explode('=', substr($arg, 2));
                $args[$arr[0]] = isset($arr[1]) ? $arr[1] : null;
            } elseif('-' == $arg[0]) { // -key val
                $args[$arg[1]] = isset($_SERVER['argv'][$i + 1]) ? $_SERVER['argv'][++$i] : null;
            } else { // subcmd
                $args['__SUBCMD__'] = $arg;
            }
        }
    }
    
    return $args;
}

function copy_dir($src,$dst) {
    $dir = opendir($src);
    if(!is_dir($dst)) mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (is_dir($src . '/' . $file)) {
                copy_dir($src . '/' . $file,$dst . '/' . $file);
            } else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function delete_dir($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delete_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}