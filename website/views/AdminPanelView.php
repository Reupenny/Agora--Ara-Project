<?php
/*
 * Admin Panel View
 * This view is responsible for displaying pending business approvals and other administration functions.
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
        
        
        $htmlContent .= '<div class="content-section">';
        $htmlContent .= '<div><h2>All Businesses</h2>';
        if (empty($pendingBusinesses)) {
            $htmlContent .= '<p>No businesses pending approval.</p>';
        } else {
            $htmlContent .= '<p>There is ' . count($pendingBusinesses) . ' businesses pending approval.</p>';
        }


            $htmlContent .= '<table class="members-table">
                <thead>
                    <tr>
                        <th>Business Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($allBusinesses as $business) {
            $statusBadge = $business['is_active'] === 'True' 
                ? '<span style="color: green;">● Active</span>' 
                : '<span style="color: orange;">● Pending</span>';
            
            $htmlContent .= '<tr>
                <td>' . htmlspecialchars($business['business_name']) . '</td>
                <td>' . htmlspecialchars($business['business_location']) . '</td>
                <td>' . $statusBadge . '</td>
                <td>' . htmlspecialchars($business['created_at']) . '</td>
                <td>';
            
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
        
        $htmlContent .= '</tbody></table></div></div>';
        
        // Set content in master page
        $this->setTemplateField('content', $htmlContent);
    }
}
