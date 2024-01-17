<?php

/**
 * plugin name: Pusher Demo
 * description: Pusher Demo
 * version: 1.0
 * author: Pusher
 */

// Path: plugins/pusher-demo/pusher-demo.php

declare(strict_types=1);

// exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use Dotenv\Dotenv;

// Include Pusher PHP library
require_once __DIR__ . '/vendor/autoload.php';

// Load .env file.
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

function pusher_demo_enqueue_scripts()
{
    wp_enqueue_script('pusher', 'https://js.pusher.com/8.2.0/pusher.min.js', array(), '8.2.0', true);
    wp_enqueue_script('pusher-demo', plugin_dir_url(__FILE__) . 'pusher-demo.js', array('pusher'), '1.0', true);
    wp_localize_script('pusher-demo', 'pusher_demo', array(
        'key' => $_ENV['key'],
        'cluster' => $_ENV['cluster'],
    ));
}
add_action('wp_enqueue_scripts', 'pusher_demo_enqueue_scripts');

// create a shortcode.
function pusher_demo_shortcode()
{
    ob_start();
?>
    <div id="pusher-demo">
        <h1>Pusher Test</h1>
        <p>
            Try publishing an event to channel <code>my-channel</code>
            with event name <code>my-event</code>.
        </p>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('pusher_demo', 'pusher_demo_shortcode');

// on init, run php-server code that listens to pusher service.
add_action('init', 'pusher_demo_init');
function pusher_demo_init()
{
    $options = array(
        'cluster' => $_ENV['cluster'],
        'useTLS' => true
    );
    $pusher = new Pusher\Pusher(
        $_ENV['key'],
        $_ENV['secret'],
        $_ENV['app_id'],
        $options
    );

    $data['message'] = 'hello from the pusher trigger in php';
    $pusher->trigger('my-channel', 'my-event', $data);
}
