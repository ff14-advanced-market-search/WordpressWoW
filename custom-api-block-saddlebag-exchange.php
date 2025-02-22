<?php
/*
Plugin Name: Custom API Block Saddlebag Exchange
Description: This plugin can use to get API 
Version: 1.0
Author: Mujahid 
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


// Enqueue block assets
function custom_api_block_register_block() {
    // Register the block JavaScript
    wp_register_script(
        'custom-api-block',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'block.js' )
    );

    // Register the editor stylesheet
    wp_register_style(
        'custom-api-block-editor',
        plugins_url( 'editor.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
    );

    // Register the frontend stylesheet
    wp_register_style(
        'custom-api-block-style',
        plugins_url( 'style.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
    );

    register_block_type( 'custom/api-block', array(
        'editor_script' => 'custom-api-block',
        'editor_style'  => 'custom-api-block-editor',
        'style'         => 'custom-api-block-style',
        'render_callback' => 'custom_api_block_render',
        'attributes' => array(
            'item_ids' => array(
                'type' => 'string',
                'default' => 0,
            ),
            'pets' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'game_edition' => array(
                'type' => 'string',
                'default' => '',
            ),
        ),
    ));
}
add_action( 'init', 'custom_api_block_register_block' );

function custom_api_block_render( $attributes ) {
    $api_endpoint = 'http://api.saddlebagexchange.com/api/wow/tsmstats';
    
    // Prepare the data to send via POST
    $cleaned_string_array = trim($attributes['item_ids'], '[]"');
    $item_ids = explode(',', $cleaned_string_array);
    $cleaned_string_id = str_replace(' ', '', $item_ids);

    $data = array(
        'item_ids' => $cleaned_string_id,
        'pets' => $attributes['pets'],
        'game_edition' => $attributes['game_edition']
    );

    // Setup the POST request arguments
    $response = wp_remote_post( $api_endpoint, array(
        'method'    => 'POST',
        'body'      => json_encode( $data ),
        'headers'   => array(
            'Content-Type' => 'application/json',
        ),
    ));

    // Check if the request was successful
    if ( is_wp_error( $response ) ) {
        return '<p>Failed to fetch data from the API. Please try again or refresh the page.</p>';
    }

    // Retrieve the response body
    $body = wp_remote_retrieve_body( $response );
    
    // Check if the response body is empty
    if ( empty($body) ) {
        return '<p>Received empty response from the API.</p>';
    }

    // Decode the JSON response
    $data = json_decode($body, true);

    // Check if there was an error decoding the JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        return '<p>Error: Invalid JSON response from API.</p>';
    }

    $response_html = '<div class="custom-api-block-main">';

    $response_html .= '<a href="https://saddlebagexchange.com/" class="duck-card">';
    $response_html .= '<img src="' . plugin_dir_url(__FILE__) . 'img/duck.png" alt="Duck" />';
    $response_html .= '<p>Powered by Saddlebag Exchange</p>';
    $response_html .= '<img src="' . plugin_dir_url(__FILE__) . 'img/duck.png" alt="Duck" />';
    $response_html .= '</a>';

    $response_html .= '<div class="custom-api-block">';  

    if (!empty($data['data'])) {
        $response_html .= '<div class="api-flex-container header">';
        $response_html .= '<div class="api-title first-title"><p>Item</p></div>';
        $response_html .= '<div class="api-title second-title"><p>Price</p></div>';
        $response_html .= '</div>';

            foreach ($data['data'] as $index => $item) {
                // Tooltip content for each row
                $tooltipContent = 'Item ID: ' . esc_html($item['itemID']) . 
                    "\nItem Name: " . esc_html($item['itemName']) . 
                    "\nEU Sale Rate: " . esc_html($item['eu_sale_rate']) . 
                    "\nEU Avg Price: " . esc_html($item['eu_average_price']) . 
                    "\nNA Sale Rate: " . esc_html($item['na_sale_rate']) . 
                    "\nNA Avg Price: " . esc_html($item['na_average_price']);

                $response_html .= '<div class="api-flex-container">';
                
                // Left side: Item Name with tooltip
                $response_html .= '<div class="api-item-left">';  // Dynamically add q1, q2, q3 based on loop index
                $response_html .= '<a href="https://www.wowhead.com/item='. esc_attr($item['itemID']) .'" class="tooltip ' . 'tool' . ($index % 100 + 1) . '" data-wowhead="item=' . esc_attr($item['itemID']) . '">'
                        . esc_html($item['itemName']) 
                        . '</a>';
                $response_html .= '</div>';
                
                // Right side: Prices with tooltip
                $response_html .= '<div class="api-item-right">';
                $response_html .= '<div class="api-item-right-inner">';
                $response_html .= '<a href="javascript:void(0)" class="tooltip" data-tooltip="' . esc_attr($tooltipContent) . '">NA Avg: <span>' . esc_html($item['na_average_price']) . '</span></a>';
                $response_html .= '<img src="' . plugin_dir_url(__FILE__) . 'img/cion.png" alt="Coin Icon" />';
                $response_html .= '</div>';
                $response_html .= '<div class="api-item-right-inner">';
                $response_html .= '<a href="javascript:void(0)" class="tooltip" data-tooltip="' . esc_attr($tooltipContent) . '">EU Avg: <span>' . esc_html($item['eu_average_price']) . '</span></a>';
                $response_html .= '<img src="' . plugin_dir_url(__FILE__) . 'img/cion.png" alt="Coin Icon" />';
                $response_html .= '</div>';
                $response_html .= '</div>';

                $response_html .= '</div>';
            }

    } else {
        $response_html .= '<p class="no-data">No data available.</p>';
    }

    $response_html .= '</div>';
    $response_html .= '</div>';

    return $response_html;
}

?>
