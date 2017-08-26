<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require 'vendor/autoload.php';
require 'lib/mysql.php';
require 'lib/Nodes.php';


$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['view'] = new \Slim\Views\PhpRenderer("templates/");

$app->get('/node', function (Request $request, Response $response) {
    $result = array();
    try {
    	$nodeObj = new \lib\Nodes($this->db);
    	//fetch all data
    	$data = $nodeObj->getAllNodes();

    	$indexedItems = array();
    	//index elements by id
		foreach ($data as $item) {
		    $item['subs'] = array();
		    $indexedItems[$item['id']] = (object) $item;
		}

		//assign to parent
		$topLevel = array();
		foreach ($indexedItems as $item) {
		    if ($item->parent_id == 0) {
		        $topLevel[] = $item;
		    } else {
		        $indexedItems[$item->parent_id]->subs[] = $item;
		    }
		}

    	$response = $this->view->render($response, "index.phtml", ["nodes" => $nodeObj->renderMenu($topLevel)]);
    	return $response;


    } catch (Exception $e) {
    	$result['error'] = true;
    	$result['errorMsg'] = $e->getMessage();
    } 
    
});

$app->get('/node/list', function (Request $request, Response $response) {
    $result = array();
    try {
    	//fetch all data
    	$data = $nodeObj->getAllNodes();
    	$result['success'] = true;
    	$result['data'] = $data;
    } catch (Exception $e) {
    	$result['error'] = true;
    	$result['errorMsg'] = $e->getMessage();
    } 
    echo json_encode($result);
	die();
});

$app->get('/node/create', function (Request $request, Response $response) {
    $result = array();
    try {
    	$input = $request->getQueryParams();
    	if(!isset($input['node'])){
    		throw new Exception("Error Processing Request", 1);
    	}
    	$nodeObj = new \lib\Nodes($this->db);
    	$data = $nodeObj->getSingleNode($input['node']);

    	if( ! $data){
    		$dataN = $nodeObj->getAllNodes();
    		if(!empty($dataN)){
    			throw new Exception("No nodes has been found", 1);
    		}
    		else{
    			$data['id'] = 0;
    		}
    		
    	}

    	$nodeObj->insertNode($data['id']);

    	$result['success'] = true;
    	$result['data'] = $nodeObj->getAllNodes();;
    } catch (Exception $e) {
    	$result['error'] = true;
    	$result['errorMsg'] = $e->getMessage();
    } 
    echo json_encode($result);
	die();
});

$app->get('/node/delete', function (Request $request, Response $response) {
    $result = array();
    try {
    	$input = $request->getQueryParams();
    	if(!isset($input['node'])){
    		throw new Exception("Error Processing Request", 1);
    	}
    	$nodeObj = new \lib\Nodes($this->db);
    	$data = $nodeObj->getSingleNode($input['node']);

    	if( ! $data){
    		throw new Exception("No nodes has been found", 1);
    	}

    	$parentId = $data['parent_id'];
    	$childs = $nodeObj->getParentNode($input['node']);
    	if( $childs ){
    		foreach ($childs as $key => $value) {
    			$nodeObj->updateNode($parentId,$value['id']);
    		}
    	}

    	$nodeObj->deleteNode($input['node']);

    	//fetch all data
    	$data = $nodeObj->getAllNodes();

    	$result['success'] = true;
    	$result['data'] = $data;
    } catch (Exception $e) {
    	$result['error'] = true;
    	$result['errorMsg'] = $e->getMessage();
    } 
    echo json_encode($result);
	die();
});

$app->run();
