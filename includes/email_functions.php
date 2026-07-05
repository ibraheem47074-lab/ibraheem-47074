<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Send email using PHPMailer or mail() function
 * @param string $to - Recipient email
 * @param string $subject - Email subject
 * @param string $message - Email content
 * @param string $from - Sender email (optional)
 * @return bool - Success status
 */
function sendEmail($to, $subject, $message, $from = null) {
    // Set default sender if not provided
    if (!$from) {
        $from = ADMIN_EMAIL;
    }
    
    // Email headers
    $headers = "From: " . SITE_NAME . " <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email using mail() function (basic setup)
    // For production, consider using PHPMailer with SMTP
    return mail($to, $subject, $message, $headers);
}

/**
 * Send advertising inquiry email
 * @param array $data - Form data
 * @return bool - Success status
 */
function sendAdvertisingInquiry($data) {
    $to = ADVERTISING_EMAIL;
    $subject = "Advertising Inquiry: " . $data['company_name'];
    
    $message = "
    <html>
    <head><title>Advertising Inquiry</title></head>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #dc3545;'>New Advertising Inquiry</h2>
        <table style='border-collapse: collapse; width: 100%;'>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Company:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['company_name']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Contact Person:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['contact_name']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['email']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Phone:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['phone']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Budget:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['budget']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Message:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['message']}</td></tr>
        </table>
        <p style='margin-top: 20px;'><em>This inquiry was sent from " . SITE_URL . "</em></p>
    </body>
    </html>";
    
    return sendEmail($to, $subject, $message);
}

/**
 * Send support ticket email
 * @param array $data - Support ticket data
 * @return bool - Success status
 */
function sendSupportTicket($data) {
    $to = SUPPORT_EMAIL;
    $subject = "Support Request: " . $data['subject'];
    
    $message = "
    <html>
    <head><title>Support Request</title></head>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #dc3545;'>New Support Request</h2>
        <table style='border-collapse: collapse; width: 100%;'>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Name:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['name']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Email:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['email']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Priority:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['priority']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Subject:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['subject']}</td></tr>
            <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Message:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$data['message']}</td></tr>
        </table>
        <p style='margin-top: 20px;'><em>This request was sent from " . SITE_URL . "</em></p>
    </body>
    </html>";
    
    return sendEmail($to, $subject, $message);
}

/**
 * Send automatic response to user
 * @param string $to - User email
 * @param string $type - Response type (inquiry, support, etc.)
 * @return bool - Success status
 */
function sendAutoResponse($to, $type) {
    $subject = "Thank you for contacting " . SITE_NAME;
    
    switch($type) {
        case 'advertising':
            $message = "
            <html>
            <head><title>Advertising Inquiry Received</title></head>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #dc3545;'>Thank You for Your Advertising Inquiry!</h2>
                <p>Dear Valued Advertiser,</p>
                <p>We have received your advertising inquiry and will respond within 24-48 hours.</p>
                <p><strong>Our Advertising Options:</strong></p>
                <ul>
                    <li>Sidebar Banner Ads: $50/month</li>
                    <li>Header Banner Ads: $100/month</li>
                    <li>Sponsored Articles: $150/article</li>
                    <li>Live Stream Sponsorship: $200/month</li>
                </ul>
                <p>For immediate assistance, please call us at: <strong>+92-XXX-XXXXXXX</strong></p>
                <p>Best regards,<br>Advertising Team<br>" . SITE_NAME . "</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </body>
            </html>";
            break;
            
        case 'support':
            $message = "
            <html>
            <head><title>Support Request Received</title></head>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #dc3545;'>Support Request Received</h2>
                <p>Dear User,</p>
                <p>We have received your support request and our team will respond within 24 hours.</p>
                <p><strong>Your Ticket ID:</strong> #" . uniqid() . "</p>
                <p>For urgent matters, please contact us at: <strong>+92-XXX-XXXXXXX</strong></p>
                <p>Best regards,<br>Support Team<br>" . SITE_NAME . "</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply to this email.</p>
            </body>
            </html>";
            break;
            
        default:
            return false;
    }
    
    return sendEmail($to, $subject, $message);
}
?>
