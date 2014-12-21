<?php

class WpJshrinkPlugin {

    public $options = array(
        'flaggedComments' => false
    );

    public $helper;

    public $wp_scripts;

    public function __construct($wp_scripts)
    {
        $this->helper = new WpJshrinkHelper();
        $this->wp_scripts = $wp_scripts;
        do_action('wp_jshrink_plugin_costruct', $this);
    }

    public function dispatch()
    {
        add_action('get_footer', array($this, 'on_wp_print_footer_scripts'));
    }

    public function get_list()
    {
        //$in_footer = $this->wp_scripts->do_items($this->wp_scripts->in_footer);
        return array_map(array($this, 'prepare_item'), $this->wp_scripts->in_footer);
    }


    public function prepare_item($handle)
    {
        $path = $this->helper->get_realpath($this->wp_scripts->registered[$handle]->src);
        return array(
            'handle' => $handle,
            'uri' => $this->wp_scripts->registered[$handle]->src,
            'path' => $path,
            'filetime' => filemtime($path)
        );
    }


    public function on_wp_print_footer_scripts()
    {
        $output = '';
        $script_list = $this->get_list();
        $hash = $this->helper->create_hash($script_list);

        if(!$this->helper->compiled_file_exists($hash)) {

            foreach ($script_list as $item) {

                // Print script localization
                $this->wp_scripts->print_extra_script($item['handle']);

                // Minify script
                //if($this->helper->is_in_current_theme($item['path'])) {
                    $code = file_get_contents($item['path']);
                    $output .= "// {$item['uri']}\n";
                    $output .= JShrink\Minifier::minify($code, $this->options) . "\n";
                //}

                // And remove it from queue
                wp_dequeue_script($item['handle']);
            }

            $this->helper->clean_cache();
            $this->helper->create_compiled_file($output, $hash);
        } else {
            foreach ($script_list as $item) {

                // Print script localization
                $this->wp_scripts->print_extra_script($item['handle']);

                // And remove it from queue// And remove it from queue// And remove it from queue
                wp_dequeue_script($item['handle']);
            }
        }

        $compiled_uri = $this->helper->get_compiled_file_uri($hash);
        wp_enqueue_script('wp_jshrink_' . $hash, $compiled_uri, null, null, true);
    }
}