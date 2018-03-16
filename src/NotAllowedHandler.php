<?php

/*
 * Copyright (C) 2018 uukrul
 * email : alex.shvager@gmail.com
 *
 */
namespace Kruul\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Handlers\NotAllowed;

class NotAllowedHandler extends NotAllowed{

    public function __construct($container) {
        $this->container=$container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods){
        $contentType = $this->determineContentType($request);
        $view_manager=$this->container['config']['view_manager'];
        if (($contentType == 'text/html') && (is_file($view_manager['template_path'].$view_manager['error/405']))){
            if ($request->getMethod() === 'OPTIONS') {
                $status = 200;
            } else {
                $status = 405;
            }
            $allow = implode(', ', $methods);
            $view=$this->container['view'];
            $view->setTemplatepath($view_manager['template_path']);
            $view->render($response, $view_manager['error/405'],['allow'=>$allow]);
            return $response->withStatus($status);
        } else {
            parent::__invoke($request, $response, $methods);
        }
    }



}
