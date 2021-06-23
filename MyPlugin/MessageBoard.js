window.onload=function(){
    //Register event.
    buttonClickEvent();
    //Load existing messages onto the message board.
    loadMesssageBoard();
}

function buttonClickEvent(){
    jQuery("[id='button']").click(function(event){
        const message = jQuery("[id='messageBox']").val();
        jQuery("[id='messageList']").append("<li>"+ message +"</li>");

        const messageBoardContents = jQuery("[id='messageList']").prop("innerHTML");
        saveMBContentsToDB(messageBoardContents);
    });
}


//Load messagess//////////////////////////////////////////////////////////////////////

function loadMesssageBoard(){
        //Making an AJAX request using jQuery.
        jQuery.ajax({
            url: wp_ajax.ajax_url, //Give the url of the backend. wp_ajax was included into this JS file from the backend using wp_localize_script() function.
            type: 'post', //Set request type to POST                                          
            dataType: 'text', //Set type to text.         
            data: {
              action:'load', //Name under which the function was registered in the backend.                                            
            },
            success: function (data){ //After the backend code is done executing the frontend will continue from here. Any data sent from the backend will be available in the data parameter.
                loadMesssageBoardCallback(data); //Instead of executing the code in this anonymous function we'll forward it to callback function.
            },
            error: function(errorThrown){ //This will handle any errors if they occour.
              console.log("This has thrown an error:" + errorThrown);
            }                     
        });
}

function loadMesssageBoardCallback(data){
    const dataObject = JSON.parse(data); //Deserialize the received json data.

    jQuery("[id='messageList']").html(dataObject);
}

///////////////////////////////////////////////////////////////////////////////////////


//Save messages////////////////////////////////////////////////////////////////////////

function saveMBContentsToDB(content){
    //Making an AJAX request using jQuery.
    jQuery.ajax({
        url: wp_ajax.ajax_url, //Give the url of the backend. wp_ajax was included into this JS file from the backend using wp_localize_script() function.
        type: 'post', //Set request type to POST                                          
        dataType: 'text', //Set type to text.         
        data: {
          action:'save', //Name under which the function was registered in the backend.
          contentToSave:content, //Putting the value of the "content" input parameter to the "contentToSave" property of the "data" object.                                            
        },
        success: function (data){ //After the backend code is done executing the frontend will continue from here. Any data sent from the backend will be available in the data parameter.
            saveMBContentsToDBCallback(data); //Instead of executing the code in this anonymous function we'll forward it to callback function.
        },
        error: function(errorThrown){ //This will handle any errors if they occour.
          console.log("This has thrown an error:" + errorThrown);
        }                     
    });           
}

function saveMBContentsToDBCallback(data){
    const dataObject = JSON.parse(data); //Deserialize the received json data.
    console.log(dataObject.message); //Log the message from the backend.
}

///////////////////////////////////////////////////////////////////////////////////////