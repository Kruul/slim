<?php
namespace Kruul\Slim;
use Exception;
use Kruul\Slim\ContainerInterface;
use Kruul\Slim\InvokableInterface;

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
        foreach ($config['factories'] as $name=>$callable){
            if ($callable instanceof \Closure) {
              if (isset($c[$name])) continue;
              $c[$name]=$c->factory($callable);
          } else $c[$name]=//$c->factory(
//                   function () use ($callable){
//                        return new $callable;
//                   }
                    function () use ($callable){
                        $obj= new $callable;
                        if ($obj instanceof ContainerInterface ){
                            $obj->setContainer($this->container);
                        }
                        if ($obj instanceof InvokableInterface){
                            return $obj();
                        } else return $obj;
                    }
                 ;
        }
        unset($config['factories']);
      }

      if (isset($config['services'])){
          $c['services']=$config['services'];
          unset($config['services']);
      }

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

      $c['view']=function ($c) use($config){
        $view=new \Kruul\Slim\PhpRenderer($c);
        $layoutpath='';
        if (isset($config['view_manager']['template_path'])) {
           $layoutpath=$config['view_manager']['template_path'];
        }

        if (isset($config['view_manager']['layout/layout'])) {
          if (is_file($file=$layoutpath.$config['view_manager']['layout/layout'])) {
              $view->setLayout($layoutpath.$config['view_manager']['layout/layout']);
          } else {
              throw new \Exception('Layout not found');
          }
          unset($config['view_manager']['layout/layout']);
        }

        if (isset($config['view_helpers'])){
            foreach ($config['view_helpers'] as $name=>$callable){
                if (!is_callable($callable)) {
                    $callable= function () use ($callable){
                        $obj= new $callable;
                        if ($obj instanceof ContainerInterface ){
                            $obj->setContainer($this->container);
                        }
                        return $obj();
                   };
                }
                if (is_callable($callable)) $view->setHelper($name,$callable);
                else throw new Exception ('Helper is not callable');
            }
        }
        return $view;
      };


      $app=new \Slim\App($c);
      if (isset($config['routes'])){
        foreach ($config['routes'] as $name=>$rule){
          if (is_array($rule['method'])) {
              foreach ($rule['route'] as $k=>$v){ $routename.=$v.' and ';}
              throw new \Exception('Route name is duplicate: '.rtrim($routename,'and '));
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
    try {
        if (!$this->container) $this->init();
        $app = $this->container->get('AppFactory');
        $app->run();
      } catch (\Exception $e) {
          echo $e->getMessage();
      }
  }

}
