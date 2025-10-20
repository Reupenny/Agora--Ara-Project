<?php
/*
	Error View
	Renders error pages using the master template
*/

class ErrorView extends AbstractView {
	
	private $errorCode;
	private $errorTitle;
	private $errorMessage;
	private $context;
	
	public function __construct($errorCode, $errorTitle, $errorMessage, $context = null) {
		parent::__construct();
		$this->errorCode = $errorCode;
		$this->errorTitle = $errorTitle;
		$this->errorMessage = $errorMessage;
		$this->context = $context;
	}
	
	public function prepare() {
		// Set page title
		$this->setTemplateField('pagename', $this->errorCode . ' - ' . $this->errorTitle);
		
		// Set site root for ##site## token
		if ($this->context !== null) {
			$site = $this->context->getURI()->getSite();
			$this->setTemplateField('site', $site);
		} else {
			$this->setTemplateField('site', '/');
		}
		
		// Load the appropriate error template
		$templateFile = 'html/error/error-' . $this->errorCode . '.html';
		if (!file_exists($templateFile)) {
			$templateFile = 'html/error/error-500.html'; // Fallback to 500 error
		}
		
		$content = file_get_contents($templateFile);
		
		// Get site root for token replacement
		$site = '/';
		if ($this->context !== null) {
			$site = $this->context->getURI()->getSite();
		}
		
		// Replace tokens in error content
		$content = str_replace('##error_message##', htmlspecialchars($this->errorMessage), $content);
		$content = str_replace('##site##', $site, $content);
		
		$this->setTemplateField('content', $content);
		
		// Set the master template
		$this->setTemplate('html/masterPage.html');
	}
}
?>
