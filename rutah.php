<?php
/**
 * Main class with the bone of an admin area of a theme
 */
class RutahThemeAdmin{
    // page title
    var $page         = 'Rutah Admin';
    // tagline/moto be displayed under title
    // leave blank to hide
    var $tagline      = 'Create something awesome';

    // used to store data in `wp_options` table
    var $option_name  = 'rutah';

    // leave blank to hide
    var $ver          = '1.0';

    // all pages are stored here
    var $pages        = array();

    // list of notices used on during operating with data
    var $notices = array();

    // default page(tab) that will be loaded
    var $def_page     = '';
    var $cur_page     = '';

    // will be defined in __construct()
    // used to include default css and js
    // no trailing slash
    var $theme_url    = '';
    var $theme_path   = '';
    var $pages_path   = '';

    function __construct(){
        $this->theme_url  = get_template_directory_uri();
        $this->theme_path = get_template_directory();

        // declare all tabs
        add_action('admin_init', array(&$this, 'add_pages'));

        // handle all the saving
        add_action('admin_init', array(&$this, 'save_data'));

        // create the WordPress Admin page
        add_action('admin_menu', array(&$this, 'add_admin_menu'));

        // get all settings
        $this->options = get_option($this->option_name, array());
    }

    /************************ Pages/Tabs ************************/
    /**
     * Scan specific folder and get all pages to display
     */
    final function add_pages(){
        // read the pages folder
        if(empty($this->pages_path)){
            $this->pages_path = dirname(__FILE__) . '/pages';
        }

        // get all pages & include them & init
        if ($dir = opendir($this->pages_path)) {
            while ($dir && false !== ($entry = readdir($dir))) {
                if ($entry != "." && $entry != ".." && $entry != ".htaccess" && !is_dir($entry)) {
                    $this->pages[] = include($this->pages_path . '/' . $entry);
                }
            }
            closedir($dir);
        }

        $this->reorder_pages();
    }

    /**
     * Respect specified page order.
     * If default page not specified - get the first
     *
     * @param  array $pages All pages in any order (the includence order)
     * @return array        All pages reordered
     */
    final function reorder_pages(){
        if(empty($this->pages) || !is_array($this->pages))
            return;

        foreach($this->pages as $page){
            $tmp[$page->position] = $page;
        }

        // make smaller position at the top of an array
        ksort($tmp);
        $this->pages = $tmp;
        unset($tmp);

        // set the first page as default
        if(empty($this->def_page)){
            $first = reset($this->pages);
            $this->def_page = $first->slug;
        }

        // set default tab if not already
        if(empty($this->cur_page)){
            $this->cur_page = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->def_page;
        }
    }

    /************************ Saving process ************************/
    /**
     * All submitted form data will be saved here
     */
    final function save_data(){
        if(!isset($_POST[$this->option_name]) || empty($_POST[$this->option_name]))
            return;

        // make the var shorter :)
        $post = $_POST[$this->option_name];
        $link = $_POST['_wp_http_referer'];

        $checked = apply_filters($this->option_name . '_options_' . $this->cur_page, $post);

        $this->options = array_merge($this->options, $checked);

        // Save settings
        $save_status = 'settings_saved_error';
        if(update_option($this->option_name, $this->options)){
            $save_status = 'settings_saved';
        }

        $link = add_query_arg(array(
                                'message' => $save_status,
                                'tab'     => $this->cur_page
                            ), $link);

        wp_redirect($link);
        die;
    }

    /************************ Admin Link & Assets ************************/
    /**
     * Create WordPress Admin Menu item
     */
    function add_admin_menu(){
        $this->admin_page = add_theme_page(
                $this->page,
                $this->page,
                'manage_options',
                $this->option_name,
                array(&$this, 'display')
            );

        $this->load_assets();
    }

    /**
     * Load all required css and js files
     *
     * @param string $admin_page The slug og a theme admin page where everything will be loaded
     */
    function load_assets(){
        add_action('admin_print_styles-'  . $this->admin_page, array(&$this,'load_assets_css'));
        add_action('admin_print_scripts-' . $this->admin_page, array(&$this,'load_assets_js'));
    }
    function load_assets_js(){
        wp_register_script('bpap_formee', $this->theme_url . '/_admin/rutah/js/formee.js', array('jquery'), '3.1');

        wp_enqueue_script('bpap_formee');
    }
    function load_assets_css(){
        wp_register_style('rutah_admin_css', $this->theme_url . '/_admin/rutah/css/admin.css',  false, $this->ver);
        wp_register_style('rutah_formee',    $this->theme_url . '/_admin/rutah/css/formee.css', false, '3.1');

        wp_enqueue_style('rutah_admin_css');
        wp_enqueue_style('rutah_formee');
    }

    /************************ Display Section ************************/
    /**
     * Main content with the submit form
     */
    function display(){?>
        <div id="rutah-admin" class="wrap">

            <?php $this->display_header(); ?>

            <form method="post" action="" enctype="multipart/form-data" class="formee">
                <?php
                wp_nonce_field( $this->option_name . '-update-options' );
                // all the content will appear right here
                do_settings_sections( $this->cur_page );
                ?>
            </form>

            <div class="clearfix"></div>

            <?php $this->display_footer(); ?>

        </div>
        <?php
    }

    /**
     * What will de displayed before tabs
     */
    function display_header(){
        $this->display_header_icon();

        $this->display_header_title();

        $this->display_notices();

        $this->display_header_tabs();
    }
    // icon
    function display_header_icon(){
        screen_icon('themes');
    }
    // title of a page and its tagline
    function display_header_title(){
        echo '<h2>';
            echo '<span class="divider">';
                echo $this->page;
                if(!empty($this->ver))
                    echo ' <em><sup>v'. $this->ver .'</sup></em>';

            echo '</span>';

            if(!empty($this->tagline))
                echo '<em>'. $this->tagline .'</em>';
        echo '</h2>';
    }
    // navigation tabs
    function display_header_tabs(){
        echo '<h3 class="nav-tab-wrapper">';
        foreach ( (array)$this->pages as $page ) {
            $active = $this->cur_page == $page->slug ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->option_name . '&tab=' . $page->slug . '">' . $page->title . '</a>';
        }
        echo '</h3>';
    }

    /**
     * What will de displayed after all the tabs
     */
    function display_footer(){ ?>
        <div id="bottom">
            <?php $this->display_footer_credits() ?>
        </div>
        <?php
    }
    /**
     * Credentials of who created the framework
     * Leave it on its place, please :)
     */
    function display_footer_credits(){
        echo '<a href="https://github.com/slaFFik/rutah/" target="_blank">'.__('Created with RUTAH framework', 'rutah').'</a>
            | <a href="http://twitter.com/slaFFik" target="_blank">@slaFFik</a>';
    }

    /************************ Notices ************************/
    /**
     * Some basic notices on saving options
     */
    // not saved because of an error
    function set_notices_settings_saved_error($message){
        $this->notices['settings_saved_error'] = $message;
    }
    // saved successfully
    function set_notices_settings_saved($message){
        $this->notices['settings_saved'] = $message;
    }

    function set_notices_default(){
        if(!isset($this->notices['settings_saved_error']) || empty($this->notices['settings_saved_error']))
            $this->notices['settings_saved_error'] = __('Nothing to save (data was not changed) OR there was an error while saving settings.', 'rutah');

        if(!isset($this->notices['settings_saved']) || empty($this->notices['settings_saved']))
            $this->notices['settings_saved'] = __('Settings were successfully saved.', 'rutah');
    }

    function display_notices(){
        $message = false;

        if(isset($_COOKIE['rutah_message'])){
            $message = $_COOKIE['rutah_message'];
        }

        if(isset($_GET['message']))
            $message = $_GET['message'];

        if(!$message)
            return;

        // Some default messages/notices
        $this->set_notices_default();

        switch($message){
            case 'settings_saved':
                echo '<div class="updated">
                   <p>'.$this->notices['settings_saved'].'</p>
                </div>';
                break;
            case 'settings_saved_error':
                echo '<div class="error">
                   <p>'.$this->notices['settings_saved_error'].'</p>
                </div>';
                break;
        }
    }
}

/************************ One Admin Page Parent Class ************************/

/**
 * Class that will be a skeleton for all other pages
 */
class RutahThemeAdminPage {
    // all these vars are required and should be overwritten
    var $position    = 0;
    var $title       = 'Example Page';
    var $slug        = 'example';
    var $option_name = 'rutah';

    // all theme options
    var $options     = array();

    /**
     * Create the actual page object
     */
    function __construct(){
        register_setting( $this->slug, $this->slug );
        add_settings_section(
            $this->slug . '_data', // section id
            '', // title
            array(&$this, 'display'), // method handler
            $this->slug // slug
        );

        // get all theme options only once
        $this->options = get_option($this->option_name, array());

        // init the save process
        add_filter($this->option_name . '_options_' . $this->slug, array(&$this, 'save'));
    }

    /**
     * HTML should be here
     */
    function display(){
        print_var($this);
    }

    /**
     * All security and data checks should be here
     * NO SAVING - just checking values submitted by users
     * This method SHOULD BE CALLED IN PARENT PAGES
     */
    function save(){
        // prepare that we have smth to save
        if(!isset($_POST[$this->option_name][$this->slug]) ||
            empty($_POST[$this->option_name][$this->slug]))
        {
            return $this->options;
        }

        // all checks will be below in child classes
    }

    /************************* Form Elements *************************/
    /**
     * Below are several helpers to make form items creation easier
     */
    function form_get_name($name){
        return 'name="'.$this->option_name.'['.$this->slug.']['.$name.']"';
    }

    function form_get_value($name){
        if(empty($name))
            return;

        return $this->options[$this->slug][$name];
    }

    function form_get_submit($value = false){
        if(empty($value))
            $value = __('Save');

        return '<input type="submit" value="'.$value.'" />';
    }
}
