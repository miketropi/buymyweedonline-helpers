<?php

/**
 * The template for displaying recipe content in archive.
 *
 * This template can be overridden by copying it to yourtheme/delicious-recipes/recipes-list.php.
 *
 * HOWEVER, on occasion WP Delicious will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://wpdelicious.com/docs/template-structure/
 * @package Delicious_Recipes/Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $recipe;
// Get global toggles.
$global_toggles   = delicious_recipes_get_global_toggles_and_labels();
$disable_wishlist = isset( $disable_wishlist ) && $disable_wishlist ? true : false;

$img_size = $global_toggles['enable_recipe_archive_image_crop'] ? 'recipe-archive-list' : 'full';
$img_size = apply_filters( 'recipes_list_img_size', $img_size );
?>
<article class="recipe-post" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
	<figure class="post-thumbnail">
		<a href="<?php the_permalink(); ?>">
			<?php
			if ( $recipe->thumbnail ) :
				the_post_thumbnail( $img_size );
			else :
				delicious_recipes_get_fallback_svg( 'recipe-archive-list' );
			endif;
			?>
		</a>
		<?php if ( $recipe->thumbnail && delicious_recipes_enable_pinit_btn() ) : ?>
			<span class="post-pinit-button">
				<a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>/&media=<?php echo esc_url( $recipe->thumbnail ); ?>&description=So%20delicious!" data-pin-custom="true">
					<img src="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>/assets/images/pinit-sm.png" alt="pinit">
				</a>
			</span>
		<?php endif; ?>

		<?php
		/**
		 * Recipe Wishlist button
		 */
		do_action( 'delicious_recipes_wishlist_button', $disable_wishlist );
		?>

		<?php
		/**
		 * Recipe Badges
		 */
		do_action( 'delicious_recipes_badges' );
		?>

		<?php if ( ! empty( $recipe->recipe_keys ) ) : ?>
			<span class="dr-category">
				<?php
				foreach ( $recipe->recipe_keys as $recipe_key ) {
					$key              = get_term_by( 'name', $recipe_key, 'recipe-key' );
					$recipe_key_metas = get_term_meta( $key->term_id, 'dr_taxonomy_metas', true );
					$key_svg          = isset( $recipe_key_metas['taxonomy_svg'] ) ? $recipe_key_metas['taxonomy_svg'] : '';
					?>
					<a href="<?php echo esc_url( get_term_link( $key, 'recipe-key' ) ); ?>" title="<?php echo esc_attr( $recipe_key ); ?>">
						<?php delicious_recipes_get_tax_icon( $key ); ?>
						<span class="cat-name"><?php echo esc_attr( $recipe_key ); ?></span>
					</a>
				<?php } ?>
			</span>
		<?php endif; ?>
	</figure>
	<div class="content-wrap">
		<header class="entry-header">
			<?php if ( ! empty( $recipe->recipe_course ) ) : ?>
				<span class="post-cat">
					<?php the_terms( $recipe->ID, 'recipe-course', '', '', '' ); ?>
				</span>
			<?php endif; ?>
			<h2 class="entry-title" itemprop="name">
				<a itemprop="url" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h2>

			<?php if ( isset( $position ) && ! empty( $position ) ) : ?>
				<meta itemprop="position" content="<?php echo $position; ?>" />
			<?php endif; ?>

			<div class="entry-meta">
				<span class="posted-on">
					<svg class="icon">
						<use xlink:href="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>assets/images/sprite.svg#calendar"></use>
					</svg>
					<time>
						<?php
						if ( isset( $global_toggles['show_updated_date'] ) && $global_toggles['show_updated_date'] ) {
							echo esc_html( delicious_recipes_get_formated_date( $recipe->date_updated ) );
						} else {
							echo esc_html( delicious_recipes_get_formated_date( $recipe->date_published ) );
						}
						?>
					</time>
				</span>
				<span class="comment">
					<svg class="icon">
						<use xlink:href="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>assets/images/sprite.svg#comment"></use>
					</svg>
					<span class="meta-text"><?php echo sprintf( _nx( '%s Comment', '%s Comments', $recipe->comments_number, 'number of comments', 'delicious-recipes' ), number_format_i18n( $recipe->comments_number ) ); ?></span>
				</span>
				<?php if ( $recipe->rating ) : ?>
					<span class="post-rating">
						<svg class="icon">
							<use xlink:href="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>assets/images/sprite.svg#rating"></use>
						</svg>
						<span class="meta-text"><?php echo esc_html( $recipe->rating ); ?></span>
					</span>
				<?php endif; ?>
			</div>
			<div class="floated-meta">
				<?php
				/*
					* Get Recipes Social Share
					*/
				delicious_recipes_social_share();

				/**
				 * Recipe Like button
				 */
				do_action( 'delicious_recipes_like_button' );
				?>
			</div>
		</header>
		<?php if ( ! empty( $recipe->recipe_description ) ) : ?>
			<div class="entry-content">
				<?php echo wp_kses_post( $recipe->recipe_description ); ?>
			</div>
		<?php endif; ?>
		<footer class="entry-footer">
			<?php if ( $recipe->total_time ) : ?>
				<span class="cook-time">
					<svg class="icon">
						<use xlink:href="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>assets/images/sprite.svg#time"></use>
					</svg>
					<span class="meta-text"><?php echo sprintf( '%1$s', esc_html( $recipe->total_time ) ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( $recipe->difficulty_level ) : ?>
				<span class="cook-difficulty">
					<svg class="icon">
						<use xlink:href="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>assets/images/sprite.svg#difficulty"></use>
					</svg>
					<span class="meta-text"><?php echo esc_html( $recipe->difficulty_level ); ?></span>
				</span>
			<?php endif; ?>
		</footer>
	</div>
</article>
<?php