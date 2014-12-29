<?php

class WpJshrinkPlugin {

    /**
     * JShrink options
     * @var array
     */
    public $options = array(
        'flaggedComments' => false
    );

    /**
     * @var WpJshrinkHelper
     */
    public $helper;

    /**
     * @var WP_Scripts
     */
    public $wp_scripts;


    /**
     * Inject WP_Scripts
     * @param WP_Scripts $wp_scripts
     */
    public function __construct($wp_scripts)
    {
        $this->helper = new WpJshrinkHelper();
        $this->wp_scripts = $wp_scripts;
        do_action('wp_jshrink_plugin_construct', $this);
    }


    /**
     * Main action
     */
    public function dispatch()
    {
        add_action('wp_footer', array($this, 'on_wp_footer'));
    }


    /**
     * Gets list of processable handles
     * @return array
     */
    public function get_list()
    {
        $list = array_map(array($this, 'prepare_item'), $this->wp_scripts->in_footer);
        return array_filter($list, array($this, 'check_item'));
    }


    /**
     * Checks whether item is processable
     * @param $item
     * @return bool
     */
    public function check_item($item)
    {
        return $this->helper->is_minifiable($item);
    }


    /**
     * Prepare item data for processing
     * @param $handle
     * @return array
     */
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


    /**
     * Callback for wp_footer
     * @throws Exception
     */
    public function on_wp_footer()
    {
        $script_list = $this->get_list();
        $hash = $this->helper->create_hash($script_list);

        if(!$this->helper->compiled_file_exists($hash)) {

            $output = '';

            foreach ($script_list as $item) {

                if(is_file($item['path'])) {

                    // Add script localization
                    $extra = $this->wp_scripts->print_extra_script($item['handle'], false);
                    if ($extra) {
                        $output .= "// Localization for: {$item['uri']}\n";
                        $output .= $extra . "\n";
                    }

                    // Minify script
                    $code = file_get_contents($item['path']);
                    $output .= "// Script: {$item['uri']}\n";
                    $output .= JShrink\Minifier::minify($code, $this->options) . "\n";

                    // And remove it from queue
                    wp_dequeue_script($item['handle']);
                }
            }

            $this->helper->clean_cache();
            $this->helper->create_compiled_file($output, $hash);

        } else {

            // Compiled file exists, remove scripts from queue
            foreach ($script_list as $item) {
                wp_dequeue_script($item['handle']);
            }
        }

        $compiled_uri = $this->helper->get_compiled_file_uri($hash);
        wp_enqueue_script('wp_jshrink_' . $hash, $compiled_uri, null, null, true);
    }
}
