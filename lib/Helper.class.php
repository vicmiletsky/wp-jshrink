<?php

class WpJshrinkHelper {

    /**
     * Where the stuff goes
     * @var string
     */
    public $compile_path = 'wp-content/uploads/wp-jshrink';

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
     * Nothing special...
     */
    public function __construct()
    {
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
            return ABSPATH . $uri;
        }
        return str_replace(site_url(), ABSPATH, $uri);
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
        return (bool) ( strpos($item['path'], 'wp-content/themes/' . get_template()) !== false
            || strpos($item['path'], 'wp-includes/js/') !== false
            || strpos($item['path'], 'wp-content/plugins/') !== false );
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
     * Actually hash items
     * @param $list
     * @return string
     */
    public function create_hash($list)
    {
        $hash_items = array_map(array($this, 'prepare_hash_item'), $list);

        return substr(md5(json_encode($hash_items)), 0, 10);
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
        if(!is_dir(ABSPATH . $this->compile_path)) {
            mkdir(ABSPATH . $this->compile_path, 0777, true);
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
        return site_url() . '/' . $this->compile_path . '/' . $this->compile_prefix . $hash . '.js';
    }


    /**
     * Compiled file filesystem path
     * @param $hash
     * @return string
     */
    public function get_compiled_file_path($hash)
    {
        return ABSPATH . $this->compile_path . '/' . $this->compile_prefix . $hash . '.js';
    }


    /**
     * Removes outdated stuff
     */
    public function clean_cache()
    {
        $path = ABSPATH . $this->compile_path;

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
