<?php
/*
    Business Management Controller
    Handles business creation and management for sellers
*/

include_once 'models/UserModel.php';
include_once 'models/BusinessManagerModel.php';
include_once 'views/BusinessManageView.php';

class BusinessManageController extends AbstractController
{
    private $businessManager;
    
    protected function getView($isPostback)
    {
        $user = $this->getContext()->getUser();
        
        // Must be logged in
        if (!$user || !$user->isLoggedIn()) {
            $this->redirectTo('login', 'You must be logged in to manage businesses.');
            return null;
        }
        
        // Must be a seller (not Agora admin or buyer)
        if (!$user->isSeller()) {
            $this->redirectTo('home', 'Only sellers can create and manage businesses.');
            return null;
        }
        
        // Initialize business manager
        $this->businessManager = new BusinessManagerModel($this->getDB());
        
        // Check if user already has a business
        $existingBusiness = $this->businessManager->getUserBusiness($user->getUsername());
        
        // If POST request, handle form submission
        if ($isPostback) {
            // Check if it's a member management action
            $action = $_POST['action'] ?? '';
            if ($action === 'manage-member' && $existingBusiness) {
                return $this->handleMemberManagement($user, $existingBusiness);
            }
            
            return $this->handleFormSubmission($user, $existingBusiness);
        }
        
        // Create view
        $view = new BusinessManageView();
        $view->setBusinessManager($this->businessManager);
        $view->setExistingBusiness($existingBusiness);
        $view->setUser($user);
        
        return $view;
    }
    
    private function handleFormSubmission($user, $existingBusiness)
    {
        try {
            // Get form data
            $businessName = trim($_POST['business-name'] ?? '');
            $businessLocation = trim($_POST['business-location'] ?? '');
            $businessEmail = trim($_POST['business-email'] ?? '');
            $businessPhone = trim($_POST['business-phone'] ?? '');
            $shortDescription = trim($_POST['business-short-description'] ?? '');
            $details = trim($_POST['details'] ?? '');
            
            // Validate required fields
            if (empty($businessName)) {
                throw new Exception('Business name is required.');
            }
            
            if (empty($businessLocation)) {
                throw new Exception('Business location is required.');
            }
            
            // Validate email if provided
            if (!empty($businessEmail) && !filter_var($businessEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address.');
            }
            
            // Check if business name already exists
            $excludeId = $existingBusiness ? $existingBusiness['business_id'] : null;
            if ($this->businessManager->businessNameExists($businessName, $excludeId)) {
                throw new Exception('A business with this name already exists. Please choose a different name.');
            }
            
            // Create or update business
            if ($existingBusiness) {
                // Update existing business - must be Administrator
                if ($existingBusiness['role_name'] !== 'Administrator') {
                    throw new Exception('Only business administrators can edit business details. Sellers cannot modify business information.');
                }
                
                $businessId = $existingBusiness['business_id'];
                
                // Handle logo upload before business update
                $imageMessages = [];
                if (isset($_FILES['business-logo']) && $_FILES['business-logo']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $this->handleLogoUpload($businessId, $_FILES['business-logo']);
                        $imageMessages[] = 'Logo uploaded successfully.';
                    } catch (Exception $e) {
                        $imageMessages[] = 'Logo upload failed: ' . $e->getMessage();
                    }
                }
                
                // Handle banner upload before business update
                if (isset($_FILES['business-banner']) && $_FILES['business-banner']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $this->handleBannerUpload($businessId, $_FILES['business-banner']);
                        $imageMessages[] = 'Banner uploaded successfully.';
                    } catch (Exception $e) {
                        $imageMessages[] = 'Banner upload failed: ' . $e->getMessage();
                    }
                }
                
                // Update business details
                $success = $this->businessManager->updateBusiness(
                    $businessId,
                    $businessName,
                    $businessLocation,
                    $details,
                    $businessEmail,
                    $businessPhone,
                    $shortDescription
                );
                
                // Build success message - don't fail if update returns false (no rows changed)
                // as images may have been successfully uploaded
                if (!empty($imageMessages)) {
                    // If we have image messages, show them
                    $message = 'Business updated successfully! ' . implode(' ', $imageMessages);
                } else {
                    // No images uploaded, just business details
                    $message = 'Business updated successfully!';
                }
            } else {
                // Create new business
                $businessId = $this->businessManager->createBusiness(
                    $businessName,
                    $businessLocation,
                    $details,
                    $user->getUsername(),
                    $businessEmail,
                    $businessPhone,
                    $shortDescription
                );
                
                if (!$businessId) {
                    throw new Exception('Failed to create business.');
                }
                
                // Handle logo upload for new business
                $imageMessages = [];
                if (isset($_FILES['business-logo']) && $_FILES['business-logo']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $this->handleLogoUpload($businessId, $_FILES['business-logo']);
                        $imageMessages[] = 'Logo uploaded successfully.';
                    } catch (Exception $e) {
                        $imageMessages[] = 'Logo upload failed: ' . $e->getMessage();
                    }
                }
                
                // Handle banner upload for new business
                if (isset($_FILES['business-banner']) && $_FILES['business-banner']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $this->handleBannerUpload($businessId, $_FILES['business-banner']);
                        $imageMessages[] = 'Banner uploaded successfully.';
                    } catch (Exception $e) {
                        $imageMessages[] = 'Banner upload failed: ' . $e->getMessage();
                    }
                }
                
                $message = 'Business created successfully! Your business is pending admin approval before you can start adding products.';
                if (!empty($imageMessages)) {
                    $message .= ' ' . implode(' ', $imageMessages);
                }
            }
            
            // Redirect back to business manage page with success message
            $this->redirectTo('business-manage', $message);
            return null;
            
        } catch (Exception $e) {
            // On error, show the form again with error message
            $view = new BusinessManageView();
            $view->setBusinessManager($this->businessManager);
            $view->setExistingBusiness($existingBusiness);
            $view->setUser($user);
            $view->setErrorMessage($e->getMessage());
            return $view;
        }
    }
    
    /**
     * Handle logo image upload and convert to WebP
     */
    private function handleLogoUpload($businessId, $file)
    {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid logo file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('Logo file is too large. Maximum size is 5MB.');
        }
        
        // Create business images directory if it doesn't exist
        $businessDir = "assets/images/businesses/{$businessId}";
        if (!is_dir($businessDir)) {
            mkdir($businessDir, 0755, true);
        }
        
        // Convert and save as WebP
        $logoPath = "{$businessDir}/logo.webp";
        $this->convertToWebP($file['tmp_name'], $logoPath, 300, 300);
        
        return true;
    }
    
    /**
     * Handle banner image upload and convert to WebP
     */
    private function handleBannerUpload($businessId, $file)
    {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid banner file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('Banner file is too large. Maximum size is 5MB.');
        }
        
        // Create business images directory if it doesn't exist
        $businessDir = "assets/images/businesses/{$businessId}";
        if (!is_dir($businessDir)) {
            mkdir($businessDir, 0755, true);
        }
        
        // Convert and save as WebP (regular size)
        $bannerPath = "{$businessDir}/banner.webp";
        $this->convertToWebP($file['tmp_name'], $bannerPath, 1200, 400);
        
        // Also create small version
        $bannerSmallPath = "{$businessDir}/banner_small.webp";
        $this->convertToWebP($file['tmp_name'], $bannerSmallPath, 600, 200);
        
        return true;
    }
    
    /**
     * Convert image to WebP format
     */
    private function convertToWebP($sourcePath, $destinationPath, $maxWidth, $maxHeight)
    {
        // Validate source file exists
        if (!file_exists($sourcePath)) {
            throw new Exception('Source image file not found.');
        }
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new Exception('Invalid image file.');
        }
        
        $mimeType = $imageInfo['mime'];
        
        // Create image resource based on type
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
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new Exception('Unsupported image format: ' . $mimeType);
        }
        
        if ($image === false) {
            throw new Exception('Failed to create image resource from file.');
        }
        
        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        if ($originalWidth === false || $originalHeight === false) {
            imagedestroy($image);
            throw new Exception('Failed to get image dimensions.');
        }
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = round($originalWidth * $ratio);
        $newHeight = round($originalHeight * $ratio);
        
        // Create new image with new dimensions
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($newImage === false) {
            imagedestroy($image);
            throw new Exception('Failed to create resized image resource.');
        }
        
        // Preserve transparency for PNG and WebP
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        
        // Resize
        $resized = imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        if ($resized === false) {
            imagedestroy($image);
            imagedestroy($newImage);
            throw new Exception('Failed to resize image.');
        }
        
        // Save as WebP
        $saved = imagewebp($newImage, $destinationPath, 90);
        
        // Free memory
        imagedestroy($image);
        imagedestroy($newImage);
        
        if ($saved === false) {
            throw new Exception('Failed to save WebP image to ' . $destinationPath);
        }
        
        // Verify file was actually created
        if (!file_exists($destinationPath)) {
            throw new Exception('Image file was not created at ' . $destinationPath);
        }
        
        return true;
    }
    
    /**
     * Handle business member management
     */
    private function handleMemberManagement($user, $existingBusiness)
    {
        try {
            // Only administrators can manage members
            if ($existingBusiness['role_name'] !== 'Administrator') {
                throw new Exception('Only administrators can manage business members.');
            }
            
            $businessId = $existingBusiness['business_id'];
            $targetUsername = trim($_POST['username'] ?? '');
            $roleName = $_POST['role-name'] ?? '';
            $isActive = $_POST['is-active'] ?? 'True';
            
            // Validate
            if (empty($targetUsername)) {
                throw new Exception('Username is required.');
            }
            
            if (!in_array($roleName, ['Administrator', 'Seller'])) {
                throw new Exception('Invalid role selected.');
            }
            
            // Check if user exists
            $userCheck = $this->getDB()->queryPrepared("SELECT username FROM users WHERE username = ?", [$targetUsername]);
            if (empty($userCheck)) {
                throw new Exception('User not found.');
            }
            
            // Update or add member
            $this->businessManager->updateBusinessMember($targetUsername, $businessId, $roleName, $isActive);
            
            $this->redirectTo('business-manage', 'Member role updated successfully!');
            return null;
            
        } catch (Exception $e) {
            $view = new BusinessManageView();
            $view->setBusinessManager($this->businessManager);
            $view->setExistingBusiness($existingBusiness);
            $view->setUser($user);
            $view->setErrorMessage($e->getMessage());
            return $view;
        }
    }
}
