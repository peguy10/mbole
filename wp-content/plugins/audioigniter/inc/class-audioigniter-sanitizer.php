<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AudioIgniter_Sanitizer
 *
 * Provides static sanitization functions.
 *
 * @since 1.0.0
 */
class AudioIgniter_Sanitizer {
	/**
	 * Sanitizes the player type.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @uses AudioIgniter->get_player_types()
	 *
	 * @param string $value Player type to sanitize.
	 *
	 * @return string
	 */
	public static function player_type( $value ) {
		$choices = AudioIgniter()->get_player_types();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}

		return 'full';
	}

	/**
	 * Sanitizes a playlist (repeatable tracks).
	 *
	 * @version 1.2.0
	 * @since 1.0.0
	 *
	 * @uses AudioIgniter_Sanitizer::playlist_track()
	 *
	 * @param array $post_tracks Input values to sanitize, as passed by the playlist metabox.
	 * @param int|null $post_id Optional. Post ID where the track belongs to.
	 *
	 * @return array
	 */
	public static function metabox_playlist( $post_tracks, $post_id = null ) {
		if ( empty( $post_tracks ) || ! is_array( $post_tracks ) ) {
			return array();
		}

		$tracks = array();

		foreach ( $post_tracks as $uid => $track_data ) {
			$track = self::playlist_track( $track_data, $post_id, $uid );
			if ( false !== $track ) {
				$tracks[] = $track;
			}
		}

		return apply_filters( 'audioigniter_sanitize_playlist', $tracks, $post_tracks, $post_id );
	}

	/**
	 * Sanitizes a single playlist track.
	 *
	 * @since 1.0.0
	 *
	 * @uses AudioIgniter::get_default_track_values()
	 *
	 * @param array $track Input values to sanitize.
	 * @param int|null $post_id Optional. Post ID where the track belongs to.
	 * @param string $track_uid Optional. UID that identifies the track in the metabox list.
	 *
	 * @return array|false Array if at least one field is completed, false otherwise.
	 */
	public static function playlist_track( $track, $post_id = null, $track_uid = '' ) {
		$track = wp_parse_args( $track, AudioIgniter::get_default_track_values() );

		$sanitized_track = array();

		$sanitized_track['cover_id']                = intval( $track['cover_id'] );
		$sanitized_track['title']                   = sanitize_text_field( $track['title'] );
		$sanitized_track['artist']                  = sanitize_text_field( $track['artist'] );
		$sanitized_track['track_url']               = esc_url_raw( $track['track_url'] );
		$sanitized_track['buy_link']                = esc_url_raw( $track['buy_link'] );
		$sanitized_track['download_url']            = esc_url_raw( $track['download_url'] );
		$sanitized_track['download_uses_track_url'] = ! empty( $track['download_uses_track_url'] ) ? 1 : 0;

		$sanitized_track = array_map( 'trim', $sanitized_track );

		$tmp = array_filter( $sanitized_track );
		if ( empty( $tmp ) ) {
			$sanitized_track = false;
		}

		return apply_filters( 'audioigniter_sanitize_playlist_track', $sanitized_track, $track, $post_id, $track_uid );
	}

	/**
	 * Sanitizes a checkbox value.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|bool $input Input value to sanitize.
	 *
	 * @return int|string Returns 1 if $input evaluates to 1, an empty string otherwise.
	 */
	public static function checkbox( $input ) {
		if ( 1 == $input ) { // WPCS: loose comparison ok.
			return 1;
		}

		return '';
	}

	/**
	 * Sanitizes a checkbox value. Value is passed by reference.
	 *
	 * Useful when sanitizing form checkboxes. Since browsers don't send any data when a checkbox
	 * is not checked, checkbox() throws an error.
	 * checkbox_ref() however evaluates &$input as null so no errors are thrown.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string|bool &$input Input value to sanitize.
	 *
	 * @return int|string Returns 1 if $input evaluates to 1, an empty string otherwise.
	 */
	public static function checkbox_ref( &$input ) {
		if ( 1 == $input ) { // WPCS: loose comparison ok.
			return 1;
		}

		return '';
	}


	/**
	 * Sanitizes integer input while differentiating zero from empty string.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Input value to sanitize.
	 *
	 * @return int|string Integer value (including zero), or an empty string otherwise.
	 */
	public static function intval_or_empty( $input ) {
		if ( is_null( $input ) || false === $input || '' === $input ) {
			return '';
		}

		if ( 0 == $input ) { // WPCS: loose comparison ok.
			return 0;
		}

		return intval( $input );
	}


	/**
	 * Returns a sanitized hex color code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $str The color string to be sanitized.
	 * @param bool $return_hash Whether to return the color code prepended by a hash.
	 * @param string $return_fail The value to return on failure.
	 *
	 * @return string A valid hex color code on success, an empty string on failure.
	 */
	public static function hex_color( $str, $return_hash = true, $return_fail = '' ) {
		if ( false === $str || empty( $str ) || 'false' === $str ) {
			return $return_fail;
		}

		// Allow keywords and predefined colors
		if ( in_array( $str, array( 'transparent', 'initial', 'inherit', 'black', 'silver', 'gray', 'grey', 'white', 'maroon', 'red', 'purple', 'fuchsia', 'green', 'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua', 'orange', 'aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blanchedalmond', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen', 'linen', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke', 'yellowgreen', 'rebeccapurple' ), true ) ) {
			return $str;
		}

		// Include the hash if not there.
		// The regex below depends on in.
		if ( substr( $str, 0, 1 ) !== '#' ) {
			$str = '#' . $str;
		}

		preg_match( '/(#)([0-9a-fA-F]{6})/', $str, $matches );

		if ( count( $matches ) === 3 ) {
			if ( $return_hash ) {
				return $matches[1] . $matches[2];
			} else {
				return $matches[2];
			}
		}

		return $return_fail;
	}

	/**
	 * Sanitizes a CSS color.
	 *
	 * Tries to validate and sanitize values in these formats:
	 * - rgba()
	 * - 3 and 6 digit hex values, optionally prefixed with `#`
	 * - Predefined CSS named colors/keywords, such as 'transparent', 'initial', 'inherit', 'black', 'silver', etc.
	 *
	 * @since 1.7.1
	 *
	 * @param string $color       The color value to sanitize
	 * @param bool   $return_hash Whether to return hex color prefixed with a `#`
	 * @param string $return_fail Value to return when $color fails validation.
	 *
	 * @return string
	 */
	public static function rgba_color( $color, $return_hash = true, $return_fail = '' ) {
		if ( false === $color || empty( $color ) || 'false' === $color ) {
			return $return_fail;
		}

		// Allow keywords and predefined colors
		if ( in_array( $color, array( 'transparent', 'initial', 'inherit', 'black', 'silver', 'gray', 'grey', 'white', 'maroon', 'red', 'purple', 'fuchsia', 'green', 'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua', 'orange', 'aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blanchedalmond', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen', 'linen', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke', 'yellowgreen', 'rebeccapurple' ), true ) ) {
			return $color;
		}

		preg_match( '/rgba\(\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1,3}\.?\d*\%?)\s*,\s*(\d{1}\.?\d*\%?)\s*\)/', $color, $rgba_matches );
		if ( ! empty( $rgba_matches ) && 5 === count( $rgba_matches ) ) {
			for ( $i = 1; $i < 4; $i++ ) {
				if ( strpos( $rgba_matches[ $i ], '%' ) !== false ) {
					$rgba_matches[ $i ] = self::float_0_100( $rgba_matches[ $i ] );
				} else {
					$rgba_matches[ $i ] = self::int_0_255( $rgba_matches[ $i ] );
				}
			}
			$rgba_matches[4] = self::float_0_1( $rgba_matches[ $i ] );
			return sprintf( 'rgba(%s, %s, %s, %s)', $rgba_matches[1], $rgba_matches[2], $rgba_matches[3], $rgba_matches[4] );
		}

		// Not a color function either. Let's see if it's a hex color.

		// Include the hash if not there.
		// The regex below depends on in.
		if ( substr( $color, 0, 1 ) !== '#' ) {
			$color = '#' . $color;
		}

		preg_match( '/(#)([0-9a-fA-F]{6})/', $color, $matches );

		if ( 3 === count( $matches ) ) {
			if ( $return_hash ) {
				return $matches[1] . $matches[2];
			} else {
				return $matches[2];
			}
		}

		return $return_fail;
	}

	/**
	 * Sanitizes a percentage value, 0% - 100%
	 *
	 * Accepts float values with or without the percentage sign `%`
	 * Returns a string suffixed with the percentage sign `%`.
	 *
	 * @since 1.7.1
	 *
	 * @param mixed $value
	 *
	 * @return string A percentage value, including the percentage sign.
	 */
	public static function float_0_100( $value ) {
		$value = str_replace( '%', '', $value );
		if ( floatval( $value ) > 100 ) {
			$value = 100;
		} elseif ( floatval( $value ) < 0 ) {
			$value = 0;
		}

		return floatval( $value ) . '%';
	}

	/**
	 * Sanitizes a decimal CSS color value, 0 - 255.
	 *
	 * Accepts float values with or without the percentage sign `%`
	 * Returns a string suffixed with the percentage sign `%`.
	 *
	 * @since 1.7.1
	 *
	 * @param mixed $value
	 *
	 * @return int A number between 0-255.
	 */
	public static function int_0_255( $value ) {
		if ( intval( $value ) > 255 ) {
			$value = 255;
		} elseif ( intval( $value ) < 0 ) {
			$value = 0;
		}

		return intval( $value );
	}

	/**
	 * Sanitizes a CSS opacity value, 0 - 1.
	 *
	 * @since 1.7.1
	 *
	 * @param mixed $value
	 *
	 * @return float A number between 0-1.
	 */
	public static function float_0_1( $value ) {
		if ( floatval( $value ) > 1 ) {
			$value = 1;
		} elseif ( floatval( $value ) < 0 ) {
			$value = 0;
		}

		return floatval( $value );
	}

	/**
	 * Removes elements whose keys are not valid data-attribute names.
	 *
	 * @since 1.0.0
	 *
	 * @param array $array Input array to sanitize.
	 *
	 * @return array()
	 */
	public static function html_data_attributes_array( $array ) {
		$keys       = array_keys( $array );
		$key_prefix = 'data-';

		// Remove keys that are not data attributes.
		foreach ( $keys as $key ) {
			if ( substr( $key, 0, strlen( $key_prefix ) ) !== $key_prefix ) {
				unset( $array[ $key ] );
			}
		}

		return $array;
	}


	/**
	 * Returns false when value is empty or null.
	 * Only use with array_filter() or similar, as the naming can lead to confusion.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Array value to check whether empty or null.
	 *
	 * @return bool false if empty or null, true otherwise.
	 */
	public static function array_filter_empty_null( $value ) {
		if ( '' === $value || is_null( $value ) ) {
			return false;
		}

		return true;
	}

}
