<?php
/*
 * Static Page Model
 * This is a simple model for static pages that do not require any database interaction.
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
