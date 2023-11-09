<?php
/**
 * @package The_SEO_Framework\Classes\Facade\Generate_Url
 * @subpackage The_SEO_Framework\Getters\URL
 */

namespace The_SEO_Framework;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

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
 * Class The_SEO_Framework\Legacy_API
 *
 * Holds various functions that relay to the new APIs.
 *
 * @since 5.0.0
 * You can access these methods via `tsf()` and `the_seo_framework()`.
 */
class Legacy_API {

	/**
	 * Calling any top file without __construct() is forbidden.
	 */
	private function __construct() { }

	/**
	 * Return SEO options from the SEO options database.
	 *
	 * @since 2.2.2
	 * @since 2.8.2 No longer decodes entities on request.
	 * @since 3.1.0 Now uses the filterable call when caching is disabled.
	 * @since 4.2.0 Now supports an option index as a $key.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed $use_cache; the cache is now dynamically updated.
	 *              3. Now returns null if the option doesn't exist.
	 * @api
	 *
	 * @param string|string[] $key Option name, or a map of indexes therefor.
	 *                             If you send an empty array, you'll get all options.
	 *                             Don't do that; use `tsf()->get_options()` instead.
	 * @return ?mixed The TSF option value. Null when not found.
	 */
	public static function get_option( $key ) {
		return static::data()->plugin()->get_option( ...(array) $key );
	}

	/**
	 * Return all SEO options from the SEO options database.
	 *
	 * @since 5.0.0
	 * @api
	 *
	 * @return array The TSF option values.
	 */
	public static function get_options() {
		return static::data()->plugin()->get_options();
	}

	/**
	 * Updates options. Also updates the option cache if the settings aren't headless.
	 *
	 * @since 2.9.0
	 * @since 5.0.0 Ennobled to be part of the legacy API.
	 * @api
	 *
	 * @param string|array $option The option key, or an array of key and value pairs.
	 * @param mixed        $value  The option value. Ignored when $option is an array.
	 * @return bool True on succesful update, false otherwise.
	 */
	public static function update_option( $option, $value = '' ) {
		return static::data()->plugin()->update_option( $option, $value );
	}

	/**
	 * Returns the meta title from custom fields. Falls back to autogenerated title.
	 *
	 * @since 3.1.0
	 * @since 3.2.2 No longer double-escapes the custom field title.
	 * @since 4.1.0 Added the third $social parameter.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 *              3. Removed the third parameter; use get_open_graph_title() or get_twitter_title() instead.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The meta title output.
	 */
	public static function get_title( $args = null ) {
		return static::title()->get_title( $args );
	}

	/**
	 * Returns the Open Graph meta title.
	 * Falls back to meta title.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 1. The first parameter now expects an array.
	 *              2. Now tries to get the homepage social title.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The Open Graph title output.
	 */
	public static function get_open_graph_title( $args = null ) {
		return static::open_graph()->get_title( $args );
	}

	/**
	 * Returns the Twitter meta title.
	 * Falls back to Open Graph title.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 1. The first parameter now expects an array.
	 *              2. Now tries to get the homepage social titles.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The Twitter title output.
	 */
	public static function get_twitter_title( $args = null ) {
		return static::twitter()->get_title( $args );
	}

	/**
	 * Returns the meta description from custom fields. Falls back to autogenerated description.
	 *
	 * @since 3.0.6
	 * @since 3.1.0 The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The meta description output.
	 */
	public static function get_description( $args = null ) {
		return static::description()->get_description( $args );
	}

	/**
	 * Returns the Open Graph meta description. Falls back to meta description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 1. Now tries to get the homepage social descriptions.
	 *              2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The Open Graph description output.
	 */
	public static function get_open_graph_description( $args = null ) {
		return static::open_graph()->get_description( $args );
	}

	/**
	 * Returns the Twitter meta description.
	 * Falls back to Open Graph description.
	 *
	 * @since 3.0.4
	 * @since 3.1.0 1. Now tries to get the homepage social descriptions.
	 *              2. The first argument now accepts an array, with "id" and "taxonomy" fields.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Ennobled to be part of the legacy API.
	 *              2. Removed the second parameter, the output is always sanitized now.
	 * @api
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                         Leave null to autodetermine query.
	 * @return string The Twitter description output.
	 */
	public static function get_twitter_description( $args = null ) {
		return static::twitter()->get_description( $args );
	}

	/**
	 * Returns the current canonical URL.
	 * Removes pagination if the URL isn't obtained via the query.
	 *
	 * @since 3.0.0
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 4.2.3 Now accepts arguments publicly.
	 * @since 5.0.0 1. No longer calls the query in the sitemap to remove pagination.
	 *              2. Ennobled to be part of the legacy API.
	 *              3. Removed support for `get_custom_field` without deprecation notice.
	 *                 You should've used create_canonical_url() instead.
	 * @api
	 *
	 * @param array|null $args The canonical URL arguments, leave null to autodetermine query : {
	 *    int    $id       The Post, Page or Term ID to generate the URL for.
	 *    string $taxonomy The taxonomy.
	 *    string $pta      The pta.
	 * }
	 * @return string The canonical URL output.
	 */
	public static function get_canonical_url( $args = null ) {
		return static::uri()->get_canonical_url( $args );
	}

	/**
	 * Returns image details.
	 *
	 * @since 4.0.0
	 * @since 4.0.5 The output is now filterable.
	 * @since 4.2.0 Now supports the `$args['pta']` index.
	 * @since 5.0.0 1. Removed the last `$clean` parameter. It always returns a cleaned image now.
	 *              2. Ennobled to be part of the legacy API.
	 * @api
	 *
	 * @param array|null $args    The query arguments. Accepts 'id', 'tax', 'pta', and 'uid'.
	 *                            Leave null to autodetermine query.
	 * @param bool       $single  Whether to fetch one image, or multiple.
	 * @param string     $context The filter context. Default 'social'.
	 * @return array[] The image details array, sequential: int => {
	 *    string url:      The image URL,
	 *    int    id:       The image ID,
	 *    int    width:    The image width in pixels,
	 *    int    height:   The image height in pixels,
	 *    string alt:      The image alt tag,
	 *    string caption:  The image caption,
	 *    int    filesize: The image filesize in bytes,
	 * }
	 */
	public static function get_image_details( $args = null, $single = true, $context = 'social' ) {
		return static::image()->get_image_details( $args, $single, $context );
	}

	/**
	 * Loads all admin scripts.
	 * However, consider filtering `the_seo_framework_register_scripts` instead.
	 *
	 * May load more depending on the page requested.
	 * `tsf` and `tsf-tt` will always be available.
	 *
	 * @since 5.0.0
	 * @api
	 */
	public static function load_admin_scripts() {
		Admin\Script\Registry::register_scripts_and_hooks();
	}

	/**
	 * Prints all meta tags.
	 *
	 * @since 5.0.0
	 * @api
	 */
	public static function print_seo_meta_tags() {
		Front\Meta\Head::print_wrap_and_tags();
	}
}
