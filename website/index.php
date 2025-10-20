<?php
/*
	Agora E-Commerce Website
	Front Controller
	=================
	
	This is the main entry point for all requests
*/

// Load the framework
include 'lib/context.php';
include 'lib/abstractController.php';
include 'lib/abstractModel.php';
include 'lib/abstractView.php';

// Get controller name from URI and load appropriate controller
function getController($uri) {
	$part = $uri->getPart();
	
	// Default to home page
	if ($part === '') {
		return 'Home';
	}
	
	// Route based on first part of URI
	switch ($part) {
		case 'home':
			return 'Home';
			
		case 'about':
		case 'contact':
		case 'privacy':
			$uri->prependPart($part);  // Put it back for the controller to use
			return 'Static';
			
		case 'login':
			return 'Login';
			
		case 'register':
			return 'Register';
			
		case 'logout':
			return 'Logout';
			
		case 'profile':
			return 'Profile';
			
		case 'shop':
		case 'products':
			return 'Products';
			
		case 'product':
			return 'Product';
			
		case 'business':
			return 'Business';
			
		case 'admin':
			return 'Admin';
			
		default:
			throw new InvalidRequestException('Unknown page: ' . $part);
	}
}

// Main execution
try {
	// Create context from configuration
	$context = Context::createFromConfigurationFile('website.conf');
	
	// Determine which controller to use
	$controllerName = getController($context->getURI());
	
	// Load and instantiate the controller
	$controllerFile = 'controllers/' . strtolower($controllerName) . 'Controller.php';
	if (!file_exists($controllerFile)) {
		throw new InvalidRequestException('Controller not found: ' . $controllerName);
	}
	
	include $controllerFile;
	$controllerClass = $controllerName . 'Controller';
	$controller = new $controllerClass($context);
	
	// Process the request
	$controller->process();
	
	// Clean up
	$context->getDB()->close();
	
} catch (ConfigurationException $e) {
	http_response_code(500);
	include_once 'views/error.php';
	$errorView = new ErrorView(500, 'Configuration Error', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (DatabaseException $e) {
	http_response_code(500);
	include_once 'views/error.php';
	$errorView = new ErrorView(500, 'Database Error', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (UnauthorizedException $e) {
	http_response_code(403);
	include_once 'views/error.php';
	$errorView = new ErrorView(403, 'Access Denied', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (InvalidRequestException $e) {
	http_response_code(404);
	include_once 'views/error.php';
	$errorView = new ErrorView(404, 'Page Not Found', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (Exception $e) {
	http_response_code(500);
	include_once 'views/error.php';
	$errorView = new ErrorView(500, 'An Error Occurred', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
}
?>
