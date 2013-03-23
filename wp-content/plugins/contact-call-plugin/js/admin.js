function isValidEmailAddress(emailAddress) 
{
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}

function showError(message)
{
	alert(message);
	jQuery('#error_mssg > font').text(message);
	jQuery('#error_mssg').slideDown();
}


function isNotValid(value)
{
	if(value == undefined)
		return true;
	
	if(value.length == 0)
		return true;
	
	return false;
}


//Init
jQuery(function () {   

	// Save
	jQuery('#save').click(validatePush2Call);
	
});

// Validate the form fields and genreate invox id
function validatePush2Call(e)
{

	// Prevent Default
	e.preventDefault();
	
	// Hide
	jQuery('#error_mssg').slideUp();
		
	// Get Forward Type
	var forwardType = jQuery('#forward_type option:selected').val();
	
	// Get Forward Value
	var forwardValue = jQuery('#forward_value').val();   
	if(isNotValid(forwardValue))
	{
		showError("Please enter a valid value for forward value");
		return false;
	}
	
	//alert(forwardType + " " + forwardValue);
	
	// Check if gtalk is properly configured
	if(forwardType.toLowerCase() == "google-talk" && !isValidEmailAddress(forwardValue))
	{
		showError("Gtalk users, please enter your full Id. If you are using Gmail's Google talk, please enter userid@gmail.com");
		return false;
	}
	
	
	if(forwardType.toLowerCase() == "google-talk")
	{
		alert("Please ensure that you are using Google Talk application as Gmail web interface does not allow receiving calls");
	}
	
		 
	// Get Email
	var email = jQuery('#user_email').val();   
	if(isNotValid(email) && !isValidEmailAddress(email))
	{
		showError("Please enter a valid email address. Voicemails would be sent here.");
		return false;
	}
	

 	var guid = jQuery('#invoxuserid').val(); 
 	if(isNotValid(guid))
 	{
 		guid = generateGuid();
 		jQuery('#invoxuserid').val(guid); 
 	}
    
    var color = jQuery('#color').val();   
	if(isNotValid(color) || color.length != 6)
	{
		showError("Please enter a valid color for forward value. Valid hexadecimal color code of length 6 is required");
		return false;
	}
	
	
	
	
	// Get Side
	var side = jQuery('#side option:selected').val();
	
	var position = jQuery('#position option:selected').val();
	
	var userurl="";
	
	var  url = "http://social.invox.com/GetPBXServer/core?command=register&callback=?";
       
    // Register at Server Level   
       
	// Show loading
	jQuery('#push2call').find("#voicelo_loading").css('display','inline');
	
	var _inv = {};	
	_inv.baseURL= jQuery("#user_url").val();
	_inv.forward_type = forwardType;
	_inv.forward_value1 = forwardValue;
	_inv.email = email;
	
	_inv.guid = guid;
	console.log(_inv);
	
	//send request to server
	jQuery.getJSON(url, _inv, function (data) {
				
		console.log(data);	 
		jQuery('#invoxsharedextension').val(data.sharedextension);
		jQuery('#push2call').submit();
	});
		
}


function S4() {
   return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}

//genreate GUID
function generateGuid() {
   return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}
