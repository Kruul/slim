<?php
namespace Kruul\Slim;
use Psr\Http\Message\UriInterface;
/*
 *  Name:   class Controller for Slim Framework
 *  Author: Shvager Alexander
 *  Email:  Alex.Shvager@gmail.com
 *  Slim Framework (http://slimframework.com)
 *
 *  2017-01-25 * fix response (get from container)
 *  2017-01-27 * add setHeader
 *  2018-01-24 * fix post
 */
abstract class Controller
{
    protected $container;

    /*    protected $request;
    protected $response;
    protected $arguments;
    */
    /**
     * @param \Slim\Container
     */
    public function __construct($container)  {
        $this->container = $container;
    }

    public function getContainer(){
        return $this->container;
    }

    public function __call($actionName,$param){
        $rc= new \ReflectionClass ($this);
        $viewdir=rtrim(pathinfo($rc->getfilename(),PATHINFO_DIRNAME),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.
                                '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR;

        $this->get('view')->setTemplatepath($viewdir);
        return call_user_func_array(array($this, trim($actionName,'__')), []);
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
        return $this->get('view')->render($this->get('response'), $file, $args);
    }

    protected function setHeader($key,$value){
        $this->get('view')->setHeader($key, $value);
        return $this;
    }

    public function setLayout($layout){
        $this->get('view')->setLayout($layout);
        return $this;
    }

    /**
     * Return true if XHR request$content = $request->getContent();
     */
    protected function isXhr(){
        return $this->get('request')->isXhr();
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
       return $this->get('request')->getBody()->getContents();
    }

    public function getParsedBodyParam($name,$defaults = null){
        return $this->get('request')->getParsedBodyParam($name, $defaults);
    }

    protected function getQueryParam($name, $default=null){
        return $this->get('request')->getQueryParam($name, $default);
    }
    /**
     * Get the GET params
     */
    protected function getQueryParams(){
        return $this->get('request')->getQueryParams();
    }

    /**
     * Shorthand method to get dependency from container
     * @param $name
     * @return mixed
     */
    protected function get($name){
         return $this->container->get($name);
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
        return $this->get('response')->withRedirect($url, $status);
    }
    /**
     * Pass on the control to another action. Of the same class (for now)
     *
     * @param  string $actionName The redirect destination.
     * @param array $data
     * @return Controller
     * @internal param string $status The redirect HTTP status code.
     */

/*    public function forward($actionName, $data=array()){
        // update the action name that was last used
        if (method_exists($this->response, 'setActionName')) {
            $this->response->setActionName($actionName.'__');
        }
        return call_user_func_array(array($this, $actionName), $data);
    }
*/
}