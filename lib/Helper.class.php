<?php

class WpJshrinkHelper {

    /**
     * Inside of WP uploads folder
     * @var string
     */
    public $compile_dir = 'wp-jshrink';

    /**
     * WP uploads folder info
     * @var array
     */
    public $wp_uploads;

    /**
     * Cache file prefix
     * @var string
     */
    public $compile_prefix = 'script-';

    /**
     * Handles to exclude from compiled file
     * @var array
     */
    public $exclude = array(
        'admin-bar'
    );

    /**
     * WP plugin folder (wp-content/plugins by default)
     * @var string
     */
    public $wp_plugins_path;


    /**
     * Nothing special...
     */
    public function __construct()
    {
        $this->wp_uploads = wp_upload_dir();
        $this->wp_plugins_path = realpath(__DIR__ . '/../..');
        do_action('wp_jshrink_script_construct', $this);
    }


    /**
     * Gets real path of item in file system
     * @param $uri
     * @return mixed|string
     */
    public function get_realpath($uri)
    {
        if($uri[0] === '/') {
            return ABSPATH . ltrim($uri, '/');
        }
        return str_replace(site_url() . '/', ABSPATH, $uri);
    }


    /**
     * Can we minify this file?
     * @param $item
     * @return bool
     */
    public function is_minifiable($item)
    {
        if(in_array($item['handle'], $this->exclude)) {
            return false;
        }
        // Minify files from active theme, wp-includes and plugins only
        return (bool) ( strpos($item['path'], get_stylesheet_directory()) === 0
            || strpos($item['path'], ABSPATH . WPINC) === 0
            || strpos($item['path'], $this->wp_plugins_path) === 0 );
    }


    /**
     * Prepare item to be hashed
     * @param $item
     * @return array
     */
    public function prepare_hash_item($item)
    {
        return array(
            'h' => $item['handle'],
            't' => $item['filetime']
        );
    }


    /**
     * Create hash based on handles and filemtime
     * @param $list
     * @return string
     */
    public function create_hash($list)
    {
        $hash_items = array_map(array($this, 'prepare_hash_item'), $list);
        return substr(md5(json_encode($hash_items)), 0, 10);
    }


    /**
     * Cache dir base url
     * @return string
     */
    public function get_cache_url()
    {
        return $this->wp_uploads['baseurl'] . '/' . $this->compile_dir;
    }


    /**
     * Cache dir filesystem path
     * @return string
     */
    public function get_cache_dir()
    {
        return $this->wp_uploads['basedir'] . '/' . $this->compile_dir;
    }


    /**
     * Checks whether compiled file exists (by hash)
     * @param $hash
     * @return bool
     */
    public function compiled_file_exists($hash)
    {
        return file_exists($this->get_compiled_file_path($hash));
    }


    /**
     * Create a file
     * @param $code
     * @param $hash
     */
    public function create_compiled_file($code, $hash)
    {
        if(!is_dir($this->get_cache_dir())) {
            mkdir($this->get_cache_dir(), 0777, true);
        }
        file_put_contents($this->get_compiled_file_path($hash), $code);
    }


    /**
     * Compiled script src
     * @param $hash
     * @return string
     */
    public function get_compiled_file_uri($hash)
    {
        return $this->get_cache_url() . '/' . $this->compile_prefix . $hash . '.js';
    }


    /**
     * Compiled file filesystem path
     * @param $hash
     * @return string
     */
    public function get_compiled_file_path($hash)
    {
        return $this->get_cache_dir() . '/' . $this->compile_prefix . $hash . '.js';
    }


    /**
     * Removes outdated stuff
     */
    public function clean_cache()
    {
        $path = $this->get_cache_dir();

        if(is_dir($path)) {
            $files = scandir($path);
            if ($files) {
                $check_time = time() - 604800;
                foreach ($files as $file) {
                    $file_path = $path . '/' . $file;
                    if (filemtime($file_path) > $check_time) {
                        continue;
                    }
                    if (is_file($file_path)) {
                        unlink($file_path);
                    }
                }
            }
        }
    }
}
