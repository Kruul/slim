<?php

namespace Kruul\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\PhpError;


class phpErrorHandler extends PhpError{
    protected $displayErrorDetails;

    public function __construct($container) {
        $this->container=$container;
        $this->displayErrorDetails=$this->container['settings']['displayErrorDetails'];
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Throwable $error){
        $contentType = $this->determineContentType($request);
        $view_manager=$this->container['config']['view_manager'];
        if (($contentType == 'text/html') && (is_file($view_manager['template_path'].$view_manager['error/index']))){
            $output = $this->renderHtmlErrorMessage($error);
            $view=$this->container['view'];
            $view->setTemplatepath($view_manager['template_path']);
            $view->render($response, $view_manager['error/index'],['error'=>$output]);
            return $response->withStatus(500);
        } else {
            return parent::__invoke($request, $response, $error);
        }

    }
    protected function renderHtmlErrorMessage(\Throwable $error) {
        if ($this->displayErrorDetails) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlError($error);

            while ($error = $error->getPrevious()) {
                $html .= '<h2>Previous error</h2>';
                $html .= $this->renderHtmlError($error);
            }
        } else {
            $html = '<p>A website error has occurred. Sorry for the temporary inconvenience.</p>';
        }


        return $html;
    }

}
