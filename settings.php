<?php

    if (defined('ALLOW_INCLUDE') === false)
        die('no direct access');

?>

<div class="wrap">
   <a name="yocaptcha"></a>
   <h2><?php _e('yoCAPTCHA Options', 'yocaptcha'); ?></h2>
   <p></p>
   
   <form method="post" action="options.php">
      <?php settings_fields('yocaptcha_options_group'); ?>

      <h3><?php _e('Authentication', 'yocaptcha'); ?></h3>
      <p><?php _e('These keys are required before you are able to do anything else.', 'yocaptcha'); ?> <?php _e('You can get the keys', 'yocaptcha'); ?> <a href="<?php echo yocaptcha_get_signup_url($this->blog_domain(), 'wordpress');?>" title="<?php _e('Get your yoCAPTCHA API Keys', 'yocaptcha'); ?>"><?php _e('here', 'yocaptcha'); ?></a>.</p>
      <p><?php _e('Be sure not to mix them up! The public and private keys are not interchangeable!'); ?></p>
      
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Public Key', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[public_key]" size="40" value="<?php echo $this->options['public_key']; ?>" />
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Private Key', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[private_key]" size="40" value="<?php echo $this->options['private_key']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Comment Options', 'yocaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'yocaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="yocaptcha_options[show_in_comments]" name="yocaptcha_options[show_in_comments]" value="1" <?php checked('1', $this->options['show_in_comments']); ?> />
               <label for="yocaptcha_options[show_in_comments]"><?php _e('Enable for comments form', 'yocaptcha'); ?></label>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Target', 'yocaptcha'); ?></th>
            <td>
               <input type="checkbox" id="yocaptcha_options[bypass_for_registered_users]" name="yocaptcha_options[bypass_for_registered_users]" value="1" <?php checked('1', $this->options['bypass_for_registered_users']); ?> />
               <label for="yocaptcha_options[bypass_for_registered_users]"><?php _e('Hide for Registered Users who can', 'yocaptcha'); ?></label>
               <?php $this->capabilities_dropdown(); ?>
            </td>
         </tr>

         <tr valign="top">
            <th scope="row"><?php _e('Presentation', 'yocaptcha'); ?></th>
            <td>
               <label for="yocaptcha_options[comments_theme]"><?php _e('Theme:', 'yocaptcha'); ?></label>
               <?php $this->theme_dropdown('comments'); ?>
            </td>
         </tr>

         <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[comments_tab_index]" size="10" value="<?php echo $this->options['comments_tab_index']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Registration Options', 'yocaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('Activation', 'yocaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="yocaptcha_options[show_in_registration]" name="yocaptcha_options[show_in_registration]" value="1" <?php checked('1', $this->options['show_in_registration']); ?> />
               <label for="yocaptcha_options[show_in_registration]"><?php _e('Enable for registration form', 'yocaptcha'); ?></label>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Presentation', 'yocaptcha'); ?></th>
            <td>
               <label for="yocaptcha_options[registration_theme]"><?php _e('Theme:', 'yocaptcha'); ?></label>
               <?php $this->theme_dropdown('registration'); ?>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Tab Index', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[registration_tab_index]" size="10" value="<?php echo $this->options['registration_tab_index']; ?>" />
            </td>
         </tr>
      </table>
      
      <h3><?php _e('General Options', 'yocaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('yoCAPTCHA Form', 'yocaptcha'); ?></th>
            <td>
               <label for="yocaptcha_options[yocaptcha_language]"><?php _e('Language:', 'yocaptcha'); ?></label>
               <?php $this->yocaptcha_language_dropdown(); ?>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Standards Compliance', 'yocaptcha'); ?></th>
            <td>
               <input type="checkbox" id ="yocaptcha_options[xhtml_compliance]" name="yocaptcha_options[xhtml_compliance]" value="1" <?php checked('1', $this->options['xhtml_compliance']); ?> />
               <label for="yocaptcha_options[xhtml_compliance]"><?php _e('Produce XHTML 1.0 Strict Compliant Code', 'yocaptcha'); ?></label>
            </td>
         </tr>
      </table>
      
      <h3><?php _e('Error Messages', 'yocaptcha'); ?></h3>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><?php _e('yoCAPTCHA Ignored', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[no_response_error]" size="70" value="<?php echo $this->options['no_response_error']; ?>" />
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><?php _e('Incorrect Guess', 'yocaptcha'); ?></th>
            <td>
               <input type="text" name="yocaptcha_options[incorrect_response_error]" size="70" value="<?php echo $this->options['incorrect_response_error']; ?>" />
            </td>
         </tr>
      </table>

      <p class="submit"><input type="submit" class="button-primary" title="<?php _e('Save yoCAPTCHA Options') ?>" value="<?php _e('Save yoCAPTCHA Changes') ?> &raquo;" /></p>
   </form>
   
   <?php do_settings_sections('yocaptcha_options_page'); ?>
</div>