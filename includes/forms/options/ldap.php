<?php
/**
 * LDAP Authentication Configuration
 */
?>

<h3><?php _e('LDAP Authentication','cftp_admin'); ?></h3>
<p><?php echo sprintf(__('Configure LDAP/Active Directory authentication settings. Note: %s requires all accounts to be available locally. When a user connects via LDAP, a local account will be created automatically.','cftp_admin'), SYSTEM_NAME); ?></p>

<div class="options_column">
    <div class="form-group row">
        <label for="ldap_signin_enabled" class="col-sm-4 control-label"><?php _e('Enable LDAP signin','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <select class="form-select" name="ldap_signin_enabled" id="ldap_signin_enabled">
                <option value="false" <?php echo (get_option('ldap_signin_enabled') == 'false') ? 'selected="selected"' : ''; ?>><?php _e('No','cftp_admin'); ?></option>
                <option value="true" <?php echo (get_option('ldap_signin_enabled') == 'true') ? 'selected="selected"' : ''; ?>><?php _e('Yes','cftp_admin'); ?></option>
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_hosts" class="col-sm-4 control-label"><?php _e('LDAP server','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_hosts" id="ldap_hosts" class="form-control" value="<?php echo get_option('ldap_hosts'); ?>" placeholder="ldap://server.domain.com:389" />
            <small class="form-text text-muted"><?php _e('LDAP server URL (e.g., ldap://server.domain.com:389 or ldaps://server.domain.com:636)','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_port" class="col-sm-4 control-label"><?php _e('LDAP port','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="number" name="ldap_port" id="ldap_port" class="form-control" value="<?php echo get_option('ldap_port', null, '389'); ?>" placeholder="389" min="1" max="65535" />
            <small class="form-text text-muted"><?php _e('Standard ports: 389 (LDAP), 636 (LDAPS)','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_bind_dn" class="col-sm-4 control-label"><?php _e('Base DN','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_bind_dn" id="ldap_bind_dn" class="form-control" value="<?php echo get_option('ldap_bind_dn'); ?>" placeholder="dc=company,dc=com" />
            <small class="form-text text-muted"><?php _e('Base Distinguished Name for LDAP searches','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_admin_user" class="col-sm-4 control-label"><?php _e('Admin username','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_admin_user" id="ldap_admin_user" class="form-control" value="<?php echo get_option('ldap_admin_user'); ?>" placeholder="cn=admin,dc=company,dc=com" />
            <small class="form-text text-muted"><?php _e('Admin user DN for binding to LDAP server','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_admin_password" class="col-sm-4 control-label"><?php _e('Admin password','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="password" name="ldap_admin_password" id="ldap_admin_password" class="form-control" value="<?php echo get_option('ldap_admin_password'); ?>" />
            <small class="form-text text-muted"><?php _e('Password for the admin user','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_search_base" class="col-sm-4 control-label"><?php _e('User search base','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_search_base" id="ldap_search_base" class="form-control" value="<?php echo get_option('ldap_search_base'); ?>" placeholder="ou=users,dc=company,dc=com" />
            <small class="form-text text-muted"><?php _e('Base DN where users are located','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_username_attribute" class="col-sm-4 control-label"><?php _e('Username attribute','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_username_attribute" id="ldap_username_attribute" class="form-control" value="<?php echo get_option('ldap_username_attribute', null, 'uid'); ?>" placeholder="uid" />
            <small class="form-text text-muted"><?php _e('Attribute used for username (uid, sAMAccountName, userPrincipalName)','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_search_filter" class="col-sm-4 control-label"><?php _e('Search filter','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_search_filter" id="ldap_search_filter" class="form-control" value="<?php echo get_option('ldap_search_filter', null, '(uid={username})'); ?>" placeholder="(uid={username})" />
            <small class="form-text text-muted"><?php _e('Filter for finding users. Use {username} as placeholder','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_email_attribute" class="col-sm-4 control-label"><?php _e('Email attribute','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_email_attribute" id="ldap_email_attribute" class="form-control" value="<?php echo get_option('ldap_email_attribute', null, 'mail'); ?>" placeholder="mail" />
            <small class="form-text text-muted"><?php _e('Attribute containing user email address','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_name_attribute" class="col-sm-4 control-label"><?php _e('Full name attribute','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_name_attribute" id="ldap_name_attribute" class="form-control" value="<?php echo get_option('ldap_name_attribute', null, 'cn'); ?>" placeholder="cn" />
            <small class="form-text text-muted"><?php _e('Attribute containing user full name','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_account_suffix" class="col-sm-4 control-label"><?php _e('Account suffix','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <input type="text" name="ldap_account_suffix" id="ldap_account_suffix" class="form-control" value="<?php echo get_option('ldap_account_suffix'); ?>" placeholder="@company.com" />
            <small class="form-text text-muted"><?php _e('Domain suffix added to usernames (optional, for Active Directory)','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_use_tls" class="col-sm-4 control-label"><?php _e('Use TLS','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <select class="form-select" name="ldap_use_tls" id="ldap_use_tls">
                <option value="false" <?php echo (get_option('ldap_use_tls', null, 'false') == 'false') ? 'selected="selected"' : ''; ?>><?php _e('No','cftp_admin'); ?></option>
                <option value="true" <?php echo (get_option('ldap_use_tls', null, 'false') == 'true') ? 'selected="selected"' : ''; ?>><?php _e('Yes','cftp_admin'); ?></option>
            </select>
            <small class="form-text text-muted"><?php _e('Use TLS encryption for LDAP connections','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_auto_create_users" class="col-sm-4 control-label"><?php _e('Auto-create LDAP users','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <select class="form-select" name="ldap_auto_create_users" id="ldap_auto_create_users">
                <option value="false" <?php echo (get_option('ldap_auto_create_users', null, 'true') == 'false') ? 'selected="selected"' : ''; ?>><?php _e('No','cftp_admin'); ?></option>
                <option value="true" <?php echo (get_option('ldap_auto_create_users', null, 'true') == 'true') ? 'selected="selected"' : ''; ?>><?php _e('Yes','cftp_admin'); ?></option>
            </select>
            <small class="form-text text-muted"><?php _e('Automatically create local accounts for LDAP users','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <label for="ldap_default_role" class="col-sm-4 control-label"><?php _e('Default role for new LDAP users','cftp_admin'); ?></label>
        <div class="col-sm-8">
            <select class="form-select" name="ldap_default_role" id="ldap_default_role">
                <option value="0" <?php echo (get_option('ldap_default_role', null, '0') == '0') ? 'selected="selected"' : ''; ?>><?php _e('Client','cftp_admin'); ?></option>
                <option value="7" <?php echo (get_option('ldap_default_role', null, '0') == '7') ? 'selected="selected"' : ''; ?>><?php _e('Uploader','cftp_admin'); ?></option>
                <option value="8" <?php echo (get_option('ldap_default_role', null, '0') == '8') ? 'selected="selected"' : ''; ?>><?php _e('Account Manager','cftp_admin'); ?></option>
            </select>
            <small class="form-text text-muted"><?php _e('Role assigned to new users created from LDAP','cftp_admin'); ?></small>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-4"></div>
        <div class="col-sm-8">
            <button type="button" class="btn btn-secondary" id="test_ldap_connection"><?php _e('Test LDAP Connection','cftp_admin'); ?></button><br>
            <small class="form-text text-muted"><?php _e('Test the connection to your LDAP server','cftp_admin'); ?></small>
            <div id="ldap_test_result" class="mt-2"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#test_ldap_connection').click(function() {
        var button = $(this);
        var originalText = button.text();
        var resultDiv = $('#ldap_test_result');
        
        button.prop('disabled', true);
        button.html('<i class="fa fa-cog fa-spin fa-fw"></i> ' + '<?php _e("Testing...", "cftp_admin"); ?>');
        resultDiv.html('').removeClass('alert-success alert-danger');
        
        $.ajax({
            url: 'process.php?do=test_ldap_connection',
            type: 'POST',
            data: {
                csrf_token: document.getElementById('csrf_token').value
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.status === 'success') {
                    resultDiv.addClass('alert alert-success').html('<i class="fa fa-check"></i> ' + result.message);
                } else {
                    resultDiv.addClass('alert alert-danger').html('<i class="fa fa-times"></i> ' + result.message);
                }
            },
            error: function() {
                resultDiv.addClass('alert alert-danger').html('<i class="fa fa-times"></i> ' + '<?php _e("Connection test failed", "cftp_admin"); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
                button.html(originalText);
            }
        });
    });
});
</script>