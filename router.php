<?php
use Symfony\Component\Routing\Route;

use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\Routing\RequestContext;

use Symfony\Component\Routing\Matcher\UrlMatcher;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Symfony\Component\HttpFoundation\Response;

$collection=new RouteCollection;
$collection->add('home', new Route('/{page}/{name}', ['page'=>1,'name'=>null], ['page'=>'\d{1,4}','name'=>'\w+']));
$collection->add('post', new Route('/post/{page}/{crud}/{id}', ['page'=>1, 'crud'=>null, 'id'=>null], ['page'=>'\d{1,3}','crud'=>'(update|delete|create|separate)', 'id'=>'\d{1,3}']));
$collection->add('product', new Route('/product/{crud}/{id}', ['crud'=>null, 'id'=>null], ['crud'=>'(update|delete)', 'id'=>'\d{1,3}']));
$collection->add('category', new Route('/category/{crud}/{id}', ['crud'=>null, 'id'=>null], ['crud'=>'(update|delete)', 'id'=>'\d{1,3}']));
$collection->add('user', new Route('/user/{action}', ['action'=>null], ['action'=>'(registration|login|logout)']));
$context=new RequestContext;
$context->fromRequest($r);
$matcher=new UrlMatcher($collection, $context);
$path=$r->getPathInfo();
$generator=new UrlGenerator($collection, $context);
//add TWIG connection
$loader=new Twig_Loader_Filesystem('tpl');
$env=new Twig_Environment($loader);
//create new function for TWIG
$twig_func=new Twig_SimpleFunction('createUrl', function ($route, $key=null, $dynamic=null) use ($generator) {
	return $generator->generate($route, [$key=>$dynamic]);
});
$env->addFunction($twig_func);
try{
	extract($matcher->match($path), EXTR_SKIP);
	ob_start();
	require sprintf("ctrl/%s.php", $_route);
	$response=new Response(ob_get_clean());
}catch(Symfony\Component\Routing\Exception\ResourceNotFoundException $e){
	require "ctrl/not_found.php";
	$response=new Response('', '404');
}catch(Exception $e){
	require "ctrl/server_error.php";
	$response=new Response('', '500');
}
$response->send();
