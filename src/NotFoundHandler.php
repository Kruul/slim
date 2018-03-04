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
        if (($contentType == 'text/html') && (is_file('public/layout/notfound.phtml'))){
            $view=$this->container['view'];
            $view->setTemplatepath('public/layout/');
            $view->render($response, 'notfound.phtml');
            return $response->withStatus(404);
        } else {
            return parent::__invoke($request, $response, $methods);
        }
    }
}
