What is Rutah?
=====

RUTAH stands for **Rapid and Unified Theme Admin Ham**. Why Ham? It could be hawk, or haven or whatever. But I just like ham.

You can use Rutah to easily create admin area for your themes. That means - the core logic for saving data into `wp-options` table, display different pages with different content - all in separate files, so you won't have a mess in your code.

----------

Rutah structure
=====

It's very minimalistic. All the logic, php code and processors are in 1 php file - `rutah.php`.

    rutah.php
    css/
        admin.css
        formee.css
    js/
        formee.js

I use [Formee](http://www.formee.org/ "Framework to help you develop and customize web based forms") to make the form nicer. It can be easily disabled via commenting appropriate lines in `rutah.php` or overwriting the `load_assets_js()` and `load_assets_css()` methods.

`rutah.php` contains (currently) 2 classes:

* **RutahThemeAdmin** (responsible for the whole logic and data management)
* **RutahThemeAdminPage** (helper to easily add new pages, should be at least one)

In future this may be changed if more functionality needed.

----------

How to use Rutah?
=====

(Almost) all  themes have `functions.php` file. So just include there a file called `milk.php` with the code below:

    // check that we don't have parent class included elsewhere
    if(!class_exists('RutahThemeAdmin'))
        include(basename(__FILE__) . '/_admin/rutah/rutah.php');

    /**
     * The main class. Some vars should be defined.
     */
    class Milk_Admin extends RutahThemeAdmin{
        // required options
        var $option_name = 'milk';
        var $ver         = '1.0';
    
        function __construct(){
            // required options
            $this->page       = __('Milk Theme Options', 'milk');
            $this->tagline    = __('With Flexibility in Mind', 'milk');
            // define the place where all our admin area pages will be placed
            $this->pages_path = dirname(__FILE__) . '/pages';
        
            // required line
            parent::__construct();
        }
    
    }
    
    // ensure that we init admin area in WordPress admin area only
    if(is_admin()){
        new Milk_Admin;
    }

That's it! Now we have admin area, that is accessible via the link called `Milk Theme Options` under Design section in WordPress sidebar navigation menu.