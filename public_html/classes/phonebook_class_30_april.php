<?php

/**
 * @Author sudhir pandey <sudhir@hostnsoft.com>
 * @createdDate 12-07-13
 * 
 */
include dirname(dirname(__FILE__)) . '/config.php';
include_once(CLASS_DIR."/db_class.php");

class phonebook_class extends fun
{
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 12-07-2013
    #function use to add contact no and email id into phonebook 

    function addContact($parm, $userid) 
    {


        #get all contact detail 
        $dbobj = new db_class();

        #all name array   
        $allname = $parm['name'];
        #all email array
        $allemail = $parm['email'];
        #all contact
        $allcontact = $parm['contact'];
        $collectionName = 'phonebook';

       
        $countTotalSave = 0;$alreadyExist=0;
        
        $msg = $this->checkContactNull($allcontact,$allemail);
        if ($msg != "success") 
        {
            return json_encode(array("status" => "error", "msg" => $msg));
        }
        
        $errorKey = $this->checkContactValidation($allemail, $allcontact);
       
        $count = count($allemail);
        
        for ($i = 0; $i < $count; $i++) 
        {

            if(!in_array($i, $errorKey))
            {
               
                #check for contact no. is already inserted in table
                $condition = array('contact.contactNo' => $allcontact[$i],'userId'=>$userid);
                $result = $dbobj->mongo_count($collectionName, $condition);

                if ($result <= 0) 
                {
                    #update contact detail 
                    $data = array('$push' => array("contact" => array("contact_id" => new mongoId(), "name" => htmlentities($allname[$i], ENT_QUOTES, 'UTF-8'), "email" => $allemail[$i], "contactNo" => $allcontact[$i]))); //    htmlentities($comment,ENT_QUOTES,'UTF-8'))));

                    $status = $dbobj->mongo_update($collectionName, array('userId' => $userid), $data);

                    if($status)
                    {
                        $countTotalSave += 1; 

                    }
                    else
                    {                 
                        //log errors
                        trigger_error ('Problem While update phonebook!!!');
                    }

                }
                else
                {
                    $alreadyExist += 1;  
                }
            }
           
        }
        
        if ($status) 
        {
            $str = $this->allContactlist($userid);
            return json_encode(array("status" => "success", "msg" => "sucessfully $countTotalSave contact add ! ", "str" => $str));
        }
        else
        {
            
            if($countTotalSave > 0)
            {
                return json_encode(array("status" => "success", "msg" => "total ".$countTotalSave." Contact successfully add !"));
            }
            else
            {
                if($alreadyExist > 0)
                {
                    return json_encode(array("status" => "error", "msg" => "Contact number already exists !"));  
                }
                else
                    return json_encode(array("status" => "error", "msg" => "Invalid contact number please provide atleast one valid contact number !"));
            }  
        }
            
    }

    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 05-08-2013
    #fuction use to add Me contact no into phonebook table 

    function addMeContact($userid) 
    {

        #db_class obj for mongo connection 
        $dbobj = new db_class();

        #collectionName 
        $collectionName = 'phonebook';

        $condition = array('userId' => $userid);
        $result = $dbobj->mongo_count($collectionName, $condition);
        if ($result <= 0) 
        {
            #get email id form validateEmail table by ues contact_class function 
            include_once("contact_class.php");
            $cont_obj = new contact_class();
            $cont = $cont_obj->getUnConfirmEmail($userid);
            $emailId = $cont['email'];

            #check verified mobile no.
            $confirmNo = $cont_obj->getConfirmMobile($userid);
            
            if($confirmNo[0]['verifiedNumber'] == '' || $confirmNo[0]['verifiedNumber'] == NULL){
            #get contactno form validnumber table 
            $contactno = $cont_obj->getUnconfirmMobile($userid);
            $meContact = $contactno['tempNumber'];
            }else
            $meContact = $confirmNo[0]['verifiedNumber'];    

           
            $data = array("userId" => $userid, "emailId" => $emailId);
            $status =  $dbobj->mongo_insert($collectionName, $data);

             
            //log errors
            if(!$status)
                trigger_error ('Problem While insert infomation phonebook!!!');
            #insert first contact no. into phonebook table (name= "me");
            $data = array('$push' => array("contact" => array("contact_id" => new mongoId(), "name" => htmlentities("me", ENT_QUOTES, 'UTF-8'), "email" => $emailId, "contactNo" => $meContact))); //    htmlentities($comment,ENT_QUOTES,'UTF-8'))));
            $status = $dbobj->mongo_update($collectionName, array('userId' => $userid), $data);
            
             
            //log errors
            if(!$status)
                trigger_error ('Problem While update phonebook!!!');
        }
    }

    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 12-07-2013
    #function use to update contact no and email id into phonebook 

    function updateContact($parm, $userid) 
    {

        $dbobj = new db_class();
        #contact id
        $contactId = $parm['contactId'];
        #all name array   
        $allname = $parm['name'];
        #all email array
        $allemail = $parm['email'];
        #all contact
       
    //   print_r($parm);
        $allcontact = $parm['contact'];
        
       
        if($parm['accessNo'])
        {
            if($parm['accessCheck'] == "100")
            {
                 $hashNo = '100';
            }
            else if($parm['accessCheck'] == "Hash")
            {
                  $hashNo  = $parm['hashSelect'];
                  
                  if(!$hashNo)
                       return json_encode(array("status" => "error", "msg" => "Please select Hash no!"));
            }
            else if(!$hashNo)
                $hashNo = '';
        }
        else 
        {
            $hashNo = '';
        }
       
        
        #check email id valid
        if ($allemail != '' || $allemail != null) 
        {
            if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $allemail)) 
            {
                return json_encode(array("status" => "error", "msg" => "email id is not valid !"));
            }
        }

        if (!preg_match("/^[0-9]{8,15}$/", $allcontact)) 
        {

            return json_encode(array("status" => "error", "msg" => "contact no. is not valid!"));
        }
        
//        if(!preg_match("/^[a-zA-Z_@-\s]+$/", $allname))
//        {
//            return json_encode(array("status" => "error", "msg" => "Please enter valid contact name!"));
//        }

        
        $data = array('$pull' => array('contact' => array('contact_id' => new mongoId($contactId))));
        
        $collectionName = 'phonebook';
        
        #delete contact no 
        $condition = array('contact.contact_id' => new mongoId($contactId));
        
        $result = $dbobj->mongo_update($collectionName, $condition, $data);

        
        $condition = array('contact.contactNo' => $allcontact,'userId'=>$userid);
        //print_r($condition);
        
        $result = $dbobj->mongo_count($collectionName, $condition);

        if ($result > 0) {
            return json_encode(array("status" => "error", "msg" => "contact no already exist !"));
        }
        
        
          if(isset($parm['accessNo']) && $parm['accessNo'] != '' && $parm['accessNo'] != NULL){
            #check access number is valid or not 
              
              
            if(!empty($parm['accessNo']))
            {
                $val = $this->checkValidAccNo($parm['accessNo']);
                if($val != 1)
                {
                    return json_encode(array("status" => "error", "msg" => "access number are not valid!"));
                    if($hashNo == "0")
                    {
                           return json_encode(array("status" => "error", "msg" => "Hash Number is not valid!"));
                    }

                }
            }
            
            $data = array('$push' => array("contact" => array("contact_id" => new mongoId(), "name" => htmlentities($allname, ENT_QUOTES, 'UTF-8'), "email" => $allemail, "contactNo" => $allcontact,"accessNo" =>$parm['accessNo'] , "hash" => $hashNo ))); 
        }else
        $data = array('$push' => array("contact" => array("contact_id" => new mongoId(), "name" => htmlentities($allname, ENT_QUOTES, 'UTF-8'), "email" => $allemail, "contactNo" => $allcontact ,"hash" => $hashNo ))); //    htmlentities($comment,ENT_QUOTES,'UTF-8'))));
        $status = $dbobj->mongo_update($collectionName, array('userId' => $userid), $data);
         
        //log errors
        if(!$status)
            trigger_error ('Problem While update phonebook!!!');
        
        $str = $this->allContactlist($userid);
        return json_encode(array("status" => "success", "msg" => "contact no. updated !", "str" => $str));

//          #check for contact no. is already inserted in table
//          $condition = array('contact.contact_id'=>new mongoId($contactId));
//          $data=array('contact.email'=>$allemail,"contact.name"=>$allname);
//          $dataArr=array('$set'=>$data);
//          $result = $dbobj->mongo_update($collectionName,$condition,$dataArr);
//          print_r($result);  
//     
    }

    function checkValidAccNo($accessNo)
    {
      $table = '91_longCodeNumber';
      
      #get all access number from 91_longCodeNumber table  
      $result = $this->selectData('*',$table,"longCodeNo=".$accessNo);
      
      if($result->num_rows > 0)
      {
           return 1;
      }else  
      return 0;
        
    }
    
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 12-07-2013
    #function use to delete contact no and email id form phonebook tabel 

    function deleteContact($parm, $userid) 
    {

        $dbobj = new db_class();
        $contactId = $parm['contactId'];
        $collectionName = 'phonebook';
        #delete contact no 
        $condition = array('contact.contact_id' => new mongoId($contactId));
        $data = array('$pull' => array('contact' => array('contact_id' => new mongoId($contactId))));
        $result = $dbobj->mongo_update($collectionName, $condition, $data);
        
         
        //log errors
        if(!$result)
            trigger_error ('Problem While update phonebook!!!');
        
        
        $str = $this->allContactlist($userid);
        return json_encode(array("status" => "success", "msg" => "contact no. successfuly deleted!", "str" => $str));
    }

    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 12-07-2013
    #function use to get all contact no and email from phonebook 

    function getAllContact($userid) 
    {
        $collectionName = 'phonebook';
        $dbobj = new db_class();
        #check for contact no. is already inserted in table
        $condition = array('userId' => $userid);
        
        $result = $dbobj->mongo_find($collectionName, $condition);
        
        //log errors
        if(!$result)
            trigger_error ('Problem While get details from phonebook!!!');
        
        foreach ($result as $res) 
        {
            $allcontact = $res['contact'];
        }

        return array("allcontact" => $allcontact);
    }
    
     /**
     * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
     * @since 31/03/2014
     * @param int $userid
     * @param string $q search string for number,email and name
     * @return array
     */
     function getMatchedContact($request,$session) {
        $collectionName = 'phonebook';
        $dbobj = new db_class();
       
         //check user id
            if(!isset($request['userId']) || !is_numeric($request['userId']) || $request['userId']== null || $request['userId'] =='')
                return json_encode(array('status' => 0,'msg' => 'InValid userId!!!'));
            else
                $userId = $request['userId'];
           
            //check user id
            if(!isset($request['term']) || $request['term']== null || $request['term'] =='')
                $q = '';
            else
                $q= $request['term'];
           
           
        $search = array();
        $cond['or'][] = array('contact.name' =>  array('$regex' =>"^$q"));
        $cond['or'][] = array('contact.contactNo' => array('$regex' =>"^$q"));
        $cond['or'][] = array('contact.email' =>  array('$regex' => "^$q"));
        #check for contact no. is already inserted in table
        $condition = array('userId' => $userId,'$or' => $cond['or']);
       
       
        $result = $dbobj->mongo_find($collectionName, $condition);
        //var_dump($result);
        $data = array();
        foreach ($result as $res)
        {
           if(isset($res['contact']))
           foreach($res['contact'] as $value)
           {
               //check for number,name and email
               if(preg_match("/^$q/",$value['name']) || preg_match("/^$q/",$value['contactNo']) || preg_match("/^$q/",$value['email']))
               {
               $data['label']= $value['name'];
               $data['value']=$value['contactNo'];

          
               $allcontact[] = $data;
               }
              
           }
           
           
            unset($res);
            unset($data);
        }
       
        return json_encode($allcontact);
    }
    

     /*
     * @author nidhi<nidhiWalkover.in>
     * this function returns contact details of a particular contact.
     * 
     */
    
    function getContactInfo($userid , $contactNo)
    {
        $collectionName = 'phonebook';
        $dbobj = new db_class();
        
        #check for contact no. is already inserted in table
        $condition = array('userId' => $userid , "contact.contactNo" => $contactNo);
        $fetchArray = array('contact.$' => 1);
        
        $result = $dbobj->mongo_find($collectionName, $condition,$fetchArray);

        //log errors
        if(!$result)
           trigger_error ('Problem While get details from phonebook!!!');

        $allcontact = array();
        foreach ($result as $key=>$res) 
        {
           $allcontact = $res['contact'];
        }

        return array("allcontact" => $allcontact);
    }

    
    
    
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 15/07/2013
    #function use to show data for edit contact detail 

    function showEditContact($parm,$userId) 
    {

        #collection name 
        $collectionName = 'phonebook';
        $dbobj = new db_class();
        #check for contact no. is already inserted in table
        $condition = array('contact.contact_id' => new mongoId($parm['contactId']));
        $result = $dbobj->mongo_find($collectionName, $condition, array('contact.$' => 1));
        
        //log errors
        if(!$result)
            trigger_error ('Problem While get details from phonebook!!!');
        #check data is present or not          
        if ($result->count() > 0) 
        {
            $res = $result->getNext();
        }
        
        $contactData = $res['contact'];
      
       //print_r($contactData);
        
        #loop for contact array (fetch name ,email ,contact no.) 
        $assignHash = "";
        foreach ($contactData as $con) 
        {
          //  print_r($con);
            
            $name = $con['name'];
            $email = $con['email'];
            $contactNo = $con['contactNo'];
            $accessNo = $con['accessNo'];
            
            if(isset($con['hash']))
                $assignHash = $con['hash'];
            
        }
        
        #get all user access number 
        $accessArr = $this->getUserAccessNumbers($userId ,$contactNo);
        
        $userAccNo = $accessArr['accessNumber'];
        
        $hash = $accessArr['hash'];
        $allHash = range(10, 99);
        
        $hashDifference = array_diff( $allHash ,$hash);
        $hashDifference[] = $assignHash;
        $hashOption = '';
        
        foreach ($hashDifference as $hashNo) 
        { 
            if(!empty($hashNo))
            if($assignHash == $hashNo)
            {
                $hashOption .='<option value="'.$hashNo.'" selected="selected">'.$hashNo.'</option>';
            }
            else
                $hashOption .='<option value="'.$hashNo.'" >'.$hashNo.'</option>';
        }
        
        
        
        #get all access number from mysql database 
        $allAccNo = $this->allAccessNo();
        
        //print_r($allAccNo);
       // print_r($userAccNo);
        
        $remainingAccNo = array_diff( $allAccNo ,$userAccNo);
        
        //print_r($remainingAccNo);
        
        $accessOption = '';
       
        foreach ($remainingAccNo as $key=>$accNo) 
        {
            $displayAll = 'pType=""';
            
            if(in_array($accNo, $accessArr['allAccessNo']))
            {
                 $displayAll = 'pType="hideAll"';
            }
            if($key == "0")
            {
                $accessOption .= '<option value="0" '.$displayAll.'>Access Number</option>';
            }
            if($accessNo == $accNo)
            {
                $accessOption .= '<option value="'.$accNo.'" selected="selected" '.$displayAll.'>'.$accNo.'</option>';
            }
            else
                $accessOption .= '<option value="'.$accNo.'" '.$displayAll.' >'.$accNo.'</option>';
        }
        
        $style = "";
        
        if($assignHash == '100' || empty($assignHash))
           $style = 'style="display:none;"';
        
		/*Modified by Lovey at 4/sep/2013*/
        $str = '<div id="dialog-confirm" title="Confirm" style="display : none;">Are You Sure You Want to Delete This Entry</div><div id="edit-contact-dialog" title="Edit Contact">
                <form id="contact_edit_form">
                  <div id="add-cnt-inner">
                    	<div class="pd2  add-cnt-form editContact">
                            	<div class="child">
                                	<p class="mrB">Name</p>
                                    <div class="">
                                    <input type="hidden" name="contactId" value="' . $parm['contactId'] . '">    
                                    <input type="text" name="name" value="' . $name . '"/>
                                    </div>
                                </div>
                                
                                <div class="child">
                                	<p class="mrB">Contact</p>
                                    <div class="">
                                        <input type="text" name="contact" value="' . $contactNo . '"/>
                                    </div>
                                </div>
								
                                <div class="child">
                                	<p class="mrB">Email</p>
                                    <div class="">
                                        <input type="text" name="email" value="' . $email . '"/>
                                    </div>
                                </div> 
                                <div class="mrT1 mrT1 fl">
                                	<p class="mrB">Access No</p>
                                    <div class="">
                                        <select name="accessNo" id="accessNoId" onchange="displayAccess(this)">
                                                        '.$accessOption.'
                                                </select>';
                                    
                                
                                if(!empty($accessOption))
                                {
                                    $str .='<select name="accessCheck" onchange="displayHashNumbers(this)" id="accessCheck">';
                                    
                                                     $str .= '<option class=""  value="0">Select</option>';
                                                     
                                                    if($assignHash == '100')
                                                       $str .= '<option class="allAcc" selected="selected" value="100">All</option>';
                                                    else 
                                                        $str .='<option class="allAcc"  value="100">Dedicated</option>';
                                                    
                                                     if($assignHash != '100' && $assignHash !="")
                                                       $str .= '<option selected="selected" value="Hash">#</option>';
                                                    else 
                                                        $str .='<option value="Hash">#</option>';
                                                    
                                                       $str .='
                                                     </select>';
                                         
                                    $str.='<select name="hashSelect" id="hashSelect" '.$style.' >
                                                        '.$hashOption.'
                                                     </select>';
                                    
                                }


                             $str .='</div> 
							 <div class="actionS"> 
                            <a class="btn btn-medium btn-primary" onclick="editcontact(this);" contactId="' . $parm['contactId'] . '"  href="javascript:void(0);" title="Edit">Update</a>
                            <a class="btn btn-medium btn-primary" onclick="confirmDelete(this);" contactId="' . $parm['contactId'] . '"  href="javascript:void(0);" title="Delete">Delete</a></div>
                            </div>
                </div>
                </form>    
            </div>';
        return $str;
    }

    
     /*
     * @auth sudhir pandey <sudhir@hostnsoft.com>
     * @desc function use to get user access number ( who used by user ) from mongo table 
     */
    function getUserAccessNumbers($userId , $contactNo=NULL)
    {
       
        $userAccessNo = array();
        extract($this->getAllContact($userId)); //$allcontact
        
        $hashArray = array();
        $allAccNo = array();
        
        foreach ($allcontact as $res) 
        {
            if(isset($res['accessNo']) && $res['hash'] == 100 )
            {
               $userAccessNo[] = $res['accessNo'];
            }
            $allAccNo[] = $res['accessNo'];
            if(isset( $res['hash']))
            {
                $hashArray[] =  $res['hash'];
            }
            
        }
       
        return array("accessNumber" => $userAccessNo, "hash" => $hashArray , "allAccessNo" => $allAccNo);
        
    }
    
    
     /*
     * @auth sudhir pandey <sudhir@hostnsoft.com>
     * @desc function use to get user access number ( who used by user ) from mongo table 
     */
    function getUserAccessNo($userId){

        $userAccessNo = array();
        extract($this->getAllContact($userId)); //$allcontact
        foreach ($allcontact as $res) 
        {
            if(isset($res['accessNo'])){
               $userAccessNo[] = $res['accessNo'];
            }
           
        }
       
        return $userAccessNo;
        
    }
    
    /*
     * @auth sudhir pandey <sudhir@hostnsoft.com>
     * @desc function use to get all access number from longcode table 
     */
    function allAccessNo( $status = "1", $type = "2" )
    {
        
      $allAccNo = array();
      $table = '91_longCodeNumber';
      
      #get all access number from 91_longCodeNumber table  
      #- last modified by nidhi nidhi@walkover.in
      #- I have added two more parameters status and type.
      #- status 0 - call , 1- sms .
      #- type 0- desable, 1-enable.
      $result = $this->selectData('*',$table,"resellerId = 2 and status =".$status." and type=".$type);
      
      if($result->num_rows > 0)
      {
           while ($row = $result->fetch_array(MYSQLI_ASSOC)){
                $allAccNo[] = $row['longCodeNo'];
           }
      }
        
      return $allAccNo;
        
    }
    
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 17/07/2013
    #function use to check email id and contact no is valid or not 

    function checkContactValidation($allemail, $allcontact) 
    {

        #msg variable use for return message (if return success then email and contact no is valid otherwise msg send )
        $msg = '';$errorKey = array();
        for ($i = 0; $i < count($allemail); $i++) 
        {
            #check email id valid
            if ($allemail[$i] != '' || $allemail[$i] != null) 
            {
                if (!preg_match("/^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/ix", $allemail[$i])) 
                {
                   $errorKey[] = $i; 
                }
            }

            #check contact is valid or not 
            if (!preg_match("/^[0-9]{8,15}$/", $allcontact[$i])) 
            {
                $errorKey[] = $i;
            }
            
        }

        return $errorKey;
    }

    #created by sudhir pandey (sudhir@hostnsot.com)
    #creation date 05/09/2013
    function checkContactNull($allcontact,$allemail)
    {
       #check first contact no is valid or not
        if(count($allcontact) == 1)
        {
            
            #check contact is valid or not 
            if ($allcontact[0] == ''|| $allcontact[0]== NULL) 
            {
                return $msg = "please enter contact number"; 
            }
             #check contact is valid or not 
            if (!preg_match("/^[0-9]{8,15}$/", $allcontact[0])) 
            {
                 return $msg = "please enter valid contact number";
            }
            
            if ($allemail[0] != '' || $allemail[0] != null) 
            {
                if (!preg_match("/^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/ix", $allemail[0])) 
                {
                    return $msg = "please enter valid email";
                }
            }
            
        }
//        for ($i = 0; $i < count($allcontact); $i++) {
//            #check contact is valid or not 
//            if ($allcontact[$i] == ''|| $allcontact[$i]== NULL) {
//                return $msg = "please enter contact number"; 
//             }
//        }
        return $msg = "success";
    }

    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 18/07/2013
    #function use to searching contact detail. 

    function allContactlist($userid) 
    {
        $str = '';

        extract($this->getAllContact($userid)); //$allcontact

        foreach ($allcontact as $res) 
        {
            $accessNo = '';
            if(isset($res['accessNo'])){
                $accessNo = "Access Number : ".$res['accessNo'];
            }                
            $str.='<li class="clear" contactId="'.$res['contact_id'].'">
			  <div class="cntAct  fixed">
				<div class="edtsiWrap">
					 <a class="clear alC" onclick="showContactEdit(this);" contactid="'.$res['contact_id'].'" href="javascript:void(0);">
						  <span class="ic-32 edit"></span> 
					 </a> 
				 </div>				
			  </div>
			  <div class="cntInfo slideAndBack" onclick="dest(\''.$res['contactNo'].'\',this)">
					<div class="innerCol">
						  <h3 class="h3 ellp fwN">'.$res['name'].'</h3>
						  <div class="fpinfo"> <i class="ic-16 call"></i>
							 <label>'.$res['contactNo'].'</label>
						  </div>
                                                  <div class="fpinfo"> <i class="call"></i>
							 <label>'.$accessNo.'</label>
						  </div>
					</div>
				</div>		      
			</li>';            
        }
        return $str;
    }
    
    /**
     * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
     * @since 14/03/2014
     * @filesource
     * @uses to get access numbers
     */
    function getAccessNumberDetails($request)
    {
        if(isset($request['voiceJsonp']) && $request['voiceJsonp'] != '')
                $callBack = 1;
           else
                $callBack = 0;
         $table = '91_longCodeNumber';
        
         //if type 1 means smsAccess number or 0 for callAccess numbers
         //$type = (isset($request['type']) && $request['type'] == 1)?1:0;
         
        $result = $this->selectData('*',$table,'resellerId=2');
        
        
        //validate result
        if(!$result)
        {
            $json = json_encode(array('status' => 0,'msg' => 'Problem while getting Access Number details!!!'));
            if(!$callBack)
               return $json;
            else
                return $request['voiceJsonp'].'('.$json.')';
        }
          
        if($result->num_rows == 0)
        {
            $json = json_encode(array('status' => 0,'msg' => 'Record Not Found!!!'));
             if(!$callBack)
               return $json;
            else
                return $request['voiceJsonp'].'('.$json.')';
            
            return json_encode(array('status' => 0,'msg' => 'Record Not found!!!'));
        }
        
         $callData = array();
         $smsdata = array();
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            if($row['type'] == 0)
            {
                $dtl['accessNumber'] =   $row['longCodeNo'];
                $dtl['country'] =  $row['country'];
                $dtl['state'] =  $row['state'];
                $callData[]= $dtl;
            }
            else if($row['type'] == 1)
            {
                $dtl['accessNumber'] =   $row['longCodeNo'];
                $dtl['country'] =  $row['country'];
                $dtl['state'] =  $row['state'];
                $smsData[]= $dtl;
           
            }
            unset($dtl);
            unset($row);
        }
        
        $json =  json_encode(array('status' => 1,'msg' => 'Record Found!!!','callAccess' => $callData,'smsAccess' =>$smsData));
        
        if(!$callBack)
            return $json;
        else
            return $request['voiceJsonp'].'('.$json.')';
        
    }

}

$pbookobj = new phonebook_class();
?>