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

    // default page(tab) that will be loaded
    var $def_page     = '';

    // will be defined in __contruct()
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
    function add_pages(){
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
    function reorder_pages(){
        if(empty($this->pages) || !is_array($this->pages))
            return;

        foreach($this->pages as $page){
            $tmp[$page->position] = $page;
        }

        ksort($tmp);
        $this->pages = $tmp;
        unset($tmp);

        // set the first page as default
        if(empty($this->def_page)){
            $first = reset($this->pages);
            $this->def_page = $first->slug;
        }
    }

    /**
     * All submitted form data will be saved here
     */
    function save_data(){
        if(!empty($_POST))
            print_var($_POST);
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
    function display(){
        // default tab if not on it already
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->def_page;
        ?>

        <div id="rutah-admin" class="wrap">

            <?php $this->display_header(); ?>

            <form method="post" action="" enctype="multipart/form-data" class="formee">
                <?php
                wp_nonce_field( $this->option_name . '-update-options' );
                // all the content will appear right here
                do_settings_sections( $tab );
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
        // default tab if not on it already
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->def_page;

        $this->display_header_icon($current_tab);

        $this->display_header_title($current_tab);

        $this->display_notices($current_tab);

        $this->display_header_tabs($current_tab);
    }
    // icon
    function display_header_icon($current_tab){
        screen_icon('themes');
    }
    // title of a page and its tagline
    function display_header_title($current_tab){
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
    function display_header_tabs($current_tab){
        echo '<h3 class="nav-tab-wrapper">';
        foreach ( (array)$this->pages as $page ) {
            $active = $current_tab == $page->slug ? 'nav-tab-active' : '';
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
     * Leave it there, please :)
     */
    function display_footer_credits(){
        echo '<a href="http://ovirium.com/portfolio/rutah-theme-admin/" target="_blank">'.__('Created with RUTAH Theme Admin framework', 'rutah').'</a>
            | <a href="http://twitter.com/slaFFik" target="_blank">@slaFFik</a>';
    }

    /**
     * Some basic notices on saving options
     */
    function display_notices(){
        $message = false;

        if(isset($_COOKIE['rutah_message'])){
            $message = $_COOKIE['rutah_message'];
        }

        if(isset($_GET['message']))
            $message = $_GET['message'];

        if(!$message)
            return;

        switch($message){
            case 'settings_saved':
                echo '<div class="updated">
                   <p>'.__('Settings were successfully saved.', 'rutah').'</p>
                </div>';
                break;
            case 'settings_saved_error':
                echo '<div class="error">
                   <p>'.__('Nothing to save OR there was an error while saving settings.', 'rutah').'</p>
                </div>';
                break;
        }
    }

}

/**
 * Class that will be a skeleton for all other pages
 */
class RutahThemeAdminPage {
    // all these vars are required and should be overwritten
    var $position = 0;
    var $title    = 'Example Page';
    var $slug     = 'example';

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
    }

    /**
     * HTML should be here
     */
    function display(){
        print_var($this);
    }
}
