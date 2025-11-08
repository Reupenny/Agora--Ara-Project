<?php
/*
	Static Page Model
	Simple model for static pages (no database interaction needed)
*/

class StaticModel extends AbstractModel {
	
	private $pageName;
	
	public function setPageName($pageName) {
		$this->pageName = $pageName;
	}
	
	public function getPageName() {
		return $this->pageName;
	}
}
?>
