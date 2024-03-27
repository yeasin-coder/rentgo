<?php

	namespace JET_ABAF;

	use JET_ABAF\Form_Fields\Check_In_Out_Render;

	/**
	 * Tools class
	 */
	class Tools {

		/**
		 * Returns verbosed slot by timestatmp
		 * @return [type] [description]
		 */
		public function get_verbosed_date ( $date, $format ) {
			if( ! $format ){
				$format = get_option( 'date_format', 'F j, Y' );
			}

			return date_i18n( $format, $date );
		}

		/**
		 * Returns js date format
		 * @return [type] [description]
		 */
		public static function date_format_php_to_js( $format = null, $mask = [] ){

			if( ! $format ){
				return '';
			}

			$parsed_format = $format;
			$mask = ! empty( $mask ) ? $mask : [
				'/H{1}/' => 'HH',
				'/h{1}/' => 'hh',
				'/Y{1}/' => 'YYYY',
				'/y{1}/' => 'YY',
				'/M{1}/' => 'MMM',
				'/n{1}/' => 'M',
				'/m{1}/' => 'MM',
				'/F{1}/' => 'MMMM',
				'/d{1}/' => 'DD',
				'/D{1}/' => 'ddd',
				'/j{1}/' => 'D',
				'/l{1}/' => 'dddd',
				'/i{1}/' => 'mm',
				'/g{1}/' => 'hh',
			];

			foreach ( $mask as $key => $value ) {
				$parsed_format = preg_replace( $key, $value, $parsed_format );
			}

			return $parsed_format;
		}

		/**
		 * Returns php date format
		 * @return [type] [description]
		 */
		public static function date_format_js_to_php( $format = null, $mask = [] ){

			if( ! $format ){
				return '';
			}

			$parsed_format = $format;
			$mask = ! empty( $mask ) ? $mask : [
				'/HH{1}/'   => 'H',
				'/hh{1}/'   => 'h',
				'/YYYY{1}/' => 'Y',
				'/YY{1}/'   => 'y',
				'/MMMM{1}/' => 'F',
				'/MMM{1}/'  => 'M',
				'/MM{1}/'   => 'm',
				'/M{1}/'    => 'n',
				'/mm{1}/'   => 'i',
				'/DD{1}/'   => 'd',
				'/D{1}/'    => 'j',
				'/dddd{1}/' => 'l',
				'/ddd{1}/'  => 'D',
			];

			foreach ( $mask as $key => $value ) {
				$parsed_format = preg_replace( $key, $value, $parsed_format );
			}

			return $parsed_format;
		}
	}
