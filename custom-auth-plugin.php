<?php
/**
 * Plugin Name: Custom Authentication Plugin
 * Description: A custom plugin for registration, login, and to-do list management with WP-CLI integration.
 * Version: 1.0
 * Author: Waleed
 */

// Register custom page templates for login, registration, and to-do list pages.
function custom_page_template($page_template) {
    if (is_page('login')) {
        $page_template = plugin_dir_path(__FILE__) . 'templates/template-login.php';
    } elseif (is_page('registration')) {
        $page_template = plugin_dir_path(__FILE__) . 'templates/template-registration.php';
    } elseif (is_page('to-do-list')) {
        $page_template = plugin_dir_path(__FILE__) . 'templates/template-to_do.php';
    }
    return $page_template;
}
add_filter('template_include', 'custom_page_template');

// Enqueue styles and scripts for the plugin.
add_action('wp_enqueue_scripts', 'initial_admin_links_hide_stylesheet');
function initial_admin_links_hide_stylesheet() {
    wp_enqueue_style('prefix-style', plugins_url('assets/css/custom-style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
function enqueue_custom_scripts(){
    wp_enqueue_script('custom-auth', plugins_url('js/custom-auth.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('custom-auth', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}

// Start custom session handling.
add_action('init', 'custom_session');
function custom_session() {
    if (!session_id()) {
        session_start();
    }
}

// Registration logic via AJAX.
add_action('wp_ajax_register_user', 'register_user_callback');
function register_user_callback() {
    $username = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'All fields are required.'));
        wp_die();
    }

    $user_id = wp_create_user($username, $password, $email);
    if ($user_id) {
        wp_send_json_success(array('status' => 'success', 'url' => home_url('/login')));
    } else {
        wp_send_json_error('An error occurred. Please try again.');
    }
    wp_die();
}

// Login logic via AJAX.
add_action('wp_ajax_login_user', 'handle_login_user');
function handle_login_user() {
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $user = wp_authenticate($email, $password);

    if ($user && !is_wp_error($user)) {
        $_SESSION['current_user'] = $user->ID;
        wp_send_json_success(array('url' => home_url('/to-do-list')));
    } else {
        wp_send_json_error('Email or Password is incorrect.');
    }
    wp_die();
}

// Logout logic via AJAX.
add_action('wp_ajax_logout_user', 'handle_logout_user');
function handle_logout_user() {
    session_destroy();
    wp_send_json_success(array('redirect' => home_url('/login')));
}

// Load tasks for logged-in user via AJAX.
add_action('wp_ajax_load_tasks', 'load_tasks');
function load_tasks() {
    $custom_user_id = $_SESSION['current_user'];
    if ($custom_user_id) {
        $tasks = get_user_meta($custom_user_id, 'tasks', true) ?: array();
        foreach ($tasks as $id => $task_data) {
            $completedClass = $task_data['completed'] ? 'completed' : '';
            echo "<li class='$completedClass' data-id='{$id}'><p class='task-text'>{$task_data['task']}</p> 
                  <button class='delete-button' data-id='{$id}'>Delete</button>
                  <button class='update-button' data-id='{$id}'>Update</button></li>";
        }
    } else {
        wp_send_json_error('User not logged in.');
    }
    wp_die();
}

// Add a new task via AJAX.
add_action('wp_ajax_add_task', 'add_task');
function add_task() {
    $task = sanitize_text_field($_POST['task']);
    $custom_user_id = $_SESSION['current_user'];

    $tasks = get_user_meta($custom_user_id, 'tasks', true) ?: array();
    foreach ($tasks as $existing_task) {
        if ($existing_task['task'] === $task) {
            wp_send_json_error(array('message' => 'Task already exists'));
            wp_die();
        }
    }

    $tasks[] = array('task' => $task, 'completed' => 0);
    update_user_meta($custom_user_id, 'tasks', $tasks);
    wp_send_json_success(array('status' => 'success'));
    wp_die();
}
add_action('wp_ajax_update_task', 'update_task');
function update_task() {
    $task_id = intval($_POST['task_id']);
    $updated_task = sanitize_text_field($_POST['task']);
    $custom_user_id = $_SESSION['current_user'];

    $tasks = get_user_meta($custom_user_id, 'tasks', true) ?: array();
    if (isset($tasks[$task_id])) {
        $tasks[$task_id]['task'] = $updated_task;
        update_user_meta($custom_user_id, 'tasks', $tasks);
        wp_send_json_success(array('message' => 'Task updated'));
    } else {
        wp_send_json_error(array('message' => 'Task not found'));
    }
    wp_die();
}
add_action('wp_ajax_delete_task', 'delete_task');
function delete_task() {
    $task_id = intval($_POST['id']);
    $custom_user_id = $_SESSION['current_user'];

    $tasks = get_user_meta($custom_user_id, 'tasks', true) ?: array();
    if (isset($tasks[$task_id])) {
        unset($tasks[$task_id]);
        update_user_meta($custom_user_id, 'tasks', $tasks);
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Task not found'));
    }
    wp_die();
}

// Toggle task completion via AJAX.
add_action('wp_ajax_toggle_task', 'toggle_task');
function toggle_task() {
    $task_id = intval($_POST['id']);
    $completed = intval($_POST['completed']);
    $custom_user_id = $_SESSION['current_user'];

    $tasks = get_user_meta($custom_user_id, 'tasks', true) ?: array();
    if (isset($tasks[$task_id])) {
        $tasks[$task_id]['completed'] = $completed;
        update_user_meta($custom_user_id, 'tasks', $tasks);
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Task not found'));
    }
    wp_die();
}

// Redirect users based on session state.
add_action('template_redirect', 'check_user_redirect');
function check_user_redirect() {
    if (is_page('to-do-list') && !isset($_SESSION['current_user'])) {
        wp_safe_redirect(home_url('/login'));
        exit;
    } elseif ((is_page('registration') || is_page('login')) && isset($_SESSION['current_user'])) {
        wp_safe_redirect(home_url('/to-do-list'));
        exit;
    }
}
if (defined('WP_CLI') && WP_CLI) {
    class Custom_WPCLI_Commands {
        public function __construct() {
            WP_CLI::add_command('custom add-task', array($this, 'add_task_command'));
            WP_CLI::add_command('custom list-tasks', array($this, 'list_tasks_command'));
            WP_CLI::add_command('custom change-task-status', array($this, 'change_task_status_command'));
        }

        public function add_task_command($args, $assoc_args) {
            if (empty($args[0])) {
                WP_CLI::error('Task description is required.');
                return;
            }
            $task_description = sanitize_text_field($args[0]);
            $user_id = isset($assoc_args['user_id']) ? intval($assoc_args['user_id']) : null;
            if (!$user_id && isset($_SESSION['current_user'])) {
                $user_id = $_SESSION['current_user'];
            }
            if (!$user_id) {
                WP_CLI::error('You must specify a user ID or be logged in to add a task.');
                return;
            }
            $tasks = get_user_meta($user_id, 'tasks', true) ?: array();
            foreach ($tasks as $existing_task) {
                if ($existing_task['task'] === $task_description) {
                    WP_CLI::error('Task already exists for this user.');
                    return;
                }
            }
            $tasks[] = array('task' => $task_description, 'completed' => 0);
            update_user_meta($user_id, 'tasks', $tasks);
            WP_CLI::success('Task added successfully for user ID: ' . $user_id);
        }
        public function list_tasks_command($args, $assoc_args) {
            $user_id = isset($assoc_args['user_id']) ? intval($assoc_args['user_id']) : null;
            if (!$user_id && isset($_SESSION['current_user'])) {
                $user_id = $_SESSION['current_user'];
            }
            if (!$user_id) {
                WP_CLI::error('You must specify a user ID or be logged in to list tasks.');
                return;
            }
            $tasks = get_user_meta($user_id, 'tasks', true) ?: array();

            if (empty($tasks)) {
                WP_CLI::success('No tasks found for user ID: ' . $user_id);
                return;
            }
            WP_CLI::line("Tasks for user ID: $user_id");
            foreach ($tasks as $index => $task) {
                $status = $task['completed'] ? 'Completed' : 'Pending';
                WP_CLI::line("[$index] Task: " . esc_html($task['task']) . " | Status: $status");
            }
            WP_CLI::success('Task listing complete.');
        }
        public function change_task_status_command($args, $assoc_args) {
            if (empty($args[0]) || !in_array($args[1], ['completed', 'pending'])) {
                WP_CLI::error('You must specify a valid task ID and a status (completed or pending).');
                return;
            }
            $task_id = intval($args[0]);
            $status = $args[1] === 'completed' ? 1 : 0;
            $user_id = isset($assoc_args['user_id']) ? intval($assoc_args['user_id']) : null;
            if (!$user_id && isset($_SESSION['current_user'])) {
                $user_id = $_SESSION['current_user'];
            }
            if (!$user_id) {
                WP_CLI::error('You must specify a user ID or be logged in to change task status.');
                return;
            }
            $tasks = get_user_meta($user_id, 'tasks', true) ?: array();
            if (!isset($tasks[$task_id])) {
                WP_CLI::error('Task with ID ' . $task_id . ' not found.');
                return;
            }
            $tasks[$task_id]['completed'] = $status;
            update_user_meta($user_id, 'tasks', $tasks);

            $status_text = $status ? 'completed' : 'pending';
            WP_CLI::success('Task ID ' . $task_id . ' status updated to ' . $status_text . '.');
        }
    }
    new Custom_WPCLI_Commands();
}


?>
