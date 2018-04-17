<?php
namespace Kruul\Slim;

interface CrudControllerInterface {
    public function  IndexAction();  //get
    public function  ShowAction();   //get
    public function  EditAction();   //get
    public function  CreateAction(); //get
    public function  AddAction();    //post
    public function  UpdateAction(); //put
    public function  DeleteAction(); //delete

}