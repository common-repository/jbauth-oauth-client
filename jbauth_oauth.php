<?php
/*
  Plugin Name: JBAuth OAuth Client
  Plugin URI: https://git.justinback.com/PixelcatProductions/JBAuth-wordpress
  Description: JBAuth Client
  Version: 1.0.2
  Author: Justin René Back
  Author URI: https://justinback.com/
  License: GPL2
 */

/*  Copyright 2018 Justin René Back  (email : jb@justinback.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_action('plugins_loaded', 'jbauthoauth_text');

function jbauthoauth_text() {
    load_plugin_textdomain('jbauth-oauth-client', false, basename(dirname(__FILE__)) . '/langs');
}

require_once 'lib/functions.php';

if (!function_exists('add_action')) {
    _e('Hi there!  I\'m just a plugin, not much I can do when called directly.', 'jbauth-oauth-client');
    exit;
}

register_activation_hook(__FILE__, 'jbauth_plugin_activate');
add_action('admin_init', 'jbauth_plugin_redirect');

function jbauth_plugin_activate() {
    add_option('jbauth_plugin_do_activation_redirect', true);
}

function jbauth_plugin_redirect() {
    if (get_option('jbauth_plugin_do_activation_redirect', false)) {
        delete_option('jbauth_plugin_do_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_redirect("options-general.php?page=jbauth-plugin");
        }
    }
}

function shortcode_jbabutton() {

    $opt_name_clientid = 'wp_jba_clientid';
    $opt_name_clientsecret = 'wp_jba_clientsecret';
    $opt_val_clientid = get_option($opt_name_clientid);
    $opt_val_clientsecret = get_option($opt_name_clientsecret);
    $client_id = $opt_val_clientid;
    $client_secret = $opt_val_clientsecret;

    $url_redirect = get_site_url(); //plugins_url('login.php', __FILE__ );
    $state = wp_create_nonce('jbauthbutton');
    $url = 'https://api.justinback.com/IOAuth/authorize.php?response_type=code&client_id=' . $client_id . '&state=' . $state . '&scope=OAuth_Access OAuth_User_Email OAuth_User_Firstname OAuth_User_Lastname OAuth_User_Username';
    if (!empty($client_id) || !empty($client_secret)) {
        ?>
        <div id="jbauth_oauth_btn">
            <a href="<?php echo $url; ?>">
                <img width="250px" src="https://cdn.justinback.com/f/d/43f4cfeafd78a9ae4dd9ade6f2eb4b23e9f9e86faed53c6709c8d05b1f352d667710dd23f97262a6d4d2d3fbe3880b6dcedf/jbauth_login.png" alt="Sign in with jbauth">
            </a>
        </div>
        <?php
    }
}

add_shortcode('jbauthbtn', 'shortcode_jbabutton');
add_filter('login_form', 'shortcode_jbabutton');

add_action('admin_init', 'jbauthoauth_meta_box');

function jbauthoauth_meta_box() {
    add_meta_box('add-custom-jbauthoauth', __('jbauth Oauth'), 'my_nav_menu_item_jbauth_meta_box', 'nav-menus', 'side', 'low');
}

function my_nav_menu_item_jbauth_meta_box() {

    $opt_name_clientid = 'wp_jba_clientid';
    $opt_name_clientsecret = 'wp_jba_clientsecret';
    $opt_val_clientid = get_option($opt_name_clientid);
    $opt_val_clientsecret = get_option($opt_name_clientsecret);
    $client_id = $opt_val_clientid;
    $client_secret = $opt_val_clientsecret;

    $url_redirect = get_site_url(); //plugins_url('login.php', __FILE__ );
    $state = 'oiEWJOD82938ojdKK';
    $url = 'https://api.justinback.com/IOAuth/authorize.php?response_type=code&client_id=' . $client_id . '&state=' . $state . '&scope=OAuth_Access OAuth_User_Email OAuth_User_Firstname OAuth_User_Lastname OAuth_User_Username';
    ?>

    <div id="posttype-wl-login" class="posttypediv">
        <div id="tabs-panel-wishlist-login" class="tabs-panel tabs-panel-active">
            <ul id ="wishlist-login-checklist" class="categorychecklist form-no-clear">
                <li>
                    <input type="checkbox" class="menu-item-checkbox" style="display:none" name="menu-item[-1][menu-item-object-id]" value="1" checked="checked">
                    </label>
                    <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
                    <p><label class="menu-item-title">
                            <?php _e('This will add a Login Button to your menu', 'jbauth-oauth-client'); ?>
                            <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" id="menu-item-title-inicial" placeholder="Title" value="Login">
                        </label></p>
                    <input type="hidden" class="menu-item-url"  name="menu-item[-1][menu-item-url]" value="<?php echo $url; ?>">
                    </label></p>
                    <input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="jbauth-oauth-login-pop">
                </li>
            </ul>
        </div>
        <p class="button-controls">
            <span class="list-controls" style="display:none">
                <a href="/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-page" class="select-all">Selecciona todo</a>
            </span>
            <span class="add-to-menu">
                <input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-wl-login" <?php if (empty($client_id) || empty($client_secret)) echo "disabled"; ?>>
                <span class="spinner"></span>
            </span>
        </p>
    </div>
    <?php
}

add_action('admin_menu', 'jbauth_setup_menu');

function jbauth_setup_menu() {
    add_options_page('JBAuth Settings Page', 'JBAuth Settings', 'manage_options', 'jbauth-plugin', 'jbauth_init', 81);
}

function jbauth_init() {

    if (!current_user_can('manage_options')) {
        wp_die(_e('You are not authorized to view this page.', 'jbauth-oauth-client'));
    }

    $opt_name_clientid = 'wp_jba_clientid';
    $opt_name_clientsecret = 'wp_jba_clientsecret';
    $opt_name_apikey = 'wp_jba_apikey';
    $opt_name_urlafter = 'wp_jba_urlafter';
    $opt_name_email_verified = 'wp_jba_email_verified';
    $opt_name_register = 'wp_jba_register';
    $opt_val_clientid = get_option($opt_name_clientid);
    $opt_val_clientsecret = get_option($opt_name_clientsecret);
    $opt_val_email_verified = get_option($opt_name_email_verified);
    $opt_val_apikey = get_option($opt_name_apikey);
    $opt_val_urlafter = get_option($opt_name_urlafter);
    $opt_val_register = get_option($opt_name_register);
    $data_field_name_clientid = 'wp_jbauth_jba_clientid';
    $data_field_name_email_verified = 'wp_jbauth_jba_email_verified';
    $data_field_name_clientsecret = 'wp_jbauth_jba_clientsecret';
    $data_field_name_apikey = 'wp_jbauth_jba_apikey';
    $data_field_name_urlafter = 'wp_jbauth_jba_urlafter';
    $data_field_name_register = 'wp_jbauth_jba_register';
    $hidden_field_name = 'wp_jbauth_jba_hidden';
    $url_redirect = get_site_url();

    if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == '23hH2098KK_12') {
        $opt_val_clientid = $_POST[$data_field_name_clientid];
        $opt_val_clientsecret = $_POST[$data_field_name_clientsecret];
        $opt_val_apikey = $_POST[$data_field_name_apikey];
        $opt_val_urlafter = $_POST[$data_field_name_urlafter];
        $opt_val_register = $_POST[$data_field_name_register];
        $opt_val_email_verified = $_POST[$data_field_name_email_verified];
        update_option($opt_name_email_verified, $opt_val_email_verified);
        update_option($opt_name_clientid, $opt_val_clientid);
        update_option($opt_name_clientsecret, $opt_val_clientsecret);
        update_option($opt_name_clientsecret, $opt_val_clientsecret);
        update_option($opt_name_urlafter, $opt_val_urlafter);
        update_option($opt_name_apikey, $opt_val_apikey);
        update_option($opt_name_register, $opt_val_register);
        ?>
        <div class="updated"><p><strong><?php _e('settings saved.', 'jbauth-oauth-client'); ?></strong></p></div>
        <?php
    }
    ?>
    <h1><?php _e('JBAuth Authentication Plugin', 'jbauth-oauth-client'); ?></h1>
    <p><span><?php _e('by Justin René Back', 'jbauth-oauth-client'); ?></span><p>
    <p><?php _e('Go to https://dev.justinback.com and create a new Application and API Key if you haven\'t already', 'jbauth-oauth-client'); ?></p>
    <p><?php _e('Then enter the  client id and secret from your Application below, the api key from the dashboard and save the changes', 'jbauth-oauth-client'); ?></p>
    <form name="form1" method="post" action="">
        <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="23hH2098KK_12">
        <p>
            <label for="<?php echo $data_field_name_clientid; ?>"><?php _e('Client ID: ', 'jbauth-oauth-client'); ?></label><br />
            <input type="text" id="<?php echo $data_field_name_clientid; ?>" name="<?php echo $data_field_name_clientid; ?>" value="<?php echo $opt_val_clientid; ?>" size="120" />
        </p>
        <p>
            <label for="<?php echo $data_field_name_apikey; ?>"><?php _e('API Key: ', 'jbauth-oauth-client'); ?></label><br />
            <input type="text" id="<?php echo $data_field_name_apikey; ?>" name="<?php echo $data_field_name_apikey; ?>" value="<?php echo $opt_val_apikey; ?>" size="120" />
        </p>
        <p>
        <p>
            <label for="<?php echo $data_field_name_clientsecret; ?>"><?php _e('Client Secret: ', 'jbauth-oauth-client'); ?></label><br />
            <input type="text" id="<?php echo $data_field_name_clientsecret; ?>" name="<?php echo $data_field_name_clientsecret; ?>" value="<?php echo $opt_val_clientsecret; ?>" size="120" />
        </p>
        <p>
        <h4><?php
            _e('Important: Make sure you have entered as authorized Url redirect, the following URI: ', 'jbauth-oauth-client');
            echo $url_redirect;
            ?></h4>
    </p>
    <p>
        <label for="<?php echo $data_field_name_register; ?>"><?php _e('Check to allow user registration ', 'jbauth-oauth-client'); ?>
            <input type="checkbox" id="<?php echo $data_field_name_register; ?>" name="<?php echo $data_field_name_register; ?>" <?php
            if ($opt_val_register) {
                echo 'checked="checked"';
            }
            ?> /></label>
    <p><span> <?php _e('If you allow that users can be registered through JBAuth, a user will be created after the login with JBAuth, if the user doesn\'t exists on the site.', 'jbauth-oauth-client'); ?> </span></p>
    
    <label for="<?php echo $data_field_name_email_verified; ?>"><?php _e('Check to allow email verification ', 'jbauth-oauth-client'); ?>
            <input type="checkbox" id="<?php echo $data_field_name_email_verified; ?>" name="<?php echo $data_field_name_email_verified; ?>" <?php
            if ($opt_val_email_verified) {
                echo 'checked="checked"';
            }
            ?> /></label>
    <p><span> <?php _e('If you allow that emails can be verified, the plugin will set the user email to verified based on the email verification status on JBAuth', 'jbauth-oauth-client'); ?> </span></p>
    

    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
    </form>
    <h3><?php _e('As a widget: [jbauthbtn]', 'jbauth-oauth-client'); ?></h3>

    <p><?php _e('To put your button in a Widget use our Widget "jbauth Oauth Widget", where you can add a Title and a Description to your Button', 'jbauth-oauth-client'); ?></p>

    <p><?php _e('To add the JBAuth button directly to your php code, you can use <code>do_shortcode(\'[jbauthbtn]\');</code>.', 'jbauth-oauth-client'); ?></p>

    <p><?php _e('To add it as a shortcode, just add [jbauthbtn]', 'jbauth-oauth-client'); ?></p>
    <?php
}

add_filter('wp_setup_nav_menu_item', 'jbauth_item_setup');

function jbauth_item_setup($item) {
    if ($item->object == 'custom' && $item->classes[0] == 'jbauth-oauth-login-pop') {
        $item->type_label = 'jbauth Oauth';
    }
    return $item;
}
?>