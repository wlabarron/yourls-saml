<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/wlabarron/yourls-saml
Description: Log in to YOURLS using SAML.
Version: 1.2
Author: Andrew Barron
Author URI: https://awmb.uk
*/

yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (!yourls_is_API()) { // Don't use SAML for API requests
        session_start();
        require(__DIR__ . '/vendor/autoload.php');
        require(__DIR__ . '/settings.php');
        $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);
        
        // If not signed in, sign in
    	if (!isset($_SESSION['samlNameId'])) $auth->login();
        
    	yourls_set_user($_SESSION['samlNameId']);
    	return isset($_SESSION['samlNameId']);
    }
}

// Remove log out link from "hello" message
yourls_add_filter('logout_link', 'wlabarron_saml_hello_user');
function wlabarron_saml_hello_user() {
	return sprintf( yourls__('Hello <strong>%s</strong>'), YOURLS_USER );
}

// Deny access to Plugins page for any users not listed in the config file
yourls_add_action( 'auth_successful', function() {
	if( yourls_is_admin() ) wlabarron_saml_intercept_admin();
} );
function wlabarron_saml_intercept_admin() {
	// we use this GET param to send up a feedback notice to user
	if ( isset( $_GET['access'] ) && $_GET['access']=='denied' ) {
		yourls_add_notice('Access Denied');
	}

	// Intercept requests for plugin management
	if(isset( $_SERVER['REQUEST_URI'] ) &&
	   preg_match('/\/admin\/plugins/', $_SERVER['REQUEST_URI'] ) ) {
           
            if (!wlabarron_saml_is_user_in_config()) {
                yourls_redirect( yourls_admin_url( '?access=denied' ), 302 );
            }
	}
}

// Hide plugins from navigation if the user isn't defined in the config file
yourls_add_filter( 'admin_links', 'wlabarron_saml_admin_links' );
function wlabarron_saml_admin_links($links) {
    if (!wlabarron_saml_is_user_in_config()) {
	  unset($links['plugins']);
	}
	
	return $links;
}

// Check if the currently logged in user is defined in the config file.
function wlabarron_saml_is_user_in_config() {
    global $yourls_user_passwords;
    $users = array_keys($yourls_user_passwords);
    
    return in_array(YOURLS_USER, $users);
}
