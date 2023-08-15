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
        <h2>Register Post</h2>
        <form method="POST" action="#" id="contact-form">
            <p>
                <label for="name">Name:</label><br />
                <input class="input-text" type="text" name="name" id="post-name" required>
            </p>

            <p>
                <label for="email">Email:</label><br />
                <input class="input-text" type="email" name="email" id="post-email" required>
            </p>

            <p>
                <label for="post_title">Post Title:</label><br />
                <input class="input-text" type="text" name="post_title" id="post-title" required>
            </p>

            <p>
                <label for="post_content">Post Content:</label><br />
                <textarea class="input-text" name="post_content" rows="4" required id="post-content"></textarea>
            </p>

            <p><input type="submit" value="Submit"  ></p>
        </form>
    </div>
    <?php
}
add_shortcode('custom_contact_form', 'assp_custom_contact_form_shortcode');

function assp_enqueue_files() {
    wp_enqueue_script( 'custom_js', plugins_url('assignment-plugin') . '/assets/js/ajax-form.js', array( 'jquery' ), '', true );
    wp_enqueue_style( 'main_css', plugins_url('assignment-plugin') . '/assets/css/style.css' );
    wp_localize_script( 'custom_js', 'form_data', array(
        'ajax_url'   => admin_url( 'admin-ajax.php' ),
        'ajax_nonce' => wp_create_nonce('assp_nonce_action_name'),
    ) );
}
add_action( 'wp_enqueue_scripts', 'assp_enqueue_files' );
function assp_update_product_info() {
    // If nonce verification fails die.
    if ( ! wp_verify_nonce( $_POST['security'], 'assp_nonce_action_name' ) ) {
        wp_die();
    }

	$name 		  = ! empty( $_POST["post_name"] ) ? sanitize_text_field( $_POST["post_name"] ) : '';
	$email 	      = ! empty( $_POST["post_email"] ) ? sanitize_email( $_POST["post_email"] ) : '';
	$post_title   = ! empty( $_POST["post_title"] ) ? sanitize_text_field( $_POST["post_title"] ) : '';
	$post_content = ! empty( $_POST["post_content"] ) ? sanitize_text_field( $_POST["post_content"] ) : '';

    $user_id =  get_current_user_id();

    $my_post = array(
        'post_title'   => $post_title,
        'post_content' => $post_content,
        'post_user_id' => get_current_user_id(),
        'post_status'  => 'publish',
        'post_type'    => 'post',
    );

    // Adding New User
    $username       = sanitize_user( $email );
    $password       = wp_generate_password();
    $user_id        = wp_create_user( $username, $password, $email );

    wp_update_user(
	    array(
            'ID' => $user_id,
            'display_name' => $name
        )
    );

    $user    = new WP_User( $user_id );
	$post_id = wp_insert_post( $my_post );

	$user->set_role( 'editor' );

	// Update post author
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_author' => $user_id
		)
	);

	$message_headers  = "xyz.com\n" . 'Content-Type: text/plain; charset ="' . get_option( 'blog_charset' ) . "\"\n";
	$message_headers .= $username;
    $get_url = get_permalink();

	$mail_status = wp_mail( $username, 'Test Email Status', "Permalink: <a href='$get_url'>$get_url</a>" , $message_headers );

    wp_send_json_success( array(
        'my_data' => $user_id,
        'data_recieved_from_js' => $_POST,
        'email_status' => $mail_status,
    ) );
}

function assp_ajax_load(){
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        add_action( 'wp_ajax_my_ajax_hook', 'assp_update_product_info' );
    }
}

add_action("plugins_loaded", "assp_ajax_load");

