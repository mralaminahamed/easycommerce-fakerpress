<?php
/**
 * Attribute Generator.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
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
	 * Generate a single attribute
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error Single attribute data, or WP_Error on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Attribute model exists.
		if ( ! class_exists( 'EasyCommerce\Models\Attribute' ) ) {
			return new WP_Error(
				'missing_model',
				__( 'EasyCommerce Attribute model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' )
			);
		}

		// 70% chance: use a predefined attribute set; 30% chance: generate custom.
		if ( $this->get_faker()->boolean( 70 ) ) {
			$data = $this->generate_predefined_attribute();
		} else {
			$data = $this->generate_custom_attribute();
		}

		// Append numeric suffix for uniqueness.
		$suffix      = $this->get_faker()->numerify( '###' );
		$unique_name = $data['name'] . ' ' . $suffix;
		$type        = $data['type'];
		$labels      = $data['values'];

		// Create the attribute via EasyCommerce model.
		$attr_model   = new \EasyCommerce\Models\Attribute();
		$attribute_id = $attr_model->add( $unique_name, $type );

		if ( ! $attribute_id ) {
			return new WP_Error(
				'attribute_creation_failed',
				__( 'Failed to create attribute using EasyCommerce model.', 'easycommerce-fakerpress' )
			);
		}

		// Add each value to the attribute.
		$val_model = new \EasyCommerce\Models\Attribute_Value();
		$values    = array();

		foreach ( $labels as $label ) {
			$value_id = $val_model->add( $attribute_id, $label );

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

		$value_count  = $this->get_faker()->numberBetween( 3, 8 );
		$values       = array();
		$values_found = 0;

		while ( $values_found < $value_count ) {
			$candidate = ucfirst( $this->get_faker()->word() );
			if ( ! in_array( $candidate, $values, true ) ) {
				$values[]     = $candidate;
				$values_found = count( $values );
			}
		}

		return array(
			'name'   => $name,
			'type'   => $type,
			'values' => $values,
		);
	}
}
