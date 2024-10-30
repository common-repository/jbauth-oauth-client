<?php

function jbauthoauth_admin_scripts() {
    wp_enqueue_script('jbauthoauth-script', plugins_url('../js/scripts.js', __FILE__), array('jquery'));
    $logged = ( is_user_logged_in() ) ? "on" : "off";
    $nonce = wp_create_nonce('jbauthbutton');
    $data_array = array(
        'text' => __('Logged', 'jbauth-oauth'),
        'lgurl' => wp_logout_url(),
        'logged' => $logged,
        'nonce' => $nonce
    );
    wp_localize_script('jbauthoauth-script', 'menuitem', $data_array);
}

add_action('wp_enqueue_scripts', 'jbauthoauth_admin_scripts');

function clean_scripts_jba($url) {
    $urlclean = preg_replace('/((\%3C)|(\&lt;)|<)(script\b)[^>]*((\%3E)|(\&gt;)|>)(.*?)((\%3C)|(\&lt;)|<)(\/script)((\%3E)|(\&gt;)|>)|((\%3C)|<)((\%69)|i|(\%49))((\%6D)|m|(\%4D))((\%67)|g|(\%47))[^\n]+((\%3E)|>)/is', "", $url);
    return $urlclean;
}

add_action('init', 'session_initjba');

function session_initjba() {
    if (isset($_GET['noheader'])) {
        require_once ABSPATH . 'wp-admin/admin-header.php';
    }
    if (isset($_GET['state'])) {

        $state = clean_scripts_jba($_GET['state']);

        if (!wp_verify_nonce($state, 'jbauthbutton')) {
            wp_die(_e('You are making a not valid call', 'jbauth-oauth-client'), 'Error', array('back_link' => true));
        } else {

            $sessionstate = $state;
            $code = clean_scripts_jba($_GET['code']);
            $url_redirect = get_site_url();
            $opt_name_clientid = 'wp_jba_clientid';
            $opt_name_clientsecret = 'wp_jba_clientsecret';
            $opt_name_apikey = 'wp_jba_apikey';
            $opt_name_urlafter = 'wp_jba_urlafter';
            $opt_name_register = "wp_jba_register";
            $opt_val_clientid = get_option($opt_name_clientid);
            $opt_val_clientsecret = get_option($opt_name_clientsecret);
            $opt_val_apikey = get_option($opt_name_apikey);
            $opt_val_urlafter = get_option($opt_name_urlafter);
            $opt_val_register = get_option($opt_name_register);
            $client_id = $opt_val_clientid;
            $client_secret = $opt_val_clientsecret;
            $redirectadm = get_site_url();

            $url = 'https://api.justinback.com/IOAuth/token.php';
            $args = array(
                'method' => 'POST',
                'httpversion' => '1.1',
                'blocking' => true,
                'body' => array(
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'key' => $opt_val_apikey,
                ),
            );

            /*
             * We disable this for the sake of security!
             */
            //add_filter('https_ssl_verify', '__return_false');
            $data = wp_remote_post($url, $args);
            if (is_wp_error($data)) {
                $error_message = $data->get_error_message();
                echo "<script>alert('" . $error_message . "');</script>";
            }
            $data = json_decode($data["body"]);

            $access_token = $data->response->result->access_token;
        }

        if (isset($access_token)) {
            $url = 'https://api.justinback.com/IOAuth/user.php';
            $args = array(
                'method' => 'POST',
                'httpversion' => '1.1',
                'blocking' => true,
                'body' => array(
                    'access_token' => $access_token,
                    'key' => $opt_val_apikey,
                ),
            );

            /*
             * We disable this for the sake of security!
             */
            //add_filter('https_ssl_verify', '__return_false');
            $response = wp_remote_post($url, $args);
            
            if (is_wp_error($response)) {
                $error_message = $data->get_error_message();
                echo "<script>alert('" . $error_message . "');</script>";
            } else {
                $json = json_decode($response['body'])->response->result;

                if (json_decode($response['body'])->response->status != 200) {
                    wp_die(_e('An internal server error occurred while contacting JBAuth!<br><br><b>' . $data->response->message . '</b>', 'jbauth-oauth-client'), 'Error');
                }

                $email = $json->Email;
                $name = $json->Firstname;
                $familyname = $json->Lastname;
                $usercom = $json->Username;
                $usern = sanitize_user($usercom);
                if (email_exists($email)) {
                    $user_id = email_exists($email);
                    wp_set_auth_cookie($user_id);
                    update_user_meta($user_id, "first_name", $name);
                    update_user_meta($user_id, "last_name", $familyname);
                    update_user_meta($user_id, "jbauth_access_token", $access_token);
                    if ($json->Admin) {
                        update_user_meta($user_id, "jbauth_employee", true);
                    }


                    wp_redirect($redirectadm);
                    exit();
                } else {
                    if (!$opt_val_register) {
                        wp_die(_e('Your JBAuth account doesn\'t match any user on this page', 'jbauth-oauth-client'), 'Error', array('back_link' => true));
                        exit;
                    } else {
                        $create = wp_create_user($usern, bin2hex(random_bytes(5)), $email);
                        if (is_wp_error($create)) {
                            wp_die($create);
                        }
                        $user_id = email_exists($email);
                        wp_set_auth_cookie($user_id);
                        update_user_meta($user_id, "jbauth_access_token", $access_token);
                        update_user_meta($user_id, "first_name", $name);
                        update_user_meta($user_id, "last_name", $familyname);
                        if ($json->Email_verified && get_option("wp_jba_email_verified")) {
                            update_user_meta($user_id, 'is_activated', (int) $json->Email_verified);
                        }
                        wp_redirect(get_site_url());
                        exit();
                    }
                }
            }
        } else {

            if (isset($_GET["error"])) {
                wp_die(_e('Error: ' . $_GET["error_description"], 'jbauth-oauth-client'), 'Error', array('back_link' => true));
            }

            if (isset($data->response->result->error)) {
                wp_die(_e('Error: ' . $data->response->result->error_description, 'jbauth-oauth-client'), 'Error', array('back_link' => true));
            }

            if ($data->response->status != 200) {
                wp_die(_e('An internal server error occurred while contacting JBAuth!<br><br><b>' . $data->response->message . '</b>', 'jbauth-oauth-client'), 'Error');
            }
        }
    }
}

function jbauthoauth_create_widget() {
    include_once plugin_dir_path(__FILE__) . 'widget.php';
    register_widget('jbauthoauth_widget');
}

add_action('widgets_init', 'jbauthoauth_create_widget');
