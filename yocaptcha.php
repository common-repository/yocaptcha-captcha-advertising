<?php

require_once('wp-plugin.php');

if (!class_exists('yoCAPTCHA')) {
    class yoCAPTCHA extends WPPlugin {
        // member variables
        private $saved_error;
        
        // php 4 constructor
        function yoCAPTCHA($options_name) {
            $args = func_get_args();
            call_user_func_array(array(&$this, "__construct"), $args);
        }
        
        // php 5 constructor
        function __construct($options_name) {
            parent::__construct($options_name);
            
            $this->register_default_options();
            
            // require the yocaptcha library
            $this->require_library();
            
            // register the hooks
            $this->register_actions();
            $this->register_filters();
        }
        
        function register_actions() {
            // load the plugin's textdomain for localization
            add_action('init', array(&$this, 'load_textdomain'));

            // styling
            add_action('wp_head', array(&$this, 'register_stylesheets')); // make unnecessary: instead, inform of classes for styling
            add_action('admin_head', array(&$this, 'register_stylesheets')); // make unnecessary: shouldn't require styling in the options page
            
            if ($this->options['show_in_registration'])
                add_action('login_head', array(&$this, 'registration_style')); // make unnecessary: instead use jQuery and add to the footer?

            // options
            register_activation_hook(WPPlugin::path_to_plugin_directory() . '/wp-yocaptcha.php', array(&$this, 'register_default_options')); // this way it only happens once, when the plugin is activated
            add_action('admin_init', array(&$this, 'register_settings_group'));

            // only register the hooks if the user wants yocaptcha on the registration page
            if ($this->options['show_in_registration']) {
                // yocaptcha form display
                if ($this->is_multi_blog())
                    add_action('signup_extra_fields', array(&$this, 'show_yocaptcha_in_registration'));
                else
                    add_action('register_form', array(&$this, 'show_yocaptcha_in_registration'));
            }

            // only register the hooks if the user wants yocaptcha on the comments page
            if ($this->options['show_in_comments']) {
                add_action('comment_form', array(&$this, 'show_yocaptcha_in_comments'));
                add_action('wp_footer', array(&$this, 'save_comment_script')); // preserve the comment that was entered

                // yocaptcha comment processing (look into doing all of this with AJAX, optionally)
                add_action('wp_head', array(&$this, 'saved_comment'), 0);
                add_action('preprocess_comment', array(&$this, 'check_comment'), 0);
                add_action('comment_post_redirect', array(&$this, 'relative_redirect'), 0, 2);
            }

            // administration (menus, pages, notifications, etc.)
            add_filter("plugin_action_links", array(&$this, 'show_settings_link'), 10, 2);

            add_action('admin_menu', array(&$this, 'add_settings_page'));
            
            // admin notices
            add_action('admin_notices', array(&$this, 'missing_keys_notice'));
        }
        
        function register_filters() {
            // only register the hooks if the user wants yocaptcha on the registration page
            if ($this->options['show_in_registration']) {
                // yocaptcha validation
                if ($this->is_multi_blog())
                    add_filter('wpmu_validate_user_signup', array(&$this, 'validate_yocaptcha_response_wpmu'));
                else
                    add_filter('registration_errors', array(&$this, 'validate_yocaptcha_response'));
            }
        }
        
        function load_textdomain() {
            load_plugin_textdomain('yocaptcha', false, 'languages');
        }
        
        // set the default options
        function register_default_options() {
            if ($this->options)
               return;
           
            $option_defaults = array();
           
            $old_options = WPPlugin::retrieve_options("yocaptcha");
           
            if ($old_options) {
               $option_defaults['public_key'] = $old_options['pubkey']; // the public key for yoCAPTCHA
               $option_defaults['private_key'] = $old_options['privkey']; // the private key for yoCAPTCHA

               // placement
               $option_defaults['show_in_comments'] = $old_options['yo_comments']; // whether or not to show yoCAPTCHA on the comment post
               $option_defaults['show_in_registration'] = $old_options['yo_registration']; // whether or not to show yoCAPTCHA on the registration page

               // bypass levels
               $option_defaults['bypass_for_registered_users'] = ($old_options['yo_bypass'] == "on") ? 1 : 0; // whether to skip yoCAPTCHAs for registered users
               $option_defaults['minimum_bypass_level'] = $old_options['yo_bypasslevel']; // who doesn't have to do the yoCAPTCHA (should be a valid WordPress capability slug)

               if ($option_defaults['minimum_bypass_level'] == "level_10") {
                  $option_defaults['minimum_bypass_level'] = "activate_plugins";
               }

               // styling
               $option_defaults['comments_theme'] = $old_options['yo_theme']; // the default theme for yoCAPTCHA on the comment post
               $option_defaults['registration_theme'] = $old_options['yo_theme_reg']; // the default theme for yoCAPTCHA on the registration form
               $option_defaults['yocaptcha_language'] = $old_options['yo_lang']; // the default language for yoCAPTCHA
               $option_defaults['xhtml_compliance'] = $old_options['yo_xhtml']; // whether or not to be XHTML 1.0 Strict compliant
               $option_defaults['comments_tab_index'] = $old_options['yo_tabindex']; // the default tabindex for yoCAPTCHA
               $option_defaults['registration_tab_index'] = 30; // the default tabindex for yoCAPTCHA

               // error handling
               $option_defaults['no_response_error'] = $old_options['error_blank']; // message for no CAPTCHA response
               $option_defaults['incorrect_response_error'] = $old_options['error_incorrect']; // message for incorrect CAPTCHA response
            }
           
            else {
               // keys
               $option_defaults['public_key'] = ''; // the public key for yoCAPTCHA
               $option_defaults['private_key'] = ''; // the private key for yoCAPTCHA

               // placement
               $option_defaults['show_in_comments'] = 1; // whether or not to show yoCAPTCHA on the comment post
               $option_defaults['show_in_registration'] = 1; // whether or not to show yoCAPTCHA on the registration page

               // bypass levels
               $option_defaults['bypass_for_registered_users'] = 1; // whether to skip yoCAPTCHAs for registered users
               $option_defaults['minimum_bypass_level'] = 'read'; // who doesn't have to do the yoCAPTCHA (should be a valid WordPress capability slug)

               // styling
               $option_defaults['comments_theme'] = 'red'; // the default theme for yoCAPTCHA on the comment post
               $option_defaults['registration_theme'] = 'red'; // the default theme for yoCAPTCHA on the registration form
               $option_defaults['yocaptcha_language'] = 'en'; // the default language for yoCAPTCHA
               $option_defaults['xhtml_compliance'] = 0; // whether or not to be XHTML 1.0 Strict compliant
               $option_defaults['comments_tab_index'] = 5; // the default tabindex for yoCAPTCHA
               $option_defaults['registration_tab_index'] = 30; // the default tabindex for yoCAPTCHA

               // error handling
               $option_defaults['no_response_error'] = '<strong>ERROR</strong>: Please fill in the yoCAPTCHA form.'; // message for no CAPTCHA response
               $option_defaults['incorrect_response_error'] = '<strong>ERROR</strong>: That yoCAPTCHA response was incorrect.'; // message for incorrect CAPTCHA response
            }
            
            // add the option based on what environment we're in
            WPPlugin::add_options($this->options_name, $option_defaults);
        }
        
        // require the yocaptcha library
        function require_library() {
            require_once($this->path_to_plugin_directory() . '/yocaptchalib.php');
        }
        
        // register the settings
        function register_settings_group() {
            register_setting("yocaptcha_options_group", 'yocaptcha_options', array(&$this, 'validate_options'));
        }
        
        // todo: make unnecessary
        function register_stylesheets() {
            $path = WPPlugin::url_to_plugin_directory() . '/yocaptcha.css';
                
//            echo '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
        }
        
        // stylesheet information
        // todo: this 'hack' isn't nice, try to figure out a workaround
        function registration_style() {
            $width = 0; // the width of the yocaptcha form

            // every theme is 358 pixels wide except for the clean theme, so we have to programmatically handle that
            if ($this->options['registration_theme'] == 'clean')
                $width = 485;
            else
                $width = 360;

            echo <<<REGISTRATION
                <script type="text/javascript">
                window.onload = function() {
                    document.getElementById('login').style.width = '{$width}px';
                    document.getElementById('reg_passmail').style.marginTop = '10px';
                    document.getElementById('yocaptcha_widget_div').style.marginBottom = '10px';
                };
                </script>
REGISTRATION;
        }
        
        function yocaptcha_enabled() {
            return ($this->options['show_in_comments'] || $this->options['show_in_registration']);
        }
        
        function keys_missing() {
            return (empty($this->options['public_key']) || empty($this->options['private_key']));
        }
        
        function create_error_notice($message, $anchor = '') {
            $options_url = admin_url('options-general.php?page=wp-yocaptcha/yocaptcha.php') . $anchor;
            $error_message = sprintf(__($message . ' <a href="%s" title="WP-yoCAPTCHA Options">Fix this</a>', 'yocaptcha'), $options_url);
            
            echo '<div class="error"><p><strong>' . $error_message . '</strong></p></div>';
        }
        
        function missing_keys_notice() {
            if ($this->yocaptcha_enabled() && $this->keys_missing()) {
                $this->create_error_notice('You enabled yoCAPTCHA, but some of the yoCAPTCHA API Keys seem to be missing.');
            }
        }
        
        function validate_dropdown($array, $key, $value) {
            // make sure that the capability that was supplied is a valid capability from the drop-down list
            if (in_array($value, $array))
                return $value;
            else // if not, load the old value
                return $this->options[$key];
        }
        
        function validate_options($input) {
            // todo: make sure that 'incorrect_response_error' is not empty, prevent from being empty in the validation phase
            
            // trim the spaces out of the key, as they are usually present when copied and pasted
            // todo: keys seem to usually be 40 characters in length, verify and if confirmed, add to validation process
            $validated['public_key'] = trim($input['public_key']);
            $validated['private_key'] = trim($input['private_key']);
            
            $validated['show_in_comments'] = ($input['show_in_comments'] == 1 ? 1 : 0);
            $validated['bypass_for_registered_users'] = ($input['bypass_for_registered_users'] == 1 ? 1: 0);
            
            $capabilities = array ('read', 'edit_posts', 'publish_posts', 'moderate_comments', 'activate_plugins');
            $themes = array ('red', 'white', 'blackglass', 'clean');
            
            $yocaptcha_languages = array ('en');
            
            $validated['minimum_bypass_level'] = $this->validate_dropdown($capabilities, 'minimum_bypass_level', $input['minimum_bypass_level']);
            $validated['comments_theme'] = $this->validate_dropdown($themes, 'comments_theme', $input['comments_theme']);
            
            $validated['comments_tab_index'] = $input['comments_tab_index'] ? $input["comments_tab_index"] : 5; // use the intval filter
            
            $validated['show_in_registration'] = ($input['show_in_registration'] == 1 ? 1 : 0);
            $validated['registration_theme'] = $this->validate_dropdown($themes, 'registration_theme', $input['registration_theme']);
            $validated['registration_tab_index'] = $input['registration_tab_index'] ? $input["registration_tab_index"] : 30; // use the intval filter
            
            $validated['yocaptcha_language'] = $this->validate_dropdown($yocaptcha_languages, 'yocaptcha_language', $input['yocaptcha_language']);
            $validated['xhtml_compliance'] = ($input['xhtml_compliance'] == 1 ? 1 : 0);
            
            $validated['no_response_error'] = $input['no_response_error'];
            $validated['incorrect_response_error'] = $input['incorrect_response_error'];
            
            return $validated;
        }
        
        // display yocaptcha
        function show_yocaptcha_in_registration($errors) {
            $format = <<<FORMAT
            <script type='text/javascript'>
            var RecaptchaOptions = { theme : '{$this->options['registration_theme']}', lang : '{$this->options['yocaptcha_language']}' , tabindex : {$this->options['registration_tab_index']} };
            </script>
FORMAT;

            $comment_string = <<<COMMENT_FORM
            <script type='text/javascript'>   
            document.getElementById('yocaptcha_table').style.direction = 'ltr';
            </script>
COMMENT_FORM;

            // todo: is this check necessary? look at the latest yocaptchalib.php
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                $use_ssl = true;
            else
                $use_ssl = false;

            $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);

            // if it's for wordpress mu, show the errors
            if ($this->is_multi_blog()) {
                $error = $errors->get_error_message('captcha');
                echo '<label for="verification">Verification:</label>';
                echo ($error ? '<p class="error">'.$error.'</p>' : '');
                echo $format . $this->get_yocaptcha_html($escaped_error, $use_ssl);
            }
            
            // for regular wordpress
            else {
                echo $format . $this->get_yocaptcha_html($escaped_error, $use_ssl);
            }
        }
        
        function validate_yocaptcha_response($errors) {
            // empty so throw the empty response error
            if (empty($_POST['yocaptcha_answer']) || $_POST['yocaptcha_answer'] == '') {
                $errors->add('blank_captcha', $this->options['no_response_error']);
                return $errors;
            }

            $response = yocaptcha_check_answer($this->options['public_key'], $this->options['private_key'], $_SERVER['REMOTE_ADDR'], $_POST['yocaptcha_session'], $_POST['yocaptcha_answer']);

            // response is bad, add incorrect response error
            if (!$response->is_valid)
                if ($response->error == 'incorrect-captcha')
                    $errors->add('captcha_wrong', $this->options['incorrect_response_error']);

           return $errors;
        }
        
        function validate_yocaptcha_response_wpmu($result) {
            // must make a check here, otherwise the wp-admin/user-new.php script will keep trying to call
            // this function despite not having called do_action('signup_extra_fields'), so the yocaptcha
            // field was never shown. this way it won't validate if it's called in the admin interface
            
            if (!$this->is_authority()) {
                // blogname in 2.6, blog_id prior to that
                // todo: why is this done?
                if (isset($_POST['blog_id']) || isset($_POST['blogname']))
                    return $result;
                    
                // no text entered
                if (empty($_POST['yocaptcha_answer']) || $_POST['yocaptcha_answer'] == '') {
                    $result['errors']->add('blank_captcha', $this->options['no_response_error']);
                    return $result;
                }
                
                $response = yocaptcha_check_answer($this->options['public_key'], $this->options['private_key'], $_SERVER['REMOTEADDR'], $_POST['yocaptcha_session'], $_POST['yocaptcha_answer']);
                
                // response is bad, add incorrect response error
                // todo: why echo the error here? wpmu specific?
                if (!$response->is_valid)
                    if ($response->error == 'incorrect-captcha') {
                        $result['errors']->add('captcha_wrong', $this->options['incorrect_response_error']);
                        echo '<div class="error">' . $this->options['incorrect_response_error'] . '</div>';
                    }
                    
                return $result;
            }
        }
        
        // utility methods
        function hash_comment($id) {
            define ("YOCAPTCHA_WP_HASH_SALT", "b7e0638d85f5d7f3694f68e944136d62");
            
            if (function_exists('wp_hash'))
                return wp_hash(YOCAPTCHA_WP_HASH_SALT . $id);
            else
                return md5(YOCAPTCHA_WP_HASH_SALT . $this->options['private_key'] . $id);
        }
        
        function get_yocaptcha_html($yocaptcha_error, $use_ssl=false) {
            return yocaptcha_get_html($this->options['public_key'], $yocaptcha_error, $use_ssl, $this->options['xhtml_compliance']);
        }
        
        function show_yocaptcha_in_comments() {
            global $user_ID;

            // set the minimum capability needed to skip the captcha if there is one
            if (isset($this->options['bypass_for_registered_users']) && $this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];

            // skip the yoCAPTCHA display if the minimum capability is met
            if ((isset($needed_capability) && $needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return;

            else {
                // Did the user fail to match the CAPTCHA? If so, let them know
                if ((isset($_GET['rerror']) && $_GET['rerror'] == 'incorrect-captcha'))
                    echo '<p class="yocaptcha-error">' . $this->options['incorrect_response_error'] . "</p>";

                //modify the comment form for the yoCAPTCHA widget
                $yocaptcha_js_opts = <<<OPTS
                <script type='text/javascript'>
                    var RecaptchaOptions = { theme : '{$this->options['comments_theme']}', lang : '{$this->options['yocaptcha_language']}' , tabindex : {$this->options['comments_tab_index']} };
                </script>
OPTS;

                // todo: replace this with jquery: http://digwp.com/2009/06/including-jquery-in-wordpress-the-right-way/
                // todo: use math to increment+1 the submit button based on what the tab_index option is
                if ($this->options['xhtml_compliance']) {
                    $comment_string = <<<COMMENT_FORM
                        <div id="yocaptcha-submit-btn-area">&nbsp;</div>
COMMENT_FORM;
                }

                else {
                    $comment_string = <<<COMMENT_FORM
                        <div id="yocaptcha-submit-btn-area">&nbsp;</div>
                        <noscript>
                         <style type='text/css'>#submit {display:none;}</style>
                         <input name="submit" type="submit" id="submit-alt" tabindex="6" value="Submit Comment"/> 
                        </noscript>
COMMENT_FORM;
                }

                $use_ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");

                $escaped_error = htmlentities($_GET['rerror'], ENT_QUOTES);

                echo $yocaptcha_js_opts . $this->get_yocaptcha_html(isset($escaped_error) ? $escaped_error : null, $use_ssl) . $comment_string;
           }
        }
        
        // this is what does the submit-button re-ordering
        function save_comment_script() {
            $javascript = <<<JS
                <script type="text/javascript">
                var sub = document.getElementById('submit');
                document.getElementById('yocaptcha-submit-btn-area').appendChild (sub);
                document.getElementById('submit').tabIndex = 6;
                if ( typeof _yocaptcha_wordpress_savedcomment != 'undefined') {
                        document.getElementById('comment').value = _yocaptcha_wordpress_savedcomment;
                }
                document.getElementById('yocaptcha_table').style.direction = 'ltr';
                </script>
JS;
            echo $javascript;
        }
        
        // todo: this doesn't seem necessary
        function show_captcha_for_comment() {
            global $user_ID;
            return true;
        }
        
        function check_comment($comment_data) {
            global $user_ID;
            
            if ($this->options['bypass_for_registered_users'] && $this->options['minimum_bypass_level'])
                $needed_capability = $this->options['minimum_bypass_level'];
            
            if (($needed_capability && current_user_can($needed_capability)) || !$this->options['show_in_comments'])
                return $comment_data;
            
            if ($this->show_captcha_for_comment()) {
                // do not check trackbacks/pingbacks
                if ($comment_data['comment_type'] == '') {
                    $challenge = $_POST['yocaptcha_session'];
                    $response = $_POST['yocaptcha_answer'];
                    
                    $yocaptcha_response = yocaptcha_check_answer($this->options['public_key'], $this->options['private_key'], $_SERVER['REMOTE_ADDR'], $challenge, $response);
                    //print_r($yocaptcha_response);
                    if ($yocaptcha_response->is_valid)
                        return $comment_data;
                        
                    else {
                        $this->saved_error = $yocaptcha_response->error;
                        // http://codex.wordpress.org/Plugin_API/Filter_Reference#Database_Writes_2
                        add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
                        return $comment_data;
                    }
                }
            }
            
            return $comment_data;
        }
        
        function relative_redirect($location, $comment) {
            if ($this->saved_error != '') {
                // replace #comment- at the end of $location with #commentform
                
                $location = substr($location, 0, strpos($location, '#')) .
                    ((strpos($location, "?") === false) ? "?" : "&") .
                    'rcommentid=' . $comment->comment_ID .
                    '&rerror=' . $this->saved_error .
                    '&rchash=' . $this->hash_comment($comment->comment_ID) .
                    '#commentform';
            }
            
            return $location;
        }
        
        function saved_comment() {
            if (!is_single() && !is_page())
                return;
            
            $comment_id = $_REQUEST['rcommentid'];
            $comment_hash = $_REQUEST['rchash'];
            
            if (empty($comment_id) || empty($comment_hash))
               return;
            
            if ($comment_hash == $this->hash_comment($comment_id)) {
               $comment = get_comment($comment_id);

               // todo: removed double quote from list of 'dangerous characters'
               $com = preg_replace('/([\\/\(\)\+\;\'])/e','\'%\'.dechex(ord(\'$1\'))', $comment->comment_content);
                
               $com = preg_replace('/\\r\\n/m', '\\\n', $com);
                
               echo "
                <script type='text/javascript'>
                var _yocaptcha_wordpress_savedcomment =  '" . $com  ."';
                _yocaptcha_wordpress_savedcomment = unescape(_yocaptcha_wordpress_savedcomment);
                </script>
                ";

                wp_delete_comment($comment->comment_ID);
            }
        }
        
        // todo: is this still needed?
        // this is used for the api keys url in the administration interface
        function blog_domain() {
            $uri = parse_url(get_option('siteurl'));
            return $uri['host'];
        }
        
        // add a settings link to the plugin in the plugin list
        function show_settings_link($links, $file) {
            if ($file == plugin_basename($this->path_to_plugin_directory() . '/wp-yocaptcha.php')) {
               $settings_title = __('Settings for this Plugin', 'yocaptcha');
               $settings = __('Settings', 'yocaptcha');
               $settings_link = '<a href="options-general.php?page=wp-yocaptcha/yocaptcha.php" title="' . $settings_title . '">' . $settings . '</a>';
               array_unshift($links, $settings_link);
            }
            
            return $links;
        }
        
        // add the settings page
        function add_settings_page() {
            // add the options page
            if ($this->environment == Environment::WordPressMU && $this->is_authority())
                add_submenu_page('wpmu-admin.php', 'WP-yoCAPTCHA', 'WP-yoCAPTCHA', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));

            if ($this->environment == Environment::WordPressMS && $this->is_authority())
                add_submenu_page('ms-admin.php', 'WP-yoCAPTCHA', 'WP-yoCAPTCHA', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
            
            add_options_page('WP-yoCAPTCHA', 'WP-yoCAPTCHA', 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
        }
        
        // store the xhtml in a separate file and use include on it
        function show_settings_page() {
            include("settings.php");
        }
        
        function build_dropdown($name, $keyvalue, $checked_value) {
            echo '<select name="' . $name . '" id="' . $name . '">' . "\n";
            
            foreach ($keyvalue as $key => $value) {
                $checked = ($value == $checked_value) ? ' selected="selected" ' : '';
                
                echo '\t <option value="' . $value . '"' . $checked . ">$key</option> \n";
                $checked = NULL;
            }
            
            echo "</select> \n";
        }
        
        function capabilities_dropdown() {
            // define choices: Display text => permission slug
            $capabilities = array (
                __('all registered users', 'yocaptcha') => 'read',
                __('edit posts', 'yocaptcha') => 'edit_posts',
                __('publish posts', 'yocaptcha') => 'publish_posts',
                __('moderate comments', 'yocaptcha') => 'moderate_comments',
                __('activate plugins', 'yocaptcha') => 'activate_plugins'
            );
            
            $this->build_dropdown('yocaptcha_options[minimum_bypass_level]', $capabilities, $this->options['minimum_bypass_level']);
        }
        
        function theme_dropdown($which) {
            $themes = array (
                __('Red', 'yocaptcha') => 'red',
                __('White', 'yocaptcha') => 'white',
                __('Black Glass', 'yocaptcha') => 'blackglass',
                __('Clean', 'yocaptcha') => 'clean'
            );
            
            if ($which == 'comments')
                $this->build_dropdown('yocaptcha_options[comments_theme]', $themes, $this->options['comments_theme']);
            else if ($which == 'registration')
                $this->build_dropdown('yocaptcha_options[registration_theme]', $themes, $this->options['registration_theme']);
        }
        
        function yocaptcha_language_dropdown() {
            $languages = array (
                __('English', 'yocaptcha') => 'en',
                __('Dutch', 'yocaptcha') => 'nl',
                __('French', 'yocaptcha') => 'fr',
                __('German', 'yocaptcha') => 'de',
                __('Portuguese', 'yocaptcha') => 'pt',
                __('Russian', 'yocaptcha') => 'ru',
                __('Spanish', 'yocaptcha') => 'es',
                __('Turkish', 'yocaptcha') => 'tr'
            );
            
            $this->build_dropdown('yocaptcha_options[yocaptcha_language]', $languages, $this->options['yocaptcha_language']);
        }
    } // end class declaration
} // end of class exists clause

?>
