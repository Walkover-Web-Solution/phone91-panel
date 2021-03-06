<?php

/**
 * @author  Rahul <rahul@hostnsoft.com>
 * @modified by sudhir <sudhir@hostnsoft.com>
 * @since 08 sep 2013
 * @package Phone91
 * @details class use for signup
 */

include dirname(dirname(__FILE__)).'/config.php';
class signup_class extends fun //validation_class.php
{
    public $newUserId;
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 08/08/2013
    #function use for get last chainId    
    function getlastChainId($reseller_id){
     #insert login detail into login table database 
     $loginTable = '91_userBalance';

     #get chain id for user 
     $this->db->select('*')->from($loginTable)->where("resellerId = '" .$reseller_id. "' ORDER BY userId DESC limit 1 ");
     $this->db->getQuery();
     $result = $this->db->execute();
     $row = $result->fetch_array(MYSQL_ASSOC);
     $chainId = $row['chainId'];
     return $chainId;
    }   
     
     
     #created by rahul sir
     #creation date 08-08-2013
     function generateId($a){
    
     $firstTwo=substr($a,0,2);
     $firstThree=substr($a,0,3);
    //echo " ";
      $first=substr($a,0,1);
    //echo " ";
      $second=substr($a,1,1);
    //echo " ";
      $third=substr($a,2,1);
    //echo " ";
      $last=substr($a,3,1);
    //echo " ";

    
   if($last=="9")
   {
        $last="a";
        $wxyz=$first.$second.$third.$last;
        return $wxyz;
   }
   
   if($last=="z")
   {
        if($third=="9")
        {
            $third="a";
            $last="1";
            $wxyz=$first.$second.$third.$last;
            return $wxyz;
        }
        if($third=="z")
        {
            if($second=="9")
            {
                $second="a";
                $last="1";
                $third="1";
                $wxyz=$first.$second.$third.$last;
                return $wxyz;
            }
            if($second=="z")
            {
               if($first=="9")
                {
                    $first="a";
                    $last="1";
                    $third="1";
                    $second="1";
                    $wxyz=$first.$second.$third.$last;
                    return $wxyz;
                } 
                
                ++$first;
                $second="1";
                $third="1";
                $last="1";
                $wxyz=$first.$second.$third.$last;
                return $wxyz;
            }
            
            ++$second;
            $third="1";
            $last="1";
            $wxyz=$first.$second.$third.$last;
            return $wxyz;
        }
        ++$third;
        $last="1";
        $wxyz=$first.$second.$third.$last;
        return $wxyz;
   }
   
   ++$last;
     $wxyz=$first.$second.$third.$last;
    return $wxyz;
}
     
    
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 09/08/2013
    #function use for create new chain id by use of generateId function 
    function newChainId($lastChainId){
         
         #last chain id first part 
         $firstpart = substr($lastChainId,0,-4);
         #last chain id second part (currentuser chain id).
         $secondpart = substr($lastChainId,-4);
         
         #increment last chain id by generateId function 
         $incId = $this->generateId($secondpart);
         if($incId =='' || $incId == $secondpart){
           $this->sendErrorMail("rahul@hostnsoft.com", "Chain Id creation problem (either chain id is blank or same as last chain id).");
         }
         
         
         #new chain id
         $newChainId = $firstpart.$incId;
         return $newChainId;
         
    }
    
    function createUser($parm,$tariff_id,$balance,$currency_id)
    {
      /* @AUTHOR : SUDHIR PANDEY 
      /* @MODIFIED BY : SAMEER RATHOD 
       * @DESC : FUNCTION INSERT THE USER INTO THE DATABSE IT DOES ENTRY IN 91_USERLOGIN
       *         91_USERBALANCE,91_PERSONALINFO
       * @PARAMETER : 
       *            $PARAM: ARRAY()
       *                    $PARM['USERNAME'] : DESIRED NAME OF THE USER 
       *                    $PARM['UNAME'] : IN CASE IF THIS IS SET THEN THIS WILL BE THE USERNAME 
       *                                     AND IS DIFFERENT FROM THE NAME OF THE USER 
       *                    $PARM['PASSWORD'] : DESIRED PASSWORD GIVEN BY THE USER
       *                    $PARM['CLIENT_TYPE'] : CLIENT TYPE STATES THAT WHETHER IT'S A RESELLER OR A USER OR A CALLSHOP
       *                    $PARM['CLIENT_LIMIT'] : DEFAULT CALL LIMIT OF THE USER
       *                    $PARM['RESELLER_ID'] : RESELLER UNDER WHICH TEH USER IS BEING CREATED 
       *  
       * @RETURN : RETURN ERROR MSG AND STATUS IN CASE OF ERROR 
       *           RETURN 1 INCASE OF SUCCESS   
       */
      
       #FETCH THE DETAILS OF THE RESELLER IF THE RESELLER IS BLOCKED OR DELETED
       #THEN THE NEW SIGNUPED USER WILL HAVE TEH SAME SETTINGS 
       
                
        $table = '91_userLogin';
        $condition = "userId = '".$parm['reseller_id']."'";
        $this->db->select('isBlocked,deleteFlag')->from($table)->where($condition);        
        $loginresult = $this->db->execute();
        if ($loginresult->num_rows > 0) {
            $logindata = $loginresult->fetch_array(MYSQL_ASSOC);
            $blockUnblockStatus = $logindata['isBlocked'];
            $deleteFlag = $logindata['deleteFlag'];
        }
        else
        {
            return json_encode(array("status"=>"error","msg"=>"Error Unable to fetch the reseller details Please Try again"));
        }
        
        
      #insert userdetail into database       
      $data=array("name"=>$parm['username']); 
      $personalTable = '91_personalInfo';
      #insert query (insert data into 91_personalInfo table )
      $personalResult = $this->insertData($data, $personalTable);

      #check data inserted or not 
      if(!$personalResult){
//        $this->sendErrorMail("sameer@hostnsoft.com", "Phone91 signup_class personal info table query fail : $qur ");
        return json_encode(array("status"=>"error","msg"=>"signup process fail !"));
          
      }
      
      #user id
      if(isset($parm['uName']) && $parm['uName'] != "")
        $userName = $parm['uName'];
      else
        $userName = $parm['username'];
      
      $userid = $this->db->insert_id;
      $this->newUserId = $this->db->insert_id;
      #insert login detail into login table database 
      $loginTable = '91_userLogin';
      $data=array("userId"=>$userid,"userName"=>$userName,"password"=>$parm['password'],"isBlocked"=>$blockUnblockStatus,"deleteFlag"=>$deleteFlag,"type"=>(int)$parm['client_type']); 

      #insert query (insert data into 91_userLogin table )
      $loginResult = $this->insertData($data, $loginTable);

      #check data inserted or not 
      if(!$loginResult){
          $this->deleteData($personalTable, "userId = ".$userid);
//         $this->sendErrorMail("rahul@hostnsoft.com", "Phone91 signup_class userlogin  table query fail : $qur ");
         return json_encode(array("status"=>"error","msg"=>"signup process fail !"));
          
      }
      
      #get last chain id from user balance table  
      $lastchainId = $this->getlastChainId($parm['reseller_id']);
      
      #new chain id (incremented id of lastchain id )
      $chainId = $this->newChainId($lastchainId);
      
      #insert login detail into login table database 
      $balanceTable = '91_userBalance';
     
      $data=array("userId"=>(int)$userid,"chainId"=>$chainId,"tariffId"=>(int)$tariff_id,"balance"=>$balance,"currencyId"=>(int)$currency_id,"callLimit"=>(int)$parm['call_limit'],"resellerId"=>(int)$parm['reseller_id']); 

      #insert query (insert data into 91_userLogin table )
      $balanceResult = $this->insertData($data, $balanceTable);
      if (!$balanceResult){
          $this->deleteData($personalTable, "userId = ".$userid);
          $this->deleteData($loginTable, "userId = ".$userid);
//         $this->sendErrorMail("rahul@hostnsoft.com", "Phone91 signup_class 91_userBalance query fail : $tempsql ");
          return json_encode(array("status"=>"error","msg"=>"signup process fail!"));  
      }
      return 1;
    }
     
    function getResellerDefaultCurrency($resellerId , $currencyId)
    {
        /* @author sameer rathod 
         * @desc get the defalut currency of the reseller
         */
        $result = $this->selectData("tariffId,balance", "91_resellerDefaultCurrency","resellerId = ".$resellerId." and currencyId =".$currencyId);
        return $result;
    }
    
    #created by sudhir pandey (sudhir@hostnsoft.com)
    #creation date 26-07-2013
    #function use to user sign up 
    function sign_up($parm){
       
       
     #check for all value inserted or not in signup form    
      if (strlen($parm['username']) < 1 || strlen($parm['password']) < 1 || strlen($parm['code']) < 1 || strlen($parm['mobileNumber']) < 1 || strlen($parm['email']) < 1) 
      {
          return json_encode(array("status"=>"error","msg"=>"Incomplete From!"));  
      }
      
      #currency id 
      $currency_id = $parm['currency'];
      #reseller id 
      $reseller_id = 2;
      $parm['reseller_id'] = 2;
      #call limit 
      $call_limit = 2;
      $parm['call_limit'] = 2;
      
      
      
//      if ($currency_id == 1) 
//      {
//            // AED
//            #tariff id from plan table 
//            $tariff_id = 9;
//            $balance = '2.0000';
//       } 
//       else if ($currency_id == 63) 
//       {
//            //  INR
//            $tariff_id = 7;
//            $balance = '10.0000';
//       } 
//       else if ($currency_id == 147) 
//       {
//            //  USD
//            $tariff_id = 84;
//            $balance = '0.2000';
//       }
       
      //remove zero from starting of number if exist
      if (substr($parm['mobileNumber'], 0, 1) == 0)
        $phone = substr($parm['mobileNumber'], 1, strlen($parm['mobileNumber']) - 1);
      
      //get contact with country code
      $contact = $parm['code'] . $phone;
      
      //check if username already exists
      $check = $this->check_user_avail($parm['username']);
      if($check == 0){
           return json_encode(array("status"=>"error","msg"=>"This User name already exists!"));
      }
      
      if($parm['password'] != $parm['repassword']){
          return json_encode(array("status"=>"error","msg"=>"Sorry password not matched!"));
      }
      
      //to check if phoneno existes or not
      $table = '91_verifiedNumbers';
      $this->db->select('*')->from($table)->where("verifiedNumber = '" . $parm['mobileNumber'] . "' and countryCode = '".$parm['code']."'");
      $result = $this->db->execute();
      if ($result->num_rows > 0){
          return json_encode(array("status"=>"error","msg"=>"Phone number already in use by another user!"));
      }
      
      //to check  email address already exists or not 
      $table = '91_verifiedEmails';
      $this->db->select('*')->from($table)->where("email = '" . $parm['email'] . "'");
      $this->db->getQuery();
      $result = $this->db->execute();
      if ($result->num_rows > 0){
          return json_encode(array("status"=>"error","msg"=>"This email address already registered!"));
      }
      
     /* RESELLER CODE END HERE */
  /***********GET THE DEFAULT TARIFF DETAILS OF THE RESELLER************************/
      $resultDefaultCurrency = $this->getResellerDefaultCurrency($reseller_id , $currency_id);
      if($resultDefaultCurrency)
      {
        $row = $resultDefaultCurrency->fetch_array(MYSQLI_ASSOC);
        $tariff_id = $row['tariffId'];
        $balance = $row['balance'];
      }
      else
          return json_encode(array("status"=>"error","msg"=>"Invalid currency type please try again"));
  /********************END HERE*****************************************************/   
      
      
      $createUserResult = $this->createUser($parm,$tariff_id,$balance,$currency_id);
      $userid = $this->newUserId;
      if($createUserResult != 1)
          return $createUserResult;
      
      #contact no and email id store in temp contact and temp email teable for contactno and email varifiction 
      # Store Contact No :
      $confirm_code = $this->generatePassword();
      
      #value for store in database 
      $tempNumTable = "91_tempNumbers";
      $data=array("userId"=>(int)$userid,"countryCode"=>(int)$parm['code'],"tempNumber"=>$parm['mobileNumber'],"confirmCode"=>$confirm_code,"date"=>date('Y-m-d H:i:s')); 

      
      #insert query (insert data into 91_tempcontact table )
      $tempNumResult = $this->insertData($data, $tempNumTable);
      
      # Assign Variables for sending sms to user
        $d["text"] = "you are successfully registered your username is: " . $parm['username'] . " and confirmation-code is: " . $confirm_code . " Please recharge to start using this account."; // sms text
        $d["to"] = $parm['code'] . $parm['mobileNumber'];
        //for 91 user
        $nine['mobiles'] = $parm['mobileNumber'];
        $nine['message'] = "you are successfully registered your username is " . $parm['username'] . " and confirmation-code is " . $confirm_code . " Please recharge to start using this account."; // sms text
           
        
        $funobj = new fun();
        if ($parm['code'] == "91"){
            $sendSmsResponse=$funobj->SendSMS91($nine);
                if($sendSmsResponse == 'code: 101'){
                     $error = 1;
                }
        }else{
            $sendSmsResponse=$funobj->SendSMSUSD($d) ;
                if($sendSmsResponse == 'error: 101'){
                    $error = 1;
                }
        }
        
        
        # Store Email in tampEmpail for email id varification
        #value for store in database 
        $emailconfirm_code = $this->generatePassword();
        $tempEmail = "91_tempEmails";       
        $data=array("userid"=>(int)$userid,"email"=>$parm['email'],"confirm_code"=>$emailconfirm_code,"date"=>date('Y-m-d H:i:s')); 
 
        #insert query (insert data into 91_tempEmails table )
        $tempEmailResult = $this->insertData($data, $tempEmail);
        
        #add taransaction detail into taransation log table 
        $msg = $this->signupTransaction($reseller_id,$userid,$balance);
        
        if(isset($parm['domain'])){
         $funobj->login_user($parm['username'],$parm['password'],0,$parm['domain'],1);
        }else        
         $funobj->login_user($parm['username'],$parm['password'],0,NULL,1);
         
        if ($tempEmailResult) 
        {	 

            #send confirm code by email id for email varification 
            $sentmail = $funobj->send_verification_mail($parm['email'],$emailconfirm_code );
            if($sentmail)
            {
                return  json_encode(array('status' => 'success', 'msg' => 'Your Confirmation link Has Been Sent To Your Email Address.'));
            }else
                return json_encode(array('status' => 'success', 'msg' => 'Signup successfully complited but your confirmation link has not send.'));
        }
        
        #check sms send or not 
        if($error){
          return json_encode(array('status' => 'success', 'msg' => 'Signup successfully complited but confirmation code not send!'));
        }
        else {
            return json_encode(array('status' => 'success', 'msg' => 'Signup successfully complited!'));
        }
        
       
    
 
    }
    
    
    function signupTransaction($reseller_id,$userid,$balance){
        
       
        
        #add taransaction detail into taransation log table 
        include_once("transaction_class.php");
        $transaction_obj = new transaction_class();
        
        /* CALL ADD TRANSACTIONAL FUNCTION FOR ADD TRANSACTION  : 
         * 
         * $resellerid : FromUser
         * $userid : toUser
         * $balance : amount for credit or debit
         * $balance : talktime amount 
         * $paymentType : cash,memo,bank
         * $description : description of transaction 
         * type : prepaid ,postpaid , partial
         * 
         */
        
        
         $msg = $transaction_obj->addTransactional_sub($reseller_id,$userid,$balance,$balance,"signUp",0,0,0,"Sign Up Transaction");
        
        
//        $msg = $transaction_obj->addTransactional($reseller_id, $userid, $balance,$balance, "cash", "sign Up", "prepaid"); //$fromUser,$toUser,$amount,$paymentType,$description,$type
       
//        #get current balance form 91_userBalance table
//        $currBalance = $transaction_obj->getcurrentbalance($userid);
//        $currentBalance = ((int)$currBalance + (int)$balance);
//        
//        #update current balance of user in userbalance table 
//        $transaction_obj->updateUserBalance($userid,$currentBalance);
        
        
    }
    
    
    function check_user_avail($username = NULL) 
    {
        if(isset($_REQUEST['username']))
            $username = $_REQUEST['username'];
        
        $table = '91_userLogin';
        $this->db->select('userName')->from($table)->where("userName = '" . $username . "' ");
//        echo $this->db->getQuery();
        $result = $this->db->execute();
//        	    var_dump($result);
        // processing the query result
        if ($result->num_rows > 0) {
            return 0; //echo "Sorry username already in use";
            exit();
        }
        else
        {
            return 1;
            exit();
        }
    }
    
    function check_email_avail($email) 
    {
        
        $email = $this->db->real_escape_string($email);
        $table = '91_verifiedEmails';
        $this->db->select('email')->from($table)->where("email = '" . $email . "' ");
        $result = $this->db->execute();
        // processing the query result
        if ($result->num_rows > 0) {
            return 0; //echo "Sorry username already in use";
        }
        else
            return 1;
    }
    
    function generatePassword() {
		$length = 4;
		$password = "";
		$possible = "0123456789";
		$i = 0;
		while ($i < $length) {
			$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
			if (!strstr($password, $char)) {
				$password .= $char;
				$i++;
			}
		}
		return $password;
	}
        
        
     
    
    function createUsername($batchId,$length=8){		
		/**
		 * @author  Rahul <rahul@hostnsoft.com>
		 * @package signup Class 
                 * @since 07 aug 13  V 1.0
                 * @depends md5 max length 32
                 * @used in create User Batch
                 * @details give random username which is not exist in Database.
		 * @
		 */
		$new = false;
		while($new == false)
		{
			$userName = $batchId.substr(md5(microtime()*3),0,$length);		
			$table = '91_userLogin';
			$this->db->select('userName')->from($table)->where("userName = '" . $userName . "' ");
			$result = $this->db->execute();
			//	    var_dump($result);
			// processing the query result
			if ($result->num_rows > 0) {
			    $new=false;		    
			}
			else
			    $new=true;
		}
		return $userName;	
	}

    #created by sudhir pandey <sudhir@hostnsoft.com>
    #creation date 03/09/2013
    #function use to create bulk client     
    function addNewClientBatch($request,$resellerId){

        #check username is blank or not
        if ($request['batchName'] == '' || $request['batchName'] == NULL) {
            return json_encode(array("status" => "error", "msg" => "Please insert Batch name ."));
        }
        
        #check valid batch name 
        if(!preg_match('/^[a-zA-Z_@-\s]+$/',$request['batchName']))
        {
          return json_encode(array('msgtype'=>'error','msg'=>'Please Enter Valid batch name'));
        }

        #check total no of client is valid or not   
        if (!is_numeric($request['totalClients']) && strlen($request['totalClients'])>4 ) {
            return json_encode(array("status" => "error", "msg" => "Total Number of client are not valid!"));
        }

        #check total no of client is valid or not 
        if (!preg_match("/^[0-9]{1,4}$/", $request['totalClients'])) {
            return json_encode(array("status" => "error", "msg" => "Total Number of client are not valid!"));
        }

        #check tariff paln is selected or not 
        if ($request['tariff'] == "select") {
            return json_encode(array("status" => "error", "msg" => "Please Select Tariff Plan ! "));
        }

        #chech payment type is selected or not 
        if ($request['payTypeBulk'] == "select") {
            return json_encode(array("status" => "error", "msg" => "Please Select Payment Type ! "));
        }
        #chech payment type is selected or not 
        if (strtotime($request['batchExpiry']) < strtotime(date("Y-m-d H:i:s"))) {
            return json_encode(array("status" => "error", "msg" => "Expiry Date Must be correct "));
        }

        #check total no of pins is numeric or not 
        if (!preg_match("/^[0-9]{1,4}$/", $request['balance'])) {
            return json_encode(array("status" => "error", "msg" => "Numeric value required in balance field ! "));
        }
        
        
        if($request['totalClients']<0 || $request['totalClients']>999)
            return json_encode(array("status" => "error", "msg" => "Invalid number of total Client ! "));

        
        $bulkUserTable = '91_bulkUser';
        
        $this->db->select('*')->from($bulkUserTable)->where("batchName = '" . $request['batchName'] . "'");
        $this->db->getQuery();
        $result = $this->db->execute();
        if ($result->num_rows > 0){
            return json_encode(array("status"=>"error","msg"=>"Batch name already registered!"));
        }
        
        
        $data = array("batchName" => $request['batchName'],"numberOfClients"=>(int)$request["totalClients"],"expiryDate"=>$request["batchExpiry"],"userId"=>$resellerId);

        #insert query (insert data into 91_bulkUser table )
        $this->db->insert($bulkUserTable, $data);
        $qur = $this->db->getQuery();
        $result = $this->db->execute();
        //var_dump($result);
        #check data inserted or not 
        if (!$result) {
            $this->sendErrorMail("rahul@hostnsoft.com", "$bulkUserTable insert query fail : $qur ");
            return json_encode(array("status" => "", "msg" => "add Batch process fail! please try again"));
        }
        $batchId = $this->db->insert_id;

                
        for($request['totalClients'];$request['totalClients']>0;$request['totalClients']--){
            $userName= $this->createUsername($batchId);
            $password= $this->createUsername($batchId);  

                #insert userdetail into database 
                $personalTable = '91_personalInfo';
                $data = array("name" => $userName);

                #insert query (insert data into 91_personalInfo table )
                $this->db->insert($personalTable, $data);
                $qur = $this->db->getQuery();
                $result = $this->db->execute();
                //var_dump($result);
                #check data inserted or not 
                if (!$result) {
                    $this->sendErrorMail("rahul@hostnsoft.com", "insert query fail : $qur ");
                    return json_encode(array("status" => "", "msg" => "add user process fail!"));
                }

                #user id 
                $userid = $this->db->insert_id;

                #get reseller block unblock status from login table 
                $table = '91_userLogin';
                $condition = "userId = '".$resellerId."'";
                $this->db->select('*')->from($table)->where($condition);
                $this->db->getQuery();
                $loginresult = $this->db->execute();
                if ($loginresult->num_rows > 0) {
                $logindata = $loginresult->fetch_array(MYSQL_ASSOC);
                $blockUnblockStatus = $logindata['isBlocked'];
                $deleteFlag = $logindata['deleteFlag'];
                 }
        

                #insert login detail into login table database 
                $loginTable = '91_userLogin';
                $data = array("userId" => (int) $userid, "userName" => $userName, "password" => $password, "isBlocked" => $blockUnblockStatus,"deleteFlag"=>$deleteFlag, "type" => 4);

                #insert query (insert data into 91_userLogin table )
                $this->db->insert($loginTable, $data);
                $qur = $this->db->getQuery();
                $result = $this->db->execute();
                //var_dump($result);
                #check data inserted or not 
                if (!$result) {
                    $this->sendErrorMail("rahul@hostnsoft.com", "insert query fail : $qur ");
                    return json_encode(array("status" => "error", "msg" => "add user process fail !"));
                }



                 #user balance from plan table  
                $balance = $request['balance'];
                #currency id 
                $currency_id = 2;
                #call limit 
                $call_limit = 2;
                
                #payment type (cash,memo,bank).
                if($parm['payType'] == "Other"){
                $paymentType = $this->db->real_escape_string($request['otherType']);     
                }else
                $paymentType = $request['payTypeBulk'];
                
                #description
                $description = '';

                #get last chain id from user balance table  
                $lastchainId = $this->getlastChainId($resellerId);

                #new chain id (incremented id of lastchain id )
                $chainId = $this->newChainId($lastchainId);
                
                #insert login detail into 91_userBalance table database 
                $loginTable = '91_userBalance';
                $data = array("userId" => (int) $userid,"chainId"=>$chainId , "tariffId" => (int) $request['tariff'], "balance" => $balance, "currencyId" => (int) $currency_id, "callLimit" => (int) $call_limit, "resellerId" => (int) $resellerId, "userBatchId"=>(int)$batchId);
                #insert query (insert data into 91_userLogin table )
                $this->db->insert($loginTable, $data);
                $tempsql = $this->db->getQuery();
                $result = $this->db->execute();
                //var_dump($result);
                if (!$result) {
                    $this->sendErrorMail("sudhir@hostnsoft.com", "insert query fail : $tempsql ");
                    return json_encode(array("status" => "error", "msg" => "add user process fail! $tempsql"));
                }
                    

               
        }
        if($result){
            return json_encode(array("status" => "success", "msg" => "successfully add bulk user"));
        }
        
    }
    
    function sendErrorMail($email,$mailData){
        require('awsSesMailClass.php');
        $sesObj = new awsSesMail();
        $from="error@phone91.com";
        $subject="Phone91 Error Report";
        $to=$email;
        $message=$mailData;
        $response= $sesObj->mailAwsSes($to, $subject, $message, $from);
    }
    
}//end of class
$signup_obj	=	new signup_class();//class object
?>