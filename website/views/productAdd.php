<?php
/*
    Product Add/Edit View
    Renders the product creation/editing form
*/

class ProductAddView extends AbstractView
{
    private $productManager;
    private $productId;
    private $productData;
    private $errorMessage = '';
    
    public function setProductManager($manager)
    {
        $this->productManager = $manager;
    }
    
    public function setProductId($id)
    {
        $this->productId = $id;
    }
    
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }
    
    public function prepare()
    {
        // Set master page template
        $this->setTemplate('html/masterPage.html');
        
        // Set master page fields
        $this->setTemplateField('pagename', 'Manage Product');
        
        // Load product data if editing
        if ($this->productId) {
            $this->productData = $this->productManager->getProduct($this->productId);
            if (!$this->productData) {
                throw new InvalidRequestException('Product not found');
            }
        }
        
        // Load product management content
        $htmlContent = file_get_contents('html/product.html');
        
        // Get all available tags
        $allTags = $this->productManager->getAllTags();
        $selectedTags = [];
        
        // Populate form with existing data if editing
        $productName = '';
        $productDescription = '';
        $productPrice = '';
        $productQuantity = '';
        $isAvailable = 'True';
        $pageTitle = 'Add New Product';
        $submitButtonText = 'Publish Product';
        $featuredImagePreview = '';
        $productIdHidden = '';
        
        if ($this->productId && $this->productData) {
            $productName = htmlspecialchars($this->productData['product_name']);
            $productDescription = $this->productData['description'];
            $productPrice = $this->productData['price'];
            $productQuantity = $this->productData['quantity'];
            $isAvailable = $this->productData['is_available'];
            $pageTitle = 'Edit Product: ' . htmlspecialchars($this->productData['product_name']);
            $submitButtonText = 'Update Product';
            
            // Get selected tags
            $selectedTags = $this->productManager->getProductTags($this->productId);
            
            // Get featured image
            $featuredImage = $this->productManager->getFeaturedImage($this->productId);
            if ($featuredImage) {
                $featuredImagePreview = '<div class="current-image">
                    <img src="##site##' . htmlspecialchars($featuredImage['image_url']) . '" alt="Current featured image" style="max-width: 200px; display: block; margin-bottom: 10px;">
                </div>';
            }
            
            // Hidden field for product ID
            $productIdHidden = '<input type="hidden" name="product_id" value="' . $this->productId . '">';
        }
        
        // Generate tags checkboxes
        $tagsHtml = '';
        foreach ($allTags as $tag) {
            $checked = in_array($tag, $selectedTags) ? 'checked' : '';
            $tagLabel = ucwords(str_replace('-', ' ', $tag));
            $tagsHtml .= '<label><input type="checkbox" name="tags[]" value="' . htmlspecialchars($tag) . '" ' . $checked . '> ' . htmlspecialchars($tagLabel) . '</label>' . "\n";
        }
        
        // If no tags exist yet, show default ones
        if (empty($tagsHtml)) {
            $defaultTags = ['plants', 'ceramic', 'indoor', 'outdoor', 'decorative', 'functional', 'modern', 'vintage', 'handmade', 'eco-friendly'];
            foreach ($defaultTags as $tag) {
                $checked = in_array($tag, $selectedTags) ? 'checked' : '';
                $tagLabel = ucwords(str_replace('-', ' ', $tag));
                $tagsHtml .= '<label><input type="checkbox" name="tags[]" value="' . htmlspecialchars($tag) . '" ' . $checked . '> ' . htmlspecialchars($tagLabel) . '</label>' . "\n";
            }
        }
        
        // Set selected status
        $availableSelected = ($isAvailable === 'True') ? 'selected' : '';
        $draftSelected = ($isAvailable === 'False') ? 'selected' : '';
        
        // Error message display
        $errorHtml = '';
        if (!empty($this->errorMessage)) {
            $errorHtml = '<div class="error-message" style="background: #fee; border: 1px solid #fcc; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #c00;">' . htmlspecialchars($this->errorMessage) . '</div>';
        }
        
        // Replace template tokens
        $htmlContent = str_replace('##page_title##', $errorHtml . $pageTitle, $htmlContent);
        $htmlContent = str_replace('##product_name##', $productName, $htmlContent);
        $htmlContent = str_replace('##product_description##', $productDescription, $htmlContent);
        $htmlContent = str_replace('##product_price##', $productPrice, $htmlContent);
        $htmlContent = str_replace('##product_quantity##', $productQuantity, $htmlContent);
        $htmlContent = str_replace('##available_selected##', $availableSelected, $htmlContent);
        $htmlContent = str_replace('##draft_selected##', $draftSelected, $htmlContent);
        $htmlContent = str_replace('##submit_button_text##', $submitButtonText, $htmlContent);
        $htmlContent = str_replace('##featured_image_preview##', $featuredImagePreview, $htmlContent);
        $htmlContent = str_replace('##tags_checkboxes##', $tagsHtml, $htmlContent);
        $htmlContent = str_replace('##product_id_hidden##', $productIdHidden, $htmlContent);
        
        // Set content in master page
        $this->setTemplateField('content', $htmlContent);
    }
}
