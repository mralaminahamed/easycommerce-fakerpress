<?php

use Faker\Factory;

class ECFP_Product_Generator
{
    private $faker;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->faker = Factory::create();
        $this->wpdb = $wpdb;
    }

    public function generate($count)
    {
        $results = [];
        
        for ($i = 0; $i < $count; $i++) {
            // Create WordPress post for product
            $product_data = [
                'post_title' => $this->faker->words(3, true),
                'post_content' => $this->faker->paragraphs(3, true),
                'post_excerpt' => $this->faker->sentence(),
                'post_status' => 'publish',
                'post_type' => 'product',
                'post_author' => 1,
            ];

            $product_id = wp_insert_post($product_data);
            
            if (!is_wp_error($product_id)) {
                // Insert into EasyCommerce product_meta table
                $this->insertProductMeta($product_id);
                
                // Create product variations
                $variations = $this->createProductVariations($product_id);
                
                // Assign taxonomies
                $this->assignTaxonomies($product_id);
                
                $results[] = [
                    'id' => $product_id,
                    'title' => $product_data['post_title'],
                    'variations' => count($variations)
                ];
            }
        }

        return [
            'generated' => count($results),
            'products' => $results
        ];
    }

    private function insertProductMeta($product_id)
    {
        $gallery_images = [];
        for ($i = 0; $i < $this->faker->numberBetween(1, 5); $i++) {
            $gallery_images[] = $this->faker->imageUrl(800, 600, 'product');
        }

        $attributes = $this->generateAttributes();

        $meta_data = [
            'product_id' => $product_id,
            'description' => $this->faker->paragraphs(2, true),
            'summary' => $this->faker->sentence(),
            'gallery' => serialize($gallery_images),
            'attributes' => serialize($attributes),
        ];

        $table_name = $this->wpdb->prefix . 'product_meta';
        $this->wpdb->insert($table_name, $meta_data);
    }

    private function createProductVariations($product_id)
    {
        $variations = [];
        $variation_count = $this->faker->numberBetween(1, 4);
        
        for ($i = 0; $i < $variation_count; $i++) {
            $variation_data = [
                'product_id' => $product_id,
                'name' => $this->faker->words(2, true),
                'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}'),
                'type' => $this->faker->randomElement(['physical', 'digital']),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'sale_price' => $this->faker->optional(0.3)->randomFloat(2, 5, 500),
                'stock_quantity' => $this->faker->numberBetween(0, 100),
                'stock_limit' => $this->faker->numberBetween(5, 20),
                'status' => $this->faker->randomElement(['in_stock', 'out_of_stock', 'backorder', 'discontinued']),
            ];

            $table_name = $this->wpdb->prefix . 'product_variations';
            $this->wpdb->insert($table_name, $variation_data);
            $variation_id = $this->wpdb->insert_id;

            // Add variation meta
            $this->insertVariationMeta($variation_id);
            
            // Add variation attributes
            $this->insertVariationAttributes($variation_id);

            $variations[] = $variation_id;
        }

        return $variations;
    }

    private function insertVariationMeta($variation_id)
    {
        $meta_entries = [
            ['variation_id' => $variation_id, 'meta_key' => 'weight', 'meta_value' => $this->faker->randomFloat(2, 0.1, 50)],
            ['variation_id' => $variation_id, 'meta_key' => 'dimensions', 'meta_value' => serialize([
                'length' => $this->faker->randomFloat(2, 1, 100),
                'width' => $this->faker->randomFloat(2, 1, 100),
                'height' => $this->faker->randomFloat(2, 1, 100),
            ])],
            ['variation_id' => $variation_id, 'meta_key' => 'shipping_class', 'meta_value' => $this->faker->randomElement(['standard', 'express', 'overnight'])],
        ];

        $table_name = $this->wpdb->prefix . 'product_variation_meta';
        foreach ($meta_entries as $meta) {
            $this->wpdb->insert($table_name, $meta);
        }
    }

    private function insertVariationAttributes($variation_id)
    {
        $attributes = $this->getAttributesList();
        
        if (empty($attributes)) {
            return;
        }
        
        $max_attributes = min(count($attributes), 3);
        $num_attributes = $this->faker->numberBetween(1, $max_attributes);
        $selected_attributes = $this->faker->randomElements($attributes, $num_attributes);

        foreach ($selected_attributes as $attribute) {
            $attribute_value = $this->getAttributeValueForType($attribute['name']);
            
            $data = [
                'variation_id' => $variation_id,
                'attribute_id' => $attribute['id'],
                'attribute_value_id' => $this->getOrCreateAttributeValue($attribute['id'], $attribute_value),
            ];

            $table_name = $this->wpdb->prefix . 'product_variation_attributes';
            $this->wpdb->insert($table_name, $data);
        }
    }

    private function getAttributesList()
    {
        $table_name = $this->wpdb->prefix . 'attributes';
        $attributes = $this->wpdb->get_results("SELECT id, name FROM {$table_name}", ARRAY_A);
        
        if (empty($attributes)) {
            // Create default attributes if none exist
            $this->createDefaultAttributes();
            $attributes = $this->wpdb->get_results("SELECT id, name FROM {$table_name}", ARRAY_A);
        }

        return $attributes;
    }

    private function createDefaultAttributes()
    {
        $default_attributes = ['Color', 'Size', 'Material', 'Brand', 'Style'];
        $table_name = $this->wpdb->prefix . 'attributes';

        foreach ($default_attributes as $attr_name) {
            $this->wpdb->insert($table_name, [
                'name' => $attr_name,
                'slug' => sanitize_title($attr_name),
                'type' => 'select',
                'order_by' => 'name',
                'has_archives' => 0,
            ]);
        }
    }

    private function getOrCreateAttributeValue($attribute_id, $value)
    {
        $table_name = $this->wpdb->prefix . 'attribute_values';
        
        // Check if value exists
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE attribute_id = %d AND value = %s",
            $attribute_id, $value
        ));

        if ($existing) {
            return $existing;
        }

        // Create new value
        $this->wpdb->insert($table_name, [
            'attribute_id' => $attribute_id,
            'value' => $value,
            'slug' => sanitize_title($value),
        ]);

        return $this->wpdb->insert_id;
    }

    private function getAttributeValueForType($attribute_name)
    {
        switch ($attribute_name) {
            case 'Color':
                return $this->faker->colorName;
            case 'Size':
                return $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']);
            case 'Material':
                return $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim']);
            case 'Brand':
                return $this->faker->company;
            case 'Style':
                return $this->faker->randomElement(['Casual', 'Formal', 'Sport', 'Vintage', 'Modern']);
            default:
                return $this->faker->word;
        }
    }

    private function generateAttributes()
    {
        $attributes = [];
        $attribute_types = ['Color', 'Size', 'Material', 'Brand'];
        
        foreach ($attribute_types as $type) {
            if ($this->faker->boolean(70)) {
                $attributes[] = [
                    'name' => $type,
                    'value' => $this->getAttributeValueForType($type),
                    'is_visible' => 1,
                    'is_variation' => $this->faker->boolean(60) ? 1 : 0,
                ];
            }
        }
        
        return $attributes;
    }

    private function assignTaxonomies($product_id)
    {
        // Assign product categories
        $this->assignProductCategories($product_id);
        
        // Assign product brands
        $this->assignProductBrands($product_id);
        
        // Assign product tags
        $this->assignProductTags($product_id);
    }

    private function assignProductCategories($product_id)
    {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'number' => 20
        ]);

        if (empty($categories) || is_wp_error($categories)) {
            // Create some default categories
            $default_categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys'];
            foreach ($default_categories as $cat_name) {
                wp_insert_term($cat_name, 'product_cat');
            }
            
            // Refresh categories list
            $categories = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'number' => 20
            ]);
        }

        if (!empty($categories) && !is_wp_error($categories)) {
            $max_categories = min(count($categories), 3);
            $num_categories = $this->faker->numberBetween(1, $max_categories);
            $random_categories = $this->faker->randomElements($categories, $num_categories);
            $category_ids = array_column($random_categories, 'term_id');
            wp_set_object_terms($product_id, $category_ids, 'product_cat');
        }
    }

    private function assignProductBrands($product_id)
    {
        $brands = get_terms([
            'taxonomy' => 'product_brand',
            'hide_empty' => false,
            'number' => 10
        ]);

        if (empty($brands) || is_wp_error($brands)) {
            // Create some default brands
            $default_brands = ['Nike', 'Adidas', 'Apple', 'Samsung', 'Sony', 'Canon'];
            foreach ($default_brands as $brand_name) {
                wp_insert_term($brand_name, 'product_brand');
            }
            
            // Refresh brands list
            $brands = get_terms([
                'taxonomy' => 'product_brand',
                'hide_empty' => false,
                'number' => 10
            ]);
        }

        if (!empty($brands) && !is_wp_error($brands)) {
            $random_brand = $this->faker->randomElement($brands);
            wp_set_object_terms($product_id, [$random_brand->term_id], 'product_brand');
        }
    }

    private function assignProductTags($product_id)
    {
        $tags = [];
        for ($i = 0; $i < $this->faker->numberBetween(2, 6); $i++) {
            $tags[] = $this->faker->word;
        }
        wp_set_object_terms($product_id, $tags, 'product_tag');
    }
}