<?php
/* 
Plugin Name: Contact Form 7 confirm email field
Description: Contact Form 7 confirm email field
Author: Vallabh vyas
Version: 1.1
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


//this is done
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

//this is fixed
function wpcf7_add_tag_generator_confirm_email() {
    $tag_generator = WPCF7_TagGenerator::get_instance();
    $tag_generator->add( 'confirm_email', __( 'confirm_email', 'contact-form-7' ),
        'wpcf7_tag_generator_confirm_email' );

}


function wpcf7_tag_generator_confirm_email( $contact_form , $args = '' ) {
    $args = wp_parse_args( $args, array() );
    $type = $args['id'];

    if ( ! in_array( $type, array( 'email', 'url', 'tel','confirm_email' ) ) ) {
        $type = 'text';
    }

    if ( 'text' == $type ) {
        $description = __( "Generate a form-tag for a single-line plain text input field. For more details, see %s.", 'contact-form-7' );
    } elseif ( 'email' == $type ) {
        $description = __( "Generate a form-tag for a single-line email address input field. For more details, see %s.", 'contact-form-7' );
    } elseif ( 'url' == $type ) {
        $description = __( "Generate a form-tag for a single-line URL input field. For more details, see %s.", 'contact-form-7' );
    } elseif ( 'tel' == $type ) {
        $description = __( "Generate a form-tag for a single-line telephone number input field. For more details, see %s.", 'contact-form-7' );
    } elseif ( 'confirm_email' == $type ) {
        $description = __( "Generate a form-tag for a single-line confirm email input field.");
    }

    $desc_link = wpcf7_link( __( 'http://contactform7.com/text-fields/', 'contact-form-7' ), __( 'Text Fields', 'contact-form-7' ) );

    ?>
    <div class="control-box">
        <fieldset>
            <legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
                            <label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
                    <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th>
                    <td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
                        <label><input type="checkbox" name="placeholder" class="option" /> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'contact-form-7' ) ); ?></label></td>
                </tr>

                <?php if ( in_array( $type, array( 'text', 'email', 'url','confirm_email' ) ) ) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( __( 'Akismet', 'contact-form-7' ) ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php echo esc_html( __( 'Akismet', 'contact-form-7' ) ); ?></legend>

                                <?php if ( 'text' == $type ) : ?>
                                    <label>
                                        <input type="checkbox" name="akismet:author" class="option" />
                                        <?php echo esc_html( __( "This field requires author's name", 'contact-form-7' ) ); ?>
                                    </label>
                                <?php elseif ( 'email' == $type ) : ?>
                                    <label>
                                        <input type="checkbox" name="akismet:author_email" class="option" />
                                        <?php echo esc_html( __( "This field requires author's email address", 'contact-form-7' ) ); ?>
                                    </label>
                                <?php elseif ( 'url' == $type ) : ?>
                                    <label>
                                        <input type="checkbox" name="akismet:author_url" class="option" />
                                        <?php echo esc_html( __( "This field requires author's URL", 'contact-form-7' ) ); ?>
                                    </label>
                                    <?php elseif ( 'confirm_email' == $type ) : ?>
                                    <label>
                                        <input type="checkbox" name="akismet:author_url" class="option" />
                                        <?php echo esc_html( __( "This field requires author's URL", 'contact-form-7' ) ); ?>
                                    </label>
                                <?php endif; ?>

                            </fieldset>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
                    <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
                    <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
                </tr>

                </tbody>
            </table>
        </fieldset>
    </div>

    <div class="insert-box">
        <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

        <div class="submitbox">
            <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
        </div>

        <br class="clear" />

        <p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
    </div>
<?php
}
?>