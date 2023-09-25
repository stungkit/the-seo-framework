<?php
/**
 * @package The_SEO_Framework\Classes\Front\Meta\Factory\Schema\Entities\Breadcrumb
 * @subpackage The_SEO_Framework\Meta\Schema
 */

namespace The_SEO_Framework\Meta\Factory\Schema\Entities;

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

use \The_SEO_Framework\Data,
	\The_SEO_Framework\Meta\Factory;

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
 * Holds BreadcrumbList generator for Schema.org structured data.
 *
 * @since 4.3.0
 * @access protected
 */
final class BreadcrumbList extends Reference {

	/**
	 * @since 4.3.0
	 * @var string|string[] $type The Schema @type.
	 */
	public static $type = 'BreadcrumbList';

	/**
	 * @since 4.3.0
	 *
	 * @param array|null $args The query arguments. Accepts 'id', 'tax', and 'pta'.
	 *                         Leave null to autodetermine query.
	 * @return ?array $entity The Schema.org graph entity. Null on failure.
	 */
	public static function build( $args = null ) {

		$list = Factory\Breadcrumbs::get_breadcrumb_list( $args );

		$list_items = [];

		foreach ( $list as $i => $item ) {
			$list_items[] = [
				'@type'    => 'ListItem',
				'position' => $i + 1, // Let's not create 0
				'item'     => \tsf()->s_url_query( $item['url'] ),
				'name'     => \tsf()->s_title_raw( $item['name'] ),
			];
		}

		if ( empty( $list_items ) ) return null;

		// Pop off the last URL, so search engines will use the page URL instead.
		unset( $list_items[ array_key_last( $list_items ) ]['item'] );

		return [
			'@type'           => static::$type,
			'@id'             => static::get_id(),
			'itemListElement' => $list_items,
		];
	}
}
