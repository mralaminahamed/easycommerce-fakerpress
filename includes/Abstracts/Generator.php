<?php
/**
 * Abstract Generator Class.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Abstracts
 */

namespace EasyCommerceFakerPress\Abstracts;

use Bluemmb\Faker\PicsumPhotosProvider;
use Exception;
use Faker\Factory;
use Faker\Generator as FakerGenerator;
use Faker\Provider\DateTime;
use WP_Error;
use wpdb;

/**
 * Abstract Generator Class
 *
 * Base class for all data generators with common functionality
 *
 * @since 1.0.0
 */
abstract class Generator {
	/**
	 * Faker instance
	 *
	 * @var FakerGenerator
	 */
	protected FakerGenerator $faker;

	/**
	 * WordPress database instance
	 *
	 * @var \wpdb
	 */
	protected wpdb $wpdb;

	/**
	 * Maximum items to generate per batch
	 *
	 * @var int
	 */
	protected int $max_batch_size = 100;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;

		// Get locale with comprehensive FakerPHP support.
		$locale = $this->get_faker_locale();

		$this->faker = Factory::create( $locale );

		// Add additional providers.
		$this->faker->addProvider( new DateTime( $this->faker ) );
		$this->faker->addProvider( new PicsumPhotosProvider( $this->faker ) );
	}

	/**
	 * Get FakerPHP locale with fallback support
	 *
	 * Maps WordPress locale to FakerPHP supported locale with intelligent fallback.
	 *
	 * @since 2.0.0
	 *
	 * @return string FakerPHP compatible locale code.
	 */
	private function get_faker_locale(): string {
		// Get WordPress locale.
		$wp_locale = get_locale();

		// Allow developers to override the locale.
		$custom_locale = apply_filters( 'easycommerce_fakerpress_locale', $wp_locale );

		// All FakerPHP supported locales (as of 2025).
		$supported_locales = $this->get_supported_faker_locales();

		// Direct match.
		if ( in_array( $custom_locale, $supported_locales, true ) ) {
			return $custom_locale;
		}

		// Try language fallback (e.g., en_GB -> en_US).
		$language = substr( $custom_locale, 0, 2 );
		foreach ( $supported_locales as $locale ) {
			if ( strpos( $locale, $language . '_' ) === 0 ) {
				return $locale;
			}
		}

		// Default fallback.
		return 'en_US';
	}

	/**
	 * Get all FakerPHP supported locales
	 *
	 * Complete list of locales supported by FakerPHP library.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of supported locale codes.
	 */
	private function get_supported_faker_locales(): array {
		return array(
			'ar_SA', // Arabic (Saudi Arabia)
			'at_AT', // Austrian German
			'bg_BG', // Bulgarian (Bulgaria)
			'bn_BD', // Bangla (Bangladesh)
			'cs_CZ', // Czech (Czech Republic)
			'da_DK', // Danish (Denmark)
			'de_AT', // German (Austria)
			'de_CH', // German (Switzerland)
			'de_DE', // German (Germany)
			'el_CY', // Greek (Cyprus)
			'el_GR', // Greek (Greece)
			'en_AU', // English (Australia)
			'en_GB', // English (Great Britain)
			'en_HK', // English (Hong Kong)
			'en_IN', // English (India)
			'en_NG', // English (Nigeria)
			'en_NZ', // English (New Zealand)
			'en_PH', // English (Philippines)
			'en_SG', // English (Singapore)
			'en_UG', // English (Uganda)
			'en_US', // English (United States)
			'en_ZA', // English (South Africa)
			'es_AR', // Spanish (Argentina)
			'es_ES', // Spanish (Spain)
			'es_PE', // Spanish (Peru)
			'es_VE', // Spanish (Venezuela)
			'et_EE', // Estonian (Estonia)
			'fa_IR', // Persian (Iran)
			'fi_FI', // Finnish (Finland)
			'fr_BE', // French (Belgium)
			'fr_CA', // French (Canada)
			'fr_CH', // French (Switzerland)
			'fr_FR', // French (France)
			'he_IL', // Hebrew (Israel)
			'hr_HR', // Croatian (Croatia)
			'hu_HU', // Hungarian (Hungary)
			'hy_AM', // Armenian (Armenia)
			'id_ID', // Indonesian (Indonesia)
			'is_IS', // Icelandic (Iceland)
			'it_CH', // Italian (Switzerland)
			'it_IT', // Italian (Italy)
			'ja_JP', // Japanese (Japan)
			'ka_GE', // Georgian (Georgia)
			'kk_KZ', // Kazakh (Kazakhstan)
			'ko_KR', // Korean (South Korea)
			'lt_LT', // Lithuanian (Lithuania)
			'lv_LV', // Latvian (Latvia)
			'me_ME', // Montenegrin (Montenegro)
			'mn_MN', // Mongolian (Mongolia)
			'ms_MY', // Malay (Malaysia)
			'nb_NO', // Norwegian Bokmål (Norway)
			'ne_NP', // Nepali (Nepal)
			'nl_BE', // Dutch (Belgium)
			'nl_NL', // Dutch (Netherlands)
			'pl_PL', // Polish (Poland)
			'pt_AO', // Portuguese (Angola)
			'pt_BR', // Portuguese (Brazil)
			'pt_PT', // Portuguese (Portugal)
			'ro_MD', // Romanian (Moldova)
			'ro_RO', // Romanian (Romania)
			'ru_RU', // Russian (Russia)
			'sk_SK', // Slovak (Slovakia)
			'sl_SI', // Slovenian (Slovenia)
			'sr_Cyrl_RS', // Serbian Cyrillic (Serbia)
			'sr_Latn_RS', // Serbian Latin (Serbia)
			'sr_RS', // Serbian (Serbia)
			'sv_SE', // Swedish (Sweden)
			'th_TH', // Thai (Thailand)
			'tr_TR', // Turkish (Turkey)
			'uk_UA', // Ukrainian (Ukraine)
			'vi_VN', // Vietnamese (Vietnam)
			'zh_CN', // Chinese (China)
			'zh_TW', // Chinese (Taiwan)
		);
	}

	/**
	 * Generate fake data
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return array<string, mixed>|WP_Error Generation results or error.
	 */
	public function generate( int $count ) {
		// Validate count.
		$validation_result = $this->validate_count( $count );
		if ( is_wp_error( $validation_result ) ) {
			return $validation_result;
		}

		$results = array();

		try {
			for ( $i = 0; $i < $count; $i++ ) {
				$item_result = $this->generate_single_item();

				if ( is_wp_error( $item_result ) ) {
					// Continue with other items but log the error.
					continue;
				}

				if ( $item_result ) {
					$results[] = $item_result;
				}
			}

			return $this->format_results( $results );
		} catch ( Exception $e ) {
			return new WP_Error(
				'generation_failed',
				sprintf(
				/* translators: %s: Error message */
					__( 'Generation failed: %s', 'easycommerce-fakerpress' ),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Generate a single item
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>|WP_Error Single item data or error.
	 */
	abstract protected function generate_single_item();

	/**
	 * Get the resource type name (e.g., 'product', 'customer')
	 *
	 * Must be implemented by child classes
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	abstract protected function get_resource_type(): string;

	/**
	 * Get the plural resource type name (e.g., 'products', 'customers')
	 *
	 * @since 1.0.0
	 *
	 * @return string Plural resource type name.
	 */
	protected function get_resource_type_plural(): string {
		return $this->get_resource_type() . 's';
	}

	/**
	 * Validate generation count
	 *
	 * @since 1.0.0
	 *
	 * @param int $count Number of items to generate.
	 *
	 * @return true|WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_count( int $count ) {
		if ( $count <= 0 ) {
			return new WP_Error(
				'invalid_count',
				__( 'Count must be a positive number.', 'easycommerce-fakerpress' )
			);
		}

		if ( $count > $this->max_batch_size ) {
			return new WP_Error(
				'count_too_large',
				sprintf(
				/* translators: %d: Maximum batch size */
					__( 'Count cannot exceed %d items per batch.', 'easycommerce-fakerpress' ),
					$this->max_batch_size
				)
			);
		}

		return true;
	}

	/**
	 * Format generation results
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, mixed> $results Generated items data.
	 *
	 * @return array<string, mixed> Formatted results.
	 */
	protected function format_results( array $results ): array {
		return array(
			'generated'                       => count( $results ),
			$this->get_resource_type_plural() => $results,
		);
	}

	/**
	 * Log generation activity
	 *
	 * @since 1.0.0
	 *
	 * @param string               $message Log message.
	 * @param string               $level Log level (info, warning, error).
	 * @param array<string, mixed> $context Additional context data.
	 *
	 * @return void
	 */
	protected function log( string $message, string $level = 'info', array $context = array() ): void {
		if ( function_exists( 'error_log' ) && WP_DEBUG_LOG ) {
			$context['resource_type'] = $this->get_resource_type();
			$log_message              = sprintf(
				'[EasyCommerce FakerPress] [%s] [%s] %s %s',
				strtoupper( $level ),
				$this->get_resource_type(),
				$message,
				! empty( $context ) ? '- Context: ' . wp_json_encode( $context ) : ''
			);
			error_log( $log_message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
