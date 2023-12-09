<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 6.1.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
$variations = $product->get_available_variations();
$class = count($variations) > 4 ? '_long_variations' : '';
do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart <?php echo $class; ?>" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<table class="variations" cellspacing="0" role="presentation"  id="add-to-cart-single-var">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<th class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></th>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options(
									array(
										'options'   => $options,
										'attribute' => $attribute_name,
										'product'   => $product,
									)
								);
						
								echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
							

$id_counter = 1;

// Sort variations based on price (ascending order)
usort($variations, function($a, $b) {
    return $a['display_price'] - $b['display_price'];
});

echo '<div class="variation-prices">';
foreach ($variations as $variation) {
    $variation_obj = wc_get_product($variation['variation_id']);
    $base_price = $variation_obj->get_price();
    $variation_attributes = $variation_obj->get_attributes();

    // Check if 'pa_weight' attribute exists and can be cast to a float
    if (isset($variation_attributes['pa_weight'])) {
        $attribute_value_1 = floatval($variation_attributes['pa_weight']);

        // Check if the cast was successful (i.e., it's a valid float)
        if (is_float($attribute_value_1)) {
            // Calculate the adjusted price
            $adjusted_price = $base_price / $attribute_value_1;
            $saved_pergram_price = (($base_price - $adjusted_price) / $base_price) * 100;

            echo "<span class='variation-price variation-id-" . $id_counter . "'>" . $variation_obj->get_price_html();
            echo "<span class='variation-per-unit-price'>" . wc_price($adjusted_price) . "/g" . "</span></span>";
        } else {
            // Handle cases where 'pa_weight' cannot be cast to a float
            echo "<span class='variation-price variation-id-" . $id_counter . "'>" . $variation_obj->get_price_html();
            echo "<span class='variation-per-unit-price'>&nbsp;</span></span>"; // Replace "N/A" with white space
        }
    } else {
        // Handle variations without 'pa_weight' attribute
        echo "<span class='variation-price variation-id-" . $id_counter . "'>" . $variation_obj->get_price_html();
        echo "<span class='variation-per-unit-price'>&nbsp;</span></span>"; // Replace "N/A" with white space
    }

    $id_counter++;
}
echo '</div>';

?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );