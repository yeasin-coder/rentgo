<?php
namespace JET_ABAF;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JET_ABAF\Advanced_Price_Rates;
use JET_ABAF\Weekend_Price;
use JET_ABAF\Seasonal_Price;

/**
 * Price class
 */
class Price {

	/**
	 * Post meta key
	 */
	private $meta_key = 'jet_abaf_price';

	/**
	 * Advanced_Price_Rates class instance
	 */
	public $rates_price = null;

	/**
	 * Seasonal_Price class instance
	 */
	public $seasonal_price = null;

	/**
	 * Weekend_Price class instance
	 */
	public $weekend_price = null;

	/**
	 * Post meta
	 */
	private $meta = null;

	private $post_id       = null;
	private $default_price = null;

	public function __construct( $post_id = false ) {

		if( ! $post_id ){
			return;
		}

		$this->post_id = $post_id;
		$this->set_meta( $this->post_id );

		$this->rates_price    = new Advanced_Price_Rates( $this->post_id, $this->meta );
		$this->seasonal_price = new Seasonal_Price( $this->meta );
		$this->weekend_price  = new Weekend_Price( $this->meta );
	}

	public function set_meta( $post_id ) {

		$this->meta = get_post_meta( $post_id, $this->meta_key, true );

		if( empty( $this->meta ) ){
			$this->meta = [
				'_apartment_price' => get_post_meta( $post_id, '_apartment_price', true ),
				'_pricing_rates'   => [],
				'_seasonal_prices' => [],
				'_weekend_prices'  => [],
			];
		}
	}

	/**
	 * Returns default price
	 */
	public function get_default_price() {

		if ( null === $this->default_price ) {
			return $this->default_price = empty( $this->meta['_apartment_price'] ) ? 0 : $this->meta['_apartment_price'] + 0 ;
		}

		return $this->default_price;
	}

	/**
	 * Get price for display.
	 *
	 * Returns price value for display.
	 *
	 * @since  2.4.0
	 * @since  2.5.2 Added `get_price()` method.
	 * @access public
	 *
	 * @return string
	 */
	public function get_price_for_display( $args = [] ) {

		$args = wp_parse_args( $args, [
			'show_price'             => 'default',
			'change_dynamically'     => true,
			'currency_sign'          => '$',
			'currency_sign_position' => 'before',
		] );

		return sprintf(
			'<span data-price-change="%1$s" data-post="%2$s" data-currency="%3$s" data-currency-position="%4$s" data-show-price="%6$s">%5$s</span>',
			( $args['change_dynamically'] ? 1 : 0 ),
			$this->post_id,
			$args['currency_sign'],
			$args['currency_sign_position'],
			$this->get_price( $args ),
			$args['show_price']
		);
	}

	/**
	 * Get price.
	 *
	 * Return apartment price depending on dynamic tag value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param array $args List of price arguments.
	 *
	 * @return string
	 */
	public function get_price( $args ) {

		switch ( $args['show_price'] ) {
			case 'min':
				$price = $this->get_min_price( $args['currency_sign'], $args['currency_sign_position'] );
				break;

			case 'max':
				$price = $this->get_max_price( $args['currency_sign'], $args['currency_sign_position'] );
				break;

			case 'range':
				$price = $this->get_min_max_range( $args['currency_sign'], $args['currency_sign_position'] );
				break;

			default:
				$price = $this->formatted_price( $this->get_default_price(), $args['currency_sign'], $args['currency_sign_position'] );
				break;
		}

		return $price;

	}

	/**
	 * Get min price.
	 *
	 * Returns formatted minimal price value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $currency          Currency sign.
	 * @param string $currency_position Currency position.
	 *
	 * @return string
	 */
	public function get_min_price( $currency, $currency_position ) {
		return $this->formatted_price( $this->get_price_value( 'min' ), $currency, $currency_position );
	}

	/**
	 * Get max price.
	 *
	 * Returns formatted maximal price value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $currency          Currency sign.
	 * @param string $currency_position Currency position.
	 *
	 * @return string
	 */
	public function get_max_price( $currency, $currency_position ) {
		return $this->formatted_price( $this->get_price_value( 'max' ), $currency, $currency_position );
	}

	/**
	 * Get min-max range.
	 *
	 * Returns formatted min/max price values range.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $currency          Currency sign.
	 * @param string $currency_position Currency position.
	 *
	 * @return string
	 */
	public function get_min_max_range( $currency, $currency_position ) {

		$min = $this->get_price_value( 'min' );
		$max = $this->get_price_value( 'max' );

		if ( $min === $max ) {
			return $this->formatted_price( $min, $currency, $currency_position );
		} else {
			return sprintf(
				'%1$s - %2$s',
				$this->formatted_price( $min, $currency, $currency_position ),
				$this->formatted_price( $max, $currency, $currency_position )
			);
		}

	}

	/**
	 * Formatted price.
	 *
	 * Return formatted price string.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $value             Price value.
	 * @param string $currency          Currency sign.
	 * @param string $currency_position Currency position.
	 *
	 * @return string
	 */
	public function formatted_price( $value, $currency, $currency_position ) {

		if ( ! $currency ) {
			return $value;
		}

		if ( 'before' === $currency_position ) {
			$format = '%1$s%2$s';
		} else {
			$format = '%2$s%1$s';
		}

		return sprintf( $format, $currency, $value );

	}

	/**
	 * Get max price value.
	 *
	 * Returns maximal price value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $type Price value type.
	 *
	 * @return string
	 */
	public function get_price_value( $type ) {

		$price         = $this->get_default_price();
		$weekend_price = $this->weekend_price->get_price();

		if ( empty( $weekend_price ) ) {
			return $price;
		}

		$price = $this->get_compared_weekend_price( $price, $weekend_price, $type );
		$rates = $this->rates_price->get_rates();

		if ( empty( $rates ) ) {
			return $price;
		}

		$price           = $this->get_compared_rates_price( $price, $rates, $type );
		$seasonal_prices = $this->seasonal_price->get_price();

		if ( empty( $seasonal_prices ) ) {
			return $price;
		}

		foreach ( $seasonal_prices as $seasonal_price ) {
			$season_price         = $seasonal_price['price'];
			$season_weekend_price = $seasonal_price['weekend_price'];
			$season_rates         = $seasonal_price['price_rates'];

			if ( ! empty( $season_weekend_price ) ) {
				$season_price = $this->get_compared_weekend_price( $season_price, $season_weekend_price, $type );
			}

			if ( ! empty( $season_rates ) ) {
				$season_price = $this->get_compared_rates_price( $season_price, $season_rates, $type );
			}

			if ( 'min' === $type ) {
				if ( $season_price < $price ) {
					$price = $season_price;
				}
			} else {
				if ( $season_price > $price ) {
					$price = $season_price;
				}
			}
		}

		return $price;

	}

	/**
	 * Get compared weekend price.
	 *
	 * Returns price compared to weekend price value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $price          Default price value.
	 * @param array  $weekend_prices Weekend price list.
	 * @param string $type           Price value type.
	 *
	 * @return mixed
	 */
	public function get_compared_weekend_price( $price, $weekend_prices, $type ) {

		foreach ( $weekend_prices as $weekend_price ) {
			if ( $weekend_price ) {
				if ( 'min' === $type ) {
					if ( $weekend_price < $price ) {
						$price = $weekend_price;
					}
				} else {
					if ( $weekend_price > $price ) {
						$price = $weekend_price;
					}
				}
			}
		}

		return $price;

	}

	/**
	 * Compare rates price.
	 *
	 * Returns price compared to rates price value.
	 *
	 * @since  2.5.2
	 * @access public
	 *
	 * @param string $price Default price value.
	 * @param array  $rates Rates list.
	 * @param string $type  Price value type.
	 *
	 * @return mixed
	 */
	public function get_compared_rates_price( $price, $rates, $type ) {

		foreach ( $rates as $rate ) {
			$value = floatval( $rate['value'] );

			if ( 'min' === $type ) {
				if ( $value < $price ) {
					$price = $value;
				}
			} else {
				if ( $value > $price ) {
					$price = $value;
				}
			}
		}

		return $price;

	}

}
