<?php

use Faker\Factory;

class ECFP_Coupon_Generator
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
            $coupon_code = $this->generateUniqueCode();
            $coupon_name = $this->generateCouponName();
            $discount_type = $this->faker->randomElement(['percentage', 'fixed']);
            $amount = $this->generateDiscountAmount($discount_type);
            
            // Insert into EasyCommerce coupons table
            $coupon_data = [
                'status' => 1, // Active
                'name' => $coupon_name,
                'code' => $coupon_code,
                'discount_type' => $discount_type,
                'amount' => $amount,
                'active' => 1,
                'created_at' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $coupons_table = $this->wpdb->prefix . 'coupons';
            $this->wpdb->insert($coupons_table, $coupon_data);
            $coupon_id = $this->wpdb->insert_id;
            
            if ($coupon_id) {
                // Add coupon rules
                $this->addCouponRules($coupon_id);
                
                $results[] = [
                    'id' => $coupon_id,
                    'name' => $coupon_name,
                    'code' => $coupon_code,
                    'amount' => $amount,
                    'type' => $discount_type
                ];
            }
        }

        return [
            'generated' => count($results),
            'coupons' => $results
        ];
    }

    private function generateUniqueCode()
    {
        $attempts = 0;
        do {
            $code = $this->generateCouponCode();
            $existing = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT id FROM {$this->wpdb->prefix}coupons WHERE code = %s",
                $code
            ));
            $attempts++;
        } while ($existing && $attempts < 10);
        
        return $code;
    }

    private function generateCouponCode()
    {
        $patterns = [
            // Pattern 1: PREFIX + NUMBER
            function() {
                $prefixes = ['SAVE', 'DISCOUNT', 'DEAL', 'SPECIAL', 'OFFER', 'PROMO', 'SALE', 'GET'];
                return $this->faker->randomElement($prefixes) . $this->faker->numberBetween(10, 99);
            },
            // Pattern 2: SEASONAL + YEAR
            function() {
                $seasons = ['SPRING', 'SUMMER', 'FALL', 'WINTER', 'HOLIDAY', 'BLACK', 'CYBER'];
                return $this->faker->randomElement($seasons) . date('Y');
            },
            // Pattern 3: PERCENT OFF
            function() {
                $percent = $this->faker->randomElement([10, 15, 20, 25, 30]);
                return $percent . 'OFF';
            },
            // Pattern 4: WELCOME + RANDOM
            function() {
                $welcomes = ['WELCOME', 'HELLO', 'NEWBIE', 'FIRST'];
                return $this->faker->randomElement($welcomes) . $this->faker->numberBetween(10, 50);
            },
            // Pattern 5: Random alphanumeric
            function() {
                return strtoupper($this->faker->regexify('[A-Z]{3}[0-9]{3}[A-Z]{2}'));
            }
        ];

        $pattern = $this->faker->randomElement($patterns);
        return $pattern();
    }

    private function generateCouponName()
    {
        $names = [
            'Welcome Discount',
            'First Time Buyer',
            'Seasonal Sale',
            'Holiday Special',
            'Flash Sale',
            'Loyalty Reward',
            'Newsletter Subscriber',
            'Social Media Promo',
            'Limited Time Offer',
            'Weekend Special',
            'Customer Appreciation',
            'Birthday Discount',
            'Referral Bonus',
            'Bulk Purchase',
            'Clearance Sale',
        ];

        return $this->faker->randomElement($names);
    }

    private function generateDiscountAmount($discount_type)
    {
        if ($discount_type === 'percentage') {
            return $this->faker->randomElement([5, 10, 15, 20, 25, 30, 40, 50]);
        } else {
            return $this->faker->randomFloat(2, 5, 200);
        }
    }

    private function addCouponRules($coupon_id)
    {
        $rules_table = $this->wpdb->prefix . 'coupon_rules';
        $rules = [];

        // Minimum order amount rule (70% chance)
        if ($this->faker->boolean(70)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'minimum_amount',
                'value' => serialize([
                    'amount' => $this->faker->randomFloat(2, 10, 500),
                    'currency' => 'USD'
                ]),
            ];
        }

        // Maximum discount amount rule (for percentage coupons)
        if ($this->faker->boolean(50)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'maximum_discount',
                'value' => serialize([
                    'amount' => $this->faker->randomFloat(2, 50, 1000),
                    'currency' => 'USD'
                ]),
            ];
        }

        // Usage limit rule (60% chance)
        if ($this->faker->boolean(60)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'usage_limit',
                'value' => serialize([
                    'total_uses' => $this->faker->numberBetween(10, 1000),
                    'per_customer' => $this->faker->numberBetween(1, 5),
                ]),
            ];
        }

        // Expiry date rule (80% chance)
        if ($this->faker->boolean(80)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'expiry_date',
                'value' => serialize([
                    'date' => $this->faker->dateTimeBetween('now', '+12 months')->format('Y-m-d H:i:s'),
                ]),
            ];
        }

        // Product category restrictions (40% chance)
        if ($this->faker->boolean(40)) {
            $categories = $this->getRandomProductCategories();
            if (!empty($categories)) {
                $rules[] = [
                    'coupon_id' => $coupon_id,
                    'type' => 'product_categories',
                    'value' => serialize([
                        'include' => array_slice($categories, 0, $this->faker->numberBetween(1, 3)),
                        'exclude' => [],
                    ]),
                ];
            }
        }

        // Customer restrictions (30% chance)
        if ($this->faker->boolean(30)) {
            $restriction_types = ['new_customers', 'returning_customers', 'specific_customers'];
            $restriction_type = $this->faker->randomElement($restriction_types);
            
            $value = ['type' => $restriction_type];
            if ($restriction_type === 'specific_customers') {
                $customers = $this->getRandomCustomers();
                $value['customer_ids'] = array_slice($customers, 0, $this->faker->numberBetween(1, 5));
            }
            
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'customer_restrictions',
                'value' => serialize($value),
            ];
        }

        // Quantity restrictions (25% chance)
        if ($this->faker->boolean(25)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'quantity_restrictions',
                'value' => serialize([
                    'min_quantity' => $this->faker->numberBetween(1, 5),
                    'max_quantity' => $this->faker->optional(0.6)->numberBetween(10, 50),
                ]),
            ];
        }

        // Time-based restrictions (20% chance)
        if ($this->faker->boolean(20)) {
            $days_of_week = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $selected_days = $this->faker->randomElements($days_of_week, $this->faker->numberBetween(1, 7));
            
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'time_restrictions',
                'value' => serialize([
                    'days_of_week' => $selected_days,
                    'start_time' => $this->faker->optional(0.5)->time('H:i'),
                    'end_time' => $this->faker->optional(0.5)->time('H:i'),
                ]),
            ];
        }

        // Exclude sale items rule (30% chance)
        if ($this->faker->boolean(30)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'exclude_sale_items',
                'value' => serialize(['exclude' => true]),
            ];
        }

        // First order only rule (15% chance)
        if ($this->faker->boolean(15)) {
            $rules[] = [
                'coupon_id' => $coupon_id,
                'type' => 'first_order_only',
                'value' => serialize(['enabled' => true]),
            ];
        }

        // Insert all rules
        foreach ($rules as $rule) {
            $this->wpdb->insert($rules_table, $rule);
        }
    }

    private function getRandomProductCategories()
    {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'fields' => 'ids',
            'number' => 10
        ]);

        return is_array($categories) ? $categories : [];
    }

    private function getRandomCustomers()
    {
        $customers = get_users([
            'role' => 'customer',
            'fields' => 'ID',
            'number' => 10,
            'orderby' => 'rand'
        ]);

        return is_array($customers) ? $customers : [];
    }
}