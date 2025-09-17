<?php
/**
 * Email Template Metadata
 * Defines available email templates with their information
 */

return [
    'default' => [
        'id' => 'default',
        'name' => __('Default ProjectSend', 'cftp_admin'),
        'description' => __('Classic ProjectSend email template with traditional styling and branding', 'cftp_admin'),
        'preview_image' => 'default/preview.png',
        'header_file' => 'default/header.html',
        'footer_file' => 'default/footer.html',
        'style' => 'default',
        'color_scheme' => 'classic',
        'features' => [
            __('Classic design', 'cftp_admin'),
            __('ProjectSend branding', 'cftp_admin'),
            __('Email-safe styling', 'cftp_admin'),
        ]
    ],
    'modern' => [
        'id' => 'modern',
        'name' => __('Modern & Clean', 'cftp_admin'),
        'description' => __('Clean, modern design with subtle colors and professional typography', 'cftp_admin'),
        'preview_image' => 'modern/preview.png',
        'header_file' => 'modern/header.html',
        'footer_file' => 'modern/footer.html',
        'style' => 'modern',
        'color_scheme' => 'blue-gray',
        'features' => [
            __('Responsive design', 'cftp_admin'),
            __('Clean typography', 'cftp_admin'),
            __('Subtle color scheme', 'cftp_admin'),
        ]
    ],
    'corporate' => [
        'id' => 'corporate',
        'name' => __('Corporate Professional', 'cftp_admin'),
        'description' => __('Professional business template with formal styling and corporate branding', 'cftp_admin'),
        'preview_image' => 'corporate/preview.png',
        'header_file' => 'corporate/header.html',
        'footer_file' => 'corporate/footer.html',
        'style' => 'corporate',
        'color_scheme' => 'dark-blue',
        'features' => [
            __('Professional layout', 'cftp_admin'),
            __('Corporate branding ready', 'cftp_admin'),
            __('Formal typography', 'cftp_admin'),
        ]
    ],
    'minimal' => [
        'id' => 'minimal',
        'name' => __('Minimal Simple', 'cftp_admin'),
        'description' => __('Ultra-clean minimal design focusing on content with maximum readability', 'cftp_admin'),
        'preview_image' => 'minimal/preview.png',
        'header_file' => 'minimal/header.html',
        'footer_file' => 'minimal/footer.html',
        'style' => 'minimal',
        'color_scheme' => 'monochrome',
        'features' => [
            __('Ultra-clean design', 'cftp_admin'),
            __('Maximum readability', 'cftp_admin'),
            __('Content-focused', 'cftp_admin'),
        ]
    ],
    'darktech' => [
        'id' => 'darktech',
        'name' => __('Dark Tech', 'cftp_admin'),
        'description' => __('Sleek dark theme with tech-oriented design and modern elements', 'cftp_admin'),
        'preview_image' => 'darktech/preview.png',
        'header_file' => 'darktech/header.html',
        'footer_file' => 'darktech/footer.html',
        'style' => 'darktech',
        'color_scheme' => 'dark-gray',
        'features' => [
            __('Dark theme design', 'cftp_admin'),
            __('Tech-oriented styling', 'cftp_admin'),
            __('Modern interface', 'cftp_admin'),
        ]
    ],
];