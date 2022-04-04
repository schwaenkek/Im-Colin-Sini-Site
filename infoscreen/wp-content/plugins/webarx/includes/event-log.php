<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to log any events that happen on the WordPress site.
 */
class W_Event_Log extends W_Core
{
	/**
	 * Add the actions required for the activity logger.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
        if (!$this->get_option('webarx_activity_log_is_enabled', true)) {
        	return;
        }

        // The activity logger feature can only be used on an activated license.
        if (!$this->license_is_active()) {
            return;
        }

        // Include all the different event loggers.
        foreach (array('posts', 'plugins', 'core', 'options', 'users', 'comments', 'attachment') as $event) {
            require_once dirname(__FILE__) . '/events/' . $event . '.php';
            $class = 'W_Event_' . ucfirst($event);
            new $class();
        }
    }

    /**
     * If any of our event listeners get triggered, it will insert data using this method.
     * 
     * @param array $args The arguments to log.
     * @return void
     */
    public function insert($args)
    {
        // Get the author name, if the user is logged in.
        $user = get_user_by('id', get_current_user_id());
        $author = !$user ? 'Unauthenticated user' : $user->data->user_login;

        // Exception for when the action is 'logged in'.
        if ($args['action'] == 'logged in') {
            $author = $args['object_name'];
        }

        // Don't log blacklisted actions.
        $blacklists = array(
            'object' => array(),
            'action' => array(),
            'object_name' => array('Private: webarx.zip')
        );
        foreach ($args as $key => $arg) {
            if (isset($blacklists[$key]) && in_array($arg, $blacklists[$key])) {
                return;
            }
        }

        // Log the action.
        if ($args['object_name'] != 'Private: webarx.zip' && !is_null($this->get_ip())) {

            // Skip unauthenticated user on post object.
            if ($author == 'Unauthenticated user' && $args['object'] == 'post') {
                return;
            }
        
            // Insert into the logs.
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'webarx_event_log',
                array(
                    'author' => $author,
                    'ip' => $this->get_ip(),
                    'object' => $args['object'],
                    'object_id' => $args['object_id'],
                    'action' => $args['action'],
                    'object_name' => $args['object_name'],
                    'date' => current_time('mysql'),
                    'flag' => '-'
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s', '%s')
            );
        }
    }
}
