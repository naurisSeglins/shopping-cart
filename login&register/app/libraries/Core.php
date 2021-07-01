<?php
/*
   * App Core Class
   * Creates URL & loads core controller
   * URL FORMAT - /controller/method/params
   */
class Core
{
    protected $currentController = 'Pages';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct()
    {
        // print_r($this->getUrl());
        $url = $this->getUrl();

        // Look in controller folder for first value
        //ucwords will capitalize first letter 
        if (file_exists('../app/controllers/' . ucwords($url[0]) . '.php')) {
            // Will set a new controller
            $this->currentController = ucwords($url[0]);
            // Unset 0 Index
            unset($url[0]);
        }

        // Require the controller
        require_once '../app/controllers/' . $this->currentController . '.php';

        // Instantiate controller class
        $this->currentController = new $this->currentController;

        // Check for second part of url
        if (isset($url[1])) {
            // Check to see if method exists in controller
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                // Unset 1 index
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl()
    {
        if (isset($_GET['url'])) {
            //strip the ending slash(take away the last "/")
            $url = rtrim($_GET['url'], '/');
            //not allowing characters that a url should not have (@ $ #)
            $url = filter_var($url, FILTER_SANITIZE_URL);
            //breaks url into a array
            $url = explode('/', $url);
            return $url;
        }
    }
}