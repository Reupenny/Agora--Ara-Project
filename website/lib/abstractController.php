<?php

abstract class AbstractController {
	
	private $context;
	private $redirect;
	
	public function __construct (IContext $context){
		$this->context=$context;
		$this->redirect=null;
	}
	protected function getContext() {
		return $this->context;
	}
	protected function getDB() {
		return $this->context->getDB();
	}
	protected function getURI() {
		return $this->context->getURI();
	}
	protected function getConfig() {
		return $this->context->getConfig();
	}
	protected function getSession() {
		return $this->context->getSession();
	}

	public function process() {
		$method=$_SERVER['REQUEST_METHOD'];
		switch($method) {
			case 'GET':  	$view=$this->getView(false);	break;
			case 'POST':  	$view=$this->getView(true);		break;
			default:
				throw new InvalidRequestException ("Invalid Request verb");
		}
		if ($view!==null) {
			$view->prepare();
			// apply global template arguments
			$site=$this->getURI()->getSite();
			$view->setTemplateField('site',$site);
			$view->render();
		} elseif ($this->redirect!==null) {
			header ('Location: '.$this->redirect);
		} else {
			throw new InvalidRequestException ("View not set");
		}
	}

	// sub-controllers will override this
	protected function getView($isPostback) {
		return null;
	}	
	
	protected function redirectTo ($page) {
		$this->redirect = $this->getURI()->getSite() . $page;
	}
}
?>
