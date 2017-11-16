<?php
namespace Kruul\Slim;
/*
Name:   Container.php
Author: Shvager Alexander
Email:  Alex.Shvager@gmail.com
Slim Framework (http://slimframework.com)

Setting for Slim Framework 3 from file "config/config.php"
  Factory
  Services
  Middleware
  Route

version 2.0
 * 2017-01-26 Modified from Container.php to Class

############################################
##             for sample                 ##
############################################
require __DIR__ . '/../vendor/autoload.php';
chdir(dirname(__DIR__));
$app=new \Kruul\Slim\Container();
$app->run();

*/

class Container {
  private $container;

  public function __construct(){

  }

  public function init(){
    $config=array();
    foreach (\glob('config/autoload/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
      $config = array_merge_recursive($config, include $file);
    }

    if (isset($config['modules'])){
      foreach ($config['modules'] as $module) {
        foreach (\glob('./module/'.$module.'/config/{{,*.}global,{,*.}local}.php', GLOB_BRACE) as $file) {
          $config = array_merge_recursive($config, include $file);
        }
        $config['template_path_stack'][$module]='.'.DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR .'View';
      }
    }

    if (isset($config['settings'])) {
      $userSettings=$config['settings'];
      unset($config['settings']);
    } else $userSettings=array();
//print_r($userSettings);exit;

    $this->container = new \Slim\Container(array('settings'=>$userSettings));
    $this->container['config'] = $config;
    $this->container['AppFactory']=function($c) {
      if (isset($c['config']['factories'])){
        foreach ($c['config']['factories'] as $name=>$callable){
            if ($callable instanceof \Closure) {
              if (isset($c[$name])) continue;
              $c[$name]=$c->factory($callable);
          } else $c[$name]=new $callable();
        }
      }
      if (isset($c['config']['services'])){
        foreach ($c['config']['services'] as $name=>$callable){
          if ($callable instanceof \Closure) {
              if (isset($c[$name])) continue;
              $c[$name]=$callable;
          }
        }
      }
      $c['view']=function ($c){
        return new \Kruul\Slim\PhpRenderer($c);
      };

      $c['phpErrorHandler'] = function ($c) {
	return function ($request, $response, $exception) use ($c) {
          return $c['response']
	        ->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write($exception->getMessage().' in file '.basename($exception->getFile()).' in line '.$exception->getLine());
		  };
	  };

      $c['notFoundHandler'] = function ($c) {
        return function ($request, $response) use ($c) {
          return $c['response']
              ->withStatus(404)
              ->withHeader('Content-Type', 'text/html')
              ->write('Page not found');
         };
      };

      $app=new \Slim\App($c);
      foreach ($c['config']['routes'] as $name=>$rule){
        $method = preg_split('[,]',strtolower($rule['method']));
        $route=$app->map($method,$rule['route'],$rule['action'].'__');
        $route->setName($name);
        if (isset($rule['middleware'])) $route->add($rule['middleware']);
      };

      if (isset($c['config']['middleware']))
        foreach ($c['config']['middleware'] as $name=>$middleware ){
          $app->add($middleware);
        };

      return $app;
    }; //AppFactory

    return $this;
  }

  public function run(){
    if (!$this->container) $this->init();
    $app = $this->container->get('AppFactory');
    $app->run();
  }

}
