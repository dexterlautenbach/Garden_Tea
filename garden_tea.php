<?php
/**
Plugin Name: Garden Tea
Description: A table to display all the Garden Teas and the dietary restrictions
Author: Dexter Lautenbach
Version: 1.0
*/
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include(plugin_dir_path(__FILE__) . 'the_wheel.php');

add_action('admin_menu', 'Garden_Tea_Menu');
function Garden_Tea_Menu(){
    add_menu_page(
        'Garden Tea',
        'Garden Tea List',
        'garden_tea',
        'garden_tea',
        'garden_tea_table',
        'dashicons-buddicons-replies',
        '5'
    );
}



function garden_tea_table(){
    global $wpdb;
    global $woocommerce, $post;
    wp_enqueue_style( 'style_garden', plugins_url( 'garden_style.css' , __FILE__ ) );
    wp_enqueue_script( 'script', plugins_url( 'js.js' , __FILE__ ) );
    //include( plugin_dir_path( __FILE__ ) . 'functions.php');
    date_default_timezone_set("America/New_York");
    ?>




    <form class="garden_tea_form" action ="" id = "garden_tea_form"  method = "POST">
    <label for="garden_search">Search for Name / Date:</label>
    <input type="text" id="garden_search">
    </form>

    <table id="garden_tea" name="garden_tea" class="garden_tea_table">
        <tr class="header">
            <th class="order_number">Order Number</th>
            <th class="customer_name">Customer Name</th>
            <th class="customer_name">Customer Email</th>
            <th class="date">Date</th>
            <th class="time_slot">Time Slot</th>
            <th class="guest">Guest</th>
            <th class="vegetarian">Vegetarian</th>
            <th class="gluten">Gluten</th>
            <th class="vegan">Vegan</th>
            <th class="nut">Nut Free</th>
        </tr>

        <?php
        $orders = get_orders_ids_by_product_id(6164);

        foreach ($orders as $order_id){
            $order = wc_get_order( $order_id );
            if ($order->get_status()<>"failed"){
                $items = $order->get_items();
                foreach ($items as $item) {
                    if (strpos($item, 'Garden Tea') !== false) {
                        $order_date_ind = date('Y-m-d', strtotime($item['_prdd_date']));
                        $time_slot_arr = explode('-', $item['_prdd_time_slot'], 2);
                        $time_slot = $time_slot_arr[0];
                        $order_date_combined = $order_date_ind . " " . $time_slot;
                        $order_date = date('Y-m-d H:i', strtotime($order_date_combined));
                        $new_order_array[$order_id] = array(
                            'order' => $order_id,
                            'date' => $order_date_ind,
                            'time_slot' => $time_slot,
                            'date_time' => $order_date
                        );
                    }
                }
            }
        }

        usort($new_order_array, "sortFunction_date");
        $current_date = strftime('%F');
        foreach ($new_order_array as $get_orders){
            $order_id = $get_orders['order'];
            $site_url = get_site_url();
            $tea_date = date('Y-m-d', strtotime($get_orders['date']));

            if ($tea_date >= $current_date){
                $order = wc_get_order( $order_id );
                $items = $order -> get_items();
                $order_refunds = $order->get_refunds();
                foreach( $order_refunds as $refund ){
                    foreach( $refund->get_items() as $item_id => $item ){
                        $refunded_item_id       = $item->get_meta('_refunded_item_id');
                        $refunded_item_total = $item['total']*-1;
                        //echo $refunded_item_id." / ".$refunded_item_total;
                    }
                }

                foreach ($items as $item){
                    if (strpos($item, 'Garden Tea') !==false){

                        $guest_attending = 0;
                        $nut_free = 0;
                        $vegetarian = 0;
                        $vegan = 0;
                        $gluten_free = 0;

                        $allmeta = $item->get_meta_data();
//                        print_r($meta);
//                        echo"<br/>";
                        foreach ($allmeta as $meta){
                            if (strpos($meta->key, 'Guest Attending')!== false){
                                $guest_attending = esc_html($meta->value);
                            }
                            if (strpos($meta->key, 'Nut Free')!== false){
                                $nut_free = esc_html($meta->value);
                            }
                            if (strpos($meta->key, 'Vegetarian')!== false){
                                $vegetarian = esc_html($meta->value);
                            }
                            if (strpos($meta->key, 'Vegan')!== false){
                                $vegan = esc_html($meta->value);
                            }
                            if (strpos($meta->key, 'Gluten Free')!== false){
                                $gluten_free = esc_html($meta->value);
                            }
                        }

                        if ($refunded_item_id == $item->get_id()){
                            if ($refunded_item_total == $item['total']){

                            }else{
                                ?>
                                <tr class="data_row_partial">
                                    <td class="order_number_data"><a href="<?php echo $site_url.'/wp-admin/post.php?post='.esc_html($order_id).'&action=edit">'.esc_html($order_id); ?></a></td>
                                    <td class="customer_name_data"><?php _e($order -> get_billing_first_name()." ".$order -> get_billing_last_name()); ?></td>
                                    <td class="customer_name_data"><?php _e($order -> get_billing_email()); ?></td>
                                    <td class="date_data"><?php _e($item['Date']); ?></td>
                                    <td class="time_slot_data"><?php _e($item['Time']); ?></td>
                                    <td class="guest_data"><b><?php _e($guest_attending); ?></b></td>
                                    <td class="vegetarian_data"><b><?php _e($vegetarian); ?></b></td>
                                    <td class="gluten_data"><b><?php _e($gluten_free); ?></b></td>
                                    <td class="vegan_data"><b><?php _e($vegan); ?></b></td>
                                    <td class="nut_data"><b><?php _e($nut_free); ?></b></td>

                                </tr>
                                <?php
                            }
                        }else{
                            ?>
                            <tr class="data_row">
                                <td class="order_number_data"><a href="<?php echo $site_url.'/wp-admin/post.php?post='.esc_html($order_id).'&action=edit">'.esc_html($order_id); ?></a></td>
                                <td class="customer_name_data"><?php _e($order -> get_billing_first_name()." ".$order -> get_billing_last_name()); ?></td>
                                <td class="customer_name_data"><?php _e($order -> get_billing_email()); ?></td>
                                <td class="date_data"><?php _e($item['Date']); ?></td>
                                <td class="time_slot_data"><?php _e($item['Time']); ?></td>
                                <td class="guest_data"><b><?php _e($guest_attending); ?></b></td>
                                <td class="vegetarian_data"><b><?php _e($vegetarian); ?></b></td>
                                <td class="gluten_data"><b><?php _e($gluten_free); ?></b></td>
                                <td class="vegan_data"><b><?php _e($vegan); ?></b></td>
                                <td class="nut_data"><b><?php _e($nut_free); ?></b></td>


                            </tr>
                            <?php
                        }
                    }
                }
            }
        }
        ?>


    </table>

    <?php

}




/**
 * Get All orders IDs for a given product ID.
 *
 * @param  integer  $product_id (required)
 * @param  array    $order_status (optional) Default is 'wc-completed'
 *
 * @return array
 */
function get_orders_ids_by_product_id( $product_id ){
    global $wpdb;

    $results = $wpdb->get_col("
SELECT order_items.order_id
FROM {$wpdb->prefix}woocommerce_order_items as order_items
LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
WHERE posts.post_type = 'shop_order'
AND order_items.order_item_type = 'line_item'
AND order_item_meta.meta_key = '_product_id'
AND order_item_meta.meta_value = '$product_id'
");

    return $results;
}


function sortFunction_date( $a, $b ) {
    return strtotime($a["date_time"]) - strtotime($b["date_time"]);
}

?>


