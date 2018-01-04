<?php
/**
 * Plugin Name: SPRV Deleted Users Log
 * Plugin URI: http://sprvtec.com
 * Description: A simple plugin for displaying log of deleted users.
 * Version: 1.0
 * Author: Suhail Akhtar
 * Author URI: http://suhail.cu.cc
 * License: GPL2
 */

! defined( 'ABSPATH' ) AND exit;


/**
 *
 * Create table on activation
 *
 */



function sprvdu_create_plugin_database_table()
{
    
    global $table_prefix, $wpdb;
    $wp_track_table = $table_prefix . "sprv_deleted_users";


    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) 
    {
		$charset_collate = $GLOBALS['wpdb']->get_charset_collate();

        $sql = "CREATE TABLE `". $wp_track_table . "` ( ";
        $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
        $sql .= "  `user_id`  int(128)   NOT NULL, ";
        $sql .= "  `user_email`  varchar(255) , ";
        $sql .= "  `user_nicename`  varchar(255 )  , ";
        $sql .= "  `date_deleted` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
        $sql .= "  PRIMARY KEY (`id`) "; 
        $sql .= ") {$charset_collate} ; ";
        $path  =  ABSPATH . 'wp-admin/includes/upgrade.php';

        require_once( $path);
        dbDelta($sql);
    }
}

 register_activation_hook( __FILE__, 'sprvdu_create_plugin_database_table' );

// Add the admin page
function sprvdu_add_users_page()
{

    add_users_page(
        // $page_title
         'Deleted users'
        // $menu_title
        ,'Deleted users'
        // $capability
        ,'read'
        // $menu_slug
        ,'sprv-deleted-users'
        ,'sprvdu_render_users_page'
    );
}
add_action( 'admin_menu', 'sprvdu_add_users_page' );

// Render the users private admin page
function sprvdu_render_users_page()
{
    global $current_user;

    if ( ! current_user_can( 'read', $current_user->ID ) )
        return;

    global $wpdb, $table_prefix;

    $html = '<div class="wrap">';
    $html .= "<h1>Deleted Users<h1>";

    $results = $wpdb->get_results( "SELECT * FROM `".$table_prefix."sprv_deleted_users` ");
    if(sizeof($results)){

        $html .= "<div class='container'>";
        $html .= "<div class='row'>";
        $html .= '<table class="wp-list-table widefat fixed striped users">';
        $html .= "<thead><tr><th>Email</th><th>Nicename</th><th>Date deleted</th></tr></thead>";
        $html .= "<tbody>";
        foreach ( $results as $user ) {
        // var_dump($user) ;
           $html .= "<tr>";
       // $table .= "<td>". esc_html( $user->user_id ) . "</td>";
           $html .= "<td>". esc_html( $user->user_email ) . "</td>";
           $html .= "<td>". esc_html( $user->user_nicename ) . "</td>";
           $html .= "<td>". esc_html( $user->date_deleted ) . "</td>";
           $html .= "</tr>";
       }
       $html .= "</tbody>";
       $html .= "</table>";
       $html .= "</div>";
       $html .= "</div>";
   } else {
    $html .= "<h3>No deleted user found.</h3>";
}
       echo $html;

}


function sprvdu_custom_remove_user( $user_id ) {

    global $table_prefix, $wpdb;
    $wp_track_table = $table_prefix . "sprv_deleted_users";

    // Get user info and insert into sprv_deleted_users table
    $user_obj  =  get_user_by('id', $user_id);

    // Insert into table
    $wpdb->insert( 
        $wp_track_table, 
        array( 
            'user_id' => $user_id, 
            'user_email' => $user_obj->user_email,
            'user_nicename' => $user_obj->user_nicename
        ), 
        array( 
            '%d',
            '%s', 
            '%s' 
        ) 
    );


}
add_action( 'delete_user', 'sprvdu_custom_remove_user', 10 );