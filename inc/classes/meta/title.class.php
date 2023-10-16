<?php
/**
 * @package The_SEO_Framework\Classes\Meta
 * @subpackage The_SEO_Framework\Meta\Title
 */

namespace The_SEO_Framework\Meta;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use function \The_SEO_Framework\{
	memo,
	normalize_generation_args,
};

use \The_SEO_Framework\Helper\{
	Post_Types,
	Query,
	Taxonomies,
};
use \The_SEO_Framework\Data;

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Holds getters for meta tag output.
 *
 * @since 4.3.0
 * @access protected
 *         Use tsf()->title() instead.
 */
class Title {

	/**
	 * Returns the meta title from custom fields. Falls back to autogenerated title.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The real title output.
	 */
	public static function get_title( $args = null ) {
		return static::get_custom_title( $args )
			?: static::get_generated_title( $args );
	}

	/**
	 * Returns an unbranded, unpaginated, and unprotected title
	 * from custom fields or an autogenerated fallback.
	 *
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The unmodified title output.
	 */
	public static function get_bare_title( $args = null ) {
		return static::get_bare_custom_title( $args )
			?: static::get_bare_generated_title( $args );
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * @since 3.1.0
	 * @since 4.0.0 Moved the filter to a separated method.
	 * @since 4.1.0 Added the third $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 1. Moved to \The_SEO_Framework\Meta\Title.
	 *              2. Removed the second $escape parameter.
	 *              3. Moved the third parameter to the second.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The custom field title.
	 */
	public static function get_custom_title( $args = null, $social = false ) {

		$title = static::get_bare_custom_title( $args );

		// Allow 0 to be the title.
		if ( ! \strlen( $title ) ) return '';

		if ( Title\Conditions::use_title_protection_status( $args ) )
			$title = static::add_protection_status( $title, $args );

		if ( Title\Conditions::use_title_pagination( $args ) )
			$title = static::add_pagination( $title );

		if ( Title\Conditions::use_title_branding( $args, $social ) )
			$title = static::add_branding( $title, $args );

		return $title;
	}

	/**
	 * Returns the autogenerated meta title.
	 *
	 * @since 3.1.0
	 * @since 3.2.4 1. Added check for title protection.
	 *              2. Moved check for title pagination.
	 * @since 4.0.0 Moved the filter to a separated method.
	 * @since 4.1.0 Added the third $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 1. Moved `sanitize_text()` to before the title merging, to be more in line with custom title merging.
	 *              2. Moved to \The_SEO_Framework\Meta\Title.
	 *              3. Removed the second $escape parameter.
	 *              4. Moved the third parameter to the second.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @param bool       $social Whether the title is meant for social display.
	 * @return string The generated title output.
	 */
	public static function get_generated_title( $args = null, $social = false ) {

		// We should always get something from here.
		$title = static::get_bare_generated_title( $args );

		if ( Title\Conditions::use_title_protection_status( $args ) )
			$title = static::add_protection_status( $title, $args );

		if ( Title\Conditions::use_title_pagination( $args ) )
			$title = static::add_pagination( $title );

		if ( Title\Conditions::use_title_branding( $args, $social ) )
			$title = static::add_branding( $title, $args );

		return $title;
	}

	/**
	 * Returns the raw filtered custom field meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public static function get_bare_custom_title( $args = null ) {

		if ( null === $args ) {
			$title = static::get_custom_title_from_query();
		} else {
			normalize_generation_args( $args );
			$title = static::get_custom_title_from_args( $args );
		}

		/**
		 * Filters the title from custom field, if any.
		 *
		 * @since 3.1.0
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 *
		 * @param string     $title The title.
		 * @param array|null $args  The query arguments. Contains 'id', 'tax', and 'pta'.
		 *                          Is null when the query is auto-determined.
		 */
		return \tsf()->sanitize_text( (string) \apply_filters(
			'the_seo_framework_title_from_custom_field',
			$title,
			$args,
		) );
	}

	/**
	 * Returns the raw filtered autogenerated meta title.
	 *
	 * @since 4.0.0
	 * @since 4.2.0 1. The first parameter can now be voided.
	 *              2. The first parameter is now rectified, so you can leave out indexes.
	 *              3. Now supports the `$args['pta']` index.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @param array|null $args   The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                           Leave null to autodetermine query.
	 * @return string The raw generated title output.
	 */
	public static function get_bare_generated_title( $args = null ) {

		isset( $args ) and normalize_generation_args( $args );

		// phpcs:ignore, WordPress.CodeAnalysis.AssignmentInCondition -- I know.
		if ( null !== $memo = memo( null, $args ) ) return $memo;

		Title\Utils::remove_default_title_filters( false, $args );

		$title = isset( $args )
			? static::generate_title_from_args( $args )
			: static::generate_title_from_query();

		Title\Utils::reset_default_title_filters();

		/**
		 * Filters the title from query.
		 *
		 * @NOTE: This filter doesn't consistently run on the SEO Settings page. var_dump() validate this
		 *        You may want to avoid this filter for the homepage and pta, by returning the default value.
		 * @since 3.1.0
		 * @since 4.2.0 Now supports the `$args['pta']` index.
		 * @param string     $title The title.
		 * @param array|null $args  The query arguments. Contains 'id', 'tax', and 'pta'.
		 *                          Is null when the query is auto-determined.
		 */
		$title = (string) \apply_filters(
			'the_seo_framework_title_from_generation',
			$title ?: static::get_untitled_title(),
			$args,
		);

		return memo(
			\strlen( $title ) ? \tsf()->sanitize_text( $title ) : '',
			$args
		);
	}

	/**
	 * Returns the custom user-inputted title.
	 *
	 * @since 3.1.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return string The custom field title, if it exists.
	 */
	public static function get_bare_unfiltered_custom_title( $args = null ) {
		return isset( $args )
			? static::get_custom_title_from_args( $args )
			: static::get_custom_title_from_query();
	}

	/**
	 * Gets a custom title, based on current query, without additions or prefixes.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 Now tests for the static frontpage metadata prior getting fallback data.
	 * @since 4.2.0 Can now return custom post type archive titles.
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @return string The custom title.
	 */
	public static function get_custom_title_from_query() {

		if ( Query::is_real_front_page() ) {
			if ( Query::is_static_frontpage() ) {
				$title = Data\Plugin::get_option( 'homepage_title' )
					  ?: Data\Plugin\Post::get_post_meta_item( '_genesis_title' );
			} else {
				$title = Data\Plugin::get_option( 'homepage_title' );
			}
		} elseif ( Query::is_singular() ) {
			$title = Data\Plugin\Post::get_post_meta_item( '_genesis_title' );
		} elseif ( Query::is_editable_term() ) {
			$title = Data\Plugin\Term::get_term_meta_item( 'doctitle' );
		} elseif ( \is_post_type_archive() ) {
			$title = Data\Plugin\PTA::get_post_type_archive_meta_item( 'doctitle' );
		}

		if ( isset( $title ) && \strlen( $title ) )
			return \tsf()->sanitize_text( $title );

		return '';
	}

	/**
	 * Gets a custom title, based on input arguments query, without additions or prefixes.
	 *
	 * @since 4.3.0
	 *
	 * @param array $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 * @return string The custom title.
	 */
	public static function get_custom_title_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$title = Data\Plugin\Term::get_term_meta_item( 'doctitle', $args['id'] );
		} elseif ( $args['pta'] ) {
			$title = Data\Plugin\PTA::get_post_type_archive_meta_item( 'doctitle', $args['pta'] );
		} elseif ( Query::is_real_front_page_by_id( $args['id'] ) ) {
			if ( $args['id'] ) {
				$title = Data\Plugin::get_option( 'homepage_title' )
					  ?: Data\Plugin\Post::get_post_meta_item( '_genesis_title', $args['id'] );
			} else {
				$title = Data\Plugin::get_option( 'homepage_title' );
			}
		} elseif ( $args['id'] ) {
			$title = Data\Plugin\Post::get_post_meta_item( '_genesis_title', $args['id'] );
		}

		if ( isset( $title ) && \strlen( $title ) )
			return \tsf()->sanitize_text( $title );

		return '';
	}

	/**
	 * Generates a title, based on current query, without additions or prefixes.
	 *
	 * @since 4.3.0
	 *
	 * @return string The generated title.
	 */
	public static function generate_title_from_query() {

		if ( Query::is_real_front_page() ) {
			$title = static::get_front_page_title();
		} elseif ( Query::is_singular() ) {
			$title = static::get_post_title();
		} elseif ( Query::is_archive() ) {
			$title = static::get_archive_title();
		} elseif ( Query::is_search() ) {
			$title = static::get_search_query_title();
		} elseif ( \is_404() ) {
			$title = static::get_404_title();
		}

		return $title ?? '';
	}

	/**
	 * Generates a title, based on expected query, without additions or prefixes.
	 *
	 * @since 4.3.0
	 *
	 * @param array $args The query arguments. Required. Accepts 'id', 'tax', and 'pta'.
	 * @return string The generated title. Empty if query can't be replicated.
	 */
	public static function generate_title_from_args( $args ) {

		normalize_generation_args( $args );

		if ( $args['tax'] ) {
			$title = static::get_archive_title( \get_term( $args['id'], $args['tax'] ) );
		} elseif ( $args['pta'] ) {
			$title = static::get_archive_title( \get_post_type_object( $args['pta'] ) );
		} else {
			if ( Query::is_real_front_page_by_id( $args['id'] ) ) {
				$title = static::get_front_page_title();
			} else {
				$title = static::get_post_title( $args['id'] );
			}
		}

		return $title;
	}

	/**
	 * Returns the archive title. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata and in admin.
	 * @see WP Core get_the_archive_title()
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|\WP_Error|null $object The Term object or error.
	 *                                                               Leave null to autodetermine query.
	 * @return string The generated archive title.
	 */
	public static function get_archive_title( $object = null ) {

		if ( $object && \is_wp_error( $object ) )
			return '';

		return static::get_archive_title_list( $object )[0];
	}

	/**
	 * Returns the archive title items. Also works in admin.
	 *
	 * @NOTE Taken from WordPress core. Altered to work for metadata.
	 * @see WP Core get_the_archive_title()
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type|null $object The Term object.
	 *                                                     Leave null to autodetermine query.
	 * @return String[$title,$prefix,$title_without_prefix] The generated archive title items.
	 */
	public static function get_archive_title_list( $object = null ) {

		[ $title, $prefix ] = $object
			? static::get_archive_title_from_object( $object )
			: static::get_archive_title_from_query();

		$title_without_prefix = $title;

		if ( Title\Conditions::use_generated_archive_prefix( $object ) ) {
			if ( $prefix ) {
				$title = sprintf(
					/* translators: 1: Title prefix. 2: Title. */
					\_x( '%1$s %2$s', 'archive title', 'default' ),
					$prefix,
					$title
				);
			}
		}

		/**
		 * Filters the archive title.
		 * This is a sibling of WordPress's `get_the_archive_title`,
		 * but then without the HTML.
		 *
		 * @since 3.0.4
		 * @since 4.2.0 Added the `$prefix` and `$origintitle_without_prefixal_title` parameters.
		 *
		 * @param string                               $title                Archive title to be displayed.
		 * @param \WP_Term|\WP_User|\WP_Post_Type|null $object               The archive object.
		 *                                                                   Is null when query is autodetermined.
		 * @param string                               $title_without_prefix Archive title without prefix.
		 * @param string                               $prefix               Archive title prefix.
		 */
		$title = (string) \apply_filters(
			'the_seo_framework_generated_archive_title',
			$title,
			$object,
			$title_without_prefix,
			$prefix,
		);

		return [
			$title,
			$prefix,
			$title_without_prefix,
		];
	}

	/**
	 * Returns the generated archive title by evaluating the input Term only.
	 *
	 * @since 4.3.0
	 *
	 * @return string[$title,$prefix] The title and prefix.
	 */
	public static function get_archive_title_from_query() {

		$title  = \__( 'Archives', 'default' );
		$prefix = '';

		if ( Query::is_category() ) {
			$title  = static::get_term_title();
			$prefix = \_x( 'Category:', 'category archive title prefix', 'default' );
		} elseif ( Query::is_tag() ) {
			$title  = static::get_term_title();
			$prefix = \_x( 'Tag:', 'tag archive title prefix', 'default' );
		} elseif ( Query::is_author() ) {
			$title  = static::get_user_title();
			$prefix = \_x( 'Author:', 'author archive title prefix', 'default' );
		} elseif ( \is_date() ) {
			if ( \is_year() ) {
				$title  = \get_the_date( \_x( 'Y', 'yearly archives date format', 'default' ) );
				$prefix = \_x( 'Year:', 'date archive title prefix', 'default' );
			} elseif ( \is_month() ) {
				$title  = \get_the_date( \_x( 'F Y', 'monthly archives date format', 'default' ) );
				$prefix = \_x( 'Month:', 'date archive title prefix', 'default' );
			} elseif ( \is_day() ) {
				$title  = \get_the_date( \_x( 'F j, Y', 'daily archives date format', 'default' ) );
				$prefix = \_x( 'Day:', 'date archive title prefix', 'default' );
			}
		} elseif ( \is_tax( 'post_format' ) ) {
			if ( \is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = \_x( 'Asides', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = \_x( 'Galleries', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-image' ) ) {
				$title = \_x( 'Images', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-video' ) ) {
				$title = \_x( 'Videos', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = \_x( 'Quotes', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-link' ) ) {
				$title = \_x( 'Links', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-status' ) ) {
				$title = \_x( 'Statuses', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = \_x( 'Audio', 'post format archive title', 'default' );
			} elseif ( \is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = \_x( 'Chats', 'post format archive title', 'default' );
			}
		} elseif ( \is_post_type_archive() ) {
			$title  = static::get_post_type_archive_title();
			$prefix = \_x( 'Archives:', 'post type archive title prefix', 'default' );
		} elseif ( Query::is_tax() ) {
			$term = \get_queried_object();

			if ( $term ) {
				$title  = static::get_term_title( $term );
				$prefix = sprintf(
					/* translators: %s: Taxonomy singular name. */
					\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
					\tsf()->sanitize_text( Taxonomies::get_taxonomy_label( $term->taxonomy ?? '' ) )
				);
			}
		}

		return [ $title, $prefix ];
	}

	/**
	 * Returns the generated archive title by evaluating the input Term only.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_Term|\WP_User|\WP_Post_Type $object The Term object.
	 * @return string[$title,$prefix] The title and prefix.
	 */
	public static function get_archive_title_from_object( $object ) {

		$title  = \__( 'Archives', 'default' );
		$prefix = '';

		if ( ! empty( $object->taxonomy ) ) {
			$title = static::get_term_title( $object );

			switch ( $object->taxonomy ) {
				case 'category':
					$prefix = \_x( 'Category:', 'category archive title prefix', 'default' );
					break;
				case 'post_tag':
					$prefix = \_x( 'Tag:', 'tag archive title prefix', 'default' );
					break;
				default:
					$prefix = sprintf(
						/* translators: %s: Taxonomy singular name. */
						\_x( '%s:', 'taxonomy term archive title prefix', 'default' ),
						Taxonomies::get_taxonomy_label( $object->taxonomy )
					);
			}
		} elseif ( $object instanceof \WP_Post_Type && isset( $object->name ) ) {
			$title  = static::get_post_type_archive_title( $object->name );
			$prefix = \_x( 'Archives:', 'post type archive title prefix', 'default' );
		} elseif ( $object instanceof \WP_User && isset( $object->ID ) ) {
			$title  = static::get_user_title( $object->ID );
			$prefix = \_x( 'Author:', 'author archive title prefix', 'default' );
		}

		return [ $title, $prefix ];
	}

	/**
	 * Returns Post Title from ID.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area and when post_title is actually supported.
	 * @see WP Core single_post_title()
	 *
	 * @since 4.3.0
	 *
	 * @param int|\WP_Post $id The Post ID or post object.
	 * @return string The generated post title.
	 */
	public static function get_post_title( $id = 0 ) {

		// Blog queries can be tricky. Use get_the_real_id to be certain.
		$post = \get_post( $id ?: Query::get_the_real_id() );

		if ( isset( $post->post_title ) && \post_type_supports( $post->post_type, 'title' ) ) {
			/**
			 * Filters the page title for a single post.
			 *
			 * @since WP Core 0.71
			 *
			 * @param string   $post_title The single post page title.
			 * @param \WP_Post $post       The current queried object as returned by get_queried_object().
			 */
			$title = \apply_filters( 'single_post_title', $post->post_title, $post );
		}

		if ( isset( $title ) && \strlen( $title ) )
			return \tsf()->sanitize_text( $title );

		return '';
	}

	/**
	 * Fetches single term title.
	 *
	 * It can autodetermine the term; so, perform your checks prior calling.
	 *
	 * Taken from WordPress core. Altered to work in the Admin area.
	 *
	 * @see WP Core single_term_title()
	 *
	 * @since 4.3.0
	 *
	 * @param null|\WP_Term $term The term name, required in the admin area.
	 * @return string The generated single term title.
	 */
	public static function get_term_title( $term = null ) {

		$term ??= \get_queried_object();

		// We're allowing `0` as a term name here. https://core.trac.wordpress.org/ticket/56518
		if ( ! isset( $term->name ) ) return '';

		switch ( $term->taxonomy ) {
			case 'category':
				/**
				 * Filter the category archive page title.
				 *
				 * @since WP Core 2.0.10
				 *
				 * @param string $term_name Category name for archive being displayed.
				 */
				$title = \apply_filters( 'single_cat_title', $term->name );
				break;
			case 'post_tag':
				/**
				 * Filter the tag archive page title.
				 *
				 * @since WP Core 2.3.0
				 *
				 * @param string $term_name Tag name for archive being displayed.
				 */
				$title = \apply_filters( 'single_tag_title', $term->name );
				break;
			default:
				/**
				 * Filter the custom taxonomy archive page title.
				 *
				 * @since WP Core 3.1.0
				 *
				 * @param string $term_name Term name for archive being displayed.
				 */
				$title = \apply_filters( 'single_term_title', $term->name );
		}

		return \strlen( $title ) ? \tsf()->sanitize_text( $title ) : '';
	}

	/**
	 * Fetches user title.
	 *
	 * @since 4.3.0
	 *
	 * @param int $user_id The user ID.
	 * @return string The generated post type archive title.
	 */
	public static function get_user_title( $user_id = 0 ) {

		$title = \get_userdata( $user_id ?: Query::get_the_real_id() )->display_name ?? '';

		return \strlen( $title ) ? \tsf()->sanitize_text( $title ) : '';
	}

	/**
	 * Fetches single term title.
	 *
	 * @NOTE Taken from WordPress core. Altered to work in the Admin area.
	 * @see WP Core post_type_archive_title()
	 *
	 * @since 4.3.0
	 *
	 * @param string $post_type The post type.
	 * @return string The generated post type archive title.
	 */
	public static function get_post_type_archive_title( $post_type = '' ) {

		$post_type = $post_type ?: Query::get_current_post_type();

		if ( \is_array( $post_type ) )
			$post_type = reset( $post_type );

		if ( ! \in_array( $post_type, Post_Types::get_public_post_type_archives(), true ) )
			return '';

		/**
		 * Filters the post type archive title.
		 *
		 * @since WP Core 3.1.0
		 *
		 * @param string $post_type_name Post type 'name' label.
		 * @param string $post_type      Post type.
		 */
		$title = \apply_filters(
			'post_type_archive_title',
			Post_Types::get_post_type_label( $post_type, false ),
			$post_type,
		);

		return \strlen( $title ) ? \tsf()->sanitize_text( $title ) : '';
	}

	/**
	 * Returns untitled title.
	 *
	 * @since 4.3.0
	 *
	 * @return string The untitled title.
	 */
	public static function get_untitled_title() {
		// FIXME: WordPress no longer outputs 'Untitled' for the title.
		// Though, it still holds this translation in wp_widget_rss_output(), which isn't going anywhere.
		return \__( 'Untitled', 'default' );
	}

	/**
	 * Returns search title.
	 *
	 * @since 4.3.0
	 *
	 * @return string The generated search title.
	 */
	public static function get_search_query_title() {
		return \tsf()->sanitize_text(
			/* translators: %s: search phrase */
			sprintf( \__( 'Search Results for &#8220;%s&#8221;', 'default' ), \get_search_query( true ) )
		);
	}

	/**
	 * Returns 404 title.
	 *
	 * @since 4.3.0
	 *
	 * @return string The generated 404 title.
	 */
	public static function get_404_title() {
		return \tsf()->sanitize_text(
			/**
			 * @since 2.5.2
			 * @since 4.3.0 Now defaults to Core translatable "Page not found."
			 * @param string $title The 404 title.
			 */
			(string) \apply_filters(
				'the_seo_framework_404_title',
				\__( 'Page not found', 'default' )
			)
		);
	}

	/**
	 * Merges title branding, when allowed.
	 *
	 * @since 4.3.0
	 *
	 * @param string     $title The title.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                          Leave null to autodetermine query.
	 * @return string The title with branding.
	 */
	public static function add_branding( $title, $args = null ) {

		if ( null === $args ) {
			if ( Query::is_real_front_page() ) {
				$addition    = static::get_addition_for_front_page();
				$seplocation = static::get_addition_location_for_front_page();
			}
		} else {
			normalize_generation_args( $args );

			if ( ! $args['tax'] && ! $args['pta'] && Query::is_real_front_page_by_id( $args['id'] ) ) {
				$addition    = static::get_addition_for_front_page();
				$seplocation = static::get_addition_location_for_front_page();
			}
		}

		$title    = trim( $title );
		$addition = trim( $addition ?? static::get_addition() );

		if ( $addition && $title ) {
			$sep = static::get_separator();

			if ( 'left' === ( $seplocation ?? static::get_addition_location() ) )
				return "$addition $sep $title";

			return "$title $sep $addition";
		}

		return $title;
	}

	/**
	 * Merges pagination with the title, if paginated.
	 *
	 * @since 4.3.0
	 *
	 * @param string $title The title.
	 * @return string The title with possible pagination.
	 */
	public static function add_pagination( $title ) {

		$page = max( Query::paged(), Query::page() );

		if ( $page >= 2 ) {
			$sep = static::get_separator();

			/* translators: %s: Page number. */
			$paging = sprintf( \__( 'Page %s', 'default' ), $page );

			if ( \is_rtl() ) {
				return "$paging $sep $title";
			} else {
				return "$title $sep $paging";
			}
		}

		return $title;
	}

	/**
	 * Merges title protection prefixes.
	 *
	 * @since 4.3.0
	 *
	 * @param string     $title The title. Passed by reference.
	 * @param array|null $args  The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                          Leave null to autodetermine query.
	 * @return string The title with possible protection status.
	 */
	public static function add_protection_status( $title, $args = null ) {

		if ( null === $args ) {
			$id  = Query::get_the_real_id();
			$add = Query::is_singular();
		} else {
			normalize_generation_args( $args );
			$id  = $args['id'];
			$add = ! $args['tax'] && ! $args['pta'];
		}

		if ( ! $add ) return $title;

		$post = $id ? \get_post( $id ) : null;

		if ( ! empty( $post->post_password ) ) {
			return sprintf(
				/**
				 * Filters the text prepended to the post title of private posts.
				 *
				 * The filter is only applied on the front end.
				 *
				 * @since WP Core 2.8.0
				 *
				 * @param string  $prepend Text displayed before the post title.
				 *                         Default 'Private: %s'.
				 * @param WP_Post $post    Current post object.
				 */
				(string) \apply_filters(
					'protected_title_format',
					/* translators: %s: Protected post title. */
					\__( 'Protected: %s', 'default' ),
					$post
				),
				$title
			);
		} elseif ( 'private' === ( $post->post_status ?? null ) ) {
			return sprintf(
				/**
				 * Filters the text prepended to the post title of private posts.
				 *
				 * The filter is only applied on the front end.
				 *
				 * @since WP Core 2.8.0
				 *
				 * @param string  $prepend Text displayed before the post title.
				 *                         Default 'Private: %s'.
				 * @param WP_Post $post    Current post object.
				 */
				$private_title_format = (string) \apply_filters(
					'private_title_format',
					/* translators: %s: Private post title. */
					\__( 'Private: %s', 'default' ),
					$post
				),
				$title
			);
		}

		return $title;
	}

	/**
	 * Generates front page title.
	 *
	 * This is an alias of get_blogname(). The difference is that this is used for
	 * the front-page title output solely, whereas the other one has a mixed usage.
	 *
	 * @since 4.3.0
	 *
	 * @return string The generated front page title.
	 */
	public static function get_front_page_title() {
		return \tsf()->sanitize_text( Data\Blog::get_public_blog_name() );
	}

	/**
	 * Returns the custom blogname from option or bloginfo.
	 *
	 * This is an alias of get_blogname(). The difference is that this is used for
	 * the title additions output solely, whereas the other one has a mixed usage.
	 *
	 * @since 4.3.0
	 *
	 * @return string The trimmed tagline.
	 */
	public static function get_addition() {
		return \tsf()->sanitize_text( Data\Blog::get_public_blog_name() );
	}

	/**
	 * Returns the custom homepage additions (tagline) from option or bloginfo, when set.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @return string The trimmed tagline.
	 */
	public static function get_addition_for_front_page() {
		return memo() ?? memo(
			\tsf()->sanitize_text(
				Data\Plugin::get_option( 'homepage_title_tagline' ) ?: Data\Blog::get_filtered_blog_description()
			)
		);
	}

	/**
	 * Returns title separator location.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @return string The separator location.
	 */
	public static function get_addition_location() {
		return Data\Plugin::get_option( 'title_location' );
	}

	/**
	 * Returns title separator location for the front page.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @return string The Seplocation for the front page.
	 */
	public static function get_addition_location_for_front_page() {
		return Data\Plugin::get_option( 'home_title_location' );
	}

	/**
	 * Gets Title Separator.
	 * Memoizes the return value.
	 *
	 * @since 2.6.0
	 * @since 4.3.0 Moved to \The_SEO_Framework\Meta\Title.
	 *
	 * @return string The Separator.
	 */
	public static function get_separator() {
		/**
		 * @since 2.3.9
		 * @param string $eparator The title separator
		 */
		return memo() ?? memo(
			(string) \apply_filters(
				'the_seo_framework_title_separator',
				Title\Utils::get_separator_list()[ Data\Plugin::get_option( 'title_separator' ) ] ?? '&#x2d;'
			)
		);
	}
}
