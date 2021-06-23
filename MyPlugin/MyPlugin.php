<?php
/**
 * Plugin Name: My Plugin
 * Plugin URI: https://eecs.blog/
 * Description: This plugin was created as part of a series of tutorials on how to make a WordPress plugin.
 * Version: 1.0.0
 * Author: Tsla
 * Author URI: https://eecs.blog/
 * License: GPL2
 */

//Prevents direct access to the file for security reasons.
if(!defined('ABSPATH'))
    die;

class MyPlugin{
    public $pluginTitle;

    function __construct(){
        //The constructor runs when the class is initialized.
        $this->pluginTitle = plugin_basename(__FILE__); 
    }

    function register(){
        //Add/register shortcode. 
        add_shortcode( 'MyPlugin', array( $this, 'shortCode' ));

        //Reference .php files. 
        require_once(dirname(__FILE__) .'\MessageBoard.php'); //__FILE__ gets the current file, dirname() gets the parent directory 

        $file = __FILE__;
        $fileDir = dirname(__FILE__);
        $fileDir2 = dirname(dirname(__FILE__));

        //Enqueue styles. plugins_url() gets the plugin directory
        wp_enqueue_style('MessageBoard', plugins_url('/MyPlugin/MessageBoard.css'));

        //Enqueue
        wp_enqueue_script('jQuery_js', plugins_url('/MyPlugin/jquery-3.5.1.min.js'));
        wp_enqueue_script('MessageBoard_js', plugins_url('/MyPlugin/MessageBoard.js'));

        //All the ajax calls to the backend should be made through the 'admin-ajax.php' file. 
        //This To do this we need to get the url of this file and somehow provide that to the frontend(so it knows where to send its ajax request).

        //We can get the url of the 'admin-ajax.php' by using the admin_url() function.
        //Then we can save this url in an associative array under the key 'ajax_url'(in this example).

        //wp_localize_script(1, 2, 3) takes in 3 parameters. (https://developer.wordpress.org/reference/functions/wp_localize_script/)
        //1: Name under which we have enqueued the script before('MessageBoard_js' in this case)
        //2: Name for the associative array.
        //3: Associative array.

        //wp_localize_script() will take the array and create a JS object with the array name we have provided. 
        //Then it will "inject" this object into the JS file we specified('MessageBoard_js' in this case). 
        //Now 'wp_ajax' will be available as an object in the frontend script.  

        //Localize.
        wp_localize_script( 'MessageBoard_js', 'wp_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ))); 
        
        //Admin page in WP backend.

        //Add admin page. 'add_admin_page' is the name of the function that will be called to add the page.
        //The array($this, 'add_admin_page') is used to specify that we are calling the function from $this object.
        add_action('admin_menu', array($this, 'add_admin_page'));

        //Add link to admin page from plugin listing on the plugins page.
        add_filter("plugin_action_links_" . $this->pluginTitle, array($this, 'AdminPageLink')); 
    }

    function activate(){
        //Remove rewrite rules and then recreate rewrite rules.
        flush_rewrite_rules();

        //When the user clicks "activate" this code will run.

        //Stuff to initially set up your plugin would be added here. 
        //Like for example, making a table in the database to store your plugins data.

        global $wpdb; 
        
        //Check if table already exists. If not add it.
        if($wpdb->query("SHOW TABLES LIKE 'message_board'") == 0){

            //Create the message_board table.
            $wpdb->query('CREATE TABLE message_board
                (
                    id INTEGER NOT NULL,
                    content TEXT,
                    PRIMARY KEY (id)
                )'
            );
            
            //"Initialize" it with some data.
            $wpdb->insert( 
                "message_board", 
                array( 
                    'content' => "", 
                    'id' => 1
                ) 
            );
        }

    }

    function deactivate(){
        //Remove rewrite rules and then recreate rewrite rules.
        flush_rewrite_rules();

        //When the user clicks "deactivate" this code will run.

        //Code to deactivate the plugin would be added here. 
    }

    //Using uninstall.php instead.
    /*function uninstall(){
        //When the user clicks "uninstall" this code will run.
    }*/

    function shortCode($attributes = []){
        //Get the attribute from shortcode.
        $attributeValue = strtolower($attributes['display']);

        $HTML = "";

        if($attributeValue == "board"){
            $messageBoard = new MessageBoard();
            $HTML = $messageBoard->getBoard();
        }else if($attributeValue == "greeting"){
            $HTML = "
                <div>
                    <h3>Hello World.</h3>
                </div>
            ";
        }else{
            $HTML = "
                <div>
                    <h3>Just some random text.</h3>
                </div>
            ";
        }

        //Return html to the spot the shortcode was placed.
        return $HTML;
    }

    public function add_admin_page(){
        require_once(dirname(__FILE__) .'\MessageBoard.php'); //__FILE__ gets the current file, dirname() gets the parent directory 

        //Add a page to the wordpress backend menu.
        
        //add_menu_page('page title', 'menu title in side bar', 'define roles', 'unique page ID slug(can be whatever)', 'function that is called to initialize page ', 'sets icon in sidebar', 'position in sidebar')
        //'define roles' will define which user can see the page https://wordpress.org/support/article/roles-and-capabilities/#manage_options
        //You can find a list of all the existing icons here: https://developer.wordpress.org/resource/dashicons/ or put a url to your custom icon
        add_menu_page('My Plugin', 'My Plugin', 'manage_options', 'MyPlugin', array($this, 'LoadMyPluginAdminPage'), 'dashicons-testimonial', 110); 

        //Enqueue styles
        wp_enqueue_style('adminAreaStyleSheet', plugins_url('MyPlugin/adminAreaStyleSheet.css'));
        //Enqueue
        wp_enqueue_script('adminArea_js', plugins_url('MyPlugin/adminArea.js'), /*NULLdependencies such as jQuery*//* array('jquery'), true*/);
        //Localize
        wp_localize_script( 'adminArea_js', 'wp_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );   
    }

    function LoadMyPluginAdminPage(){
        require_once dirname(__FILE__) .'\AdminPage.php';
    }

    public function AdminPageLink($links){
        //$links is a array of links that are present in the plugin listing on the plugins page.
        //To add a new link lets simply push a new HTML link into this array. 
        array_push($links, "<a href='admin.php?page=MyPlugin'>Settings</a>");

        return $links;
    }
}

//Create a new instance of the plugin class.
$myPlugin = new MyPlugin();
//Call the register function.
$myPlugin->register();

//Register the activation hook.
register_activation_hook(__FILE__, array($myPlugin, 'activate')); 
//Register the deactivation hook.
register_deactivation_hook(__FILE__, array($myPlugin, 'deactivate')); 

//We will be Using uninstall.php instead as it's the recommanded way.
//Register the uninstall hook.
//register_uninstall_hook(__FILE__, array($myPlugin, 'uninstall')); 