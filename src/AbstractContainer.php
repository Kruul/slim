<?php
namespace Kruul\Slim;
/*
 * Copyright (C) 2018 uukrul
 * email : alex.shvager@gmail.com
 *
 */
use Kruul\Slim\ContainerInterface;

abstract class AbstractContainer implements ContainerInterface{
    private $container;

    function setContainer($container){
        $this->container=$container;
        return $this;
    }

    function getContainer(){
        return $this->container;
    }
}
