<?php

namespace Kruul\Slim;

use Slim\Handlers\NotFound;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundHandler_test extends NotFound {

    public function __construct($container) {
        $this->container=$container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
        parent::__invoke($request, $response);
        $view=$this->container['view'];
        //echo "ssssss";
        $view->setTemplatepath('public/layout/');
        $view->render($response, 'notfound.html');

        return $response->withStatus(404);
    }

}
