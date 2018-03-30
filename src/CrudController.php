<?php
namespace Kruul\Slim;

use Kruul\Slim\Controller;

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

abstract class CrudController extends Controller {

    public function __construct($container) {
        parent::__construct($container);
    }

        public function  IndexAction(){ //get
      return $this->render('',[]);
    }

    public function  ShowAction(){ //get
      $sss=$this->getQueryParams();
      $this->id=$this->getArgument('id');
      return $this->render('',[]);
    }

    public function  EditAction(){ //get
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