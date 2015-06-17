<?php
/* 
Plugin Name: Contact Form 7 confirm email field
Description: Contact Form 7 confirm email field
Author: Vallabh vyas
Version: 1.0
Author URI: http://www.omkarsoft.com
Plugin URI: http://www.omkarsoft.com
*/




add_action('plugins_loaded', 'contact_form_7_confirm_email', 10);

function contact_form_7_confirm_email() {
	global $pagenow;
	if(function_exists('wpcf7_add_shortcode')) {
		wpcf7_add_shortcode( array( 'confirm_email', 'confirm_email*' ), 'wpcf7_confirm_email_shortcode_handler', true );
		add_filter( 'wpcf7_validate_confirm_email', 'wpcf7_confirm_email_validation_filter', 10, 2 );
        add_filter( 'wpcf7_validate_confirm_email*', 'wpcf7_confirm_email_validation_filter', 10, 2 );
		add_action( 'admin_init', 'wpcf7_add_tag_generator_confirm_email', 30 );
	} else {
		if($pagenow != 'plugins.php') { return; }
		add_action('admin_notices', 'cfconfirm_emailfieldserror');
		wp_enqueue_script('thickbox');
		function cfconfirm_emailfieldserror() {
			$out = '<div class="error" id="messages"><p>';
			$out .= 'The Contact Form 7 plugin must be installed and activated for the confirm_email Validation for Contact Form 7 to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>';
			$out .= '</p></div>';
			echo $out;
		}
	}
}



function wpcf7_confirm_email_shortcode_handler( $tag ) {
    $tag = new WPCF7_Shortcode( $tag );

    if ( empty( $tag->name ) )
        return '';

    $validation_error = wpcf7_get_validation_error( $tag->name );

    $class = wpcf7_form_controls_class( $tag->type, 'wpcf7-confirm_email' );

    if ( in_array( $tag->basetype, array( 'email', 'url', 'tel','confirm_email' ) ) )
        $class .= ' wpcf7-validates-as-' . $tag->basetype;

    if ( $validation_error )
        $class .= ' wpcf7-not-valid';

    $atts = array();

    $atts['size'] = $tag->get_size_option( '40' );
    $atts['maxlength'] = $tag->get_maxlength_option();
    $atts['minlength'] = $tag->get_minlength_option();

    if ( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ) {
        unset( $atts['maxlength'], $atts['minlength'] );
    }

    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    if ( $tag->has_option( 'readonly' ) )
        $atts['readonly'] = 'readonly';

    if ( $tag->is_required() )
        $atts['aria-required'] = 'true';

    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

    $value = (string) reset( $tag->values );

    if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
        $atts['placeholder'] = $value;
        $value = '';
    }

    $value = $tag->get_default_option( $value );

    $value = wpcf7_get_hangover( $tag->name, $value );

    $atts['value'] = $value;

    if ( wpcf7_support_html5() ) {
        $atts['type'] = $tag->basetype;
    } else {
        $atts['type'] = 'confirm_email';
    }

    $atts['name'] = $tag->name;

    $atts = wpcf7_format_atts( $atts );

    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
        sanitize_html_class( $tag->name ), $atts, $validation_error );

    return $html;
}



function wpcf7_confirm_email_validation_filter( $result, $tag ) {
    $tag = new WPCF7_Shortcode( $tag );


	$type = $tag->basetype;
    $name = $tag->name;
    $values = $tag->values;

    $value = isset( $_POST[$name] )
        ? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
        : '';

    if ( 'confirm_email' == $tag->basetype ) {
        if ( $tag->is_required() && '' == $value ) {
            $result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
        } elseif ( '' != $value && ! wpcf7_is_email( $value ) ) {
            $result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );
        }elseif (  $value != $_POST['your-email'] ) {
            $result->invalidate( $tag, wpcf7_get_message( 'invalid_confirm_email' ) );
        }
    }
	
	return $result;
}


function wpcf7_add_tag_generator_confirm_email() {
    if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
        return;
    wpcf7_add_tag_generator( 'confirm_email', __( 'confirm_email', 'contact-form-7' ),
        'wpcf7-tg-pane-confirm_email', 'wpcf7_tg_pane_confirm_email' );
}


function wpcf7_tg_pane_confirm_email( $contact_form ) {
        $type = 'confirm_email';
    ?>
    <div id="wpcf7-tg-pane-confirm_email" class="hidden">
        <form action="">
            <table>
                <tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'contact-form-7' ) ); ?></td></tr>
                <tr><td><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
            </table>


            <table>
                <tr>
                    <td><code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
                        <input type="text" name="id" class="idvalue oneline option" /></td>

                    <td><code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
                        <input type="text" name="class" class="classvalue oneline option" /></td>
                </tr>

                <tr>
                    <td><code>size</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
                        <input type="number" name="size" class="numeric oneline option" min="1" /></td>

                    <td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
                        <input type="number" name="maxlength" class="numeric oneline option" min="1" /></td>
                </tr>

                <?php if ( in_array( $type, array( 'text', 'email', 'url' ) ) ) : ?>
                    <tr>
                        <td colspan="2"><?php echo esc_html( __( 'Akismet', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
                            <?php if ( 'text' == $type ) : ?>
                                <input type="checkbox" name="akismet:author" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's name", 'contact-form-7' ) ); ?><br />
                            <?php elseif ( 'email' == $type ) : ?>
                                <input type="checkbox" name="akismet:author_email" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's email address", 'contact-form-7' ) ); ?>
                            <?php elseif ( 'url' == $type ) : ?>
                                <input type="checkbox" name="akismet:author_url" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's URL", 'contact-form-7' ) ); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br /><input type="text" name="values" class="oneline" /></td>

                    <td>
                        <br /><input type="checkbox" name="placeholder" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as placeholder?', 'contact-form-7' ) ); ?>
                    </td>
                </tr>
            </table>

            <div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?><br /><input type="text" name="<?php echo $type; ?>" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>

            <div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'contact-form-7' ) ); ?><br /><input type="text" class="mail-tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" /></div>
        </form>
    </div>
<?php
}



if ( !function_exists( 'cf7_custom_validation_messages' ) ) {
    function cf7_custom_validation_messages( $messages ) {
        return array_merge( $messages, array(
            'invalid_confirm_email' => array(
                'description' => __( "Confirm email does not match", 'contact-form-7' ),
                'default' => __( 'Confirm email does not match.', 'contact-form-7' )
            )
        ));
    }


    add_filter( 'wpcf7_messages', 'cf7_custom_validation_messages' );

}
?>
