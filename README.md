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

* RutahThemeAdmin (responsible for the whole logic and data management)
* RutahThemeAdminPage (helper to easily add new pages, should be at least one)

In future this may be changed if more functionality needed.

----------

How to use Rutah?
=====

