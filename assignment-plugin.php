<?php
/*
* Plugin Name:       Assignment Plugin
* Plugin URI:        https://wordpress.org/plugins/
* Description:       This plugin registers a shortcode and renders a form.additionally it sends an email to the mentioned email address and so on.
* Version:           1.0.0
* Requires at least: 5.2
* Requires PHP:      7.2
* Author:            Sanzida
* Author URI:        https://sanzida.me/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Update URI:        https://example.com/my-plugin/
* Text Domain:       assignment-plugin
* Domain Path:       /languages
*/

function assp_activation_hook(){}
register_activation_hook(__FILE__ , "assp_activation_hook");
function assp_deactivation_hook(){}
register_deactivation_hook(__FILE__, "assp_deactivation_hook");
function assp_text_domain(){
    load_plugin_textdomain("assignment-plugin",false , dirname(__FILE__). "/languages");
}
add_action("plugins_loaded", "assp_text_domain");

function assp_custom_contact_form_shortcode(){
    ?>
    <div class="custom-contact-form">
        <form method="POST" action="#" >
            <p><label for="name">Name:</label><br />
                <input type="text" name="name" required></p>

            <p><label for="email">Email:</label><br />
                <input type="email" name="email" required></p>

            <p><label for="post_title">Post Title:</label><br />
                <input type="text" name="post_title" required></p>

            <p><label for="post_content">Post Content:</label><br />
                <textarea name="post_content" rows="4" required></textarea></p>

            <p><input type="submit" value="Submit"></p>
        </form>
    </div>
    <?php
}

function assp_custom_contact_form_submit() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Process form data and send email
        $post_title = sanitize_text_field($_POST["post_title"]);
        $post_content = sanitize_text_field($_POST["post_content"]);
        $name = sanitize_text_field($_POST["name"]);
        $email = sanitize_email($_POST["email"]);

        // Create a new post
        $post_args = array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_user_id' => get_current_user_id(),
            'post_status' => 'publish',
            'post_type' => 'post',
        );

        $to = $email;
        $subject = 'Email with Attachment';
        $message = 'This email includes an attachment.';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $attachments =  get_permalink();

        $sent = wp_mail($to, $subject, $message, $headers, $attachments);

        if ($sent) {
            echo 'Email sent successfully!';
        } else {
            echo 'Email not sent.';
        }


        // Adding New User
        $username = sanitize_user($email);
        $password = wp_generate_password();
        $user_id = wp_create_user($username, $password, $email);
        wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        $post_id = wp_insert_post($post_args);
        // Update post author
        wp_update_post(array('ID' => $post_id, 'post_author' => $user_id));

    }
}
add_shortcode('custom_contact_form', 'assp_custom_contact_form_shortcode');
add_action("init", "assp_custom_contact_form_submit");

