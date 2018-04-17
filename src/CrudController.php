<?php
namespace Kruul\Slim;

use Kruul\Slim\Controller;
use Kruul\Slim\CrudControllerInterface;

/** sample route
 *
   ['routes'=>array(
      'client' => array(
        'method'     => 'CRUD',
        'route'      => '/section/client',
        'controller' => 'Section\Controller\ClientController',
        'role'       => 'user'
      ),
    ]
 *
 **/

abstract class CrudController extends Controller{

    public function __construct($container) {
        parent::__construct($container);
    }

    public function  IndexAction(){ //get
      return $this->render('',[]);
    }

    public function  ShowAction(){ //get

      $this->id=$this->getArgument('id');
      return $this->render('',[]);
    }

    public function  EditAction(array $param){ //get
      return $this->render('',[]);
    }

    public function  CreateAction(){ //get
      return $this->render('',[]);
    }

    public function  AddAction(){ //post
      return $this->render('',[]);
    }

    public function  UpdateAction(){ //put
      return $this->render('',[]);
    }

    public function  DeleteAction(){ //delete
      return $this->render('',[]);
    }

}