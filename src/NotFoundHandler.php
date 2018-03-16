<?php
namespace Kruul\Slim;

use Slim\Handlers\NotFound;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler extends NotFound {

    public function __construct($container) {
        $this->container=$container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
        $contentType = $this->determineContentType($request);
        $view_manager=$this->container['config']['view_manager'];
        if (($contentType == 'text/html') && (is_file($view_manager['template_path'].$view_manager['error/404']))){
            $view=$this->container['view'];
            $view->setTemplatepath($view_manager['template_path']);
            $view->render($response, $view_manager['error/404']);
            return $response->withStatus(404);
        } else {
            return parent::__invoke($request, $response, $methods);
        }
    }
}
