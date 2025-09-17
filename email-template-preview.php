<?php
/**
 * Email Template Preview
 * Shows a preview of a selected email template
 */
$allowed_levels = array(9);
require_once 'bootstrap.php';
log_in_required($allowed_levels);

$template_id = isset($_GET['template']) ? $_GET['template'] : null;

if (!$template_id) {
    exit('Template ID required');
}

$emails = new \ProjectSend\Classes\Emails;
$template_data = $emails->getTemplateData($template_id);

if (!$template_data) {
    exit('Template not found');
}

$preview_content = $emails->previewTemplate($template_id);

if (!$preview_content) {
    exit('Could not generate template preview');
}

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Output the preview
echo $preview_content;
?>