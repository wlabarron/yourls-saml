<?php
/**
 *  SP Single Logout Service Endpoint
 */

session_start();

require(dirname(__DIR__).'/vendor/autoload.php');
require_once(dirname(__DIR__).'/settings.php');
$auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

$auth->processSLO();

$errors = $auth->getErrors();

if (empty($errors)) {
    echo 'Sucessfully logged out';
} else {
    echo implode(', ', $errors);
}