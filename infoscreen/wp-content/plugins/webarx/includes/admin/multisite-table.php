<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class WebARX_Network_Sites_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();

        usort($data, array(&$this, 'sort_data'));
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));

        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'url' => 'URL',
            'activated' => 'License Activated',
            'firewall_status' => 'Firewall Enabled',
            'edit' => 'Manage'
        );
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();

        $blogs_ids = get_sites();
        foreach ($blogs_ids as $b) {
            $site_info = get_blog_details($b->blog_id);

            // Search functionality
            $match = false;
            if (isset($_GET['s'])) {
                if (strpos($b->blog_id, $_GET['s'])) {
                    $match = true;
                }

                if (strpos($b->blogname, $_GET['s'])) {
                    $match = true;
                }

                if (strpos($site_info->siteurl, $_GET['s'])) {
                    $match = true;
                }

            }

            if (!isset($_GET['s']) || $match) {
                $is_firewall_enabled = get_blog_option($b->blog_id, 'webarx_basic_firewall');
                $is_activated = get_blog_option($b->blog_id, 'webarx_clientid', '') != '';

                $data[] = array(
                    'id' => $b->blog_id,
                    'title' => esc_html($b->blogname),
                    'url' => '<a href="' . $site_info->siteurl . '">' . $site_info->siteurl . '</a>',
                    'activated' =>  $is_activated ? '<i class="fa fa-lg fa-check" aria-hidden="true"></i>' : '<i class="fa fa-lg fa-times" aria-hidden="true"></i>',
                    'firewall_status' => $is_firewall_enabled ? '<i class="fa fa-lg fa-check" aria-hidden="true"></i>' : '<i class="fa fa-lg fa-times" aria-hidden="true"></i>',
                    'edit' => $is_activated ? '<a href="' . get_admin_url($b->blog_id) . 'options-general.php?page=webarx"><i class="fa fa-cogs" aria-hidden="true"></i> Edit Settings</a>' : ''
                );
            }
        }
        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'title':
            case 'url':
            case 'activated':
            case 'firewall_status':
            case 'edit':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @param Array $a
     * @param Array $b
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'id';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (isset($_GET['orderby']) && !empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (isset($_GET['order']) && !empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}
