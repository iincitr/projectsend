<?php
/*
Template name: Retro 90s
URI: https://www.projectsend.org/templates/retro90s
Author: ProjectSend
Author URI: https://www.projectsend.org/
Author e-mail: contact@projectsend.org
Description: A nostalgic throwback to the golden era of the web - complete with tables, marquees, and that classic 90s aesthetic!
*/

$ld = 'retro90s_template'; // specify the language domain for this template

define('TEMPLATE_RESULTS_PER_PAGE', get_option('pagination_results_per_page'));
define('TEMPLATE_THUMBNAILS_WIDTH', '80');
define('TEMPLATE_THUMBNAILS_HEIGHT', '60');

$filter_by_category = isset($_GET['category']) ? $_GET['category'] : null;

$current_url = get_form_action_with_existing_parameters('index.php');

include_once ROOT_DIR . '/templates/common.php'; // include the required functions for every template
require_once dirname(__FILE__) . '/csv_helper.php'; // include 90s entertainment CSV helper

// Get random entertainment content for this page load
$random_movies = getRandomMovies(5);
$random_music = getRandomMusic(4);
$random_videogames = getRandomVideoGames(4);

$window_title = __('File downloads', 'retro90s_template');

$page_id = 'retro90s_template';

$body_class = array('template', 'retro90s-template', 'hide_title');

// Flash errors
if (!$count) {
    if (isset($no_results_error)) {
        switch ($no_results_error) {
            case 'search':
                $flash->error(__('Your search keywords returned no results.', 'cftp_admin'));
                break;
            case 'filter':
                $flash->error(__('The filters you selected returned no results.', 'cftp_admin'));
                break;
        }
    } else {
        $flash->warning(__('There are no files available.', 'cftp_admin'));
    }
}

// Header buttons
if (current_user_can_upload()) {
    $header_action_buttons = [
        [
            'url' => BASE_URI.'upload.php',
            'label' => __('Upload file', 'cftp_admin'),
        ],
    ];
}

// Search + filters bar data
$search_form_action = 'index.php';
$filters_form = [
    'action' => '',
    'items' => [],
];

if (!empty($cat_ids)) {
    $selected_parent = (isset($_GET['category'])) ? [$_GET['category']] : [];
    $category_filter = [];
    $generate_categories_options = generate_categories_options($get_categories['arranged'], 0, $selected_parent, 'include', $cat_ids);
    $format_categories_options = format_categories_options($generate_categories_options);
    foreach ($format_categories_options as $key => $category) {
        $category_filter[$category['id']] = $category['label'];
    }
    $filters_form['items']['category'] = [
        'current' => (isset($_GET['category'])) ? $_GET['category'] : null,
        'placeholder' => [
            'value' => '0',
            'label' => __('All categories', 'cftp_admin')
        ],
        'options' => $category_filter,
    ];
}

// Results count and form actions 
$elements_found_count = (isset($count_for_pagination)) ? $count_for_pagination : 0;
$bulk_actions_items = [
    'none' => __('Select action', 'cftp_admin'),
    'zip' => __('Download zipped', 'cftp_admin'),
];

?>
<!DOCTYPE html>
<html lang="<?php echo SITE_LANG; ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_output( $client_info['name'].' | '.$window_title . ' &raquo; ' . SYSTEM_NAME ); ?></title>
    <?php meta_favicon(); ?>

    <link rel="stylesheet" href="<?php echo $this_template_url; ?>font-awesome-4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" media="all" type="text/css" href="<?php echo $this_template_url; ?>main.css" />

    <script src="<?php echo $this_template_url; ?>js/jquery.1.11.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
    
    <script>
        window.base_url = '<?php echo BASE_URI; ?>';
    </script>

    <?php render_custom_assets('head'); ?>
</head>

<body background="<?php echo $this_template_url; ?>images/bg-pattern.gif">
    <?php render_custom_assets('body_top'); ?>

<!-- Main Container Table -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#c0c0c0">
    <tr>
        <td>
            <!-- Header Table -->
            <table width="100%" cellpadding="8" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0">
                        <table width="100%" cellpadding="4" cellspacing="1" border="0">
                            <tr bgcolor="#000080">
                                <td>
                                    <font face="Arial, sans-serif" color="#ffff00" size="5">
                                        <b>
                                            <?php if ($logo_file_info['exists'] === true) { ?>
                                                <?php echo get_branding_layout(true); ?>
                                            <?php } else { ?>
                                                ★ <?php echo SYSTEM_NAME; ?> ★
                                            <?php } ?>
                                        </b>
                                    </font>
                                </td>
                                <td align="right">
                                    <font face="Arial, sans-serif" color="#00ffff" size="2">
                                        <blink>ONLINE!</blink>
                                    </font>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#c0c0c0">
                        <marquee behavior="scroll" direction="left" bgcolor="#ffff00">
                            <font face="Arial, sans-serif" color="#ff0000" size="3">
                                <b>Welcome <?php echo htmlspecialchars($client_info['name']); ?>! You have <?php echo $elements_found_count; ?> files available for download!</b>
                            </font>
                        </marquee>
                    </td>
                </tr>
            </table>

            <!-- Navigation Table -->
            <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0">
                        <table width="100%" cellpadding="2" cellspacing="1" border="0">
                            <tr bgcolor="#808080">
                                <td>
                                    <font face="Arial, sans-serif" color="#ffffff" size="2">
                                        <b>► NAVIGATION</b>
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#c0c0c0">
                                <td>
                                    <font face="Arial, sans-serif" size="2">
                                        <a href="<?php echo CLIENT_VIEW_FILE_LIST_URL; ?>">🏠 My Files</a> |
                                        <?php if (current_user_can_upload()) { ?>
                                            <a href="<?php echo BASE_URI; ?>upload.php">📤 Upload</a> |
                                        <?php } ?>
                                        <a href="<?php echo BASE_URI; ?>manage-files.php">⚙️ Manage</a> |
                                        <a href="<?php echo BASE_URI; ?>process.php?do=logout">🚪 Logout</a>
                                    </font>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Search & Filters Table -->
            <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0">
                        <table width="100%" cellpadding="2" cellspacing="1" border="0">
                            <tr bgcolor="#808080">
                                <td colspan="2">
                                    <font face="Arial, sans-serif" color="#ffffff" size="2">
                                        <b>🔍 SEARCH & FILTER</b>
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#c0c0c0">
                                <td width="50%">
                                    <?php if (!empty($search_form_action)) { ?>
                                        <form action="<?php echo $search_form_action; ?>" name="form_search" method="get">
                                            <?php echo form_add_existing_parameters( array('search', 'action') ); ?>
                                            <font face="Arial, sans-serif" size="2">
                                                <b>Search:</b><br>
                                                <input type="text" name="search" value="<?php if(isset($_GET['search']) && !empty($_GET['search'])) { echo html_output($_GET['search']); } ?>" size="20" />
                                                <input type="submit" value="GO!" />
                                            </font>
                                        </form>
                                    <?php } ?>
                                </td>
                                <td width="50%">
                                    <?php if (!empty($filters_form['items'])) { ?>
                                        <form action="<?php echo $filters_form['action']; ?>" name="actions_filters" method="get">
                                            <?php echo form_add_existing_parameters(array_keys($filters_form['items'])); ?>
                                            <font face="Arial, sans-serif" size="2">
                                                <b>Category:</b><br>
                                                <?php foreach ($filters_form['items'] as $name => $data) { ?>
                                                    <select name="<?php echo $name; ?>" onchange="this.form.submit()">
                                                        <?php if (!empty($data['placeholder'])) { ?>
                                                            <option value="<?php echo $data['placeholder']['value']; ?>"><?php echo $data['placeholder']['label']; ?></option>
                                                        <?php } ?>
                                                        <?php foreach ($data['options'] as $value => $option) { ?>
                                                            <option value="<?php echo $value; ?>" <?php if (isset($data['current']) && $data['current'] == $value) { echo 'selected="selected"'; } ?>>
                                                                <?php echo is_array($option) ? $option['name'] : $option; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                <?php } ?>
                                            </font>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Folders Navigation Table -->
            <?php if (!empty($_GET['folder_id']) || !empty($folders)) { ?>
                <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                    <tr>
                        <td bgcolor="#c0c0c0">
                            <table width="100%" cellpadding="2" cellspacing="1" border="0">
                                <?php if (!empty($_GET['folder_id'])) { ?>
                                    <tr bgcolor="#808080">
                                        <td>
                                            <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                <b>📁 FOLDER NAVIGATION</b>
                                            </font>
                                        </td>
                                    </tr>
                                    <tr bgcolor="#c0c0c0">
                                        <td>
                                            <?php
                                                $root_link = modify_url_with_parameters($current_url, [], ['folder_id']);
                                            ?>
                                            <font face="Arial, sans-serif" size="2">
                                                <a href="<?php echo $root_link; ?>" class="retro-button">
                                                    🏠 ROOT
                                                </a>
                                                
                                                <?php
                                                    $get_parent = new \ProjectSend\Classes\Folder($_GET['folder_id']);
                                                    $parent_data = $get_parent->getData();
                                                    if (!empty($parent_data['parent'])) {
                                                        $up_link = modify_url_with_parameters($current_url, ['folder_id' => $parent_data['parent']], ['folder_id']);
                                                ?>
                                                        <a href="<?php echo $up_link; ?>" class="retro-button">
                                                            ⬆️ UP
                                                        </a>
                                                <?php } ?>
                                            </font>
                                        </td>
                                    </tr>
                                <?php } ?>
                                
                                <!-- Show Folders if any exist -->
                                <?php if (!empty($folders)) { ?>
                                    <tr bgcolor="#808080">
                                        <td>
                                            <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                <b>📂 FOLDERS</b>
                                            </font>
                                        </td>
                                    </tr>
                                    <tr bgcolor="#c0c0c0">
                                        <td>
                                            <table width="100%" cellpadding="2" cellspacing="1" border="0">
                                                <?php
                                                // Responsive folders per row - more on desktop, fewer on mobile
                                                $folders_per_row = 5; // Desktop: 5 folders per row
                                                $folder_count = 0;
                                                $cell_width = floor(100 / $folders_per_row); // Calculate width percentage
                                                
                                                foreach ($folders as $folder) {
                                                    $folder_obj = new \ProjectSend\Classes\Folder($folder['id']);
                                                    $folder_data = $folder_obj->getData();
                                                    $link = modify_url_with_parameters($current_url, ['folder_id' => $folder_data['id']], ['folder_id']);
                                                    
                                                    if ($folder_count % $folders_per_row == 0) {
                                                        echo '<tr bgcolor="#f0f0f0">';
                                                    }
                                                ?>
                                                    <td width="<?php echo $cell_width; ?>%" align="center">
                                                        <a href="<?php echo $link; ?>" class="retro-button folder-button">
                                                            <font face="Arial, sans-serif" size="1">
                                                                <b>📁<br><?php echo htmlspecialchars($folder_obj->name); ?></b>
                                                            </font>
                                                        </a>
                                                    </td>
                                                <?php
                                                    $folder_count++;
                                                    if ($folder_count % $folders_per_row == 0) {
                                                        echo '</tr>';
                                                    }
                                                }
                                                // Close the row if it wasn't completed
                                                if ($folder_count % $folders_per_row != 0) {
                                                    while ($folder_count % $folders_per_row != 0) {
                                                        echo '<td width="' . $cell_width . '%">&nbsp;</td>';
                                                        $folder_count++;
                                                    }
                                                    echo '</tr>';
                                                }
                                                ?>
                                            </table>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </td>
                    </tr>
                </table>
            <?php } ?>

            <!-- Files Table -->
            <form action="" name="files_list" method="get" class="batch_actions">
                <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                    <tr>
                        <td bgcolor="#c0c0c0">
                            <?php if (isset($count) && $count > 0) { ?>
                                <!-- Files Header -->
                                <table width="100%" cellpadding="2" cellspacing="1" border="0">
                                    <tr bgcolor="#808080">
                                        <td>
                                            <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                <b>💾 YOUR FILES (<?php echo $elements_found_count; ?> total)</b>
                                            </font>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Bulk Actions -->
                                <table width="100%" cellpadding="2" cellspacing="1" border="0" id="bulk-actions-table" style="display: none;">
                                    <tr bgcolor="#ffff00">
                                        <td>
                                            <font face="Arial, sans-serif" size="2">
                                                <b>Selected: <span id="selected-count">0</span> files</b>
                                                <input type="button" value="Clear All" onclick="clearSelection()" />
                                                <input type="button" value="Download Zip" onclick="downloadSelected()" />
                                            </font>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Files List Table -->
                                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                                    <tr bgcolor="#000080">
                                        <td width="30">
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>✓</b>
                                            </font>
                                        </td>
                                        <td width="60">
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>TYPE</b>
                                            </font>
                                        </td>
                                        <td>
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>FILE NAME</b>
                                            </font>
                                        </td>
                                        <td width="80">
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>SIZE</b>
                                            </font>
                                        </td>
                                        <td width="100">
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>DATE</b>
                                            </font>
                                        </td>
                                        <td width="120">
                                            <font face="Arial, sans-serif" color="#ffffff" size="1">
                                                <b>ACTIONS</b>
                                            </font>
                                        </td>
                                    </tr>
                                    <?php
                                    $row_color = 0;
                                    foreach ($available_files as $file_id) {
                                        $file = new \ProjectSend\Classes\Files($file_id);
                                        $bg_color = ($row_color % 2 == 0) ? '#e0e0e0' : '#f0f0f0';
                                        $row_color++;
                                        
                                        // File type icon
                                        $file_icon = '📄';
                                        if ($file->isImage()) {
                                            $file_icon = '🖼️';
                                        } elseif (in_array(strtolower($file->extension), ['pdf'])) {
                                            $file_icon = '📕';
                                        } elseif (in_array(strtolower($file->extension), ['doc', 'docx'])) {
                                            $file_icon = '📝';
                                        } elseif (in_array(strtolower($file->extension), ['xls', 'xlsx'])) {
                                            $file_icon = '📊';
                                        } elseif (in_array(strtolower($file->extension), ['zip', 'rar', '7z'])) {
                                            $file_icon = '📦';
                                        } elseif (in_array(strtolower($file->extension), ['mp3', 'wav', 'ogg'])) {
                                            $file_icon = '🎵';
                                        } elseif (in_array(strtolower($file->extension), ['mp4', 'avi', 'mov'])) {
                                            $file_icon = '🎬';
                                        }
                                        
                                        if ($file->expired) {
                                            $bg_color = '#ffcccc';
                                        }
                                        ?>
                                        <tr bgcolor="<?php echo $bg_color; ?>">
                                            <td align="center">
                                                <?php if (!$file->expired) { ?>
                                                    <input type="checkbox" name="files[]" value="<?php echo $file->id; ?>" class="batch_checkbox" onchange="updateBulkActions()" />
                                                <?php } else { ?>
                                                    ❌
                                                <?php } ?>
                                            </td>
                                            <td align="center">
                                                <font size="3"><?php echo $file_icon; ?></font><br>
                                                <font face="Arial, sans-serif" size="1">
                                                    <b><?php echo strtoupper($file->extension); ?></b>
                                                </font>
                                            </td>
                                            <td>
                                                <font face="Arial, sans-serif" size="2">
                                                    <b><?php echo htmlspecialchars($file->title); ?></b>
                                                    <?php if ($file->title != $file->filename_original) { ?>
                                                        <br><font size="1" color="#666666">(<?php echo htmlspecialchars($file->filename_original); ?>)</font>
                                                    <?php } ?>
                                                    <?php if (!empty($file->description)) { ?>
                                                        <br><font size="1"><?php echo htmlspecialchars($file->description); ?></font>
                                                    <?php } ?>
                                                    <?php if ($file->expires == '1') { ?>
                                                        <br>
                                                        <?php if ($file->expired) { ?>
                                                            <font color="#ff0000" size="1"><b>⚠️ EXPIRED</b></font>
                                                        <?php } else { ?>
                                                            <font color="#ff6600" size="1"><b>⏰ Expires: <?php echo date('M j, Y', strtotime($file->expiry_date)); ?></b></font>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </font>
                                            </td>
                                            <td align="center">
                                                <font face="Arial, sans-serif" size="1">
                                                    <b><?php echo $file->size_formatted; ?></b>
                                                </font>
                                            </td>
                                            <td align="center">
                                                <font face="Arial, sans-serif" size="1">
                                                    <?php echo date('M j, Y', strtotime($file->uploaded_date)); ?>
                                                </font>
                                            </td>
                                            <td align="center">
                                                <?php if ($file->expired) { ?>
                                                    <font face="Arial, sans-serif" size="1" color="#ff0000">
                                                        <b>EXPIRED</b>
                                                    </font>
                                                <?php } else { ?>
                                                    <a href="<?php echo $file->download_link; ?>" target="_blank" class="retro-button">
                                                        <font face="Arial, sans-serif" size="1"><b>📥 DL</b></font>
                                                    </a>
                                                    <?php if ($file->embeddable) { ?>
                                                        <br>
                                                        <a href="#" class="preview-link retro-button" data-url="<?php echo BASE_URI; ?>process.php?do=get_preview&file_id=<?php echo $file->id; ?>">
                                                            <font face="Arial, sans-serif" size="1"><b>👁️ VIEW</b></font>
                                                        </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>

                                <!-- Pagination -->
                                <?php
                                if (isset($count) && $count > 0) {
                                    $pagination = new \ProjectSend\Classes\Layout\Pagination;
                                    echo '<br><center>';
                                    echo $pagination->make([
                                        'link' => 'my_files/index.php',
                                        'current' => $pagination_page,
                                        'item_count' => $count_for_pagination,
                                        'items_per_page' => TEMPLATE_RESULTS_PER_PAGE,
                                    ]);
                                    echo '</center>';
                                }
                                ?>

                            <?php } else { ?>
                                <!-- No Files Message -->
                                <table width="100%" cellpadding="20" cellspacing="1" border="0">
                                    <tr bgcolor="#ffff00">
                                        <td align="center">
                                            <font face="Arial, sans-serif" size="4" color="#ff0000">
                                                <b>📁 NO FILES FOUND! 📁</b>
                                            </font>
                                            <br><br>
                                            <font face="Arial, sans-serif" size="2">
                                                <?php echo __('There are currently no files to display.', 'retro90s_template'); ?>
                                            </font>
                                            <?php if (current_user_can_upload()) { ?>
                                                <br><br>
                                                <a href="<?php echo BASE_URI; ?>upload.php" class="retro-button big-button">
                                                    <font face="Arial, sans-serif" size="3"><b>📤 UPLOAD FILES! 📤</b></font>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </form>

            <!-- Retro Separator -->
            <div style="margin-top: 50px; margin-bottom: 50px;">
                <center>
                    <table width="90%" cellpadding="2" cellspacing="0" border="0">
                        <tr>
                            <td bgcolor="#808080" height="3" style="border-top: 1px solid #ffffff; border-left: 1px solid #ffffff;"></td>
                        </tr>
                        <tr>
                            <td bgcolor="#c0c0c0" height="2" style="border-bottom: 1px solid #000000; border-right: 1px solid #000000;"></td>
                        </tr>
                    </table>
                    <br>
                    <font face="Arial, sans-serif" color="#666666" size="1">
                        <blink>★ ★ ★</blink> ENTERTAINMENT ZONE <blink>★ ★ ★</blink>
                    </font>
                </center>
            </div>

            <!-- MUST WATCH MOVIES Section (Very 90s!) -->
            <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0">
                        <table width="100%" cellpadding="2" cellspacing="1" border="0">
                            <tr bgcolor="#000080">
                                <td>
                                    <font face="Arial, sans-serif" color="#ffff00" size="3">
                                        <b>🎬 MUST WATCH MOVIES! 🎬</b>
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#ffff00">
                                <td>
                                    <marquee behavior="scroll" direction="left" bgcolor="#ffff00">
                                        <font face="Arial, sans-serif" color="#ff0000" size="2">
                                            <b>*** COMING SOON TO VHS! *** RENT NOW AT BLOCKBUSTER! *** GET YOUR COPY BEFORE THEY'RE GONE! ***</b>
                                        </font>
                                    </marquee>
                                </td>
                            </tr>
                            <tr bgcolor="#c0c0c0">
                                <td>
                                    <table width="100%" cellpadding="3" cellspacing="1" border="0">
                                        <tr bgcolor="#808080">
                                            <td width="50%">
                                                <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                    <b>📼 RECENT BLOCKBUSTERS!</b>
                                                </font>
                                            </td>
                                            <td width="50%">
                                                <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                    <b>🌟 COMING ATTRACTIONS!</b>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#f0f0f0">
                                            <td valign="top">
                                                <font face="Arial, sans-serif" size="2">
                                                    <?php 
                                                    $half = ceil(count($random_movies) / 2);
                                                    for ($i = 0; $i < $half; $i++) {
                                                        if (isset($random_movies[$i])) {
                                                            $movie = $random_movies[$i];
                                                            echo '<b>• ' . htmlspecialchars($movie['title']) . '</b> (' . htmlspecialchars($movie['year']) . ')<br>';
                                                            echo '<font size="1">' . htmlspecialchars($movie['icon']) . ' ' . htmlspecialchars($movie['description']) . '</font>';
                                                            if ($i < $half - 1) echo '<br><br>';
                                                        }
                                                    }
                                                    ?>
                                                </font>
                                            </td>
                                            <td valign="top">
                                                <font face="Arial, sans-serif" size="2">
                                                    <?php 
                                                    for ($i = $half; $i < count($random_movies); $i++) {
                                                        if (isset($random_movies[$i])) {
                                                            $movie = $random_movies[$i];
                                                            echo '<b>• ' . htmlspecialchars($movie['title']) . '</b> (' . htmlspecialchars($movie['year']) . ')<br>';
                                                            echo '<font size="1">' . htmlspecialchars($movie['icon']) . ' ' . htmlspecialchars($movie['description']) . '</font>';
                                                            if ($i < count($random_movies) - 1) echo '<br><br>';
                                                        }
                                                    }
                                                    ?>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#e0e0e0">
                                            <td colspan="2" align="center">
                                                <font face="Arial, sans-serif" size="2">
                                                    <b>🎭 CLASSIC MUST-SEES:</b>
                                                    <blink>Titanic</blink> • 
                                                    <blink>The Silence of the Lambs</blink> • 
                                                    <blink>Goodfellas</blink> • 
                                                    <blink>Dances with Wolves</blink> • 
                                                    <blink>Pretty Woman</blink>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ffcccc">
                                            <td colspan="2" align="center">
                                                <font face="Arial, sans-serif" size="2" color="#ff0000">
                                                    <b>⚠️ WARNING: Please be kind, rewind! ⚠️</b><br>
                                                    <font size="1">Late fees apply after 3 days! No exceptions!</font>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ccffcc">
                                            <td colspan="2" align="center">
                                                <font face="Arial, sans-serif" size="1">
                                                    💰 <b>SPECIAL OFFER:</b> Rent 2 movies, get 1 FREE popcorn! 🍿<br>
                                                    Valid only at participating Blockbuster stores. Expires Dec 31, 1999.
                                                </font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr bgcolor="#000080">
                                <td align="center">
                                    <font face="Arial, sans-serif" color="#00ffff" size="1">
                                        <blink>*** Visit your local video store today! ***</blink><br>
                                        Powered by the latest VHS technology!
                                    </font>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- MUSIC & GAMES Section (More 90s Fun!) -->
            <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0">
                        <table width="100%" cellpadding="2" cellspacing="1" border="0">
                            <tr bgcolor="#800080">
                                <td colspan="2">
                                    <font face="Arial, sans-serif" color="#ffff00" size="3">
                                        <b>🎵 HOTTEST CDs & 🎮 COOLEST GAMES! 🎵</b>
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#ff00ff">
                                <td colspan="2">
                                    <marquee behavior="scroll" direction="right" bgcolor="#ff00ff">
                                        <font face="Arial, sans-serif" color="#ffffff" size="2">
                                            <b>*** NOW AT TOWER RECORDS & ELECTRONICS BOUTIQUE! *** GET THE LATEST HITS! ***</b>
                                        </font>
                                    </marquee>
                                </td>
                            </tr>
                            <tr bgcolor="#c0c0c0">
                                <td width="50%" valign="top">
                                    <table width="100%" cellpadding="2" cellspacing="1" border="0">
                                        <tr bgcolor="#800080">
                                            <td>
                                                <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                    <b>💿 TOP ALBUMS ON CD!</b>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ffffcc">
                                            <td>
                                                <font face="Arial, sans-serif" size="2">
                                                    <?php 
                                                    foreach ($random_music as $index => $album) {
                                                        echo '<b>• ' . htmlspecialchars($album['artist']) . ' - ' . htmlspecialchars($album['album']) . '</b> (' . htmlspecialchars($album['year']) . ')<br>';
                                                        echo '<font size="1">' . htmlspecialchars($album['icon']) . ' ' . htmlspecialchars($album['description']) . '</font>';
                                                        if ($index < count($random_music) - 1) echo '<br><br>';
                                                    }
                                                    ?>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ccffff">
                                            <td align="center">
                                                <font face="Arial, sans-serif" size="1">
                                                    <b>🎧 NEW!</b> Portable CD Players with ANTI-SKIP!<br>
                                                    <blink>Perfect for jogging!</blink>
                                                </font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="50%" valign="top">
                                    <table width="100%" cellpadding="2" cellspacing="1" border="0">
                                        <tr bgcolor="#800080">
                                            <td>
                                                <font face="Arial, sans-serif" color="#ffffff" size="2">
                                                    <b>🕹️ EPIC VIDEOGAMES!</b>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ccffcc">
                                            <td>
                                                <font face="Arial, sans-serif" size="2">
                                                    <?php 
                                                    foreach ($random_videogames as $index => $game) {
                                                        echo '<b>• ' . htmlspecialchars($game['title']) . '</b> (' . htmlspecialchars($game['year']) . ')<br>';
                                                        echo '<font size="1">' . htmlspecialchars($game['icon']) . ' ' . htmlspecialchars($game['description']) . '</font>';
                                                        if ($index < count($random_videogames) - 1) echo '<br><br>';
                                                    }
                                                    ?>
                                                </font>
                                            </td>
                                        </tr>
                                        <tr bgcolor="#ffccff">
                                            <td align="center">
                                                <font face="Arial, sans-serif" size="1">
                                                    <b>🎮 NEW!</b> 32-bit graphics! CD-quality sound!<br>
                                                    <blink>The future is HERE!</blink>
                                                </font>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr bgcolor="#ffff00">
                                <td colspan="2" align="center">
                                    <font face="Arial, sans-serif" size="2" color="#ff0000">
                                        <b>🔥 HOT DEALS:</b>
                                        <blink>Buy 2 CDs, get 1 cassette FREE!</blink> • 
                                        <blink>Rent 3 games, keep 1 extra day!</blink> • 
                                        <blink>Trade-ins accepted!</blink>
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#e0e0e0">
                                <td width="50%" align="center">
                                    <font face="Arial, sans-serif" size="1">
                                        💿 <b>Also available on:</b> Cassette Tape<br>
                                        🎵 For your Walkman or Boom Box!
                                    </font>
                                </td>
                                <td width="50%" align="center">
                                    <font face="Arial, sans-serif" size="1">
                                        🕹️ <b>Compatible systems:</b> NES, SNES, Genesis<br>
                                        🎮 Game Boy, PC (DOS), Arcade!
                                    </font>
                                </td>
                            </tr>
                            <tr bgcolor="#800080">
                                <td colspan="2" align="center">
                                    <font face="Arial, sans-serif" color="#00ffff" size="1">
                                        <blink>*** Experience the digital revolution! ***</blink><br>
                                        CD players and 16-bit consoles - Technology at its finest!
                                    </font>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Footer Table -->
            <table width="100%" cellpadding="4" cellspacing="2" border="0" bgcolor="#008080">
                <tr>
                    <td bgcolor="#c0c0c0" align="center">
                        <font face="Arial, sans-serif" size="1" color="#666666">
                            <?php render_footer_text(); ?>
                            <br>
                            <blink>★ GEOCITIES STYLE ★</blink>
                        </font>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

<!-- Preview Modal (90s Style) -->
<div id="previewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999;">
    <center>
        <table cellpadding="4" cellspacing="2" border="0" bgcolor="#008080" style="margin-top: 50px; max-width: 800px;">
            <tr>
                <td bgcolor="#c0c0c0">
                    <table width="100%" cellpadding="2" cellspacing="1" border="0">
                        <tr bgcolor="#000080">
                            <td>
                                <font face="Arial, sans-serif" color="#ffff00" size="3">
                                    <b>📺 PREVIEW</b>
                                </font>
                            </td>
                            <td align="right">
                                <a href="#" onclick="closePreview()" style="color: #ff0000; text-decoration: none;">
                                    <font face="Arial, sans-serif" size="2"><b>[CLOSE]</b></font>
                                </a>
                            </td>
                        </tr>
                        <tr bgcolor="#ffffff">
                            <td colspan="2" id="previewContent" style="padding: 10px;">
                                <!-- Preview content will be loaded here -->
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</div>

<!-- JavaScript -->
<script src="<?php echo $this_template_url; ?>js/template.js"></script>

<?php render_custom_assets('body_bottom'); ?>

</body>
</html>