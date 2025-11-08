<?php
/*
	Profile Controller
	Handles user profile viewing and editing
*/

include_once 'models/UserModel.php';
include_once 'models/ProfileModel.php';
include_once 'views/ProfileView.php';

class ProfileController extends AbstractController {
	
	protected function getView($isPostback) {
		// Check if user is logged in
		$user = $this->getContext()->getUser();
		if (!$user || !$user->isLoggedIn()) {
			$this->redirectTo('login', 'Please login to view your profile');
			return null;
		}
		
		// If POST request, handle profile update
		if ($isPostback) {
			return $this->handleProfileUpdate($user);
		}
		
		// Load user profile data
		$model = new ProfileModel($this->getDB());
		$model->loadUserProfile($user->getUsername());
		
		$view = new ProfileView();
		$view->setModel($model);
		$view->setUser($user);
		$view->setErrorMessages([]);
		$view->setSuccessMessage('');
		
		return $view;
	}
	
	private function handleProfileUpdate($user) {
		// Get form data from POST
		$data = [
			'first_name' => $_POST['first-name'] ?? '',
			'last_name' => $_POST['last-name'] ?? '',
			'email' => $_POST['email'] ?? '',
			'current_password' => $_POST['current-password'] ?? '',
			'new_password' => $_POST['new-password'] ?? '',
			'confirm_password' => $_POST['confirm-password'] ?? ''
		];
		
		// Handle profile image upload
		$imageUploadSuccess = true;
		$imageError = '';
		
		if (isset($_FILES['profile-image']) && $_FILES['profile-image']['error'] === UPLOAD_ERR_OK) {
			$uploadResult = $this->handleImageUpload($_FILES['profile-image'], $user->getUsername());
			if (!$uploadResult['success']) {
				$imageUploadSuccess = false;
				$imageError = $uploadResult['error'];
			}
		}
		
		// Update profile
		$model = new ProfileModel($this->getDB());
		
		if ($model->updateProfile($user->getUsername(), $data)) {
			// Reload profile data
			$model->loadUserProfile($user->getUsername());
			
			// Update session data
			$user->createSession($user->getUsername());
			
			// Prepare success message
			$successMessage = 'Profile updated successfully!';
			if (!$imageUploadSuccess) {
				$successMessage .= ' However, there was an issue with the image: ' . $imageError;
			}
			
			// Show success message
			$view = new ProfileView();
			$view->setModel($model);
			$view->setUser($user);
			$view->setErrorMessages([]);
			$view->setSuccessMessage($successMessage);
			
			return $view;
		} else {
			// Show errors
			$model->loadUserProfile($user->getUsername());
			
			$errors = $model->getErrorMessages();
			if (!$imageUploadSuccess) {
				$errors[] = 'Image upload error: ' . $imageError;
			}
			
			$view = new ProfileView();
			$view->setModel($model);
			$view->setUser($user);
			$view->setErrorMessages($errors);
			$view->setSuccessMessage('');
			$view->setFormData($data);
			
			return $view;
		}
	}
	
	private function handleImageUpload($file, $username) {
		// Validate file type
		$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
		$fileType = mime_content_type($file['tmp_name']);
		
		if (!in_array($fileType, $allowedTypes)) {
			return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.'];
		}
		
		// Validate file size (max 5MB)
		$maxSize = 5 * 1024 * 1024; // 5MB
		if ($file['size'] > $maxSize) {
			return ['success' => false, 'error' => 'File is too large. Maximum size is 5MB.'];
		}
		
		// Create image resource based on file type
		switch ($fileType) {
			case 'image/jpeg':
			case 'image/jpg':
				$image = imagecreatefromjpeg($file['tmp_name']);
				break;
			case 'image/png':
				$image = imagecreatefrompng($file['tmp_name']);
				break;
			case 'image/gif':
				$image = imagecreatefromgif($file['tmp_name']);
				break;
			case 'image/webp':
				$image = imagecreatefromwebp($file['tmp_name']);
				break;
			default:
				return ['success' => false, 'error' => 'Unsupported image format.'];
		}
		
		if (!$image) {
			return ['success' => false, 'error' => 'Failed to process image.'];
		}
		
		// Get original dimensions
		$originalWidth = imagesx($image);
		$originalHeight = imagesy($image);
		
		// Calculate new dimensions (max 500x500, maintaining aspect ratio)
		$maxDimension = 500;
		if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
			if ($originalWidth > $originalHeight) {
				$newWidth = $maxDimension;
				$newHeight = (int)($originalHeight * ($maxDimension / $originalWidth));
			} else {
				$newHeight = $maxDimension;
				$newWidth = (int)($originalWidth * ($maxDimension / $originalHeight));
			}
		} else {
			$newWidth = $originalWidth;
			$newHeight = $originalHeight;
		}
		
		// Create resized image
		$resizedImage = imagecreatetruecolor($newWidth, $newHeight);
		
		// Preserve transparency for PNG and GIF
		imagealphablending($resizedImage, false);
		imagesavealpha($resizedImage, true);
		
		// Resize the image
		imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
		
		// Save as WebP
		$uploadDir = 'assets/images/users/';
		$filename = $username . '.webp';
		$filepath = $uploadDir . $filename;
		
		// Delete old profile image if exists
		$oldFiles = glob($uploadDir . $username . '.*');
		foreach ($oldFiles as $oldFile) {
			if (file_exists($oldFile)) {
				unlink($oldFile);
			}
		}
		
		// Save the WebP image
		$saved = imagewebp($resizedImage, $filepath, 90); // 90% quality
		
		// Clean up
		imagedestroy($image);
		imagedestroy($resizedImage);
		
		if (!$saved) {
			return ['success' => false, 'error' => 'Failed to save image.'];
		}
		
		return ['success' => true, 'filename' => $filename];
	}
}
?>
