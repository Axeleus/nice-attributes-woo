<?php
/**
 * Author: Vitaly Kukin
 * Date: 16.09.2017
 * Time: 10:47
 */

/**
 * Output the variable product add to cart area.
 */
function woocommerce_variable_add_to_cart() {

	global $product;

	// Enqueue variation scripts
	wp_enqueue_script( 'wc-add-to-cart-variation' );

	$available_variations = $product->get_available_variations();
	$attributes           = $product->get_variation_attributes();
	$selected_attributes  = $product->get_default_attributes();

	$attribute_keys = array_keys( $attributes );

	do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo htmlspecialchars( json_encode( $available_variations ) ) ?>">
		<?php do_action( 'woocommerce_before_variations_form' ); ?>

		<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
			<p class="stock out-of-stock"><?php _e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
		<?php else : ?>
			<table class="variations" cellspacing="0">
				<tbody>

				<?php

				$product_id = $product->get_id();
				$foo         = array();
				$foo_key     = '';
				$a           = 1;
				$i           = 0;

				foreach ( $attributes as $attribute => $val ) {

					if($i == 0 ) {
						$foo_key = $attribute;
						$foo = $val;
					}

					$i++;
				}

				$attr_img = $product_id . '-' . str_replace('pa_', '', $foo_key, $a );

				if( count( $foo ) == 0 ) {
					$foo = array_values($attributes)[0];
				}

				$ex_attr = array();

				foreach ( $available_variations as $var ) {

					if(
						$var['variation_is_visible'] == 1 &&
						$var['variation_is_active'] == 1 &&
						$var['is_purchasable'] == 1 &&
						isset( $var['attributes'] ) &&
						count( $var['attributes'] ) > 0 ) {

						foreach ( $var['attributes'] as $k => $v ) {

							if( $k == 'attribute_' . $foo_key &&
							    ! empty($v) &&
							    in_array($v, $foo) ) {
								$thumb_id    = get_post_thumbnail_id( $var['variation_id'] );
								$thumb_url   = $thumb_id != '' ? wp_get_attachment_image_src( $thumb_id, 'thumbnail', true ) : false;
								$ex_attr[$v] = $thumb_url && ! strpos($thumb_url[0], '/wp-includes/') ? $thumb_url[0] : false;
							}
						}
					}
				}

				?>

				<?php foreach ( $attributes as $attribute_name => $options ) : ?>

					<?php $sanit_attribute_name = sanitize_title( $attribute_name ); ?>
					<tr>
						<td class="label"><label for="<?php echo $sanit_attribute_name; ?>"><?php echo wc_attribute_label( $attribute_name ); ?></label></td>
						<td class="value">
							<?php
							$selected = isset( $_REQUEST[ 'attribute_' . $sanit_attribute_name ] ) ? wc_clean( urldecode( $_REQUEST[ 'attribute_' . $sanit_attribute_name ] ) ) : $product->get_variation_default_attribute( $attribute_name );
							wc_dropdown_variation_attribute_options(
								array(
									'options'     => $options,
									'attribute'   => $attribute_name,
									'product'     => $product,
									'selected'    => $selected,
									'images'      => $ex_attr,
									'image_key'   => $attr_img
								)
							);
							echo end( $attribute_keys ) === $attribute_name ? apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' ) : '';
							?>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>

			<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

			<div class="single_variation_wrap">
				<?php
				/**
				 * woocommerce_before_single_variation Hook.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * woocommerce_single_variation hook. Used to output the cart button and placeholder for variation data.
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * woocommerce_after_single_variation Hook.
				 */
				do_action( 'woocommerce_after_single_variation' );
				?>
			</div>

			<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_variations_form' ); ?>
	</form>

	<?php
	do_action( 'woocommerce_after_add_to_cart_form' );
}

/**
 * Output a list of variation attributes for use in the cart forms.
 *
 * @param array $args
 * @since 2.4.0
 */
function wc_dropdown_variation_attribute_options( $args = array() ) {

	$args = wp_parse_args(
		apply_filters(
			'woocommerce_dropdown_variation_attribute_options_args', $args ),
		array(
			'options'          => false,
			'attribute'        => false,
			'product'          => false,
			'selected' 	       => false,
			'name'             => '',
			'id'               => '',
			'class'            => '',
			'show_option_none' => __( 'Choose an option', 'woocommerce' ),
			'images'           => array(),
			'image_key'        => ''
		)
	);

	$options     = $args['options'];
	$product     = $args['product'];
	$attribute   = $args['attribute'];
	$name        = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
	$id          = $args['id'] ? $args['id'] : sanitize_title( $attribute );
	$class       = $args['class'];

	$product_id  = get_the_ID();

	$a = 1;
	$slug_key = str_replace( 'pa_', $product_id . '-', $attribute, $a );

	if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
		$attributes = $product->get_variation_attributes();
		$options    = $attributes[ $attribute ];
	}

	$html = '<div class="na-attribute-option">';
	$sel = sprintf(
		'<select id="%s" class="%s" name="%s" data-attribute_name="attribute_%s" style="display:none">',
		esc_attr( $id ), esc_attr( $class ), esc_attr( $name ), esc_attr( sanitize_title( $attribute ) )
	);

	if ( $args['show_option_none'] ) {
		$sel .= '<option value="">' . esc_html( $args['show_option_none'] ) . '</option>';
	}
	if ( ! empty( $options ) ) {
		if ( $product && taxonomy_exists( $attribute ) ) {

			$terms = wc_get_product_terms( $product_id, $attribute, array( 'fields' => 'all' ) );

			foreach ( $terms as $term ) {

				if ( in_array( $term->slug, $options ) ) {

					$active   = sanitize_title( $args['selected'] ) == $term->slug ? 'active' : '';
					$selected = $active != '' ? 'selected="selected"' : '';
					$sel .= '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';

                    if( $slug_key == $args['image_key'] ) {

                        if( isset($args['images'][ $term->slug ]) && na_is_url($args['images'][ $term->slug ]) ) {
                            $html .= sprintf(
                                '<span class="meta-item-img sku-set %s" data-value="%s" title="%s">%s</span>',
                                $active, esc_attr( $term->slug ),
                                esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ),
                                '<img src="' . $args['images'][ $term->slug ] . '">'
                            );
                        } else {
                            $html .= sprintf(
                                '<span class="meta-item-text sku-set %s" data-value="%s">%s</span>',
                                $active, esc_attr( $term->slug ),
                                esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) )
                            );
                        }
                    } else {

                        $html .= sprintf(
                            '<span class="meta-item-text sku-set %s" data-value="%s">%s</span>',
                            $active, esc_attr( $term->slug ),
                            esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) )
                        );
                    }
				}
			}
		} else {
			foreach ( $options as $option ) {

				$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
				$sel .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';

				$active = sanitize_title( $args[ 'selected' ] ) === $args[ 'selected' ] ? sanitize_title( $option ) : $args[ 'selected' ];

				$html .= sprintf(
					'<span class="meta-item-text sku-set %s" data-value="%s">%s</span>',
					$active == $option ? 'active' : '',
					esc_attr( $option ),
					esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) )
				);
			}
		}
	}

	$sel .= '</select>';

	$html .= '</div>';
	$html .= $sel;

	echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args );
}

function na_is_url( $url ) {
	return (bool) preg_match( '|(\/\/)(www\.)?(.)*[\.](.)*$|iu', $url );
}

function na_init_front_js() {

	$args = array(
		'na-single' => array(
			'url'    => '/js/single.js',
			'parent' => array( 'jquery' ),
			'ver'    => '1.0'
		)
	);

	foreach( $args as $key => $val ) {
		wp_register_script( $key, NA_URL . $val[ 'url' ], $val[ 'parent' ], $val[ 'ver' ] );
	}
}
add_action( 'init', 'na_init_front_js' );

function na_scripts_method() {

	if( is_singular( 'product' ) )
		wp_enqueue_script( 'na-single' );
}
add_action( 'wp_enqueue_scripts', 'na_scripts_method' );

function na_init_front_styles() {

	if( is_singular( 'product' ) ) {
		$foo = array(
			'global'   => '/css/style.css?ver=1.0',
		);

		foreach($foo as $key => $val) {
			printf('<link id="%s" href="%s" rel="stylesheet" type="text/css"/>' . "\n", $key, NA_URL . $val);
		}
	}
}
add_action( 'wp_head', 'na_init_front_styles' );