<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
*/

$active_group = 'default';
$query_builder = TRUE;

// Load environment variables if exists
if (file_exists(FCPATH . 'application/config/env.php')) {
	require_once FCPATH . 'application/config/env.php';
}

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => (class_exists('Env') ? Env::get('DB_HOST', 'localhost') : 'localhost'),
	'username' => (class_exists('Env') ? Env::get('DB_USERNAME', 'root') : 'root'),
	'password' => (class_exists('Env') ? Env::get('DB_PASSWORD', '') : ''),
	'database' => (class_exists('Env') ? Env::get('DB_DATABASE', 'accounting25') : 'accounting25'),
	'dbdriver' => (class_exists('Env') ? Env::get('DB_DRIVER', 'mysqli') : 'mysqli'),
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);