<?php
/*
    Admin Panel View
    Shows pending business approvals and admin functions
*/

class AdminPanelView extends AbstractView
{
    private $businessManager;
    private $errorMessage = '';
    
    public function setBusinessManager($manager)
    {
        $this->businessManager = $manager;
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
        $this->setTemplateField('pagename', 'Admin Panel');
        
        $htmlContent = '<h1>Admin Panel</h1>';
        
        // Error message
        if (!empty($this->errorMessage)) {
            $htmlContent .= '<div class="status-box status-inactive" style="margin-bottom: 20px;">' . 
                           '<strong>Error:</strong> ' . htmlspecialchars($this->errorMessage) . 
                           '</div>';
        }
        
        // Get pending businesses
        $pendingBusinesses = $this->businessManager->getPendingBusinesses();
        $allBusinesses = $this->businessManager->getAllBusinesses();
        
        // Pending approvals section
        $htmlContent .= '<div class="admin-section" style="margin-bottom: 30px;">
            <h2>Pending Business Approvals</h2>';
        
        if (empty($pendingBusinesses)) {
            $htmlContent .= '<p>No businesses pending approval.</p>';
        } else {
            $htmlContent .= '<table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; text-align: left;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Business Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Location</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Owner</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Created</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($pendingBusinesses as $business) {
                $htmlContent .= '<tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['business_name']) . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['business_location']) . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['owner_username'] ?? 'N/A') . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['created_at']) . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="business_id" value="' . $business['business_id'] . '">
                            <button type="submit" name="action" value="approve" class="btn-primary" style="padding: 5px 10px; margin-right: 5px;">Approve</button>
                        </form>
                    </td>
                </tr>';
            }
            
            $htmlContent .= '</tbody></table>';
        }
        
        $htmlContent .= '</div>';
        
        // All businesses section
        $htmlContent .= '<div class="admin-section">
            <h2>All Businesses</h2>
            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; text-align: left;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Business Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Location</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Created</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($allBusinesses as $business) {
            $statusBadge = $business['is_active'] === 'True' 
                ? '<span style="color: green;">● Active</span>' 
                : '<span style="color: orange;">● Pending</span>';
            
            $htmlContent .= '<tr>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['business_name']) . '</td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['business_location']) . '</td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . $statusBadge . '</td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($business['created_at']) . '</td>
                <td style="padding: 10px; border: 1px solid #ddd;">';
            
            if ($business['is_active'] === 'True') {
                $htmlContent .= '<form method="post" style="display: inline;">
                    <input type="hidden" name="business_id" value="' . $business['business_id'] . '">
                    <button type="submit" name="action" value="deactivate" class="btn-secondary" style="padding: 5px 10px;">Deactivate</button>
                </form>';
            } else {
                $htmlContent .= '<form method="post" style="display: inline;">
                    <input type="hidden" name="business_id" value="' . $business['business_id'] . '">
                    <button type="submit" name="action" value="approve" class="btn-primary" style="padding: 5px 10px;">Approve</button>
                </form>';
            }
            
            $htmlContent .= '</td></tr>';
        }
        
        $htmlContent .= '</tbody></table></div>';
        
        // Add CSS
        $htmlContent .= '<style>
.admin-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-box {
    padding: 15px;
    border-radius: 4px;
}

.status-inactive {
    background: #f8d7da;
    border: 1px solid #dc3545;
    color: #721c24;
}
</style>';
        
        // Set content in master page
        $this->setTemplateField('content', $htmlContent);
    }
}
