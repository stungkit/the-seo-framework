<?php
/**
 * @package The_SEO_Framework\Views\Inpost
 */

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = the_seo_framework_class() and $this instanceof $_this or die;

//* Get the language the Google page should assume.
$language = $this->google_language();

//* Fetch Term ID and taxonomy.
$term_id  = $object->term_id;
$taxonomy = $object->taxonomy;
$data     = $this->get_term_meta( $object->term_id );

$title       = isset( $data['doctitle'] ) ? $data['doctitle'] : '';
$description = isset( $data['description'] ) ? $data['description'] : '';
$noindex     = isset( $data['noindex'] ) ? $data['noindex'] : '';
$nofollow    = isset( $data['nofollow'] ) ? $data['nofollow'] : '';
$noarchive   = isset( $data['noarchive'] ) ? $data['noarchive'] : '';

/**
 * Generate static placeholder for when title or description is emptied
 *
 * @since 2.2.4
 */
$title_placeholder = $this->get_generated_title( [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
] );
$description_placeholder = $this->get_generated_description( [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
] );

$robots_defaults = $this->robots_meta( [
	'id'       => $term_id,
	'taxonomy' => $taxonomy,
], 0b11 );

// TODO reintroduce the info blocks, and place the labels at the left, instead??
$robots_settings = [
	'noindex'   => [
		'id'        => 'autodescription-meta[noindex]',
		'option'    => 'noindex',
		'force_on'  => 'index',
		'force_off' => 'noindex',
		'label'     => __( 'Set term indexability to:', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noindex'] ) ? 'index' : 'noindex',
		'_value'    => $noindex,
		// '_info'     => [
		// 	__( 'This tells search engines not to show this term in their search results.', 'autodescription' ),
		// 	'https://support.google.com/webmasters/answer/93710?hl=' . $language,
		// ],
	],
	'nofollow'  => [
		'id'        => 'autodescription-meta[nofollow]',
		'option'    => 'nofollow',
		'force_on'  => 'follow',
		'force_off' => 'nofollow',
		'label'     => __( 'Set link followability to:', 'autodescription' ),
		'_default'  => empty( $robots_defaults['nofollow'] ) ? 'follow' : 'nofollow',
		'_value'    => $nofollow,
		// '_info'     => [
		// 	__( 'This tells search engines not to follow links on this term.', 'autodescription' ),
		// 	'https://support.google.com/webmasters/answer/96569?hl=' . $language,
		// ],
	],
	'noarchive' => [
		'id'        => 'autodescription-meta[noarchive]',
		'option'    => 'noarchive',
		'force_on'  => 'archive',
		'force_off' => 'noarchive',
		'label'     => __( 'Set term archivability to:', 'autodescription' ),
		'_default'  => empty( $robots_defaults['noarchive'] ) ? 'archive' : 'noarchive',
		'_value'    => $noarchive,
		// '_info'     => [
		// 	__( 'This tells search engines not to save a cached copy of this term.', 'autodescription' ),
		// 	'https://support.google.com/webmasters/answer/79812?hl=' . $language,
		// ],
	],
];

?>
<h3>
	<?php
	/* translators: %s = Term type */
	printf( esc_html__( '%s SEO Settings', 'autodescription' ), esc_html( $type ) );
	?>
</h3>

<table class="form-table">
	<tbody>
		<?php if ( $this->get_option( 'display_seo_bar_metabox' ) ) : ?>
		<tr>
			<th scope="row" valign="top"><?php esc_html_e( 'Doing it Right', 'autodescription' ); ?></th>
			<td>
				<?php $this->post_status( $term_id, $taxonomy, true ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[doctitle]">
					<strong><?php esc_html_e( 'Meta Title', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta title can be used to determine the title used on search engine result pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#page-titles'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[doctitle]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[doctitle]', 'title' );
				?>
			</th>
			<td>
				<div id="tsf-title-wrap">
					<input name="autodescription-meta[doctitle]" id="autodescription-meta[doctitle]" type="text" placeholder="<?php echo esc_attr( $title_placeholder ); ?>" value="<?php echo esc_attr( $title ); ?>" size="40" autocomplete=off />
					<?php $this->output_js_title_elements(); ?>
				</div>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="autodescription-meta[description]">
					<strong><?php esc_html_e( 'Meta Description', 'autodescription' ); ?></strong>
					<?php
					echo ' ';
					$this->make_info(
						__( 'The meta description can be used to determine the text used under the title on search engine results pages.', 'autodescription' ),
						'https://support.google.com/webmasters/answer/35624?hl=' . $language . '#meta-descriptions'
					);
					?>
				</label>
				<?php
				$this->get_option( 'display_character_counter' )
					and $this->output_character_counter_wrap( 'autodescription-meta[description]' );
				$this->get_option( 'display_pixel_counter' )
					and $this->output_pixel_counter_wrap( 'autodescription-meta[description]', 'description' );
				?>
			</th>
			<td>
				<textarea name="autodescription-meta[description]" id="autodescription-meta[description]" placeholder="<?php echo esc_attr( $description_placeholder ); ?>" rows="5" cols="50" class="large-text"><?php echo esc_html( $description ); ?></textarea>
				<?php echo $this->output_js_description_elements(); ?>
			</td>
		</tr>

		<tr>
			<th scope="row" valign="top">
				<?php
				esc_html_e( 'Robots Meta Settings', 'autodescription' );
				echo ' ';
				$this->make_info(
					__( 'These directives may urge robots not to display, follow links on, or create a cached copy of this term.', 'autodescription' ),
					'https://developers.google.com/search/reference/robots_meta_tag#valid-indexing--serving-directives'
				);
				?>
				</th>
			<td>
				<?php
				foreach ( $robots_settings as $_s ) :
					echo $this->make_single_select_form( [
						'id'      => $_s['id'],
						'class'   => 'tsf-select-wrap',
						'name'    => sprintf( 'autodescription[%s]', $_s['option'] ),
						'label'   => $_s['label'] . ' ',
						'options' => [
							/* translators: %s = default option value */
							0  => sprintf( __( 'Default (%s)', 'autodescription' ), $_s['_default'] ),
							-1 => $_s['force_on'],
							1  => $_s['force_off'],
						],
						'default' => $_s['_value'],
					] );
				endforeach;
				?>
			</td>
		</tr>
		<?php
		// Output saved flag, if set then it won't fetch alternative meta anymore.
		?>
		<label class="hidden" for="autodescription-meta[saved_flag]">
			<input name="autodescription-meta[saved_flag]" id="autodescription-meta[saved_flag]" type="checkbox" value="1" checked="checked" />
		</label>
	</tbody>
</table>
<?php
