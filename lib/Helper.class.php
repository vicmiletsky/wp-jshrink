<?php

class WpJshrinkHelper {

    public $compile_path = 'wp-content/uploads/wp-jshrink';

    public $compile_prefix = 'script-';

    public $exclude = array(
        'admin-bar'
    );

    public function __construct()
    {
        do_action('wp_jshrink_script_construct', $this);
    }

    public function get_realpath($uri)
    {
        if($uri[0] === '/') {
            return ABSPATH . $uri;
        }
        return str_replace(site_url(), ABSPATH, $uri);
    }

    public function is_minifiable($item)
    {
        if(in_array($item['handle'], $this->exclude)) {
            return false;
        }
        return (bool) ( strstr($item['path'], 'wp-content/themes/' . get_template())
            || strstr($item['path'], 'wp-includes/js/') );
    }

    public function print_script($code)
    {
        echo "<script>\n";
        echo "$code\n";
        echo "</script>\n";
    }

    public function prepare_hash_item($item)
    {
        return array(
            'h' => $item['handle'],
            't' => $item['filetime']
        );
    }

    public function create_hash($list)
    {
        $hash_items = array_map(array($this, 'prepare_hash_item'), $list);

        return substr(md5(json_encode($hash_items)), 0, 10);
    }

    public function compiled_file_exists($hash)
    {
        return file_exists($this->get_compiled_file_path($hash));
    }

    public function create_compiled_file($code, $hash)
    {
        if(!is_dir(ABSPATH . $this->compile_path)) {
            mkdir(ABSPATH . $this->compile_path, 0777, true);
        }
        file_put_contents($this->get_compiled_file_path($hash), $code);
    }

    public function get_compiled_file_uri($hash)
    {
        return site_url() . '/' . $this->compile_path . '/' . $this->compile_prefix . $hash . '.js';
    }

    public function get_compiled_file_path($hash)
    {
        return ABSPATH . $this->compile_path . '/' . $this->compile_prefix . $hash . '.js';
    }

    public function clean_cache()
    {
        $path = ABSPATH . $this->compile_path;

        if(is_dir($path)) {
            $files = scandir($path);
            if ($files) {
                $check_time = time() - 604800;
                foreach ($files as $file) {
                    $full_path = ABSPATH . $this->compile_path . '/' . $file;
                    if (filemtime($full_path) > $check_time) {
                        continue;
                    }
                    if (is_file($full_path)) {
                        unlink($full_path);
                    }
                }
            }
        }
    }
}