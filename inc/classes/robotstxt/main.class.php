<?php
/**
 * @package The_SEO_Framework\Classes\RobotsTXT\Main
 * @subpackage The_SEO_Framework\RobotsTXT
 */

namespace The_SEO_Framework\RobotsTXT;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\{
	Data,
	Helper\Compatibility,
	Helper\Query,
	Meta,
	RobotsTXT, // Yes, it is legal to import the same namespace.
	Sitemap,
};

/**
 * The SEO Framework plugin
 * Copyright (C) 2023 - 2024 Sybre Waaijer, CyberWire B.V. (https://cyberwire.nl/)
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
 * Holds various methods for the robots txt output.
 *
 * @since 5.0.0
 * @access protected
 *         Use tsf()->robotstxt() instead.
 */
class Main {

	/**
	 * Edits the robots.txt output.
	 * Requires the site not to have a robots.txt file in the root directory.
	 *
	 * This methods completely hijacks default output. This is intentional (read next paragraph).
	 * Use a higher filter priority to override TSF's output.
	 *
	 * The robots.txt file should not be used to block endpoints that are supposed to be hidden.
	 * This is because the robots.txt file is public; adding endpoints there would expose them.
	 * Blocking pages via robots.txt is not effective, either; if a direct link to a page is found,
	 * it can still be crawled and indexed. Use the robots meta tags (and headers) instead.
	 *
	 * @hook robots_txt 10
	 * @since 5.0.0
	 * @since 5.0.7 Refactored to output the directives via a priority system.
	 * @link <https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt>
	 *
	 * @return string Robots.txt output.
	 */
	public static function get_robots_txt() {

		$site_path = parse_url( \site_url(), \PHP_URL_PATH ) ?: '';

		/**
		 * @since 2.5.0
		 * @todo deprecate 5.1.0
		 * @param bool $disallow Whether to disallow robots queries.
		 */
		$disallow_queries = \apply_filters( 'the_seo_framework_robots_disallow_queries', false )
			? '/*?*'
			: '';

		$sitemaps = [];

		// Add extra whitespace and sitemap full URL
		if ( Data\Plugin::get_option( 'sitemaps_robots' ) ) {
			if ( Data\Plugin::get_option( 'sitemaps_output' ) ) {
				foreach ( Sitemap\Registry::get_sitemap_endpoint_list() as $id => $data )
					if ( ! empty( $data['robots'] ) )
						$sitemaps[] = \esc_url( Sitemap\Registry::get_expected_sitemap_endpoint_url( $id ) );

			} elseif ( ! Compatibility::get_active_conflicting_plugin_types()['sitemaps'] && Sitemap\Utils::use_core_sitemaps() ) {
				$wp_sitemaps_server = \wp_sitemaps_get_server();

				if ( method_exists( $wp_sitemaps_server, 'add_robots' ) ) {
					// Already escaped.
					$sitemaps[] = trim( "\n", \wp_sitemaps_get_server()->add_robots( '', Data\Blog::is_public() ) );
				}
			}
		}

		/**
		 * @since 5.0.7
		 * @param array  $robots The robots directives, associative by key.
		 *                       All input is expected to be escaped: string => {
		 *    string raw:           Raw robots.txt output. A newline is automatically added.
		 *                          Content from this entry is added before all other output.
		 *                          Hint: It can be used for "# comments."
		 *    ?string[] user-agent: The user agent to apply the directives for.
		 *    ?string[] disallow:   The disallow directives.
		 *    ?string[] allow:      The allow directives.
		 *    ?string[] sitemaps:   The sitemap directives. You shouldn't combine this with other directives.
		 *    int       priority:   The priority of the output, a lower priority means earlier output.
		 *                          Defaults to 10.
		 * }
		 * @param string $site_path The determined site path. Use this path to prefix URLs.
		 */
		$robots = (array) \apply_filters(
			'the_seo_framework_robots',
			[
				'derpecated_before' => [
					/**
					 * @since 2.5.0
					 * @todo deprecate 5.1.0
					 * @param string $pre The output before this plugin's output.
					 *                    Don't forget to add line breaks ( "\n" )!
					 */
					'raw'      => (string) \apply_filters( 'the_seo_framework_robots_txt_pre', '' ),
					'priority' => 0,
				],
				'default'           => [
					'user-agent' => [ '*' ],
					'disallow'   => [ "$site_path/wp-admin/", $disallow_queries ],
					'allow'      => [ "$site_path/wp-admin/admin-ajax.php" ],
				],
				'block_ai'          => Data\Plugin::get_option( 'robotstxt_block_ai' ) ? [
					'user-agent' => array_keys( RobotsTXT\Utils::get_user_agents( 'ai' ) ),
					'disallow'   => [ '/' ],
				] : [],
				'block_seo'         => Data\Plugin::get_option( 'robotstxt_block_seo' ) ? [
					'user-agent' => array_keys( RobotsTXT\Utils::get_user_agents( 'seo' ) ),
					'disallow'   => [ '/' ],
				] : [],
				'derpecated_after'  => [
					/**
					 * @since 2.5.0
					 * @todo deprecate 5.1.0
					 * @param string $pro The output after this plugin's output.
					 *                    Don't forget to add line breaks ( "\n" )!
					 */
					'raw'      => (string) \apply_filters( 'the_seo_framework_robots_txt_pro', '' ),
					'priority' => 500,
				],
				'sitemaps'          => [
					'sitemaps' => $sitemaps,
					'priority' => 1000,
				],
			],
			$site_path,
		);

		// We need to use uasort to maintain index association, but we don't read the indexes.
		usort( $robots, fn( $a, $b ) => ( $a['priority'] ?? 10 ) <=> ( $b['priority'] ?? 10 ) );

		$pieces     = [];
		$directives = [
			'user-agent' => 'User-agent',
			'disallow'   => 'Disallow',
			'allow'      => 'Allow',
			'sitemaps'   => 'Sitemap',
		];
		foreach ( $robots as $section ) {
			$piece = '';

			if ( isset( $section['raw'] ) )
				$piece .= $section['raw'];

			if ( ! empty( $section['user-agent'] ) || ! empty( $section['sitemaps'] ) )
				foreach ( $directives as $key => $directive ) // implies order and allowed keys.
					foreach ( $section[ $key ] ?? [] as $value )
						$piece .= \strlen( $value ) ? "$directive: $value\n" : '';

			if ( \strlen( $piece ) )
				$pieces[] = $piece;
		}

		$output = implode( "\n", $pieces );

		$raw_uri = rawurldecode( stripslashes( $_SERVER['REQUEST_URI'] ) )
				?: '/robots.txt';

		// Simple test for invalid directory depth. Even //robots.txt is an invalid location.
		// To be fair, though, up to 5 redirects from /robots.txt are allowed. However, nobody has notified us of this usage.
		if ( strrpos( $raw_uri, '/' ) > 0 ) {
			$correct_location = \esc_url(
				\trailingslashit( Meta\URI\Utils::set_preferred_url_scheme(
					Meta\URI\Utils::get_site_host()
				) ) . 'robots.txt'
			);

			$output = "# This is an invalid robots.txt location.\n# Please visit: $correct_location\n\n$output";
		}

		/**
		 * The robots.txt output.
		 *
		 * @since 4.0.5
		 * @param string $output The robots.txt output.
		 */
		return (string) \apply_filters( 'the_seo_framework_robots_txt', $output );
	}
}
