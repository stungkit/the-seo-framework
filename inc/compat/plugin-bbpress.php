<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\bbPress
 */

namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Override wp_title's bbPress title with the one generated by The SEO Framework.
 *
 * @since 2.3.5
 */
\add_filter( 'bbp_title', [ $this, 'get_document_title' ], 99, 3 );

\add_filter( 'the_seo_framework_seo_column_keys_order', __NAMESPACE__ . '\\_bbpress_filter_order_keys' );
/**
 * Filters the order keys for The SEO Bar.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $current_keys The current column keys TSF looks for.
 * @return array Expanded keyset.
 */
function _bbpress_filter_order_keys( $current_keys = [] ) {

	$new_keys = [
		'bbp_topic_freshness',
		'bbp_forum_freshness',
		'bbp_reply_created',
	];

	return array_merge( $current_keys, $new_keys );
}

\add_filter( 'the_seo_framework_title_from_generation', __NAMESPACE__ . '\\_bbpress_filter_pre_title', 10, 2 );
/**
 * Fixes bbPress tag titles.
 *
 * @since 2.9.0
 * @since 3.1.0 1. Updated to support new title generation.
 *              2. Now no longer fixes the title when `is_tax()` is true. Because,
 *                 this method is no longer necessary when bbPress fixes this issue.
 *                 This should be fixed as of bbPress 2.6. Which seemed to be released internally August 6th, 2018.
 * @access private
 *
 * @param string $title The filter title.
 * @param array $args The title arguments.
 * @return string $title The bbPress title.
 */
function _bbpress_filter_pre_title( $title = '', $args = [], $escape = true ) {

	if ( \is_bbpress() ) {
		if ( \bbp_is_topic_tag() && ! \the_seo_framework()->is_tax() ) {
			$term = \get_queried_object();
			$title = isset( $term->name ) ? $term->name : \the_seo_framework()->get_static_untitled_title();
		}
	}

	return $title;
}

\add_filter( 'the_seo_framework_fetched_description_excerpt', __NAMESPACE__ . '\\_bbpress_filter_excerpt_generation', 10 );
/**
 * Fixes bbPress excerpts.
 *
 * bbPress has a hard time maintaining WordPress' query after the original query.
 * Reasons unknown.
 * This function fixes the Excerpt part.
 *
 * @since 2.9.0
 * @since 3.0.4 : Default value for $max_char_length has been increased from 155 to 300.
 * @since 3.1.0 Now no longer fixes the description when `is_tax()` is true.
 *              @see `_bbpress_filter_pre_title()` for explanation.
 * @access private
 *
 * @param string $excerpt The excerpt to use.
 * @return string The excerpt.
 */
function _bbpress_filter_excerpt_generation( $excerpt = '' ) {

	if ( \is_bbpress() ) {
		if ( \bbp_is_topic_tag() && ! \the_seo_framework()->is_tax() ) {
			$term = \get_queried_object();
			$description = $term->description ?: '';

			//* Always overwrite, even when none is found.
			$excerpt = \the_seo_framework()->s_description_raw( $description );
		}
	}

	return $excerpt;
}

\add_filter( 'the_seo_framework_custom_field_description', __NAMESPACE__ . '\\_bbpress_filter_custom_field_description' );
/**
 * Fixes bbPress custom Description for social meta.
 *
 * bbPress has a hard time maintaining WordPress' query after the original query.
 * Reasons unknown.
 * This function fixes the Custom Description part.
 *
 * @since 2.9.0
 * @access private
 *
 * @param string $description The description.
 * @return string The custom description.
 */
function _bbpress_filter_custom_field_description( $description = '' ) {

	if ( \is_bbpress() ) {
		if ( \bbp_is_topic_tag() ) {
			$data = \the_seo_framework()->get_term_meta( \get_queried_object_id() );
			if ( ! empty( $data['description'] ) ) {
				$description = $data['description'];
			} else {
				$description = '';
			}
		}
	}

	return $description;
}

\add_filter( 'the_seo_framework_do_adjust_archive_query', __NAMESPACE__ . '\\_bbpress_filter_do_adjust_query', 10, 2 );
/**
 * Fixes bbPress exclusion of first reply.
 *
 * bbPress has a hard time maintaining WordPress' query after the original query.
 * Reasons unknown.
 * This function fixes the query alteration part.
 *
 * @since 3.0.3
 * @access private
 * @link <https://bbpress.trac.wordpress.org/ticket/2607> (regression)
 *
 * @param bool      $do       Whether to adjust the query.
 * @param \WP_Query $wp_query The query.
 * @return bool
 */
function _bbpress_filter_do_adjust_query( $do, $wp_query ) {

	if ( \is_bbpress() && isset( $wp_query->query['post_type'] ) ) {
		if ( in_array( 'reply', (array) $wp_query->query['post_type'], true ) ) {
			$do = false;
		}
	}

	return $do;
}
