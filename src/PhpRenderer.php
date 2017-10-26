<?php
namespace Kruul\Slim;
use Psr\Http\Message\ResponseInterface;
// for slim framework
// author by Shvager Alexander
/*
 2017-01-25 add setHeader()
*/

class PhpRenderer {
    private $templatePath;
    private $header;

    public function __construct($container){
      $this->container=$container;
    }
    public function setTemplatepath($templatePath){
      $this->templatePath=$templatePath;
      return $this;
    }
    public function setHeader($key, $value){
      $this->header[$key]=$value;
      return $this;
    }

    public function render(ResponseInterface $response, $template, $data = []){
       $render = function ($template, $data) {
            extract($data);
            include $template;
        };

        ob_start();

        if (is_array($this->header))
          foreach ($this->header as $key => $value) {
            $this->container->response=$this->container->response->withHeader($key,$value);
          }

        $render($this->templatePath . $template, $data);
        $output = ob_get_clean();

        $this->container->response->getBody()->write($output);
        return $this->container->response;
    }
}



