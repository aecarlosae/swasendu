<?php
namespace Aecarlosae\Swasendu;

use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client as HttpClient;
use \WC_Logger;

function swasendu() {
    if (!class_exists( 'WC_Shipping_Swasendu')) {
        class WC_Shipping_Swasendu extends \WC_Shipping_Method
        {
            public function __construct()
            {
                $this->id = 'wc_shipping_swasendu';
                $this->method_title = __( 'Software Agíl Sendu' );
                $this->method_description = __( 'Software Agíl Sendu' );

                $this->enabled = 'yes';
                $this->title = 'Sendu';

                $this->init();
            }

            function init()
            {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                if ($this->validateSettingForm()) {
                    $this->getRegions();
                    $this->getCommunes();
                    $this->getCouriers();
                    $this->getTrackingStates();
                }

                // Save settings in admin if you have any defined
                add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
            }

            function init_form_fields()
            {
                $this->form_fields = [
                    'api_url' => [
                        'title' => __('API URL', 'swasendu'),
                        'type' => 'text',
                        'description' => __( 'Enter the API URL', 'swasendu'),
                        'default' => 'https://app.sendu.cl/api/'
                    ],
                    'user_email' => [
                        'title' => __('User email', 'swasendu'),
                        'type' => 'text',
                        'description' => __('Enter the user email to authenticate in Sendu API', 'swasendu'),
                        'default' => 'soporte@sendu.cl'
                    ],
                    'user_token' => [
                        'title' => __('User token', 'swasendu'),
                        'type' => 'text',
                        'description' => __( 'Enter the user password to authenticate in Sendu API', 'swasendu'),
                        'default' => 'WZAxajMgsyns6s3ipGGQ'
                    ],
                    'order_status' => [
                        'title' => __('Order status', 'swasendu'),
                        'type' => 'select',
                        'options' => wc_get_order_statuses(),
                        'description' => __( 'Select the status to generate Sendu work order', 'swasendu'),
                        'default' => 'wc-processing'
                    ],
                    'lost_coverage' => [
                        'title' => __('Lost coverage', 'swasendu'),
                        'type' => 'checkbox',
                        'description' => __( 'Lost coverage', 'swasendu'),
                        'default' => false
                    ],
                ];
            }

            public function validateSettingForm()
            {
                $prefix = 'woocommerce_wc_shipping_swasendu_';
                if (
                    (isset($_POST[$prefix . 'api_url']) && !empty($_POST[$prefix . 'api_url']))
                    && isset($_POST[$prefix . 'user_email']) && !empty($_POST[$prefix . 'user_email'])
                    && isset($_POST[$prefix . 'user_token']) && !empty($_POST[$prefix . 'user_token'])
                ) {
                    return true;
                }
                return false;
            }

            public function getRegions()
            {
                if (!(new \WP_Query(['post_type' => 'swasendu_regions']))->have_posts()) {
                    try {
                        $client = new HttpClient([
                            'headers' => [
                                'X-User-Email' => $this->get_option('user_email'),
                                'X-User-Token' => $this->get_option('user_token'),
                            ]
                        ]);
                        $response = $client->request(
                            'GET',
                            sprintf('%s/%s', $this->get_option('api_url'), 'regions.json')
                        );
                        $regions = json_decode($response->getBody()->getContents());
        
                        foreach ($regions as $region) {
                            $regionSchema = [
                                'post_title' => $region[1],
                                'post_type' => 'swasendu_regions',
                                'post_status' => 'publish',
                                'meta_input' => [
                                    'name' => $region[1],
                                    'region_id' => $region[0],
                                ]
                            ];
        
                            if (!wp_insert_post($regionSchema)) {
                                (new WC_Logger())->log(
                                    'info',
                                    sprintf('The region post %s could not be saved', $region[1])
                                );
                            }
                        }
                    } catch (GuzzleException $e) {
                        (new WC_Logger())->log('error', $e->getMessage());
                    }
                }
            }

            public function getCommunes()
            {
                if (!(new \WP_Query(['post_type' => 'swasendu_communes']))->have_posts()) {
                    try {
                        $posts = get_posts([
                            'post_type' => 'swasendu_regions',
                            'numberposts' => -1
                        ]);
                        $client = new HttpClient([
                            'headers' => [
                                'X-User-Email' => $this->get_option('user_email'),
                                'X-User-Token' => $this->get_option('user_token'),
                            ]
                        ]);

                        foreach ($posts as $post) {
                            $response = $client->request(
                                'GET',
                                sprintf(
                                    '%s/%s?region_id=%s',
                                    $this->get_option('api_url'),
                                    'comunas_by_region.json',
                                    $post->region_id
                                )
                            );
                            $communesByRegions = json_decode($response->getBody()->getContents());
            
                            foreach ($communesByRegions as $communesByRegion) {
                                $communesByRegion = [
                                    'post_title' => $communesByRegion[1],
                                    'post_type' => 'swasendu_communes',
                                    'post_status' => 'publish',
                                    'meta_input' => [
                                        'name' => $communesByRegion[1],
                                        'commune_id' => $communesByRegion[0],
                                        'region_id' => $post->region_id,
                                    ]
                                ];
            
                                if (!wp_insert_post($communesByRegion)) {
                                    (new WC_Logger())->log(
                                        'info',
                                        sprintf('The commune post %s could not be saved', $communesByRegion[1])
                                    );
                                }
                            }
                        }
                    } catch (GuzzleException $e) {
                        (new WC_Logger())->log('error', $e->getMessage());
                    }
                }
            }

            public function getCouriers()
            {
                if (!(new \WP_Query(['post_type' => 'swasendu_couriers']))->have_posts()) {
                    try {
                        $client = new HttpClient([
                            'headers' => [
                                'X-User-Email' => $this->get_option('user_email'),
                                'X-User-Token' => $this->get_option('user_token'),
                            ]
                        ]);
                        $response = $client->request(
                            'GET',
                            sprintf('%s/%s', $this->get_option('api_url'), 'couriers.json')
                        );
                        $couriers = json_decode($response->getBody()->getContents());
        
                        foreach ($couriers as $courier) {
                            $courierSchema = [
                                'post_title' => $courier[1],
                                'post_type' => 'swasendu_couriers',
                                'post_status' => 'publish',
                                'meta_input' => [
                                    'name' => $courier[1],
                                    'courier_id' => $courier[0],
                                ]
                            ];
        
                            if (!wp_insert_post($courierSchema)) {
                                (new WC_Logger())->log(
                                    'info',
                                    sprintf('The courier post %s could not be saved', $courier[1])
                                );
                            }
                        }
                    } catch (GuzzleException $e) {
                        (new WC_Logger())->log('error', $e->getMessage());
                    }
                }
            }

            public function getTrackingStates()
            {
                if (!(new \WP_Query(['post_type' => 'swasendu_status']))->have_posts()) {
                    try {
                        $client = new HttpClient([
                            'headers' => [
                                'X-User-Email' => $this->get_option('user_email'),
                                'X-User-Token' => $this->get_option('user_token'),
                            ]
                        ]);
                        $response = $client->request(
                            'GET',
                            sprintf('%s/%s', $this->get_option('api_url'), 'tracking_states.json')
                        );
                        $trackingStates = json_decode($response->getBody()->getContents());
        
                        foreach ($trackingStates as $trackingState) {
                            $trackingStatesSchema = [
                                'post_title' => $trackingState->name,
                                'post_type' => 'swasendu_status',
                                'post_status' => 'publish',
                                'meta_input' => [
                                    'name' => $trackingState->name,
                                    'status_id' => $trackingState->id,
                                    'description' => $trackingState->description,
                                ]
                            ];
        
                            if (!wp_insert_post($trackingStatesSchema)) {
                                (new WC_Logger())->log(
                                    'info',
                                    sprintf('The status post %s could not be saved', $trackingState->name)
                                );
                            }
                        }
                    } catch (GuzzleException $e) {
                        (new WC_Logger())->log('error', $e->getMessage());
                    }
                }
            }

            public function calculate_shipping($package = [])
            {
                if ($package['destination']['country'] != 'CL') {
                    return;
                }
                
                try {
                    $client = new HttpClient([
                        'headers' => [
                            'X-User-Email' => $this->get_option('user_email'),
                            'X-User-Token' => $this->get_option('user_token'),
                            'Content-Type' => 'application/json',
                        ]
                    ]);

                    $totalWeight = 0;
                    $dimensions = [];
                    $cubage = 0;
                    $heightDimension = 0;
                    $largeDimension = 0;
                    $deepDimension = 0;

                    if (count($package['contents']) > 1) {
                        foreach ($package['contents'] as $content) {
                            $totalWeight += $content['quantity'] * $content['data']->get_weight();
                            $cubage += (
                                floatval($content['data']->get_height())
                                * floatval($content['data']->get_length())
                                * floatval($content['data']->get_width())
                                * intval($content['quantity'])
                            );
                            array_push(
                                $dimensions,
                                $content['data']->get_height(),
                                $content['data']->get_length(),
                                $content['data']->get_width()
                            );
                        }
    
                        $heightDimension = max($dimensions);
                        $largeDimension = sqrt(2 / 3 * $cubage / $heightDimension);
                        $deepDimension = $cubage / $heightDimension / $largeDimension;
                    } else {
                        $content = $package['contents'][array_key_first($package['contents'])];
                        $totalWeight += $content['quantity'] * $content['data']->get_weight();
                        $heightDimension = $content['data']->get_height();
                        $largeDimension = $content['data']->get_length();
                        $deepDimension = $content['data']->get_width();
                    }

                    $requestBody = [
                        'to' => (int) str_replace('C-', '', $package['destination']['state']),
                        'weight' => floatval($totalWeight),
                        'price_products' => (int) round($package['contents_cost']),
                        'dimensions' => [
                            'height' => floatval($heightDimension),
                            'large' => floatval($largeDimension),
                            'deep' => floatval($deepDimension),
                        ]
                    ];

                    $swasenduTransient = get_transient('swasendu-' . md5(json_encode($requestBody)));
                    if ((bool)$swasenduTransient) {
                        $rate = json_decode($swasenduTransient);
                    } else {
                        $response = $client->request(
                            'GET',
                            sprintf('%s/%s', $this->get_option('api_url'), 'calculator.json'),
                            [
                                'body' => json_encode($requestBody, JSON_PRESERVE_ZERO_FRACTION),
                            ]
                        );

                        $responseContent = $response->getBody()->getContents();
                        set_transient(
                            'swasendu-' . md5(json_encode($requestBody)),
                            $responseContent,
                            500
                        );

                        $rate = json_decode($responseContent);
                    }

                    $courier = get_posts([
                        'post_type' => 'swasendu_couriers',
                        'meta_key' => 'courier_id',
                        'meta_value' => $rate->courier_id,
                        'numberposts' => 1,
                    ])[0];

                    $this->add_rate([
                        'id' => sprintf('%s-%s', $rate->courier_id, $courier->name),
                        'label' => $courier->name,
                        'cost' => $rate->customer_cost,
                        'calc_tax' => 'per_item',
                    ]);
                } catch (GuzzleException $e) {
                    (new WC_Logger())->log('error', $e->getMessage());
                }
            }
        }
    }
}