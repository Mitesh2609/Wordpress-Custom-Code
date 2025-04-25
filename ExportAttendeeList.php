<?php
namespace PLRB\EventTicket;
class ExportAttendeeList
{
    public function __construct ()
    {
        add_filter( 'tribe_events_tickets_attendees_csv_export_columns', [ $this, 'add_custom_csv_columns' ], 10, 3 );
        add_filter( 'tribe_events_tickets_attendees_csv_items', [ $this, 'populate_custom_csv_columns' ], 10, 2 );
    }
    public function add_custom_csv_columns( $columns, $items, $event_id ) {
        $columns['user_id'] = esc_html_x( 'User ID', 'attendee export', 'event-tickets' );
        $columns['user_first_name'] = esc_html_x( 'Login User First Name', 'attendee export', 'event-tickets' );
        $columns['user_last_name'] = esc_html_x( 'Login User Last Name', 'attendee export', 'event-tickets' );
        $columns['user_email_address'] = esc_html_x( 'Login User Email Address', 'attendee export', 'event-tickets' );
        return $columns;
    }
    
    public function populate_custom_csv_columns($items, $event_id) {
        foreach ($items as $index => &$item) {
            if ($index === 0) {
                continue;
            }
    
            if (isset($item[9]) && !empty($item[9])) 
            {
                $user_details = getUserDetailByIDorEmail($item[9]);
                $item[10] = !empty($user_details['first_name']) ? $user_details['first_name'] : '';
                $item[11] = !empty($user_details['last_name']) ? $user_details['last_name'] : '';
                $item[12] = !empty($user_details['email_address']) ? $user_details['email_address'] : '';
            }
        }
        return $items; 
    }
}
