<?php
/*
 * Business Manage View
 * This view is responsible for rendering the business creation/editing form.
 */

class BusinessManageView extends AbstractView
{
    private $businessManager;
    private $existingBusiness;
    private $user;
    private $errorMessage = '';
    
    public function setBusinessManager($manager)
    {
        $this->businessManager = $manager;
    }
    
    public function setExistingBusiness($business)
    {
        $this->existingBusiness = $business;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
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
        $this->setTemplateField('pagename', 'Manage Business');
        
        // Load business management content
        $htmlContent = file_get_contents('html/business-manage-full.html');
        
        // Populate form with existing data if editing
        $businessName = '';
        $businessLocation = '';
        $businessEmail = '';
        $businessPhone = '';
        $shortDescription = '';
        $businessDetails = '';
        $pageTitle = 'Create Your Business';
        $submitButtonText = 'Create Business';
        $statusMessage = '';
        $approvalStatus = '';
        $businessId = '';
        $logoPreview = '';
        $bannerPreview = '';
        $businessRolesSection = '';
        
        // Get stats
        $accountAge = 'N/A';
        $totalOrders = '0';
        $totalProducts = '0';
        
        if ($this->existingBusiness) {
            $businessId = $this->existingBusiness['business_id'];
            $businessName = htmlspecialchars($this->existingBusiness['business_name']);
            $businessLocation = htmlspecialchars($this->existingBusiness['business_location']);
            $businessEmail = htmlspecialchars($this->existingBusiness['business_email'] ?? '');
            $businessPhone = htmlspecialchars($this->existingBusiness['business_phone'] ?? '');
            $shortDescription = htmlspecialchars($this->existingBusiness['short_description'] ?? '');
            $businessDetails = $this->existingBusiness['details'] ?? '';
            $pageTitle = 'Manage Your Business';
            $submitButtonText = 'Update Business';
            
            // Get business stats
            $stats = $this->businessManager->getBusinessStats($businessId);
            $totalProducts = $stats['total_products'];
            $totalOrders = $stats['total_orders'];
            if ($stats['created_at']) {
                $accountAge = date('M Y', strtotime($stats['created_at']));
            }
            
            // Check for existing images
            $logoPath = "assets/images/businesses/{$businessId}/logo.webp";
            $bannerPath = "assets/images/businesses/{$businessId}/banner.webp";
            
            if (file_exists($logoPath)) {
                $logoPreview = '<div class="logo-preview"><img src="##site##' . $logoPath . '" alt="Current Logo"></div>';
            }
            
            if (file_exists($bannerPath)) {
                $bannerPreview = '<div class="banner-preview"><img src="##site##' . $bannerPath . '" alt="Current Banner"></div>';
            }
            
            // Show approval status
            if ($this->existingBusiness['is_active'] === 'True') {
                $businessUrl = '##site##business/' . $businessId;
                $approvalStatus = 'Active';
            } else {
                $approvalStatus = 'Pending';
            }
            
            // Business roles section (only for active businesses)
            if ($this->existingBusiness['is_active'] === 'True' && $this->existingBusiness['role_name'] === 'Administrator') {
                $businessRolesSection = $this->generateBusinessRolesSection($businessId);
            }
        }
        
        // Error message display
        if (!empty($this->errorMessage)) {
            $statusMessage = '<div class="status-box status-inactive">' . 
                             '<strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . 
                             '</div>';
        }
        
        // Replace template tokens
        $htmlContent = str_replace('##page_title##', $pageTitle, $htmlContent);
        $htmlContent = str_replace('##business_name##', $businessName, $htmlContent);
        $htmlContent = str_replace('##business_location##', $businessLocation, $htmlContent);
        $htmlContent = str_replace('##business_email##', $businessEmail, $htmlContent);
        $htmlContent = str_replace('##business_phone##', $businessPhone, $htmlContent);
        $htmlContent = str_replace('##short_description##', $shortDescription, $htmlContent);
        $htmlContent = str_replace('##business_details##', $businessDetails, $htmlContent);
        $htmlContent = str_replace('##submit_button_text##', $submitButtonText, $htmlContent);
        $htmlContent = str_replace('##status_message##', $statusMessage, $htmlContent);
        $htmlContent = str_replace('##approval_status##', $approvalStatus, $htmlContent);
        $htmlContent = str_replace('##account_age##', $accountAge, $htmlContent);
        $htmlContent = str_replace('##total_orders##', $totalOrders, $htmlContent);
        $htmlContent = str_replace('##total_products##', $totalProducts, $htmlContent);
        $htmlContent = str_replace('##logo_preview##', $logoPreview, $htmlContent);
        $htmlContent = str_replace('##banner_preview##', $bannerPreview, $htmlContent);
        $htmlContent = str_replace('##business_roles_section##', $businessRolesSection, $htmlContent);
        $htmlContent = str_replace('##business_id##', $businessId, $htmlContent);
        
        // Set content in master page
        $this->setTemplateField('content', $htmlContent);
    }
    
    /**
     * Generate business roles management section
     */
    private function generateBusinessRolesSection($businessId)
    {
        $members = $this->businessManager->getBusinessMembers($businessId);
        
        $html = '<div class="business-roles-section">
            <h2>Manage Business Roles</h2>
            <div class="form-section">
            <div class="current-members">
                <h3>Current Members</h3>
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($members as $member) {
            $statusBadge = $member['is_active'] === 'True' 
                ? '<span style="color: green;">● Active</span>' 
                : '<span style="color: gray;">● Inactive</span>';
            
            $html .= '<tr>
                <td>' . htmlspecialchars($member['username']) . '</td>
                <td>' . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . '</td>
                <td>' . htmlspecialchars($member['email']) . '</td>
                <td>' . htmlspecialchars($member['role_name']) . '</td>
                <td>' . $statusBadge . '</td>
                <td>';

            if ($this->user->getUsername() !== $member['username']) {
                $html .= '<form action="" method="post" onsubmit="return confirm(\'Are you sure you want to remove this member?\');">
                    <input type="hidden" name="action" value="remove-member">
                    <input type="hidden" name="username" value="' . htmlspecialchars($member['username']) . '">
                    <button type="submit" class="button secondary">Remove</button>
                </form>';
            }

            $html .= '</td>
            </tr>';
        }
        
        $html .= '</tbody>
                </table>
            </div>
            
            <div class="roles-form">
                <h3>Add/Update Member</h3>
                <form action="" method="post">
                    <input type="hidden" name="action" value="manage-member">
                    <div class="roles-grid">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" placeholder="jane.doe" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="role-name">Role *</label>
                            <select name="role-name" required>
                                <option value="">Select role...</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Seller">Seller</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is-active">Status *</label>
                            <select name="is-active" required>
                                <option value="True">Active</option>
                                <option value="False">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="role-actions">
                        <button type="submit" class="btn-primary">Assign/Update Role</button>
                    </div>
                </form>
            </div></div>
        </div>';
        
        return $html;
    }
}
