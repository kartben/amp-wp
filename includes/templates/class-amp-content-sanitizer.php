<?php
/**
 * Class AMP_Content_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Content_Sanitizer
 *
 * @since 0.4.1
 */
class AMP_Content_Sanitizer {

	/**
	 * Sanitize _content_.
	 *
	 * @since 0.4.1
	 *
	 * @param string   $content HTML content string or DOM document.
	 * @param string[] $sanitizer_classes Sanitizer classes.
	 * @param array    $global_args       Global args.
	 * @return array Tuple containing sanitized HTML, scripts array, and styles array.
	 */
	public static function sanitize( $content, array $sanitizer_classes, $global_args = array() ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $content );

		$results = self::sanitize_document( $dom, $sanitizer_classes, $global_args );
		return array(
			AMP_DOM_Utils::get_content_from_dom( $dom ),
			$results['scripts'],
			$results['styles'],
		);
	}

	/**
	 * Sanitize document.
	 *
	 * @since 0.7
	 *
	 * @param DOMDocument $dom               HTML document.
	 * @param string[]    $sanitizer_classes Sanitizer classes.
	 * @param array       $global_args       Global args passed into .
	 * @return array {
	 *     Scripts and styles needed by sanitizers.
	 *
	 *     @type array $scripts Scripts.
	 *     @type array $styles  Styles.
	 * }
	 */
	public static function sanitize_document( &$dom, $sanitizer_classes, $global_args ) {
		$scripts = array();
		$styles  = array();
		foreach ( $sanitizer_classes as $sanitizer_class => $args ) {
			if ( ! class_exists( $sanitizer_class ) ) {
				/* translators: %s is sanitizer class */
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Sanitizer (%s) class does not exist', 'amp' ), esc_html( $sanitizer_class ) ), '0.4.1' );
				continue;
			}

			/**
			 * Sanitizer.
			 *
			 * @type AMP_Base_Sanitizer $sanitizer
			 */
			$sanitizer = new $sanitizer_class( $dom, array_merge( $global_args, $args ) );

			if ( ! is_subclass_of( $sanitizer, 'AMP_Base_Sanitizer' ) ) {
				/* translators: %s is sanitizer class */
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Sanitizer (%s) must extend `AMP_Base_Sanitizer`', 'amp' ), esc_html( $sanitizer_class ) ), '0.1' );
				continue;
			}

			$sanitizer->sanitize();

			$scripts = array_merge( $scripts, $sanitizer->get_scripts() );
			$styles  = array_merge( $styles, $sanitizer->get_styles() );
		}

		return compact( 'scripts', 'styles' );
	}
}

