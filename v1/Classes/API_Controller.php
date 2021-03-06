<?php

	abstract class API_Controller {
	
    	protected $req_method = '';
    	protected $endpoint = ''; 
    	protected $args = Array();
    
	    /* Constructor: __construct
    	 * Allow for CORS, assemble and pre-process the data */
	    public function __construct($request) {
    	    header("Access-Control-Allow-Orgin: *");
       		header("Access-Control-Allow-Methods: *");
       		header("Content-Type: application/json");

        	$this->args = explode('/', rtrim($request, '/'));
        	$this->endpoint = array_shift($this->args);

			$this->req_method = $_SERVER['REQUEST_METHOD'];
        	switch($this->req_method) {
        		case 'POST':
            		$this->args = $this->_cleanInputs($_POST);
            		break;
        		case 'GET':
            		$this->request = $this->_cleanInputs($_GET);
            		break;
        		default:
            		$this->_response($this->req_method . " is an Invalid Request Method for this API", 405);
            		break;
        	}
    	}
    
    	public function processAPI() {
        	if (method_exists($this, $this->endpoint)) {
        		$reflection = new ReflectionMethod($this, $this->endpoint);
    			if ($reflection->isPublic())
    				return $this->_response($this->validateAndApplyFunction($this->endpoint, $this->args)); 
 	 	  	}
            return $this->_response($this->{'_invalidEndpoint'}());
    	}

	    private function _response($data, $status = 200) {
    	    header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        	return json_encode($data);
    	}

	    private function _cleanInputs($data) {
    	    $clean_input = Array();
    	    if (is_array($data)) {
    	        foreach ($data as $k => $v) $clean_input[$k] = $this->_cleanInputs($v);
        	} else {
            	$clean_input = trim(strip_tags($data));
        	}
        	return $clean_input;
   		}

    	private function _requestStatus($code) {
        	$status = array(  
            	200 => 'OK',
            	404 => 'Not Found',   
            	405 => 'Method Not Allowed',
            	500 => 'Internal Server Error',
        	); 
        	return ($status[$code])?$status[$code]:$status[500]; 
    	}
    	
    	/* Error handler */
		private function _invalidEndpoint() {
			return array("status" => "Error", "message" => "Sorry, that endpoint is not valid");
     	}
	}
?>