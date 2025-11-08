<?php
/*
 * Static Page View
 * This view is responsible for rendering static pages using the master template.
 */

class StaticView extends AbstractView {
	
	public function prepare() {
		$model = $this->getModel();
		$pageName = $model->getPageName();
		
		// Set the master page template
		$this->setTemplate('html/masterPage.html');
		
		// Set page-specific fields
		$this->setTemplateField('pagename', ucfirst($pageName));
		
		// Load the content for this page
		$contentFile = 'html/' . $pageName . '.html';
		if (file_exists($contentFile)) {
			$content = file_get_contents($contentFile);
		} else {
			$content = '<h1>' . ucfirst($pageName) . '</h1><p>Coming soon...</p>';
		}
		
		$this->setTemplateField('content', $content);
	}
}
?>
