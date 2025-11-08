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
			$uri->prependPart($part);
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
			
		case 'product-add':
			return 'ProductAdd';
			
		case 'product-edit':
			return 'ProductAdd';
			
		case 'business':
			return 'Business';
			
		case 'businesses':
			return 'Businesses';
			
		case 'business-manage':
			return 'BusinessManage';
			
		case 'admin':
			return 'Admin';
			
		case 'admin-panel':
			return 'AdminPanel';
			
		case 'cart':
			return 'Cart';
			
		case 'orders':
			return 'Orders';
			
		case 'order':
			return 'Order';
			
		case 'seller-orders':
			return 'SellerOrders';
			
		case 'seller-order':
			return 'SellerOrder';
			
		default:
			throw new InvalidRequestException('Unknown page: ' . $part);
	}
}

// Main execution
try {
	// Create context from configuration
	$context = Context::createFromConfigurationFile('website.conf');
	
	// Initialise user from session
	include_once 'models/UserModel.php';
	$user = new User($context);
	$context->setUser($user);
	
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
	include_once 'views/ErrorView.php';
	$errorView = new ErrorView(500, 'Configuration Error', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (DatabaseException $e) {
	http_response_code(500);
	include_once 'views/ErrorView.php';
	$errorView = new ErrorView(500, 'Database Error', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (UnauthorizedException $e) {
	http_response_code(403);
	include_once 'views/ErrorView.php';
	$errorView = new ErrorView(403, 'Access Denied', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (InvalidRequestException $e) {
	http_response_code(404);
	include_once 'views/ErrorView.php';
	$errorView = new ErrorView(404, 'Page Not Found', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
	
} catch (Exception $e) {
	http_response_code(500);
	include_once 'views/ErrorView.php';
	$errorView = new ErrorView(500, 'An Error Occurred', $e->getMessage(), $context ?? null);
	$errorView->prepare();
	$errorView->render();
}
?>
