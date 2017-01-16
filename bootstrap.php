<?php
require "vendor/autoload.php";

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Validator\ValidatorBuilder;

use Doctrine\ORM\Tools\Setup;

use Doctrine\ORM\EntityManager;

$con=[
	'driver'=>'pdo_mysql',
	'host'=>'localhost',
	'user'=>'root',
	'password'=>'',
	'dbname'=>'market'	
];
//configuration for Yaml
//['./config'] folder with all YAML's code
$conf=Setup::createYAMLMetadataConfiguration(['./config']);
$em=EntityManager::create($con, $conf);
$r=Request::createFromGlobals();
$redirect=new RedirectResponse('/');
$session=new Session;
$session->start();

$builder=new ValidatorBuilder;
$builder->addYamlMapping('config/Validation.yml');
$validator=$builder->getValidator();
