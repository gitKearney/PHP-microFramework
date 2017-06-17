<?php

namespace Controllers;

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

/**
 * @deprecated
 */ 
interface BaseControllerInterface
{
       
    /**
     * Method to process HTTP DELETES
     * @param Request $request
     * @param Response $response
     */
    public function delete(Request $request, Response $response);
    
    /**
     * Method to process HTTP GET reqeusts
     * @param Request $request
     * @param Response $response
     */
    public function get(Request $request, Response $response);
    
    /**
     * Method to process HTTP HEAD
     * @param Request $request
     * @param Response $response
     */
    public function head(Request $request, Response $response);
    
    /**
     * Method to process HTTP OPTION requests
     * @param Request $request
     * @param Response $response
     */
    public function options(Request $request, Response $response);
    
    /**
     * Method to process HTTP PATCH reqeusts
     * @param Request $request
     * @param Response $response
     */
    public function patch(Request $request, Response $response);
    
    /**
     * Method to process HTTP POST requests
     * @param Request $request
     * @param Response $response
     */
    public function post(Request $request, Response $response);
    
    /**
     * Method to process HTTP PUT requests
     * @param Request $request
     * @param Response $response
     */
    public function put(Request $request, Response $response);
}