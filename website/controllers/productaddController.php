<?php
/*
 * Add/Edit Product Controller
 * This controller is responsible for handling the creation and editing of products.
 */

include_once 'models/UserModel.php';
include_once 'models/ProductManagerModel.php';
include_once 'views/ProductAddView.php';

class ProductAddController extends AbstractController
{
    private $productManager;
    private $productId;
    private $businessId;
    
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in
        if (!$user || !$user->isLoggedIn()) {
            $this->redirectTo('login', 'You must be logged in to manage products.');
            return null;
        }
        
        // Must be a seller
        if (!$user->isSeller() && !$user->isAdmin()) {
            $this->redirectTo('home', 'Only sellers can manage products.');
            return null;
        }
        
        // Initialise product manager
        $this->productManager = new ProductManagerModel($this->getDB());
        
        $this->productId = null;
        
        // Try to get ID from URI first (e.g., /product-edit/5)
        try {
            $this->productId = $this->getURI()->getID();
        } catch (InvalidRequestException $e) {
            // No ID in URI
            $this->productId = null;
        }
        
        // Fall back to GET parameter if no URI ID
        if (!$this->productId && isset($_GET['id'])) {
            $this->productId = intval($_GET['id']);
        }
        
        // Check if user has a business
        $this->businessId = $this->productManager->getUserBusinessId($user->getUsername());
        if (!$this->businessId) {
            // Redirect to business creation page
            $this->redirectTo('business-manage', 'You must create or join a business before adding products.');
            return null;
        }
        
        // If editing, verify permissions
        if ($this->productId) {
            if (!$this->productManager->userCanEditProduct($user->getUsername(), $this->productId)) {
                $this->redirectTo('home', 'You do not have permission to edit this product.');
                return null;
            }
        }
        
        // If POST request, handle form submission
        if ($isPostback) {
            return $this->handleFormSubmission($user);
        }
        
        // Create view
        $view = new ProductAddView();
        $view->setProductManager($this->productManager);
        $view->setProductId($this->productId);
        
        return $view;
    }
    
    private function handleFormSubmission($user)
    {
        try {
            // Get form data
            $productName = trim($_POST['product-name'] ?? '');
            $description = $_POST['description'] ?? '';
            $price = floatval($_POST['price'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 0);
            $isAvailable = $_POST['is-available'] ?? 'False';
            $categories = $_POST['categories'] ?? [];
            $action = $_POST['action'] ?? 'save';

            // Handle delete action
            if ($action === 'delete') {
                if (!$this->productId) {
                    throw new Exception('Product ID is missing.');
                }
                if (!$this->productManager->userCanEditProduct($user->getUsername(), $this->productId)) {
                    throw new Exception('You do not have permission to delete this product.');
                }
                
                $this->productManager->deleteProduct($this->productId);
                
                // Redirect to business management page with a success message
                $this->redirectTo('business-manage', 'Product has been successfully deleted.');
                return null;
            }
            
            // Validate required fields
            if (empty($productName)) {
                throw new Exception('Product name is required.');
            }
            
            if ($price <= 0) {
                throw new Exception('Price must be greater than zero.');
            }
            
            if ($quantity < 0) {
                throw new Exception('Quantity cannot be negative.');
            }
            
            // If action is 'publish', make sure it's available
            if ($action === 'publish') {
                $isAvailable = 'True';
            }
            
            // Create or update product
            if ($this->productId) {
                // Update existing product
                $success = $this->productManager->updateProduct(
                    $this->productId,
                    $productName,
                    $description,
                    $price,
                    $quantity,
                    $isAvailable
                );
                
                $productId = $this->productId;
                $message = 'Product updated successfully!';
            } else {
                // Create new product
                $productId = $this->productManager->createProduct(
                    $this->businessId,
                    $productName,
                    $description,
                    $price,
                    $quantity,
                    $isAvailable
                );
                
                if (!$productId) {
                    throw new Exception('Failed to create product.');
                }
                
                $message = 'Product created successfully!';
            }
            
            // Update categories
            $this->productManager->addProductCategories($productId, $categories);
            
            // Handle featured image upload
            if (isset($_FILES['featured-image']) && $_FILES['featured-image']['error'] === UPLOAD_ERR_OK) {
                $this->handleFeaturedImageUpload($productId);
            }
            
            // Handle additional images upload
            if (isset($_FILES['product-images']) && is_array($_FILES['product-images']['name'])) {
                $this->handleAdditionalImagesUpload($productId);
            }
            
            // Redirect to product page or back to edit
            if ($action === 'publish') {
                $this->redirectTo('product/' . $productId, $message);
            } else {
                // If editing, redirect back to edit page; if creating, redirect to edit the new product
                $this->redirectTo('product-edit/' . $productId, $message);
            }
            return null;
            
        } catch (Exception $e) {
            // On error, show the form again with error message
            $view = new ProductAddView();
            $view->setProductManager($this->productManager);
            $view->setProductId($this->productId);
            $view->setErrorMessage($e->getMessage());
            return $view;
        }
    }
    
    private function handleFeaturedImageUpload($productId)
    {
        $file = $_FILES['featured-image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid image format. Please upload a JPEG, PNG, GIF, or WebP image.');
        }
        
        // Create directory if it doesn't exist
        $uploadDir = 'assets/images/products/' . $productId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'featured_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload image.');
        }
        
        // Convert to WebP and create thumbnails
        $webpPath = $this->convertToWebP($targetPath, $uploadDir);
        $thumbPath = $this->createThumbnail($webpPath, $uploadDir, 300, 300);
        $blurPath = $this->createBlurredPreview($webpPath, $uploadDir);
        
        // Save to database with sort_order = 0 (featured)
        $this->productManager->addProductImage($productId, $webpPath, $thumbPath, $blurPath, 0);
        
        // Delete original if not WebP
        if ($extension !== 'webp') {
            unlink($targetPath);
        }
    }
    
    private function handleAdditionalImagesUpload($productId)
    {
        $files = $_FILES['product-images'];
        $fileCount = count($files['name']);
        
        // Create directory if it doesn't exist
        $uploadDir = 'assets/images/products/' . $productId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $sortOrder = 1; // Start from 1 (0 is for featured)
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $files['tmp_name'][$i]);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                continue; // Skip invalid files
            }
            
            // Generate unique filename
            $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $filename = 'gallery_' . time() . '_' . $i . '.' . $extension;
            $targetPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                continue; // Skip failed uploads
            }
            
            // Convert to WebP and create thumbnails
            $webpPath = $this->convertToWebP($targetPath, $uploadDir);
            $thumbPath = $this->createThumbnail($webpPath, $uploadDir, 300, 300);
            $blurPath = $this->createBlurredPreview($webpPath, $uploadDir);
            
            // Save to database
            $this->productManager->addProductImage($productId, $webpPath, $thumbPath, $blurPath, $sortOrder);
            
            // Delete original if not WebP
            if ($extension !== 'webp') {
                unlink($targetPath);
            }
            
            $sortOrder++;
        }
    }
    
    private function convertToWebP($sourcePath, $uploadDir)
    {
        $imageInfo = getimagesize($sourcePath);
        $mimeType = $imageInfo['mime'];
        
        // Create image resource from source
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                return $sourcePath; // Already WebP
            default:
                throw new Exception('Unsupported image type');
        }
        
        // Generate WebP filename
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '.webp';
        $webpPath = $uploadDir . $filename;
        
        // Convert to WebP
        imagewebp($image, $webpPath, 85);
        imagedestroy($image);
        
        return $webpPath;
    }
    
    private function createThumbnail($sourcePath, $uploadDir, $maxWidth, $maxHeight)
    {
        $image = imagecreatefromwebp($sourcePath);
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        $newWidth = intval($origWidth * $ratio);
        $newHeight = intval($origHeight * $ratio);
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save thumbnail
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_thumb.webp';
        $thumbPath = $uploadDir . $filename;
        imagewebp($thumbnail, $thumbPath, 80);
        
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    private function createBlurredPreview($sourcePath, $uploadDir)
    {
        $image = imagecreatefromwebp($sourcePath);
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        
        // Create small blurred version (20x20)
        $blurred = imagecreatetruecolor(20, 20);
        imagecopyresampled($blurred, $image, 0, 0, 0, 0, 20, 20, $origWidth, $origHeight);
        
        // Apply blur filter
        for ($i = 0; $i < 3; $i++) {
            imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        // Save blurred preview
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_blur.webp';
        $blurPath = $uploadDir . $filename;
        imagewebp($blurred, $blurPath, 50);
        
        imagedestroy($image);
        imagedestroy($blurred);
        
        return $blurPath;
    }
}
