<?php
/**
 * Date
 *
 * Note: This library is under development, so do not use this in production
 *
 * @package     Give
 * @subpackage  Utilite/Date
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.5.0
 */

if ( ! class_exists( '\\Carbon\\Carbon' ) ) {
	require_once GIVE_PLUGIN_DIR . 'includes/libraries/carbon.php';
}

use Carbon\Carbon;

/**
 * Implements date formatting helpers for EDD.
 *
 * @since 2.5.0
 *
 * @see   \Carbon\Carbon
 * @see   \DateTime
 */
final class Give_Date extends Carbon {
	/**
	 * Setup Date.
	 *
	 * Please see the testing aids section (specifically static::setTestNow())
	 * for more on the possibility of this constructor returning a test instance.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param string|null               $time
	 * @param \DateTimeZone|string|null $tz
	 */
	public function __construct( $time = 'now', \DateTimeZone $tz = null ) {
		if ( null === $tz ) {
			$tz = new \DateTimeZone( $this->getWpTimezone() );
		}

		parent::__construct( $time, $tz );
	}

	/**
	 * Determine time zone from WordPress options and return as object.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return string
	 */
	public function getWpTimezone() {
		$retval = 'UTC';

		// Get some useful values
		$timezone   = get_option( 'timezone_string' );
		$gmt_offset = get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;;

		// Use timezone string if it's available
		if ( ! empty( $timezone ) ) {
			$retval = $timezone;

			// Use GMT offset to calculate
		} elseif ( is_numeric( $gmt_offset ) ) {
			$hours   = abs( floor( $gmt_offset / HOUR_IN_SECONDS ) );
			$minutes = abs( floor( ( $gmt_offset / MINUTE_IN_SECONDS ) % MINUTE_IN_SECONDS ) );
			$math    = ( $gmt_offset >= 0 ) ? '+' : '-';
			$value   = ! empty( $minutes ) ? "{$hours}:{$minutes}" : $hours;
			$retval  = "GMT{$math}{$value}";
		}

		return $retval;
	}

	/**
	 * Predefined dates
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_predefined_dates() {
		$predefined_dates = array(
			'today'        => esc_html__( 'Today', 'give' ),
			'yesterday'    => esc_html__( 'Yesterday', 'give' ),
			'this_week'    => esc_html__( 'This Week', 'give' ),
			'last_week'    => esc_html__( 'Last Week', 'give' ),
			'last_30_Days' => esc_html__( 'Last 30 Days', 'give' ),
			'this_month'   => esc_html__( 'This Month', 'give' ),
			'last_month'   => esc_html__( 'Last Month', 'give' ),
			'this_quarter' => esc_html__( 'This Quarter', 'give' ),
			'last_quarter' => esc_html__( 'Last Quarter', 'give' ),
			'this_year'    => esc_html__( 'This Year', 'give' ),
			'last_year'    => esc_html__( 'Last Year', 'give' ),
		);

		/**
		 * Filter the predefined dates.
		 */
		$predefined_dates = apply_filters( 'give_stats_predefined_dates', $predefined_dates );

		return $predefined_dates;
	}

	/**
	 * Parse predefined date ranges
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param string|array $range    Predefined date range.
	 * @param bool         $relative Flag to to get relative date or not
	 *
	 * @return array
	 */
	public function parse_date_for_range( $range = 'last_30_days', $relative = false ) {
		if ( is_string( $range ) && ! array_key_exists( $range, $this->get_predefined_dates() ) ) {
			$range = 'last_30_days';
		}

		$dates = array();

		switch ( $range ) {

			case 'this_month':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->startOfMonth(),
						'end'   => $this->copy()->endOfMonth(),
					)
					: array(
						'start' => $this->copy()->subMonth( 1 )->startOfMonth(),
						'end'   => $this->copy()->subMonth( 1 )->endOfMonth(),
					);

				break;

			case 'last_month':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subMonth( 1 )->startOfMonth(),
						'end'   => $this->copy()->subMonth( 1 )->endOfMonth(),
					)
					: $dates = array(
						'start' => $this->copy()->subMonth( 2 )->startOfMonth(),
						'end'   => $this->copy()->subMonth( 2 )->endOfMonth(),
					);
				break;

			case 'today':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->startOfDay(),
						'end'   => $this->copy()->endOfDay(),
					)
					: $dates = array(
						'start' => $this->copy()->subDay( 1 )->startOfDay(),
						'end'   => $this->copy()->subDay( 1 )->endOfDay(),
					);
				break;

			case 'yesterday':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subDay( 1 )->startOfDay(),
						'end'   => $this->copy()->subDay( 1 )->endOfDay(),
					)
					: array(
						'start' => $this->copy()->subDay( 2 )->startOfDay(),
						'end'   => $this->copy()->subDay( 2 )->endOfDay(),
					);
				break;

			case 'this_week':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->startOfWeek(),
						'end'   => $this->copy()->endOfWeek(),
					)
					: array(
						'start' => $this->copy()->subWeek( 1 )->startOfWeek(),
						'end'   => $this->copy()->subWeek( 1 )->endOfWeek(),
					);
				break;

			case 'last_week':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subWeek( 1 )->startOfWeek(),
						'end'   => $this->copy()->subWeek( 1 )->endOfWeek(),
					)
					: array(
						'start' => $this->copy()->subWeek( 2 )->startOfWeek(),
						'end'   => $this->copy()->subWeek( 2 )->endOfWeek(),
					);
				break;

			case 'last_30_days':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subDay( 30 )->startOfDay(),
						'end'   => $this->copy()->endOfDay(),
					)
					: array(
						'start' => $this->copy()->subDay( 60 )->startOfDay(),
						'end'   => $this->copy()->subDay( 30 )->endOfDay(),
					);
				break;

			case 'this_quarter':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->startOfQuarter(),
						'end'   => $this->copy()->endOfQuarter(),
					)
					: array(
						'start' => $this->copy()->subQuarter( 1 )->startOfQuarter(),
						'end'   => $this->copy()->subQuarter( 1 )->endOfQuarter(),
					);
				break;

			case 'last_quarter':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subQuarter( 1 )->startOfQuarter(),
						'end'   => $this->copy()->subQuarter( 1 )->endOfQuarter(),
					)
					: array(
						'start' => $this->copy()->subQuarter( 2 )->startOfQuarter(),
						'end'   => $this->copy()->subQuarter( 2 )->endOfQuarter(),
					);
				break;

			case 'this_year':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->startOfYear(),
						'end'   => $this->copy()->endOfYear(),
					)
					: array(
						'start' => $this->copy()->subYear( 1 )->startOfYear(),
						'end'   => $this->copy()->subYear( 1 )->endOfYear(),
					);
				break;

			case 'last_year':
				$dates = ! $relative
					? array(
						'start' => $this->copy()->subYear( 1 )->startOfYear(),
						'end'   => $this->copy()->subYear( 1 )->endOfYear(),
					)
					: array(
						'start' => $this->copy()->subYear( 2 )->startOfYear(),
						'end'   => $this->copy()->subYear( 2 )->endOfYear(),
					);
				break;

			default:
				$required_args     = array( 'start_date', 'end_date' );
				$has_required_args = 2 === count( array_intersect( array_keys( $range ), $required_args ) );

				if ( is_array( $range ) && $has_required_args ) {
					$sub_days = 0;
					$start    = $this->convert_date( $range['start_date'] );
					$end      = $this->convert_date( $range['end_date'] );

					if( ! is_wp_error( $start ) && ! is_wp_error( $end ) ) {
						if ( $relative ) {
							$sub_days = (int) $start->diff( $end )->days + 1;
						}

						$dates = array(
							'start' => $start->subDays( $sub_days ),
							'end'   => $end->subDays( $sub_days ),
						);
					}
				}

				break;
		}

		return $dates;
	}

	/**
	 * Formats a given date string according to WP date and time formats and timezone.
	 *
	 * @since 2.5.0
	 *
	 * @param string|true $format Optional. How to format the date string.  Accepts 'date',
	 *                            'time', 'datetime', 'mysql', 'timestamp', 'wp_timestamp',
	 *                            'object', or any valid date_format() string. If true, 'datetime'
	 *                            will be used. Default 'date'.
	 *
	 * @return string|int|\DateTime Formatted date string, timestamp if `$type` is timestamp,
	 *                              or a DateTime object if `$type` is 'object'.
	 */
	public function format( $format = 'date' ) {
		if ( ! is_string( $format ) ) {
			$format = 'date';
		}

		switch ( $format ) {

			case 'date':
			case 'mysql':
				$formatted = parent::format( $this->get_date_format_string( $format ) );
				break;

			default:
				$formatted = parent::format( $format );
				break;
		}

		return $formatted;
	}

	/**
	 * Retrieves a date format string based on a given short-hand format.
	 *
	 * @see   edd_get_date_format()
	 * @see   edd_get_date_picker_format()
	 *
	 * @since 3.0
	 *
	 * @param string $format Shorthand date format string. Accepts 'date', 'time', 'mysql', 'datetime',
	 *                       'picker-field' or 'picker-js'. If none of the accepted values, the
	 *                       original value will simply be returned. Default is the value of the
	 *                       `$date_format` property, derived from the core 'date_format' option.
	 *
	 * @return string date_format()-compatible date format string.
	 */
	public function get_date_format_string( $format = 'date' ) {

		// Default to 'date' if empty
		if ( ! is_string( $format ) ) {
			$format = 'date';
		}

		// Bail if format is not known
		if ( ! in_array( $format, array( 'date', 'mysql' ), true ) ) {
			return $format;
		}

		// What known format are we getting?
		switch ( $format ) {

			// MySQL datetime columns
			case 'mysql':
				$retval = 'Y-m-d H:i:s';
				break;

			// WordPress date_format only
			case 'date':
			default:
				$retval = give_date_format();
				break;
		}

		return $retval;
	}

	/**
	 * Convert string or timestamp date to Give_date
	 *
	 * @access public
	 *
	 * @param  string $date Date.
	 *
	 * @return Give_Date|WP_Error   If the date is invalid, a WP_Error object will be returned.
	 */
	public function convert_date( $date ) {
		$rst = new WP_Error(
			'invalid_date',
			esc_html__( 'Improper date provided.', 'give' )
		);

		if ( array_key_exists( (string) $date, $this->get_predefined_dates() ) ) {

			/* @var Give_Date $date */
			$rst = $this->date->parse_date_for_range( $date );

		} else if ( is_numeric( $date ) ) {
			$rst = self::create(
				date( 'Y', $date ),
				date( 'm', $date ),
				date( 'd', $date ),
				date( 'G', $date ),
				date( 'i', $date ),
				date( 's', $date ),
				$this->getWpTimezone()
			);

		} else if ( is_string( $date ) && false !== strtotime( $date ) ) {
			/* @var Give_Date $date */
			$rst = new Give_Date( $date );
		}

		return $rst;
	}
}
