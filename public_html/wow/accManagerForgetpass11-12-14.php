<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta https-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Callplz, Let the websites do the talk!</title>
<style>
h2{  border-top: 1px solid #F7F7F7;    padding: 8px 0 10px;}
#conError { color:red; font-size:12px; display:block; text-transform:capitalize}
label { font-size:14px; margin-bottom:3px; display:block;}  #usubmit:hover , #getWithVoice:hover{ opacity:0.9}
#usubmit , #getWithVoice { color:#fff; cursor:pointer;}
#usubmit { background: #17A7DB;} #getWithVoice { background:#666;}
.popUpPanel input[type="button"], .popUpPanel input[type="submit"] { padding:6px 10px}
</style>
</head>
<body>
<div class="form1" id="forgot_div">
<h2>Get Password</h2>
<form name="f" id="forgot_form" method="post" action="javascript:;">
	<label class="mrB">Enter Username </label>
	<input tabindex="1" name="uname" type="text" id="uname" placeholder="" />
    <span id="conError"></span>
	<label class="getCode">Get Confirmation code via</label>
    <div class="mrT">
    <input name="SMS" tabindex="3" type="button" value="SMS" id="usubmit" class="btn pdL2 pdR2"/>
	<input name="CALL" tabindex="3" type="button" value="CALL" id="getWithVoice" class="btn pdL2 pdR2"/>
    </div>
 </form>
</div>

<script type="text/javascript">
    //function to reset password
function reset_pass(code,key,num)
{
    //get password field values
    var n1= $("#new_pwd").val();
    var n2= $("#confirm_pwd").val();
   
   //validation for password
    if((n1.length <= 7 || n2.length <= 7) || n1.length!=n2.length || n1 != n2)
    {
            $("#new_pwd").focus().attr('value','');
            $("#confirm_pwd").attr('value','');
            $("#confirm_pwd").next().addClass("error_red").html("please enter valid Password,Minimum password length is 8!!!");
            $("#new_pwd").addClass("error_red");
            $("#confirm_pwd").addClass("error_red");
    }		
    else if(n1 == n2)
    {
        
		//make a request to reset password
		$.ajax({type: "POST",
                        url: "action_layer.php?action=reset_pwd",
                        data: {new_pwd: n1 ,code:code,key:key,mobNum:num},
                        dataType:"json",
                        success: function(response)
                            {
                                
                                    //if password successfully changed
                                    if(response.msgtype == "success")
                                    {
                                            $("#new_pwd").attr('value','');				
                                            $("#confirm_pwd").attr('value','');				
                                            $("#new_pwd").removeClass("error_red");
                                            $("#confirm_pwd").removeClass("error_red");
                                            $("#forgot_div").empty();
                                            $("#forgot_div").html("Password Changed Successfully");
                                    }
                                    
                                    if(response.msgtype == "error")//if problem in password
                                    {
                                            $("#new_pwd").addClass("error_red");
                                            $("#confirm_pwd").addClass("error_red");
                                            $("#new_pwd").attr('value','');
                                            $("#confirm_pwd").attr('value','');
                                            $("#forgot_div").empty();
                                            $("#passError").html("weak password please chose another one.");

                                    }
                                    
                                    show_message(response.msg,response.msgtype);

                            }//end of success function
		});//end of ajax function
	
    }
    else
        $("#passError").html("New Password are not matched.");
}

    //when user click on SMS or call button
$("#getWithVoice,#usubmit").click(function(ths){
        //get button value
        var uname = $("#uname").val();
        //validation for uname
        if(uname != "")
         {
            //request to send code via sms or call
            $.ajax({url:"/controller/settingController.php?action=accManagerForgetPass",
                    data:'smsCall='+ths.target.value+'&uname='+uname,
                    dataType:"JSON",
                    success:function(data)
                    {  
                        //if(data.msg=='Sorry user with this username or id not found.' || data.msg=='This User ID does not exists')
                        if(data.status == 'error')
			{
                            
                            $("#uname").focus().attr('value','');    
//                            $("#conError").addClass("error_red").html("Sorry user with this username or id not found");
                           show_message(data.msg,data.status);
                            return;

                        }
                        //else if(data.msg=='confirmation code has been sent to your mobile')
                        else if(data.status == 'success')
			{
                            show_message(data.msg,data.status);
                                  //empty div and append text field and button
                            $("#forgot_div").empty();
                            //append confirm box below forget div
                            $('<p>'+data.msg+'</p><p>Enter  confirmation code:</p><input tabindex="1" name="textCon" type="text" id="textConfirm" /><input name="" tabindex="3" type="button" value="confirm" id="idconfirm" class="btn"/><span id="codeError"></span>').appendTo("#forgot_div");
        
                            //when click on confirm after insert confirmation code
                            $("#idconfirm").click(function(){
                                var code = $("#textConfirm").val();
                                if(code.length > 3)
                                {
                                    //make a request to verify code
                                        $.ajax({url:"action_layer.php?action=verifyConfirmation",
                                        data:{"code":code,"number":Number(data.contact[0].number.replace("-", ""))},
                                        dataType:"JSON",
                                        success:function(flag)
                                            {//flag contain userid or 0
                                                //if valid confirmation code
                                                console.log(flag);
                                                
                                                if(flag.key != "" && flag.key != undefined)
                                                {
                                                    //empty forgat_pass div and append fields for resend
                                                    $("#forgot_div").empty();
                                                    $('<p>Successfully confirmed.</p><div id="changePass"><h3>Reset Password</h3><div class=""><form name="f" method="post" class="form1" action="resetpass.php"><label>New Password</label><input type="hidden" name="mobNum" id="mobNum" value="'+data.number+'"><input type="password" name="new_pass" id="new_pwd" /><div></div><label>Confirm New Password</label><input type="password" name="confirm" id="confirm_pwd" /><div></div><input type="button" id="change_pwd_btn" name="submit" class="search_btn medium green awesome" value="Set Password" onClick="reset_pass(\''+code+'\',\''+flag.key+'\',\''+Number(data.contact[0].number.replace("-", ""))+'\');" /></form></div></div><span id="passError"></span>').appendTo("#forgot_div");
                                                    show_message("Successfully confirmed","success");
                                                }
                                                else //if wrong confirmation code
                                                {
                                                    $("#textConfirm").focus().attr('value','');
                                                    $("#codeError").addClass("error_red").html("Wrong confirmation code,please  enter a valid confirmation code");
                                                    show_message("Wrong confirmation code,please  enter a valid confirmation code","error");
                                                }
                                                    
                                                

                                             }//end of success function 
                                         });//end of ajax function 
                               }
                               else//show error msg
                               {
                                  $("#textConfirm").focus().attr('value','');
                                  $("#codeError").addClass("error_red").html("Please enter valid confirmation code");              
                                  show_message("Please enter valid confirmation code","error");
                               }   
                            });//end of click on confirm button
                        }
//                        else
//                        {
//                            $("#forgot_div").html('Sorry Your request could not proccessed at this moment.  Please try after some time');
//                            show_message("Sorry Your request could not proccessed at this moment.  Please try after some time","error");
//                        }
                    }//end of success
            });//end of ajax function 
         }
         else
         {
            $("#conError").html("enter username or mobile number");
            show_message("enter username or mobile number","error");
            return;
         }
        
    });//end of click on SMS or CALL button
    
$(document).ready(function() { 

					
       });
	   function forgot_response(responseText, statusText, xhr, $form)
	   {
		  
		if(responseText=='Sorry user with this username or id not found.' || responseText=='This User ID does not exists')
		{
                    $("#uname").attr('value','');
                    $("#conError").html("Sorry user with this username or id not found");
                    return;
                    
		}
                else if(responseText=='confirmation code has been sent to your mobile')
		{
			  // $("#forgot_div").html(responseText);
		}
		else
                {
                    $("#forgot_div").html('Sorry Your request could not proccedd at this moment.  Please try after some time');
		}
		
	}//end of forget_response function
</script>

</body>
</html>