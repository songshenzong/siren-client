<?php
/**
 * Created by PhpStorm.
 * User: connor
 * Date: 12/12/17
 * Time: 12:41 AM
 */


$request = [
    'REQUEST'         => $_REQUEST ?? [],
    'HTTP_HOST'       => $_SERVER['HTTP_HOST'] ?? '',
    'REQUEST_URI'     => $_SERVER['REQUEST_URI'] ?? '',
    'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
];

print_r($request);
