<?php

/*
 * @author sudhir pandey <sudhir@hostnsoft.com>
 * @package Phone91
 * @description create pin user , if recharge skype and gtalk by pin then new user create and use pin balance  
 */
include_once '../function_layer.php';

$funobj = new fun();

if(isset($_REQUEST['type']) && isset($_REQUEST['emailId']) && isset($_REQUEST['pin'])){
    
#account type : 1 for gtalk and 2 for skype 
$accountType = $_REQUEST['type'];

#emailid : for skype or gtalk
$emailId  = $_REQUEST['emailId'];

#pin
$pin = $_REQUEST['pin'];


echo $msg = createPinUser($accountType,$emailId,$pin);
}else
echo "Please provide valid accountType ,emailid and pin";    



function createPinUser($accountType,$emailId,$pin){
    
    $funobj = new fun();
    
    #check emailid is velide or not 
    if(preg_match("/[^a-zA-Z0-9\.\_\@\-\$]+/", $emailId)){
         return "emailId is not valid";
    }
    
    if($accountType == 1)
      {
          $tableName = '91_verifiedGtalkId';
      }
      elseif($accountType == 2)
      {
          $tableName = '91_verifiedSkypeId';
         
      }else
          return "account type is not valid";
      
    #check user Name already exist 
    $result = $funobj->selectData('*', $tableName, "email ='".$emailId."'");

    #check the resulting value exists or not 
    if($result->num_rows > 0)
    {
        return "Email id already exist";
    }
        
    #get pin detail 
    $pinDetail = getPinDetail($pin);
    $pinDataDetail = json_decode($pinDetail,TRUE);  //$pinGenerator,$pinTariff,$pinCurrency,$pinBalance   
        
    if($pinDataDetail['status'] == "error"){
        return $pinDataDetail['msg'];
    }
    
//    echo "id".$pinDataDetail['pinGenerator']." ".$pinDataDetail['pinTariff']." ".$pinDataDetail['pinCurrency']." ".$pinDataDetail['pinBalance'];
        
    $userData = createUser($pinDataDetail['pinGenerator'],$pinDataDetail['pinTariff'],$pinDataDetail['pinCurrency'],$pinDataDetail['pinBalance']); 
    $userDetail = json_decode($userData,TRUE);  
    
    if($userDetail['status'] == "error"){
        return $userDetail['msg'];
    }
    
   $userId = $userDetail['userId'];
    
   $data = array("email"=>$emailId,"userId"=>$userId);
   $resInsert = $funobj->insertData($data, $tableName);

    
   
    #update pin status 
    $table = '91_pinDetails';
    $data=array("usedDate"=>date('Y-m-d H:i:s'),"status"=>1,"usedBy"=>$userId); 
    $condition = "pincode='".$pin."'";
    $funobj->db->update($table, $data)->where($condition);	
    $funobj->db->getQuery();
    $result = $funobj->db->execute(); 
 
    $msg = "successfully id recharge by pin.. ";
    return $msg;
}

function getPinDetail($pin){
    
    $funobj = new fun();
    
    # check pin valid or not 
    if(!isset($pin) || strlen($pin)<5)
        {
         return json_encode(array('status'=>'error','msg'=>'Invalide pin!')); 
        }	

    # get pin status (1 for used or 0 for unused).
    $table = '91_pinDetails';

    #selecting the item from table 91_pinDetails
    $funobj->db->select('*')->from($table)->where("pincode ='".$pin."'");
    $funobj->db->getQuery();

    #execute query
    $result=$funobj->db->execute();

    #check the resulting value exists or not 
    if($result->num_rows == 0)
      {
         return json_encode(array('status'=>'error','msg'=>'Invalide pin!')); 
      }

    $row = $result->fetch_array(MYSQL_ASSOC);
    if($row['status'] == 1){
        return json_encode(array('status'=>'error','msg'=>'pin already used by another user!')); 
    }
    
     
    $pinTable = '91_pin';
    $condition = "batchId = '" . $row['batchId'] ."'"; //userId= '".$userid."' or 
    $funobj->db->select('*')->from($pinTable)->where($condition);
    $batchResult = $funobj->db->execute();

    // processing the query result
    if ($batchResult->num_rows == 0) {	
        return json_encode(array('status'=>'error','msg'=>'You Have No Permission To Use This Pin!')); 

    }

    $batchDetail = $batchResult->fetch_array(MYSQL_ASSOC);

    if(strtotime(date('Y-m-d',strtotime($batchDetail['expiryDate']))) < strtotime(date('Y-m-d'))){
         return json_encode(array('status'=>'error','msg'=>'Pin are expired !')); 
    }

    #pin tariff id 
    $pinTariff = $batchDetail['tariffId'];
       
    #find pin currency (call function_layer.php function) 
    $pinCurrency = $funobj->getOutputCurrency($batchDetail['tariffId']);
    
    #pin Generator id 
    $pinGenerator = $batchDetail['userId'];
    
    #pin balance
    $pinBalance = $batchDetail['amountPerPin'];
    
    return json_encode(array('status'=>'success','pinGenerator'=>$pinGenerator,'pinTariff'=>$pinTariff,'pinCurrency'=>$pinCurrency,'pinBalance'=>$pinBalance)); 
    
}

function createUser($resellerId,$tariff_id,$currency_id,$balance)
    {
        $funobj = new fun();
        
        $table = '91_userLogin';
        $condition = "userId = '".$resellerId."'";
        $funobj->db->select('isBlocked,deleteFlag')->from($table)->where($condition);        
        $loginresult = $funobj->db->execute();
        if ($loginresult->num_rows > 0) {
            $logindata = $loginresult->fetch_array(MYSQL_ASSOC);
            $blockUnblockStatus = $logindata['isBlocked'];
            $deleteFlag = $logindata['deleteFlag'];
        }
        else
        {
            return json_encode(array("status"=>"error","msg"=>"Error Unable to fetch the reseller details Please Try again"));
        }
        
      
     $userName = $funobj->createUsername($resellerId);
     $password = $funobj->createUsername($resellerId);     
        
      #insert userdetail into database       
      $data=array("name"=>$userName); 
      $personalTable = '91_personalInfo';
      #insert query (insert data into 91_personalInfo table )
      $personalResult = $funobj->insertData($data, $personalTable);

      #check data inserted or not 
      if(!$personalResult){
//        $this->sendErrorMail("sameer@hostnsoft.com", "Phone91 signup_class personal info table query fail : $qur ");
        return json_encode(array("status"=>"error","msg"=>"pin User not created !"));
          
      }
      
           
      $userid = $funobj->db->insert_id;
      
      #insert login detail into login table database 
      $loginTable = '91_userLogin';
      $data=array("userId"=>$userid,"userName"=>$userName,"password"=>$password,"isBlocked"=>$blockUnblockStatus,"deleteFlag"=>$deleteFlag,"type"=>5); 

      #insert query (insert data into 91_userLogin table )
      $loginResult = $funobj->insertData($data, $loginTable);

      #check data inserted or not 
      if(!$loginResult){
          $funobj->deleteData($personalTable, "userId = ".$userid);
//         $this->sendErrorMail("rahul@hostnsoft.com", "Phone91 signup_class userlogin  table query fail : $qur ");
         return json_encode(array("status"=>"error","msg"=>"pin User not created!"));
          
      }
      
      #get last chain id from user balance table  
      $lastchainId = $funobj->getlastChainId($resellerId);
      
      #new chain id (incremented id of lastchain id )
      $chainId = $funobj->newChainId($lastchainId);
      
      #insert login detail into login table database 
      $balanceTable = '91_userBalance';
     
      $data=array("userId"=>(int)$userid,"chainId"=>$chainId,"tariffId"=>(int)$tariff_id,"balance"=>$balance,"currencyId"=>(int)$currency_id,"callLimit"=>2,"resellerId"=>(int)$resellerId); 

      #insert query (insert data into 91_userLogin table )
      $balanceResult = $funobj->insertData($data, $balanceTable);
      if (!$balanceResult){
          $funobj->deleteData($personalTable, "userId = ".$userid);
          $funobj->deleteData($loginTable, "userId = ".$userid);
          return json_encode(array("status"=>"error","msg"=>"pin User not created!"));  
      }
      return json_encode(array("status"=>"success","userId"=>$userid));  
    }

   
     
?>