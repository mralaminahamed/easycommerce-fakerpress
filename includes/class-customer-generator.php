<?php

use Faker\Factory;

class ECFP_Customer_Generator
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function generate($count)
    {
        $results = [];
        
        for ($i = 0; $i < $count; $i++) {
            $first_name = $this->faker->firstName;
            $last_name = $this->faker->lastName;
            $email = $this->faker->unique()->safeEmail;
            
            $user_data = [
                'user_login' => $this->generateUsername($first_name, $last_name),
                'user_email' => $email,
                'user_pass' => wp_generate_password(),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name,
                'role' => 'customer'
            ];

            $user_id = wp_insert_user($user_data);
            
            if (!is_wp_error($user_id)) {
                $this->addCustomerMeta($user_id, $first_name, $last_name, $email);
                $results[] = [
                    'id' => $user_id,
                    'name' => $user_data['display_name'],
                    'email' => $email
                ];
            }
        }

        return [
            'generated' => count($results),
            'customers' => $results
        ];
    }

    private function generateUsername($first_name, $last_name)
    {
        $base_username = strtolower($first_name . $last_name);
        $base_username = preg_replace('/[^a-z0-9]/', '', $base_username);
        
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        return $username;
    }

    private function addCustomerMeta($user_id, $first_name, $last_name, $email)
    {
        // Generate billing address data
        $billing_address = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $this->faker->phoneNumber,
            'address_1' => $this->faker->streetAddress,
            'address_2' => $this->faker->optional(0.3)->secondaryAddress,
            'country' => $this->faker->countryCode,
            'state' => $this->faker->stateAbbr,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
        ];

        // Generate shipping address data (70% chance same as billing)
        $shipping_address = $this->faker->boolean(70) ? $billing_address : [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $this->faker->phoneNumber,
            'address_1' => $this->faker->streetAddress,
            'address_2' => $this->faker->optional(0.3)->secondaryAddress,
            'country' => $this->faker->countryCode,
            'state' => $this->faker->stateAbbr,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
        ];

        // Store addresses as serialized data (EasyCommerce format)
        update_user_meta($user_id, 'billing_address', serialize($billing_address));
        update_user_meta($user_id, 'shipping_address', serialize($shipping_address));

        // Store individual fields for compatibility
        update_user_meta($user_id, 'phone', $billing_address['phone']);
        
        // Generate cart hash for EasyCommerce
        update_user_meta($user_id, '_easycommerce_cart_hash', wp_generate_password(32, false));

        // Additional customer metadata
        update_user_meta($user_id, 'customer_since', $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'));
        update_user_meta($user_id, 'date_of_birth', $this->faker->date('Y-m-d', '-18 years'));
        update_user_meta($user_id, 'customer_notes', $this->faker->optional(0.3)->sentence());
        
        // Customer preferences
        update_user_meta($user_id, 'marketing_opt_in', $this->faker->boolean(60));
        update_user_meta($user_id, 'newsletter_subscription', $this->faker->boolean(40));
        update_user_meta($user_id, 'preferred_language', $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it']));
        update_user_meta($user_id, 'timezone', $this->faker->timezone);

        // Customer statistics
        update_user_meta($user_id, 'total_orders', 0);
        update_user_meta($user_id, 'total_spent', 0.00);
        update_user_meta($user_id, 'average_order_value', 0.00);
        update_user_meta($user_id, 'last_order_date', '');

        // Account settings
        update_user_meta($user_id, 'account_status', 'active');
        update_user_meta($user_id, 'email_verified', $this->faker->boolean(80));
        update_user_meta($user_id, 'phone_verified', $this->faker->boolean(60));
        update_user_meta($user_id, 'two_factor_enabled', $this->faker->boolean(20));

        // Social media links (optional)
        if ($this->faker->boolean(30)) {
            $social_links = [];
            if ($this->faker->boolean(60)) {
                $social_links['facebook'] = 'https://facebook.com/' . strtolower($first_name . $last_name);
            }
            if ($this->faker->boolean(40)) {
                $social_links['twitter'] = 'https://twitter.com/' . strtolower($first_name . $last_name);
            }
            if ($this->faker->boolean(50)) {
                $social_links['instagram'] = 'https://instagram.com/' . strtolower($first_name . $last_name);
            }
            if (!empty($social_links)) {
                update_user_meta($user_id, 'social_links', serialize($social_links));
            }
        }

        // Customer tags (for segmentation)
        $customer_tags = [];
        $possible_tags = ['vip', 'frequent_buyer', 'new_customer', 'high_value', 'loyal', 'at_risk', 'seasonal_buyer'];
        $selected_tags = $this->faker->randomElements($possible_tags, $this->faker->numberBetween(0, 3));
        if (!empty($selected_tags)) {
            update_user_meta($user_id, 'customer_tags', serialize($selected_tags));
        }

        // Customer source (how they found the store)
        $sources = ['organic_search', 'social_media', 'email_marketing', 'referral', 'direct', 'paid_advertising', 'affiliate'];
        update_user_meta($user_id, 'acquisition_source', $this->faker->randomElement($sources));
        update_user_meta($user_id, 'acquisition_date', $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'));

        // Customer loyalty points (if applicable)
        if ($this->faker->boolean(70)) {
            update_user_meta($user_id, 'loyalty_points', $this->faker->numberBetween(0, 5000));
            update_user_meta($user_id, 'loyalty_tier', $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']));
        }

        // Communication preferences
        $communication_prefs = [
            'email_orders' => $this->faker->boolean(90),
            'email_promotions' => $this->faker->boolean(60),
            'email_newsletters' => $this->faker->boolean(40),
            'sms_orders' => $this->faker->boolean(30),
            'sms_promotions' => $this->faker->boolean(20),
            'push_notifications' => $this->faker->boolean(50),
        ];
        update_user_meta($user_id, 'communication_preferences', serialize($communication_prefs));
    }
}