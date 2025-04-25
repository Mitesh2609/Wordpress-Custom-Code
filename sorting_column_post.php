<?php 
/**
 * Manage Column Admin Side of Custom Post Type
 * Date and Last Updated Field
 * Sorting functionality on both field.
 */

 add_filter( 'manage_{post_type}_posts_columns', 'set_custom_column_jurisdiction' );
 function set_custom_column_jurisdiction($columns) {
     unset( $columns['date'] );
     $columns['published_date'] = "Date";
     $columns['last_updated_date'] = "Last Updated";
     return $columns;
 }
 
 add_action( 'manage_jurisdiction_posts_custom_column', 'custom_column_jurisdiction', 10, 2 );
 function custom_column_jurisdiction( $column, $post_id ) {
     switch ( $column ) {
         case 'published_date':
             echo esc_html( 'Published' ); echo "<br>";
             echo get_the_date( 'Y/m/d \a\t g:i a', $post_id );
             break;
 
         case 'last_updated_date':
             echo esc_html( 'Last Modified' ); echo "<br>";
             echo get_post_modified_time( 'Y/m/d \a\t g:i a', false, $post_id );
             break;
     }
 }
 
 add_filter( 'manage_edit-jurisdiction_sortable_columns', 'sortable_custom_jurisdiction_columns' );
 function sortable_custom_jurisdiction_columns( $columns ) {
     $columns['published_date'] = 'published_date';
     $columns['last_updated_date'] = 'last_updated_date';
     return $columns;
 }
 
 add_action( 'pre_get_posts', 'custom_sortable_column_query_jurisdiction' );
 function custom_sortable_column_query_jurisdiction( $query ) {
     if ( is_admin() && $query->is_main_query() && $query->get( 'post_type' ) === 'jurisdiction' ) 
     {
         $orderby = $query->get( 'orderby' );
 
         if ( $orderby === 'published_date' ) {
             $query->set( 'orderby', 'date' );
         }
 
         if ( $orderby === 'last_updated_date' ) {
             $query->set( 'orderby', 'modified' ); 
         }
     }
 }
