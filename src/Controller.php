<?php
namespace Kruul\Slim;
use Psr\Http\Message\UriInterface;
//use Slim\Http\Request;
use Kruul\Slim\ContainerInterface;
/*
 *  Name:   class Controller for Slim Framework
 *  Author: Shvager Alexander
 *  Email:  Alex.Shvager@gmail.com
 *  Slim Framework (http://slimframework.com)
 *
 *  2017-01-25 * fix response (get from container)
 *  2017-01-27 * add setHeader
 *  2018-01-24 * fix post
 *  2018-02-23 * fix __call
 *  2018-02-26 * add isPost, isGet ...
 *  2018-03-20 * implements ContainerInterface
 */
abstract class Controller implements ContainerInterface
{
    protected $container;
    protected $arguments;
    protected $request;
    protected $response;

    /**
     * @param \Slim\Container
     */
    public function __construct($container)  {
        $this->container = $container;
    }

    public function setContainer($container) {
        $this->container=$container;
        return $this;
    }

    public function getContainer(){
        return $this->container;
    }

    public function __call($actionName,$param){
        $rc= new \ReflectionClass ($this);
        $actionName=trim($actionName,'__');
        if(!method_exists($rc->name,$actionName))            throw new \Exception('Action not found');
        $viewdir=rtrim(pathinfo($rc->getfilename(),PATHINFO_DIRNAME),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.
                                '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR;

        $this->get('view')->setTemplatepath($viewdir);
        $this->request=$param[0];
        $this->response=$param[1];
        //$this->arguments=$this->request->getAttribute('route')->getArguments();
        $this->arguments=$param[2];
        return call_user_func_array(array($this, $actionName), [$this->arguments]);
    }

/*
    public function setRequest($request){
        $this->request = $request;
    }

    public function setResponse($response){
        //print_r($this->response);
        $this->response = $response;
    }
*/
    /**
     * Render the view from within the controller
     * @param string $file Name of the template/ view to render
     * @param array $args Additional variables to pass to the view
     * @param Response?
     */
    protected function render($file, $args=array()){
        return $this->get('view')->render($this->response, $file, $args);
    }

    protected function setHeader($key,$value){
        $this->get('view')->setHeader($key, $value);
        return $this;
    }

    public function setLayout($layout){
        $this->get('view')->setLayout($layout);
        return $this;
    }

    public function refresh($data){
        if (empty($data))  {
           $data=$this->getParsedBody();
        }
        $data=http_build_query($data);
        $Url = (string)($this->request->getUri()->withQuery($data) );
        return $this->redirect($Url);
    }

    public function isMethod($method){
        return $this->request->getMethod() === $method;
    }

    public function isGet(){
        return $this->request->isMethod('GET');
    }

    public function isPost(){
        return $this->request->isMethod('POST');
    }

    public function isPut(){
        return $this->request->isMethod('PUT');
    }

    public function isPatch(){
        return $this->request->isMethod('PATCH');
    }

    public function isDelete(){
        return $this->request->isMethod('DELETE');
    }

    public function isHead(){
        return $this->request->isMethod('HEAD');
    }

    public function isOptions(){
        return $this->request->isMethod('OPTIONS');
    }

    /**
     * Return true if XHR request$content = $request->getContent();
     */
    public function isXhr(){
        return $this->request->isXhr();
    }

    public function setArguments($arg){
        $this->arguments=$arg;
        return $this;
    }

    public function getArguments(){
        return $this->arguments;
    }

    /**
     * Retrieve a specific route argument
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */

    public function getArgument($name, $default = null){
        if (array_key_exists($name, $this->arguments)) {
            return $this->arguments[$name];
        }
        return $default;
    }

    public function getBodyContent(){
       return $this->request->getBody()->getContents();
    }

    public function getParsedBody(){
        return $this->request->getParsedBody();
    }

    public function getParsedBodyParam($name,$defaults = null){
        return $this->request->getParsedBodyParam($name, $defaults);
    }

    protected function getQueryParam($name, $default=null){
        return $this->request->getQueryParam($name, $default);
    }

    /**
     * Get the GET params
     */
    protected function getQueryParams(){
        return $this->request->getQueryParams();
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function get($name){
         return $this->container->get($name);
    }

    protected function has($name){
         return $this->container->has($name);
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int                 $status The redirect HTTP status code.
     * @return self
     */
    protected function redirect($url, $status = 302){
        return $this->response->withRedirect($url, $status);
    }
    /**
     * Pass on the control to another action. Of the same class (for now)
     *
     * @param  string $actionName The redirect destination.
     * @param array $data
     * @return Controller
     * @internal param string $status The redirect HTTP status code.
     */

    public function forward($actionName, $data=array()){
        return call_user_func_array(array($this, $actionName), $data);
    }

}