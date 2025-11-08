<?php
/*
 * Product Add/Edit View
 * This view is responsible for rendering the product creation/editing form.
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
        
        // Get all available categories
        $allCategories = $this->productManager->getAllCategories();
        $selectedCategories = [];
        
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
        $deleteButton = '';
        
        if ($this->productId && $this->productData) {
            $productName = htmlspecialchars($this->productData['product_name']);
            $productDescription = $this->productData['description'];
            $productPrice = $this->productData['price'];
            $productQuantity = $this->productData['quantity'];
            $isAvailable = $this->productData['is_available'];
            $pageTitle = 'Edit Product: ' . htmlspecialchars($this->productData['product_name']);
            $submitButtonText = 'Update Product';
            
            // Add delete button
            $deleteButton = '<button type="submit" name="action" value="delete" class="button secondary" onclick="return confirm(\'Are you sure you want to permanently delete this product? This cannot be undone.\')">Delete Product</button>';

            // Get selected categories
            $selectedCategories = $this->productManager->getProductCategories($this->productId);
            
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
        
        // Generate categories checkboxes
        $categoriesHtml = '';
        foreach ($allCategories as $category) {
            $checked = in_array($category, $selectedCategories) ? 'checked' : '';
            $categoryLabel = ucwords(str_replace('-', ' ', $category));
            $categoriesHtml .= '<label><input type="checkbox" name="categories[]" value="' . htmlspecialchars($category) . '" ' . $checked . '> ' . htmlspecialchars($categoryLabel) . '</label>' . "\n";
        }
        
        // If no categories exist yet, show default ones
        if (empty($categoriesHtml)) {
            $defaultCategories = ['plants', 'ceramic', 'indoor', 'outdoor', 'decorative', 'functional', 'modern', 'vincategorye', 'handmade', 'eco-friendly'];
            foreach ($defaultCategories as $category) {
                $checked = in_array($category, $selectedCategories) ? 'checked' : '';
                $categoryLabel = ucwords(str_replace('-', ' ', $category));
                $categoriesHtml .= '<label><input type="checkbox" name="categories[]" value="' . htmlspecialchars($category) . '" ' . $checked . '> ' . htmlspecialchars($categoryLabel) . '</label>' . "\n";
            }
        }
        
        // Set selected status
        $availableSelected = ($isAvailable === 'True') ? 'selected' : '';
        $draftSelected = ($isAvailable === 'False') ? 'selected' : '';
        
        // Error message display
        $errorHtml = '';
        if (!empty($this->errorMessage)) {
            $errorHtml = '<div class="error-message">' . htmlspecialchars($this->errorMessage) . '</div>';
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
        $htmlContent = str_replace('##delete_button##', $deleteButton, $htmlContent);
        $htmlContent = str_replace('##featured_image_preview##', $featuredImagePreview, $htmlContent);
        $htmlContent = str_replace('##categories_checkboxes##', $categoriesHtml, $htmlContent);
        $htmlContent = str_replace('##product_id_hidden##', $productIdHidden, $htmlContent);
        
        // Set content in master page
        $this->setTemplateField('content', $htmlContent);
    }
}
