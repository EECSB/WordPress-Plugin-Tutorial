<?php

//Save // add_action("wp_ajax_"+"action property contents", "function to be called");
add_action('wp_ajax_save', 'save'); //For logged in users.
add_action('wp_ajax_nopriv_save', 'save'); //For not logged in users.       
//Load
add_action('wp_ajax_load', 'load');
add_action('wp_ajax_nopriv_load', 'load');   
//Clear
//Make this available only to logged in users.
add_action('wp_ajax_clear', 'clear');

function clear(){
    //Check if the user has admin privileges.

    //Get the current user data.
    $user = wp_get_current_user();
    //Take the $user->roles array and chekc if it contains the 'admin' role.
    if(!in_array( 'administrator', (array) $user->roles )){
        //If not, return a warning and stop the code execution.
        echo json_encode(["message" => "You need to be an Admin to clear the message board."]);
        die; //Terminate php execution.
    }

    //$wpdb is a global object that contains functions to work with the DB. It is instantiated and provided by wordpress automatically.
    //So this is all we have to do to use it: 
    global $wpdb; //Get the global instance of the wpdb class(used to work with the DB).     
    //Run update query.
    $wpdb->query("UPDATE message_board SET content = '' WHERE id LIKE 1");

    //Make an array with a message, serialize it to json and return 
    echo json_encode(["message" => "Message Board was cleared."]);

    die; //Terminate php execution.
}

function save(){
    //Storing the actual HTML in the DB is not the best idea. Ideally, it should be serialized then stored in the DB. This is just quicker and easier for the demonstration.
    //Here I will use urlencode() to mitigate SQL injection. Usually, you would use the strip_tags() function, but in this case it would ruin our HTML. Or use the prepare() function provided by WP. 

    //All the properties that were sent via POST request can be accessed from the $_POST[] array.
    $inputData = urlencode($_POST["contentToSave"]);

    //$wpdb is a global object that contains functions to work with the DB. It is instantiated and provided by wordpress automatically.
    //So this is all we have to do to use it: 
    global $wpdb; //Get the global instance of the wpdb class(used to work with the DB).     
    //Run update query.
    $wpdb->query("UPDATE message_board SET content = '" . $inputData . "' WHERE id LIKE 1");

    //Make an array with a message, serialize it to json and return 
    echo json_encode(["message" => "The content was saved."]);

    die; //Terminate php execution.
}

function load(){
    //$wpdb is a global object that contains functions to work with the DB. It is instantiated and provided by wordpress automatically.
    //So this is all we have to do to use it: 
    global $wpdb; //Get the global instance of the wpdb class(used to work with the DB). 
    //Get stored messages.
    $content = $wpdb->get_var("SELECT content FROM message_board WHERE id LIKE 1");
    
    $content = urldecode($content);

    echo json_encode($content);

    die; //Terminate php execution.
}

class MessageBoard{
    function getBoard(){
        return "
            <div id='wrapperDiv'>
                <div id='boardWrapper'>
                    <h3>Message board</h3>
                    <ul id='messageList'>
                    </ul>
                </div>
                <div id='messageWrapper'>
                    <h4>Enter a message.</h4>
                    <input type='text' id='messageBox'/>
                    <input type='button' id='button' value='Submit'>
                </div>
            </div>
        ";
    }
}

//Examples//////////////////////////////////////////

function WP_DB_Functions_Demo()
{
    //$wpdb is a global object that contains functions to work with the DB. It is instantiated and provided by wordpress automatically.
    //Get the global instance of the wpdb class(used to work with the DB).
    global $wpdb;      

    //This function can be used to execute any MySQL statment: 
    //query("your SQL query statement");
    $users = $wpdb->query("SELECT * FROM users");


    //Prevents SQL injection.
    //prepare("your SQL query statement", "array with the values"); //% represents the parameter (%s for string; %d for integer, %f for float)
    $unsanitizedString = "bob";
    $user = $wpdb->query($wpdb->prepare("SELECT * FROM users WHERE name LIKE %s", [$unsanitizedString]));


    //Inserts new data:    
    //insert("table name", "your data as an associative array");
    $wpdb->insert( 
        "users", 
        [ "id" => 1, "name" => "bob" ], //column => value 
        [ "%d", "%s"] //format (%s for string; %d for integer, %f for float)
    );


    //Gets a single variable: 
    //get_var("your SQL query statement");
    $userName = $wpdb->get_var("SELECT name FROM users WHERE id LIKE 1");


    //Gets multiple results and returns associative array.
    //get_results("your SQL query statement", output_type ); //output_type how you get the reults indexed array = ARRAY_N, associative array = ARRAY_A, object = OBJECT
    $allUsers = $wpdb->get_results("SELECT name FROM users", ARRAY_A); 


    //Updates table: 
    //update("table name", "your data as an associative array", "where condition as an associative array");
    $wpdb->update(
        "users",
        ["name" => "alice"],
        ["name" => "bob"]
    );


    //Deletes table row: 
    //delete("table name", "where condition as an associative array");
    $wpdb->delete("users", ['id' => 1]);

    //You can get all the available functions and examples in the official WordPress documentation here: 
    //https://developer.wordpress.org/reference/classes/wpdb/
}

function WP_Filesystem_Functions_Demo(){
    //Here I will demonstrate a few of the more common For the full list and full functionality of the WP filesystem functions check the official documentation.
    //https://developer.wordpress.org/reference/classes/wp_filesystem_direct/#methods

    //Call WP_Filesystem() or the $wp_filesystem global will be null;
    WP_Filesystem();
    //Get the file system object.
    global $wp_filesystem;

    //Here we'll just get the plugin folder path.///////////
    $path = dirname(__FILE__);

    $dirPath = $path . "/MyNewFolder";       //Define path/name for our new directory.
    $filepath = $dirPath . "MyNewFile.txt";  //Define path/name for our new file.

    ////////////////////////////////////////////////////////


    //Make a directory.
    $wp_filesystem->mkdir($dirPath);

    //Make a file.
    $wp_filesystem->put_contents($filepath, "Hello World!");

    //Get the data from the file.
    $fileContents = $wp_filesystem->get_contents($filepath);

    //Changes file/folder permissions.
    //chmod(file/folder path, permissions(same logic as linux permissions), recursive(optional) apply to all files/subfolders)
    $wp_filesystem->chmod($filepath, 777); //777 gives everyone all the permissions.

    //Copy file, with new name into the same directory.
    //copy(source, destination, overwrite the destination file if it exists(optional parameter))
    $wp_filesystem->copy($filepath, $path. "\MyNewestFile.txt");

    //Move the file file. 
    //move(source, destination, overwrite the destination file if it exists(optional parameter))
    $wp_filesystem->move($filepath, $path . "\MyNewFile.txt"); //This will move the file from MyNewFolder to the plugin root folder.

    //Check if the file exists...
    if($wp_filesystem->exists($path . "\MyNewFile.txt"))
        $wp_filesystem->delete($path . "\MyNewFile.txt"); //if so delete it.

    //Remove directory. 
    $wp_filesystem->rmdir($dirPath);
    //Delete all the files and subdirectories within the folder.
    //rmdir(path, recursive(optional))
    $wp_filesystem->rmdir($dirPath, true); 
}

////////////////////////////////////////////////////