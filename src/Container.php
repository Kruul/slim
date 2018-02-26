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

version 3.0
 * 2017-01-26 Modified from Container.php to Class
 * 2018-02-21 Modified and optimized AppFactory, added view/layout, added Handlers.

############################################
##             for sample                 ##
############################################
require __DIR__ . '/../vendor/autoload.php';
chdir(dirname(__DIR__));
$app=new \Kruul\Slim\Container();
$app->run();

*/

use Exception;

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

    $userSettings=array();
    if ( isset($config['settings']) && (is_array($config['settings'])) )  {
      $userSettings=$config['settings'];
      unset($config['settings']);
    }

    $this->container = new \Slim\Container(array('settings'=>$userSettings));

    $this->container['AppFactory']=function($c) use ($config) {
      if (isset($config['factories'])){
        $c['factories']=$config['factories'];
        unset($config['factories']);
      }

      if (isset($config['services'])){
          $c['services']=$config['services'];
          unset($config['services']);
      }

      $c['view']=function ($c) use($config){
        $view=new \Kruul\Slim\PhpRenderer($c);
        $layoutpath='';
        if (isset($config['view_manager']['template_path'])) {
           $layoutpath=$config['view_manager']['template_path'];
        }

        if (isset($config['view_manager']['layout/layout'])) {
          $view->setLayout($layoutpath.$config['view_manager']['layout/layout']);
          unset($config['view_manager']['layout/layout']);
        }
        return $view;
      };

      if (isset($config['notFoundHandler'])){
          $c['notFoundHandler']=$config['notFoundHandler'];
          unset($config['notFoundHandler']);
      } else {
          $c['notFoundHandler'] = function ($c) {
              return new NotFoundHandler($c, function ($request, $response) use ($c) {
                  return $c['response']->withStatus(404);
                  });
          };
      }



      if (isset($config['errorHandler'])) {
          $c['errorHandler'] = $config['errorHandler'];
          unset($config['errorHandler']);
      } else {
          $c['errorHandler'] = function ($c) {
            return new phpErrorHandler($c);
          };
      }

      if (isset($config['phpErrorHandler'])) {
          $c['phpErrorHandler'] = $config['phpErrorHandler'];
          unset($config['phpErrorHandler']);
      } else   {
          $c['phpErrorHandler'] = function ($c) {
          return new phpErrorHandler($c);
//          return $c['response']
//	        ->withStatus(500)
//                ->withHeader('Content-Type', 'text/html')
//                ->write($exception->getMessage().' in file '.basename($exception->getFile()).' in line '.$exception->getLine());
//		  };
          };
      }



      if (isset($config['notAllowedHandler'])) {
          $c['notAllowedHandler'] = $config['notAllowedHandler'];
      } else {
          $c['notAllowedHandler'] = function ($c){
              return new NotAllowedHandler($c);
          };
      }

      $app=new \Slim\App($c);
      if (isset($config['routes'])){
        foreach ($config['routes'] as $name=>$rule){
          if (is_array($rule['method'])) {
              foreach ($rule['route'] as $k=>$v){ $routename.=$v.' and ';}
              $c['ERRORMESSAGE']='Route name is duplicate: '.rtrim($routename,'and ');
              continue;
          }

          $method = preg_split('[,]',strtolower($rule['method']));
          $route=$app->map($method,$rule['route'],$rule['action'].'__');
          $route->setName($name);
          if (isset($rule['middleware'])) $route->add($rule['middleware']);
        };
        //unset($config['routes']);
      }

      if (isset($config['middleware'])) {
        foreach ($config['middleware'] as $name=>$middleware ){
          $app->add($middleware);
        };
        unset($config['middleware']);
      }
      $c['config'] = $config;
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
