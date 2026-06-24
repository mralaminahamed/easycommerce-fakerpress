<?php
/**
 * Attribute Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Attribute as AttributeModel;
use EasyCommerce\Models\Attribute_Value as AttributeValueModel;
use WP_Error;

/**
 * Attribute Generator Class
 *
 * Generates realistic fake attribute data for EasyCommerce
 *
 * @since 1.0.0
 */
class Attribute extends Generator {

	/**
	 * Predefined attribute sets with types and values.
	 *
	 * Each entry maps an attribute name to its type and a list of
	 * representative values used when generating predefined attributes.
	 *
	 * @since 1.0.0
	 * @var array<string, array{type: string, values: string[]}>
	 */
	private const ATTRIBUTE_SETS = array(
		'Color'    => array(
			'type'   => 'Color',
			'values' => array( 'Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange', 'Pink', 'Gray' ),
		),
		'Size'     => array(
			'type'   => 'Text',
			'values' => array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL' ),
		),
		'Material' => array(
			'type'   => 'Text',
			'values' => array( 'Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim', 'Linen', 'Nylon' ),
		),
		'Storage'  => array(
			'type'   => 'Text',
			'values' => array( '64GB', '128GB', '256GB', '512GB', '1TB', '2TB' ),
		),
		'Weight'   => array(
			'type'   => 'Text',
			'values' => array( '100g', '250g', '500g', '1kg', '2kg', '5kg' ),
		),
		'Style'    => array(
			'type'   => 'Text',
			'values' => array( 'Classic', 'Modern', 'Vintage', 'Sport', 'Casual', 'Formal', 'Slim Fit', 'Regular Fit' ),
		),
		'Scent'    => array(
			'type'   => 'Text',
			'values' => array( 'Vanilla', 'Lavender', 'Rose', 'Citrus', 'Mint', 'Sandalwood', 'Unscented' ),
		),
		'Pattern'  => array(
			'type'   => 'Text',
			'values' => array( 'Solid', 'Striped', 'Plaid', 'Polka Dot', 'Floral', 'Abstract', 'Geometric' ),
		),
	);

	/**
	 * Hex colour values for the predefined Color attribute set.
	 *
	 * @since 2.1.0
	 * @var array<string, string>
	 */
	private const COLOR_HEX = array(
		'Red'    => '#FF0000',
		'Blue'   => '#0000FF',
		'Green'  => '#008000',
		'Black'  => '#000000',
		'White'  => '#FFFFFF',
		'Yellow' => '#FFFF00',
		'Purple' => '#800080',
		'Orange' => '#FFA500',
		'Pink'   => '#FFC0CB',
		'Gray'   => '#808080',
	);

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'attribute';
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @since 1.0.0
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'attributes' => __( 'Product Attributes with Values', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @since 1.0.0
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return 'Generates product attributes with associated values, supporting predefined sets (Color, Size, Material, etc.) and randomly generated custom attributes for testing ecommerce product variation functionality.';
	}

	/**
	 * Generate a single attribute
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single attribute data, or WP_Error on failure.
	 */
	protected function generate_single_item() {
		if ( ! class_exists( AttributeModel::class ) ) {
			return new WP_Error(
				'missing_model',
				__( 'EasyCommerce Attribute model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' )
			);
		}

		if ( ! class_exists( AttributeValueModel::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Attribute_Value model not found.', 'easycommerce-fakerpress' ) );
		}

		if ( $this->get_faker()->boolean( 70 ) ) {
			$data = $this->generate_predefined_attribute();
		} else {
			$data = $this->generate_custom_attribute();
		}

		$suffix      = $this->get_faker()->numerify( '###' );
		$unique_name = $data['name'] . ' ' . $suffix;
		$type        = $data['type'];
		$labels      = $data['values'];

		$attr_model   = new AttributeModel();
		$attribute_id = $attr_model->add( $unique_name, $type );

		if ( ! $attribute_id ) {
			return new WP_Error(
				'attribute_creation_failed',
				__( 'Failed to create attribute using EasyCommerce model.', 'easycommerce-fakerpress' )
			);
		}

		$val_model = new AttributeValueModel();
		$values    = array();

		foreach ( $labels as $label ) {
			$hex_value = ( 'Color' === $type && isset( self::COLOR_HEX[ $label ] ) )
				? self::COLOR_HEX[ $label ]
				: null;
			$value_id  = $val_model->add( $attribute_id, $label, $hex_value );

			if ( $value_id ) {
				$values[] = array(
					'id'    => $value_id,
					'label' => $label,
				);
			}
		}

		return array(
			'id'     => $attribute_id,
			'name'   => $unique_name,
			'type'   => $type,
			'slug'   => sanitize_title( $unique_name ),
			'values' => $values,
		);
	}

	/**
	 * Generate data for a predefined attribute set
	 *
	 * Picks a random predefined attribute (e.g., Color, Size) and selects
	 * a subset of 3 to all available values from that set.
	 *
	 * @since 1.0.0
	 *
	 * @return array{name: string, type: string, values: string[]} Attribute data.
	 */
	private function generate_predefined_attribute(): array {
		$set_names = array_keys( self::ATTRIBUTE_SETS );
		$name      = $this->get_faker()->randomElement( $set_names );
		$set       = self::ATTRIBUTE_SETS[ $name ];

		$all_values = $set['values'];
		$count      = $this->get_faker()->numberBetween( 3, count( $all_values ) );
		$selected   = $this->get_faker()->randomElements( $all_values, $count, false );

		return array(
			'name'   => $name,
			'type'   => $set['type'],
			'values' => $selected,
		);
	}

	/**
	 * Generate data for a custom attribute
	 *
	 * Creates an attribute with a random word name, a random type from
	 * Text/Color/Image, and 3–8 unique word values.
	 *
	 * @since 1.0.0
	 *
	 * @return array{name: string, type: string, values: string[]} Attribute data.
	 */
	private function generate_custom_attribute(): array {
		$name = ucfirst( $this->get_faker()->word() );
		$type = $this->get_faker()->randomElement( array( 'Text', 'Color', 'Image' ) );

		$value_count   = $this->get_faker()->numberBetween( 3, 8 );
		$values        = array();
		$attempts      = 0;
		$values_so_far = 0;

		while ( $values_so_far < $value_count && $attempts < 50 ) {
			$word = ucfirst( $this->get_faker()->word() );
			if ( ! in_array( $word, $values, true ) ) {
				$values[]      = $word;
				$values_so_far = count( $values );
			}
			++$attempts;
		}

		return array(
			'name'   => $name,
			'type'   => $type,
			'values' => $values,
		);
	}
}
