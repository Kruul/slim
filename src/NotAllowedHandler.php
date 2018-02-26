<?php

/*
 * Copyright (C) 2018 uukrul
 * email : alex.shvager@gmail.com
 *
 */
namespace Kruul\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;
use UnexpectedValueException;
use Slim\Handlers\NotAllowed;

class NotAllowedHandler extends NotAllowed{

    public function __construct($container) {
        $this->container=$container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods){
        $contentType = $this->determineContentType($request);
        if ($contentType == 'text/html'){
            if ($request->getMethod() === 'OPTIONS') {
                $status = 200;
            } else {
                $status = 405;
            }
            $allow = implode(', ', $methods);
            $view=$this->container['view'];
            $view->setTemplatepath('public/layout/');
            $view->render($response, 'notallowed.phtml',['allow'=>$allow]);
            return $response->withStatus($status);
        } else {
            parent::__invoke($request, $response, $methods);
        }
    }



}
