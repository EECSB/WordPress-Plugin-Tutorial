//On page load....
window.onload=function(){
    //Register event.
    jQuery("[id='clearBoardButton']").click(function(event){
        //Making an AJAX request using jQuery.
        jQuery.ajax({
            url: wp_ajax.ajax_url, //Give the url of the backend. wp_ajax was included into this JS file from the backend using wp_localize_script() function.
            type: 'post', //Set request type to POST                                          
            dataType: 'text', //Set type to text.         
            data: { action:'clear' }, //Name under which the function was registered in the backend.                                        
            success: function (data){ //After the backend code is done executing the frontend will continue from here. Any data sent from the backend will be available in the data parameter.
                //Deserialize the json string into an object.
                deserializedData = JSON.parse(data);
                //Display message. 
                jQuery("[id='message']").html(deserializedData.message);
            },
            error: function(errorThrown){ //This will handle any errors if they occour.
                console.log("This has thrown an error:" + errorThrown);
            }                     
        });   
    });
}