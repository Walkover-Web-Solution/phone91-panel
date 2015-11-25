<?php

/* @author: sameer 
 * @created: 16-08-2013
 * @desc : the class consist of all the functions required for call log and recent calls 
 */
include dirname(dirname(__FILE__)) . '/config.php';
class log_class extends fun{
    
    var $msg;
    var $status;
    
    function getRecentCalls($userid)
    {
        $columns = " c.uniqueId,c.caller_id,c.called_number,balance.deductBalance ";
        $table = " 91_chainBalanceReport balance , 91_calls c ";
        $condition = " c.call_Type='C2C' and c.id_client = ".$userid." and c.uniqueId = balance.uniqueId order by c.call_start desc limit 20 ";
        $this->db->select($columns)->from($table)->where($condition);
       
        $result = $this->db->execute();
        echo $this->db->error;
        if($result)
            return $result;
        else 
            return 0;
        
    }
    
    function getCallLogs($userId)
    {
        $columns = " `called_number`,call_type,id_client,call_start ";
        $table = " `91_calls` ";
        $userId = $this->db->real_escape_string($userId);
        $condition = " id_client=".$userId." and status='ANSWERED' order by call_start desc ";
        $sql = "SELECT * FROM (SELECT ".$columns." FROM ".$table." WHERE ".$condition.") as nestedTable group by called_number "; //limit 20
        $result = $this->db->query($sql);
//       echo $this->db->getQuery();
//        $result = $this->db->execute();
        if($result)
            return $result;
        else 
            return 0;
    }
    function getCallLogsDetails($number,$userId)
    {
        if(is_null($number))
        {
            $response['msg'] = "Invalid Input please try again";
            $response['type'] = "error";
            die(json_encode($response));
        }
        $columns = " c.`called_number`,c.call_type,c.id_client,c.status,c.call_start,c.duration,balance.deductBalance,balance.currencyId ";
        $table = " 91_chainBalanceReport balance , 91_calls c ";
        $condition = " c.id_client=".$userId." and c.called_number=".$number." and balance.uniqueId=c.uniqueId  and balance.userId = c. id_client order by call_start desc ";//limit 20
        $this->db->select($columns)->from($table)->where($condition);
//        echo $this->db->getQuery();
        $result = $this->db->execute();
        if($result)
            return $result;
        else 
            return 0;
    }
    function searchCallLogs($keyWord,$userId)
    {
        if(is_null($keyWord))
        {
            $response['msg'] = "Invalid Input please try again";
            $response['type'] = "error";
            die(json_encode($response));
        }
        $columns = " `called_number`,call_type,id_client,call_start ";
        $table = " `91_calls` ";
        $condition = " id_client=".$userId." and called_number LIKE '".$keyWord."%' group by called_number  order by call_start desc limit 20 ";
        $this->db->select($columns)->from($table)->where($condition);
        $result = $this->db->execute();
        if($result)
            return $result;
        else 
            return 0;
    }
    function getCallLogSummary($type,$userId)
    {
        if(is_null($userId))
            exit ();
     
        switch($type)
        {
            case "status":
            {
                $table = "91_status";
                break;
            }
            case "callVia":
            {
                $table = "91_callvia";
                break;
            }
        }
            
        $startDate = date("Y-m-d 00:00:00");
        $endDate = date("Y-m-d 23:59:59");
        $condition = "1" ;//" userId = ".$userId." date between '".$startDate."' and '".$endDate."' ";
        $this->db->select("*")->from($table)->where($condition);
//        echo $this->db->getQuery();
        $result = $this->db->execute();
        while ($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $resultant['data'][] =  $row; 
        }
        $resultant['count'] = count($resultant['data']);
        return json_encode($resultant);
    }
    function getCreditGraph($chainId)
    {
        if($chainId == "" ||preg_match('/[^a-zA-Z0-9]+/',$chainId))
                return json_encode(array("msg"=> "Invalid chain Id","status"=> "error"));
        $query = "select * from 91_closingAmount where `closingAmount` > 0 AND userId in (SELECT userId  FROM `91_userBalance` WHERE chainId like '".$chainId."____')";
        $result = $this->db->query($query);
        if($result)
        {
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
//                $resultant['data'][] = $row;
                
                $namedata = $this->getUserInformation($row['userId'],1);
                $resultant["data"][$namedata['userName']] =ABS($row["closingAmount"]);
            }
            $resultant['count'] = count($resultant['data']);
            
        }
        else
        {
            $resultant = array("msg"=> "Error fetching details for credit graph","status"=>"error");
        }
        return $resultant;
    }
    /**
     * @author sameer
     * modified by : Jisne graph pr kam kiya tha.
     */
    function getStatusAndTypeDetails($chainId,$date,$type,$endDate = NULL)
    {
        
        if($endDate == NULL){
            $endDate = date('Y-m-d');
        }
        
        if($chainId == "" ||preg_match('/[^a-zA-Z0-9]+/',$chainId))
                return json_encode(array("msg"=> "Invalid chain Id","status"=> "error"));
        if($date == "" ||preg_match('/[^0-9\-]+/',$date))
                return json_encode(array("msg"=> "Invalid date","status"=> "error"));
        if($type == "" ||preg_match('/[^A-Za-z]+/',$type))
                return json_encode(array("msg"=> "Invalid Type","status"=> "error"));
        
        if($type == "status")
        {
            $table = " 91_callStatusDaily ";
            $columns = " sum(total) as total ,sum(totalDuration) as totalNumber ,status as type";
            $condition = "chainId like '".$chainId."%' and date(date) between '".$date."' and '".$endDate."' group by status";
        }
        elseif($type == "callType" )
        {
            $table = " 91_callType";
            $columns = " sum(total) as total,callType as type";
            $condition = "chainId like '".$chainId."%' and date(date) between '".$date."' and '".$endDate."' group by callType";
        }
        $selResult = $this->selectData($columns, $table,$condition);
        $response = array();
        if($selResult)
        {
            $sumOfCall = 0;
            while($row = $selResult->fetch_array(MYSQLI_ASSOC))
            {
                //$response[$row['type']] = $row;
                
                $response[$row['type']] = $row['total'];
                
                $sumOfCall += $row['total'];
                if($type == "status")
                  $sumofDuration += $row['totalNumber'];
                
            }
            $finalResponse['data']=$response;
            //$response['sum']['totalSumDuration'] = $sumOfCall;
            $finalResponse['totalSumDuration']=$sumOfCall;
//          $response['sum']['avgCallDuration'] = ($response['ANSWERED']['totalDuration']/$response['ANSWERED']['total']);
            if($type == "status"){
            
                if($sumOfCall == 0 )
                $finalResponse['answeredCallPercent'] = number_format((($response['ANSWERED'] * 100) / $sumOfCall),2);
                else
                  $finalResponse['answeredCallPercent'] = 0;  
                
            $customerTime = $this->getResellerTotalStatistics($chainId,$date,2,$endDate);
            $myTime = $this->getResellerTotalStatistics($chainId,$date,1,$endDate);
            $finalResponse['customerTime'] = $this->convertSecondtoTime($customerTime['durationCharged']);//$this->convertSecondtoTime($sumofDuration);
            $finalResponse['myTime'] = $this->convertSecondtoTime($myTime['durationCharged']);//$this->getMyTotalTime($chainId,$date);
            }
        }
        else
        {
            $response = array("msg"=>"Error Fetching Details","status"=>"error");
        }
        
        return $finalResponse;
    }
    
    /**
     * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
     * @since 9/6/2014
     * @param int $routeId
     * @param string $fromDate
     * @param string $type
     * @param string $toDate
     * @uses function to get route status details
     * @return json
     */
    function getRouteStatusDetails($routeId,$fromDate,$type,$toDate = NULL)
    {
	if($toDate == NULL){
            $toDate = date('Y-m-d');
        }
	
	if($fromDate == NULL){
            $fromDate = date('Y-m-d');
        }
	if($routeId == "" ||preg_match('/[^0-9]+/',$routeId))
                return json_encode(array("msg"=> "Invalid route selected!!!","status"=> "error"));
        if($fromDate == "" ||preg_match('/[^0-9\-]+/',$fromDate))
                return json_encode(array("msg"=> "Invalid from date","status"=> "error"));
	if($toDate == "" ||preg_match('/[^0-9\-]+/',$toDate))
                return json_encode(array("msg"=> "Invalid To Date","status"=> "error"));
	
        if($type == "" ||preg_match('/[^A-Za-z]+/',$type))
                return json_encode(array("msg"=> "Invalid Type","status"=> "error"));
	
	//get route
	include_once('routeClass.php');
	$routeObj = new routeClass();
	
	$parm['routeId'] = $routeId;
	$routeDetailJson = $routeObj->getRoutedetail($parm,$_SESSION['userid']);
	
	$routeDtlArr = json_decode($routeDetailJson,TRUE);
	
	if(empty($routeDtlArr['routeName']))
	    return json_encode(array("msg"=> "Invalid Route!!!","status"=> "error"));
		
	$routeName = $routeDtlArr['routeName'];
	 if($type == "status")
        {
            $table = " 91_calls";
	    $startDate = $fromDate.' 00:00:00';
	    $endDate = $toDate.' 23:59:59';
            $columns = " route,status,count(status) as statusCount";
            $condition = "route='$routeName' and date(call_start) between '".$startDate."' and '".$endDate."' group by status";
        }
	
	 $selResult = $this->selectData($columns, $table,$condition);
    
        $sumOfCall = 0;
	 $response = array();
        if($selResult)
	{
	    
	     while($row = $selResult->fetch_array(MYSQLI_ASSOC))
            {
                //$response[$row['type']] = $row;
                if(!empty($row['status']))
		{
		    $response[$row['status']] = $row['statusCount'];
               
		
		
		    $sumOfCall += $row['statusCount'];
		}
//                if($type == "status")
//                  $sumofDuration += $row['totalNumber'];
                
            }
	}
	
	$finalRes['data'] = $response;
	$finalRes['totalCalls'] = $sumOfCall;
	
	return json_encode($finalRes);
	
	
    }
    
    
    function getAllCallDetails($fromDate,$toDate = NULL , $chainId = NULL )
    {
     
        
        
        $table = " 91_calls";
        
        $startDate = (isset($fromDate) && !empty($fromDate)) ? $fromDate.' 00:00:00':"";
        $endDate = (isset($toDate) && !empty($toDate)) ? $toDate.' 23:59:59':"";
        
        $condition = "";
        
        $columns = "status,count(status) as statusCount";
        
        if(!empty($startDate) &&  !empty($endDate))
        $condition .= " date(call_start) between '".$startDate."' and '".$endDate."'   ";
        
        $condition .=" group by status ";
        
        $selResult = $this->selectData($columns, $table,$condition);
    
        //echo 'query '. $this->querry;
        
        $sumOfCall = 0;
	$response = array();
        
        if($selResult)
	{
	    
	     while($row = $selResult->fetch_array(MYSQLI_ASSOC))
            {
                //$response[$row['type']] = $row;
                if(!empty($row['status']))
		{
		    $response[$row['status']] = $row['statusCount'];
               
		
		
		    $sumOfCall += $row['statusCount'];
		}
//                if($type == "status")
//                  $sumofDuration += $row['totalNumber'];
                
            }
	}
	
        $con = "";
        
        if(!empty($startDate) &&  !empty($endDate))
        $con .= "   date  between '".$startDate."' and '".$endDate."'   ";
        
        $aedRate = $this->currencyConvert("AED" , "USD" , 1 );
        $inrRate = $this->currencyConvert("INR" , "USD" , 1 );

        $aedRate =   $this->getNumberWithTwoDecimal($aedRate);
        $inrRate =  $this->getNumberWithTwoDecimal($inrRate);

         
         
        /*
         * 
         * SELECT sum(IF(currencyId = '1',deductBalance * 0.27 ,0)) as Aed , 
         * sum(IF(currencyId = '63',deductBalance * 0.016  ,0)) as inr ,  
         * sum(IF(currencyId = '147',deductBalance  ,0)) as usd    
         * FROM 91_chainBalanceReport WHERE   date  between
         * '2014-12-11 00:00:00' and '2014-12-18 23:59:59' 
         * 
         * 
         */
        
        $balResult = $this->selectData("sum(IF(currencyId = '1',deductBalance * $aedRate ,0)) as AED ,sum(IF(currencyId = '63',deductBalance * $inrRate  ,0)) as INR , sum(IF(currencyId = '147',deductBalance  ,0)) as USD  ", "91_chainBalanceReport",$con);
        
       // echo $this->querry;
        $balance = "0";
        
        if($balResult)
        {
            while($row = $balResult->fetch_array(MYSQLI_ASSOC))
            {
                //$response[$row['type']] = $row;
               // if(!empty($row['sum(deductBalance)']))
		{
		   $balance = $this->getNumberWithTwoDecimal($row['AED']+$row['INR']+$row['USD']);
                   
		}
//                if($type == "status")
//                  $sumofDuration += $row['totalNumber'];
                
            }
        }
        
	$finalRes['data'] = $response;
	$finalRes['totalCalls'] = $sumOfCall;
	$finalRes['totalBal'] = $balance;
	return json_encode($finalRes);
        
	
    }
    
    
    
    
    /*
     * @author sudhir pandey <sudhir@hostnsoft.com>
     * @since 19-12-2013
     * @description function use to get my total time duration 
     */
    function getMyTotalTime($chainId,$date){
        
        $table = " 91_callStatusDaily ";
        $columns = "sum(totalDuration) as totalNumber";
        $condition = "chainId like '".$chainId."' and date(date) between '".$date."' and date(now()) group by status";
        $selResult = $this->selectData($columns, $table,$condition);
        $sumofDuration = 0;
        if($selResult)
        {
           while($row = $selResult->fetch_array(MYSQLI_ASSOC))
            {
               $sumofDuration += $row['totalNumber'];
            }
            
           $mytime = $this->convertSecondtoTime($sumofDuration);
        }else
           $mytime = "00:00:00 Hrs";
        
        return $mytime;
    }
    
    function convertSecondtoTime($init){
        $hours = floor($init / 3600);
        $minutes = floor(($init / 60) % 60);
        $seconds = $init % 60;

        return "$hours:$minutes:$seconds Hrs";
      }
      
    function getResellerTotalStatistics($chainId,$date,$type,$endDate = NULL)
    {
       
        /* callduration and profit
         * $type : 1 is for My total time 
         * $type : 2 is for total customer time
         * $type : 3 is for Total Profit
         */
        
        if($chainId == "" ||preg_match('/[^a-zA-Z0-9]+/',$chainId))
                return json_encode(array("msg"=> "Invalid chain Id","status"=> "error"));
        if($date == "" ||preg_match('/[^0-9\-]+/',$date))
                return json_encode(array("msg"=> "Invalid date","status"=> "error"));
        
       
        if($endDate == NULL){
            $endDate = date('Y-m-d');
        }
        $table = " 91_durationCharged";
        switch($type)
        {
            
            case 1:
            {
                $columns = " sum(durationCharged) as durationCharged ";
                $condition = "chainId = '".$chainId."' and date(date) between '".$date."' and '".$endDate."' ";
                break;
            }
            case 2:
            {
                $columns = " sum(durationCharged) as durationCharged";
                $condition = "resellerId = '".$chainId."' and date(date) between '".$date."' and '".$endDate."' ";
                break;
            }
            case 3:
            {
                $table = " 91_chainProfit";
                $columns = " sum(profit) as profit";
                $condition = "parentId = ".$chainId." and date(date) between '".$date."' and '".$endDate."' ";
               
                //$this->sendErrorMail("nidhi@walkover.in", "condition " . $condition );
               
                break;
            }
        }
        
        
        
        
        $selResult = $this->selectData($columns, $table,$condition);
      
       
        
        
        if($selResult)
        {
            $row = $selResult->fetch_array(MYSQLI_ASSOC);
            return $row;
        }
        else
            return false;
//            return json_encode(array("msg"=>"Error fetching details","status"=>"error"));
        
        
    }
    function getResProfitDurationGraphDetails($chainId,$date,$type,$endDate = NULL)
    {
        if($chainId == "" ||preg_match('/[^a-zA-Z0-9]+/',$chainId))
                return json_encode(array("msg"=> "Invalid chain Id","status"=> "error"));
        if($date == "" ||preg_match('/[^0-9\-]+/',$date))
                return json_encode(array("msg"=> "Invalid date","status"=> "error"));
        
        if($endDate == NULL){
            $endDate = date('Y-m-d');
        }
        
        $chainId = $this->db->real_escape_string($chainId);
        $date = $this->db->real_escape_string($date);
        if($type == "duration")
        {
            $query = "select sum(durationCharged) as clmValue,chainId,resellerId from (SELECT * FROM `91_durationCharged` where resellerId = '".$chainId."' and date(date) between '".$date."' and '".$endDate."' order by durationCharged DESC ) as durationTable group by chainId  ";
        }
        elseif($type == "profit" || $type == "loss")
        {
          $query = "select sum(profit) as clmValue ,childId as chainId,currencyDesc as currency from (SELECT * FROM `91_chainProfit` where parentId = '".$chainId."' and date(date) between '".$date."' and '".$endDate."' order by profit DESC ) as t group by childId ";
        }
     
       
        $result = $this->db->query($query);
       
        
        if($result)
        {
            
            $totalCallDuration = 0;$currency = '';
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                
                $response[$row['chainId']] = $row['clmValue'];
                $chainIdArr[] = $row['chainId'];
                $totalCallDuration += $row['clmValue'];
                if($type == "profit" || $type == "loss") 
                    $currency = $row['currency'];
            }
       
            
            $userNameArr = $this->getUserNameViaChainIdArray($chainIdArr);
            if(!$userNameArr)
                json_encode(array("msg"=>"Error fetching User Details please try again later","status"=>"error"));
            
            foreach($response as $key => $value)
            {
//                $finalArr[$userNameArr[$key]]['clmValue'] = $value;
//                $finalArr[$userNameArr[$key]]['percentage'] = (($value/$totalCallDuration)*100);
                 
                if($type == "duration")
                    {
                        $hours = floor($value / 3600).".".floor(($value / 60) % 60);
                        $finalArr[$userNameArr[$key]] = (float)$hours;
                    }
                    elseif($type == "profit")
                    {
                        if($value > 0){
                        $finalArr[$userNameArr[$key]] = $value;//number_format((($value/$totalCallDuration)*100),2);
                        }
                    }elseif($type == "loss"){
                        if($value < 0){
                        $finalArr[$userNameArr[$key]] = -$value;//number_format((($value/$totalCallDuration)*100),2);
                        }
                    }
                               
            }
            $finalResponse['data']=$finalArr;
            $finalResponse['totalProfit'] = $totalCallDuration . " " .$currency;
            
            return $finalResponse;
        }
        else
        {
            return json_encode(array("msg"=> "Error Fetching Details please try agian later","status"=>"error"));
        }
    }
    function getUserNameViaChainIdArray($chainIdArr)
    {
        if($chainIdArr == "" || !is_array($chainIdArr))
            return json_encode(array("msg" => "invalid Input Provided","status"=>"error"));
        $chainIdArr = array_unique($chainIdArr);
        $chainIdArr = array_values($chainIdArr);
        if(count($chainIdArr) <1)
            return json_encode(array("msg" => "invalid Input Provided","status"=>"error"));
        $chainIdString  = implode("','",$chainIdArr);
        $table = "91_manageClient";
        $column = "userName,chainId";
        $condition = "chainId IN ('".$chainIdString."')";
        $result  = $this->selectData($column, $table,$condition);
        if($result)
        {
            while($row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $response[$row['chainId']] = $row['userName'];
            }
            return $response;
        }
        else
            return false;
//            return json_encode(array("msg"=>"Error fetching Details please try again later","status"=>"error"));
    }
    
     /**
     * @author Ankit patidar <ankitpatidar@hostnsoft.com>
     * @since 10/03/2014
     * @filesource
     * @uses to get details from  call failed error log
     * @abstract called from adminController
     * @param array $request: contains start date ,end date and search string
     * 
     */
    function callFailedErrorLog($request)
    {
        //set date in required format
        $sDate = (isset($request['sDate']) && $request['sDate'] != '')?date('Y-m-d 00:00:00',strtotime($request['sDate'])):date('Y-m-d 00:00:00');
        $eDate = (isset($request['eDate']) && $request['eDate'] != '')?date('Y-m-d 23:59:59',strtotime($request['eDate'])):date('Y-m-d 23:59:59');
       
        $qString = (isset($request['q']) && $request['q']) != ''?$this->db->real_escape_string($request['q']):'';
        
        if(preg_match(NOTTEXT_REGX,$qString))
		return json_encode(array('status' => 0,'msg' => 'Invalid search do not use special characters for search')); 
	
	
	
        if(!isset($_SESSION['userid']))
        {
           return json_encode(array('status' => 0,'msg' => 'Your session has destroyed ,Please Login again')); 
        }
        
        $inClause = '';
        
        $qString= trim($qString);

        if($qString !='')
        {
            //code to get user chain id by name
            if(strlen($qString) > 3)
            {
                $resultCi = $this->selectData('chainId','91_manageClient',"userName LIKE '$qString%'");
               
                
                if($resultCi->num_rows > 0)
                {
                    $cIdArr = array();
                    while($cRow = $resultCi->fetch_array(MYSQLI_ASSOC))
                    {
                        $cIdArr[] = '"'.$cRow['chainId'].'"';
                        unset($cRow);
                    }
                    
                    if(!empty($cIdArr))
                        $inClause = ' chainId IN('.implode(",", $cIdArr).') or';
                }
                
                 
            }
            
            $likeQ = "reason LIKE '%$qString%' or $inClause telNum LIKE '%$qString%' and ";
        }
        else
            $likeQ = '';


        if(isset($request['pageNo']) && is_numeric($request['pageNo']))
            $pageNo = $request['pageNo'];
        else
            $pageNo = 1;

        $limit = 20;

        $skip = $limit*($pageNo-1);
        
        $table = '91_rejectCalls';
        
        $this->db->select('SQL_CALC_FOUND_ROWS *')->from($table)->where($likeQ.' date BETWEEN "'.$sDate.'" AND "'.$eDate.'"')->orderBy('date DESC')->limit($limit)->offset($skip);
        
        $qur = $this->db->getQuery();
        
        //$this->sendErrorMail("nidhi@walkover.in", "Error mail ".$qur);
        
        $result = $this->db->execute();
        
        $resultCount = $this->db->query('SELECT FOUND_ROWS() as totalRows');
        $countRes = mysqli_fetch_assoc($resultCount);
        $pages = ceil($countRes['totalRows']/$limit);

        if($qString != '')
        {
//            var_dump($this->db);
//            var_dump($result);
//            echo 'query:'.$this->db->getQuery(); 
//            echo '<br>';
//            echo $likeQ.' date BETWEEN "'.$sDate.'" AND "'.$eDate.'"';
            
        }
//validate result
        if(!$result)
        {
            trigger_error('Problem while getting call failed error log details,query:'.$qur);
            return json_encode(array('status' => 0,'msg' => 'Problem while getting call error log details!!!'));
        }
        if($result->num_rows == 0)
        {
            return json_encode(array('status' => 0,'msg' => 'no Record found!!!'));
        }
        
        $data = array();
        $chainIds= array();
        
        While($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            
//            $clientTypeJson = $this->getUserOneDetail($row['chainId'],'chainId','type');
//            $clientType = json_decode($clientTypeJson,TRUE);
//            $row['clientType'] = $clientType['type'];
            
            $clientTypeJson = $this->getUserOneDetail($row['chainId'],'chainId','userId');
            $clientType = json_decode($clientTypeJson,TRUE);
            $row['userId'] = $clientType['userId'];
            
            $allUserInformation =  $this->getUserInformation(  $row['userId']  , 1 );
            
             $row['clientType'] = $allUserInformation['type'];
             $row['batchId'] = $allUserInformation['userBatchId'];
             
             $data[] = $row;
             
//            $clientNameJson = $this->getUserOneDetail($row['chainId'],'chainId','userName');
//            $clientName = json_decode($clientNameJson,TRUE);
    
            $chainIds['chainIds'][$row['chainId']]= $allUserInformation['userName'];  
          //  $chainIds['clientType'][$row['chainId']]= $clientName['type'];     

            unset($row);
        }
        
        return json_encode(array('status' => 1,'msg' => 'Record Found@!!!','callFailedData' => $data,'chainIds' =>$chainIds,"pages" => $pages));
        
    } //end of function callFailedErrorLog()
    
    function getCallDeatilsAdmin($userId,$keyword,$type,$route,$status,$sDate,$eDate,$pageNo=1,$exportOpt=0)
    {
        if($userId  == "" || preg_match(NOTNUM_REGX, $userId))
        {
            $this->msg = "Invalid user please try again";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }
            
        
        if(!$this->check_admin($userId))
        {
            $this->msg = "Invalid user please try again";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }

        $sDateArr  = explode('-', $sDate);
       
        if (!checkdate($sDateArr[1], $sDateArr[2], $sDateArr[0])) {
           $this->msg = "Invalid From date!";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }
            
         $eDateArr  = explode('-', $eDate);
        if (!checkdate($eDateArr[1], $eDateArr[2], $eDateArr[0])) {
           $this->msg = "Invalid to date!";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }
        
        if(preg_match(NOTNUM_REGX, $type))
        {
            $this->msg = "Invalid type please contact provider";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }
        
        $condition = "";
        
        switch($type)
        {
            case '1':{
               
              
                
            if(preg_match(NOTUSERNAME_REGX, trim($keyword)) || $keyword == "" || strlen($keyword) <3 || strlen($keyword) > 20 )
            {
                $this->msg = "Invalid username please provide a valid user name";
                $this->status = "error";
                return (array("msg"=>$this->msg,"status"=>$this->status));
            }
            
            $user = $this->getUserId($keyword);
            if(!$user)
            {
                $this->msg = "Invalid user no user exist with this name";
                $this->status = "error";
                return (array("msg"=>$this->msg,"status"=>$this->status));
            }
            
            $condition = " id_client=".$user." ";
            
                break;
                
            }
            case '2':{
            if(preg_match(NOTNUM_REGX, $keyword) || $keyword == "" || strlen($keyword) < 7 || strlen($keyword) > 18)
            {
                $this->msg = "Invalid keyword please provide a valid search parameter";
                $this->status = "error";
                return (array("msg"=>$this->msg,"status"=>$this->status));
            }
                $condition = " caller_id='".$keyword."' or called_number='".$keyword."' ";
                break;
                
            }
            case '3':{
            if(preg_match(NOTALPHANUM_REGX, $route) || $route == "")
            {
                $this->msg = "Invalid route please contact provider";
                $this->status = "error";
                return (array("msg"=>$this->msg,"status"=>$this->status));
            }
            
            $condition = " route='".$route."' ";
            
                break;
                
            }
            case '4':{
                if(preg_match(NOTALPHABATE_REGX, $status) || $status == "" )
                {
                    $this->msg = "Invalid status please contact provider";
                    $this->status = "error";
                    return (array("msg"=>$this->msg,"status"=>$this->status));
                }
                $condition = " status='".$status."' ";
                break;
                
            }
            default :{
                    $condition = " 1 ";
                }
        }

        $limit = 20;

        $skip = $limit*($pageNo-1);

        $columns = "SQL_CALC_FOUND_ROWS id_client,id_chain,caller_id,called_number,call_start,call_end,status,hangup_reason,call_type,balance_deduct,route,didNumber,duration,uniqueId";
        $table = "91_calls";
        //$condition .= "and call_start between '".$sDate." 00:00:00' and '".$eDate." 23:59:59' order by id_call DESC limit 20";
        //$result = $this->selectData($columns, $table,$condition);
        
        if($exportOpt === 'CSV' || $exportOpt === 'XLS'){       
        $this->db->select($columns)->from($table)->where($condition.'and call_start between \''.$sDate.' 00:00:00\' and \''.$eDate.' 23:59:59\'')->orderBy('id_call DESC');
        }else
        $this->db->select($columns)->from($table)->where($condition.'and call_start between \''.$sDate.' 00:00:00\' and \''.$eDate.' 23:59:59\'')->orderBy('id_call DESC')->limit($limit)->offset($skip);
          
        $result = $this->db->execute();

         $resultCount = $this->db->query('SELECT FOUND_ROWS() as totalRows');
        $countRes = mysqli_fetch_assoc($resultCount);

        if(!$countRes)
        {
            trigger_error('Problem while get call log detail count');
            $this->msg = "Problem while get call log record!";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        }

        $pages = ceil($countRes['totalRows']/$limit);

        if(!$result || $result->num_rows < 1)
        {
            $this->msg = "Error no record found";
            $this->status = "error";
            return (array("msg"=>$this->msg,"status"=>$this->status));
        
        }
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $data[] = $row;
            $userIdArr[] = $row['id_client'];
        }

        $userIdArr = array_unique($userIdArr);
        $userIdStr = implode(",",$userIdArr);
        unset($userIdArr);
        
        $resultUser = $this->selectData("userName,userId,currencyId,type,userBatchId","91_manageClient"," userId IN (".$userIdStr.")");
        
        if(!$resultUser)
        {
            $this->msg = "Error fetching user details please try again later";
            $this->status = "error";
            return (array("msg" => $this->msg, "status" => $this->status));
        }
        
        while($row = $resultUser->fetch_array(MYSQLI_ASSOC))
        {
            $userIdArr[$row['userId']] = $row['userName'];
            
            $userIdArr['currency'][$row['userId']] = $this->getCurrencyName($row['currencyId']); 
            $userIdArr['type'][$row['userId']] = $row['type']; 
            $userIdArr['userBatchId'][$row['userId']] = $row['userBatchId']; 
            
        }
        foreach($data as $key =>$value)
        {
            
            $data[$key]['userName'] = $userIdArr[$value['id_client']];
            $data[$key]['currency'] = $userIdArr['currency'][$value['id_client']];
            $data[$key]['type'] = $userIdArr['type'][$value['id_client']];
            $data[$key]['userBatchId'] = $userIdArr['userBatchId'][$value['id_client']];
        }
        
        $data['pages'] = $pages;
        
       
        //echo "sudhir".$exportOpt."pandey";
       
        if($exportOpt === 'CSV'){
            //var_dump($exportOpt);
          $this->exportCallLog($data);    
          die();
        }
        
        if($exportOpt === 'XLS'){
            //var_dump($exportOpt);
          $this->exportCallLogXLS($data);    
          die();
        }
        return $data;
    }
    
    
    function exportCallLogXLS($data){
     $fieldType = array('A' => 'userName' , 
                        'B' => 'caller_id' , 
                        'C' => 'called_number',
                        'D' => 'call_start',
                        'E' => 'call_end',
                        'F' => 'route',
                        'G' => 'status',
                        'H' => 'hangup_reason',
                        'I' => 'call_type',
                        'J' => 'balance_deduct'
                        );
       
                 $fileName = $this->exportRecords($data,'xlsx' ,'Call Log Detail' ,$fieldType);
                
                if($fileName != FALSE)
                    $this->downloadExportedFile($fileName);
                else
                    return json_encode(array("msg"=>"File can not export.","status"=>"error"));
        
    }
    
    
    function exportCallLog($data){
        
        $str = 'User Name,Source Number,Destination Number,Call Start Time,Call End Time,route,Status,HangUp Reason,Call Type,Balance Deducted';
//        $str.= '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="cmntbl  boxsize" id="editfundLog">
//                <thead>
//                <tr>
//                    <th width="15%">User Name</th>
//                    <th width="10%" class="alC">Source Number</th>
//                    <th width="10%" class="alC">Destination Number</th>
//                    <th width="10%" class="alC">Call Start Time</th>
//                    <th width="10%" class="alC">Call End Time</th>
//                    <th width="10% noBorder" >route</th>
//                    <th width="10% noBorder">Status</th>
//                    <th width="10% noBorder">HangUp Reason</th>
//                    <th width="10% noBorder">Call Type</th>
//                    <th width="10% noBorder">Balance Deducted</th>
//                </tr>
//                </thead><tbody>';
        $str.="\n\n";
        foreach($data as $key=>$value){
         $str.='"'.$value['userName'].'","'.$value['caller_id'].'","'.$value['called_number'].'","'.$value['call_start'].'","'.$value['call_end'].'","'.$value['route'].'","'.$value['status'].'","'.$value['hangup_reason'].'","'.$value['call_type'].'","'.$value['balance_deduct'].'"';
         $str.="\n";
//            $str.= '<tr>
//                            <td>'.$value['userName'].'</td>
//                            <td class="alC">'.$value['caller_id'].'</td>
//                            <td class="alC">'.$value['called_number'].'</td>
//                            <td class="alC blueThmCrl">'.$value['call_start'].'</td>
//                            <td class="alC blueThmCrl">'.$value['call_end'].'</td>
//                            <td class="alC ">'.$value['route'].'</td>
//                            <td class="alC ">'.$value['status'].'</td>
//                            <td class="alC ">'.$value['hangup_reason'].'</td>
//                            <td class="alC ">'.$value['call_type'].'</td>
//                            <td class="alC ">'.$value['balance_deduct'].'</td>
//                            </tr>';
        }
//         $str.='</tbody></table>';
//        echo $str;
        $this->exportCsv($str);
       
        
    }
     function exportCsv($str)
        {
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=callLogList.csv");
            header("Pragma: no-cache");
            header("Expires: 0");
                      
            
            echo $str; 
            
        }
    /**
    *@author Ankit patidar <ankitpatidar@hostnsoft.com>
    *@since 20/6/2014
    *@abstract called in getRouteTimeLine function of currenct class
    *@return array
    */
    function get_months($date1, $date2)
    { 
           $time1  = strtotime($date1); 
           $time2  = strtotime($date2); 
           $my     = date('mY', $time2); 

           $months = array(date('F', $time1)); 

           while($time1 < $time2) { 
              $time1 = strtotime(date('Y-m-d', $time1).' +1 month'); 
              if(date('mY', $time1) != $my && ($time1 < $time2)) 
                 $months[] = date('F', $time1); 
           } 

           $months[] = date('F', $time2); 
           return $months; 
}  


    /**
    *@author Ankit patidar <ankitpatidar@hostnsoft.com>
    *@abstract called from userCallLogcontroller
    *@param string $sdate
    *@param string $edate
    *@param int $routeId
    *@param array $session
    *@return json
    *@filesource
    **
    */
    function getRouteTimeLine($sdate,$edate,$searchType=1,$routeId,$session)
    {

        
        if($edate == NULL){
            $edate = date('Y-m-d');
        }
    
        if($sdate == NULL){
                $sdate = date('Y-m-d');
            }
        if($routeId == "" ||preg_match('/[^0-9]+/',$routeId))
                    return json_encode(array("msg"=> "Invalid route selected!!!","status"=> "error"));
        if($sdate == "" ||preg_match('/[^0-9\-]+/',$sdate))
                    return json_encode(array("msg"=> "Invalid from date","status"=> "error"));
        if($edate == "" ||preg_match('/[^0-9\-]+/',$edate))
                    return json_encode(array("msg"=> "Invalid To Date","status"=> "error"));
    
        if(empty($session['client_type']) || $session['client_type'] != 1)
                return json_encode(array("msg"=> "session expired please try again","status"=> "error"));        

        //get date diffence between sdate and edate
        $diffSecs = strtotime($edate)-strtotime($sdate);

        switch ($searchType) {
            case 1:
                # code...
                    $dateDiff = floor($diffSecs/(60*60*24));    
                break;
            case 2:
                # code...
                    $monthArr = $this->get_months($sdate,$edate);
                    if(!is_array($monthArr))
                        $dateDiff = 0;
                    else
                        $dateDiff = count($monthArr);
            //        $dateDiff = floor($diffSecs/(60*60*24));    
                break;
            
            default:
                # code...
                break;
        }
        

       
        //if($dateDiff < 31)
        return $this->getRouteTimeLineByDateWise($sdate,$edate,$searchType,$routeId,$dateDiff);
        // else
        //     return json_encode(array("msg"=> "Your have entered large date range,Please enter small date range!!!","status"=> "error"));            
    }

    /**
    *@author Ankit Patidar <ankitpatidar@hostnsoft.com>
    *@abstract called from getRouteTimeLine function 
    *@param string  $sdate 
    *@param string $edate
    *@param int $routeId
    *@param int $dateDiff
    *@filesource
    *@return json
    ******/
    function getRouteTimeLineByDateWise($sdate,$edate,$searchType=1,$routeId,$dateDiff=0)
    {
        //get total call duration
        //$query='SELECT DAYOFMONTH(call_start) as day,sum(duration) as durationSum FROM `91_calls` where call_start between '$sdate' and '$edate' group by DAYOFMONTH(call_start)' ;

        //set date formats for sql query and next date
        switch ($searchType) {
            case 1:
                # code...
                    $sqlDateFormat = '%Y-%m-%d';
                    $nextDateFormat = 'days';
                    $dateCatFormat = 'Y-m-d';
                break;
            case 2:
                # code...
                    $sqlDateFormat = '%Y-%m';
                    $nextDateFormat = 'months';
                     $dateCatFormat = 'Y-m';
                break;
            
            default:
                # code...
                break;
        }

        $result = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration) as durationSum","91_calls","call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

        //set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));
       
       
        //error handling
        if(!$result || $result->num_rows == 0)
        {
            trigger_error('problem while getting total call duration,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['totalCallDuration'] = array();
      
       $detailArr['dayArr'][] = $dateNo; 
        //this while will give the totalcallDuration array
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['totalCallDuration'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                $detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['totalCallDuration'][] =(float)($row['durationSum']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            $detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['totalCallDuration'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            $detailArr['dayArr'][]= $dateNo;
        }


##############################Route wise call duration######################################

        //set route name by route id

        //get route
        include_once('routeClass.php');
        $routeObj = new routeClass();
        
        $parm['routeId'] = $routeId;
        $routeDetailJson = $routeObj->getRoutedetail($parm,$_SESSION['userid']);
        
        $routeDtlArr = json_decode($routeDetailJson,TRUE);
        
        if(empty($routeDtlArr['routeName']))
            return json_encode(array("msg"=> "Invalid Route!!!","status"=> "error"));
            
        $routeName = $routeDtlArr['routeName'];

         $resultRWise = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration) as durationSum","91_calls","route='".$routeName."' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

         
        //set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

        //error handling
        if(!$resultRWise || $resultRWise->num_rows == 0)
        {
            trigger_error('problem while getting routewise call duration,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['routeCallDuration'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultRWise->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['routeCallDuration'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['routeCallDuration'][] = (float)($row['durationSum']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['routeCallDuration'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }
##############################Route wise call duration end######################################

#####################################code to get ACD array############################################################        
//set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

         $resultAcd = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration)/count(id_call) as acd","91_calls","call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

        //error handling
        if(!$resultAcd || $resultAcd->num_rows == 0)
        {
            trigger_error('problem while getting route ACD,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['acd'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultAcd->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['acd'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['acd'][] = (float)($row['acd']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['acd'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

#####################################code to get ACD array end of code############################################################    

##############################code to get ASR (average seisure ratio)############################################
//set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

         $query = "select a.dateA as day,a.countA/b.countF as ASR from
(SELECT count(id_call) as countA,DATE_FORMAT(call_start, '".$sqlDateFormat."') as dateA FROM `91_calls` where status='ANSWERED' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."') ) a
cross join
(SELECT count(id_call) as countF,DATE_FORMAT(call_start, '".$sqlDateFormat."') as dateA FROM `91_calls` where status<>'ANSWERED' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')) b where a.dateA = b.dateA";

        $resultAsr = $this->db->query($query);
        
         //$resultAcd = $this->selectData("DAYOFMONTH(call_start) as day,sum(duration)/count(id_call) as acd","91_calls","call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DAYOFMONTH(call_start)");

        //error handling
        if(!$resultAsr || $resultAsr->num_rows == 0)
        {
            trigger_error('problem while getting route ASR,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['asr'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultAsr->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['asr'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['asr'][] = (float)($row['ASR']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['asr'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }



        return json_encode(array('status' => 'success','msg' => 'Record found!!!','detail' => $detailArr));
       

    }
    
    /**
     * @author Ankit patidar <ankitpatidar@hostnsoft.com>
     * @since 27/06/2014
     * @param array $request
     * @param int $type 1 for admin all detail 2 for route detail 3 for reseller detail by chain id
     * @abstract called from userCallLog controller
     * @return json
     */
    function getCountryLogDetail($request,$session,$type=1)
    {

       if(empty($session['userid']))
                json_encode(array('status' => 'error','msg' => 'You session has been expired please login again!'));
	
	 //set date in required format
        $sdate = (isset($request['sDate']) && $request['sDate'] != '')?date('Y-m-d',strtotime($request['sDate'])):date('Y-m-d');
        $edate = (isset($request['eDate']) && $request['eDate'] != '')?date('Y-m-d',strtotime($request['eDate'])):date('Y-m-d');
	
        //check for route and if exists then 
        $routeCondition = '';
        if($type == 2 && isset($request['route']))
        {
             if(empty($session['userid']) || $session['client_type'] != 1)
                json_encode(array('status' => 'error','msg' => 'You do not have access for this action!'));
            if(preg_match('/[^a-zA-Z0-9]+/',$request['route']))
            {
                json_encode(array('status' => 'error','msg' => 'Invalid route selected!'));
            }

            $routeCondition = 'route=\''.$request['route'].'\' and' ;

        }
        else if($type == 3)
        {
            if(empty($session['userid']) || $session['client_type'] != 2)
                json_encode(array('status' => 'error','msg' => 'You do not have access for this action!'));

            $chainId = $session['chainId'];
             $routeCondition = 'chainId LIKE \''.$chainId.'%\' AND';
        }

	   $result = $this->selectData("iso,status ,country ,count(status ) AS cCount","91_countryCallLog","$routeCondition status='ANSWERED' AND date BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' GROUP BY iso");
	 
    	if(!$result) 
    	{
    	    trigger_error('Problem while get country data'.$this->querry);
    	    return json_encode(array('status' => 'error' ,"msg" => 'Problem while get country data!'));
    	}
	
	   $country = array();
	   //set for answered
    	while($row = $result->fetch_array(MYSQLI_ASSOC))
    	{   
            $iso = explode('/',$row['iso']);
    	   	$country[$iso[0]]['Answered'] = (float)$row['cCount'];
    		$country[$iso[0]]['Failed'] = 0;
    	   $country[$iso[0]]['country'] = $row['country'];
    	}
	
    	//get all failed
    	 $resultFail = $this->selectData("iso,status ,country, count(status ) AS cCount","91_countryCallLog","$routeCondition status<>'ANSWERED' AND date BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' GROUP BY iso");
    	 
    	 if(!$resultFail)
    	 {
    	     trigger_error('Problem while get country data'.$this->querry);
    	    return json_encode(array('status' => 'error' ,"msg" => 'Problem while get country data!'));
    	 }
    	 
    	 //set for answered
    	while($row = $resultFail->fetch_array(MYSQLI_ASSOC))
    	{   
    	    $iso = explode('/',$row['iso']);
    		$country[$iso[0]]['Failed'] = (float)$row['cCount'];
    		if(!isset($country[$iso[0]]['Answered']))
    		    $country[$iso[0]]['Answered'] = 0;

            if(!isset($country[$iso[0]]['country']))
                $country[$iso[0]]['country'] = $row['country'];

    	   
    	    
    	}
	
    	if(count($country))
    	    return json_encode(array('status' =>"success" ,'msg'=> 'Record Found!','detail' => $country));
    	else
    	    return json_encode(array('status' =>"error" ,'msg'=> 'Record not Found!','detail' => array()));
	
	
	 
    }


    
    
    /**
     * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
     * @since 27/06/2014
     * @abstract called from userCallLog controller
     * @param array $request
     * @param int $type 1 default 2 for route and 3 for reseller wise detail
     * @return json
     */
    function getCountryPieDetail($request,$session,$type=1)
    {

        if(empty($session['userid']))
            json_encode(array('status' => 'error','msg' => 'You session has been expired please login again!'));
	 //set date in required format
        $sdate = (isset($request['sDate']) && $request['sDate'] != '')?date('Y-m-d',strtotime($request['sDate'])):date('Y-m-d');
        $edate = (isset($request['eDate']) && $request['eDate'] != '')?date('Y-m-d',strtotime($request['eDate'])):date('Y-m-d');
	
         $routeCondition = '';
        if($type == 2 && isset($request['route']))
        {
             if($session['client_type'] != 1)
                json_encode(array('status' => 'error','msg' => 'You do not have access for this action!'));
            if(preg_match('/[^a-zA-Z0-9]+/',$request['route']))
            {
                json_encode(array('status' => 'error','msg' => 'Invalid route selected!'));
            }

            $routeCondition = 'route=\''.$request['route'].'\' and' ;

        }
        else if($type == 3)
        {
            if($session['client_type'] != 2)
                json_encode(array('status' => 'error','msg' => 'You do not have access for this action!'));

            $chainId = $session['chainId'];
             $routeCondition = 'chainId LIKE \''.$chainId.'%\' AND';
        }



	   $result = $this->selectData("iso,status ,country, count(status ) AS cCount","91_countryCallLog","$routeCondition date BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' GROUP BY iso");
	 
	
	//get total count 
	   $resultCount = $this->selectData("count(sNo) as totalCalls","91_countryCallLog","$routeCondition date BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59'");
        $countRes = mysqli_fetch_assoc($resultCount);
        $totalCount = $countRes['totalCalls']; 
	
    	if(!$result) 
    	{
    	    trigger_error('Problem while get country data for pie'.$this->querry);
    	    return json_encode(array('status' => 'error' ,"msg" => 'Problem while get country data!'));
    	}
    	
    	$country = array();
    	
    	while($row= $result->fetch_array())
    	{
    	   $percentage = ($row['cCount']/$totalCount)*100;
    	    $country[] = array($row['country'],$percentage);
    	    //$country['percentage'][] = ($row['cCount']/$totalCount)*100;
    	}
    	
    	if(count($country))
    	{
    	    return json_encode(array('status' => 'success','msg' => 'Record Found!','data' => $country));
    	}
    	else
    	{
    	    return json_encode(array('status' => 'error','msg' => 'Record Not Found!','data' => array()));
    	}
    }

    /**
    * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
    * @since 7/2014
    * @abstract called from userCallLog controller
    * @param  array $request it contains all required parameters
    * @return json
    */
    function getRouteCreditDetail($request,$session)
    {

        if(empty($session['userid']))
            return json_encode(array());

        $userId = $session['userid'];
        //include route class
        include_once('routeClass.php');
        $routeObj = new routeClass();


        $dataJson = $routeObj->getRoute(array(),$userId);

        $routeResult = json_decode($dataJson,TRUE); 
        $routeDetail = array();

        foreach ($routeResult as $key => $value) {
            # code...

            $routeDetail[] = array('routeName' => $value['routeName'],
                                    'routeClosingAmt' => $value['routeClosingAmt'],
                                     'currency' => $value['currency']); 

            // $routeDetail['routeName'][] = $value['routeName'];
            // $routeDetail['routeClosingAmt'][] =$value['routeClosingAmt'];
            // $routeDetail['currency'][] = $value['currency']; 

        }


       return json_encode($routeDetail);

    }

	function getRouteProfitDetail($request,$session)
    {

        if(empty($session['userid']))
            return json_encode(array());

        $userId = $session['userid'];
        //include route class
        include_once('routeClass.php');
        $routeObj = new routeClass();


        $dataJson = $routeObj->getRouteProfit($request,$userId);

        $routeResult = json_decode($dataJson,TRUE);
        $routeDetail = array();

        foreach ($routeResult as $key => $value) {
            # code...

            $routeDetail[] = array('routeName' => $value['routeName'],
                                    'routeClosingAmt' => $value['profit'],
                                     'currency' => $value['currency']);

            // $routeDetail['routeName'][] = $value['routeName'];
            // $routeDetail['routeClosingAmt'][] =$value['routeClosingAmt'];
            // $routeDetail['currency'][] = $value['currency']; 

        }


       return json_encode($routeDetail);

    }


    /**
    * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
    * @since 7/7/2014
    * @abstract called from userCallLog controller 
    * @param int @searchType 1 for date wise and 2 for monthwise
    * @return json
    */
    function resellerTimeLine($sdate,$edate,$searchType=1,$session)
    {
         if($edate == NULL){
            $edate = date('Y-m-d');
        }
    
        if($sdate == NULL){
                $sdate = date('Y-m-d');
            }

        if(empty($session['chainId']))
                return json_encode(array("msg"=> "Your session has been expired,Please login again!","status"=> "error"));

        if($sdate == "" ||preg_match('/[^0-9\-]+/',$sdate))
                    return json_encode(array("msg"=> "Invalid from date","status"=> "error"));
        if($edate == "" ||preg_match('/[^0-9\-]+/',$edate))
                    return json_encode(array("msg"=> "Invalid To Date","status"=> "error"));
    
        if(empty($session['client_type']) || $session['client_type'] != 2)
                return json_encode(array("msg"=> "session expired please try again","status"=> "error"));        

        //get date diffence between sdate and edate
        $diffSecs = strtotime($edate)-strtotime($sdate);

        switch ($searchType) {
            case 1:
                # code...
                    $dateDiff = floor($diffSecs/(60*60*24));    
                break;
            case 2:
                # code...
                    $monthArr = $this->get_months($sdate,$edate);
                    if(!is_array($monthArr))
                        $dateDiff = 0;
                    else
                        $dateDiff = count($monthArr);
            //        $dateDiff = floor($diffSecs/(60*60*24));    
                break;
            
            default:
                # code...
                break;
        }
       
        return $this->getCustomerTimeLine($sdate,$edate,$searchType,$session,$dateDiff);
        
    }


    /**
    * @author Ankit Patidar <ankitpatidar@hostnsoft.com>
    * @since 7/7/2014
    * @abstract called from current class
    * @param int searchType 1 for date wise 2 for month wise
    * @dateDiff
    */
    function getCustomerTimeLine($sdate,$edate,$searchType=1,$session,$dateDiff=0)
    {
         //set date formats for sql query and next date
        switch ($searchType) {
            case 1:
                # code...
                    $sqlDateFormat = '%Y-%m-%d';
                    $nextDateFormat = 'days';
                    $dateCatFormat = 'Y-m-d';
                break;
            case 2:
                # code...
                    $sqlDateFormat = '%Y-%m';
                    $nextDateFormat = 'months';
                     $dateCatFormat = 'Y-m';
                break;
            
            default:
                # code...
                break;
        }

        //get chainId from session
        if(empty($session['chainId']))
        {
            return json_encode(array('status' => 'error','msg' => 'Your session has been expired, Please Login again!'));
        }

        $chainId = $session['chainId']; 

        $result = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration) as durationSum","91_calls","id_chain='".$chainId."' and  call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

        //set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));
       
       
        //error handling
        if(!$result)
        {
            trigger_error('problem while getting total call duration,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['totalCallDuration'] = array();
      
       $detailArr['dayArr'][] = $dateNo; 
        //this while will give the totalcallDuration array
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['totalCallDuration'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                $detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['totalCallDuration'][] =(float)($row['durationSum']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            $detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['totalCallDuration'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            $detailArr['dayArr'][]= $dateNo;
        }



        #####################customer minutes####################
         $resultRWise = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration) as durationSum","91_calls","id_chain LIKE '".$chainId."%' and id_chain<>'".$chainId."' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

         
        //set counter and datedayNo
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

        //error handling
        if(!$resultRWise)
        {
            trigger_error('problem while getting customer call duration,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['customerCallDuration'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultRWise->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['customerCallDuration'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['customerCallDuration'][] = (float)($row['durationSum']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['customerCallDuration'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }


        ##########################ACD##################################

         $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

         $resultAcd = $this->selectData("DATE_FORMAT(call_start, '".$sqlDateFormat."') as day,sum(duration)/count(id_call) as acd","91_calls","id_chain LIKE '".$chainId."' AND call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')");

        //error handling
        if(!$resultAcd)
        {
            trigger_error('problem while getting reseller ACD,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['acd'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultAcd->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['acd'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['acd'][] = (float)($row['acd']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['acd'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }


        #######################calculate ASR###########################
        $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

         $query = "select a.dateA as day,a.countA/b.countF as ASR from
(SELECT count(id_call) as countA,DATE_FORMAT(call_start, '".$sqlDateFormat."') as dateA FROM `91_calls` where status='ANSWERED' and id_chain LIKE '".$chainId."%' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."') ) a
cross join
(SELECT count(id_call) as countF,DATE_FORMAT(call_start, '".$sqlDateFormat."') as dateA FROM `91_calls` where status<>'ANSWERED' and id_chain LIKE '".$chainId."%' and call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(call_start, '".$sqlDateFormat."')) b where a.dateA = b.dateA";

        $resultAsr = $this->db->query($query);
        
         //$resultAcd = $this->selectData("DAYOFMONTH(call_start) as day,sum(duration)/count(id_call) as acd","91_calls","call_start between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DAYOFMONTH(call_start)");

        //error handling
        if(!$resultAsr)
        {
            trigger_error('problem while getting reseller ASR,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['asr'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultAsr->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['asr'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['asr'][] = (float)($row['ASR']/60); 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['asr'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }


        ################################Calculate Profit####################################
       
          $i = 0;
        $dateNo = date($dateCatFormat,strtotime($sdate));

         $resultProfit = $this->selectData("sum(profit) as summation,DATE_FORMAT(date, '".$sqlDateFormat."') as day","91_chainProfit","parentId = '".$chainId."' and date(date) between '".$sdate." 00:00:00' and '".$edate." 23:59:59' group by DATE_FORMAT(date, '".$sqlDateFormat."')");

        //error handling
        if(!$resultProfit)
        {
            trigger_error('problem while getting profit,qur:'.$this->querry);
            return json_encode(array('status' => 'error','msg' => 'Problem while getting details!!!'));
        }

       $detailArr['profit'] = array();
      
       
        //this while will give the totalcallDuration array
         while($row = $resultProfit->fetch_array(MYSQLI_ASSOC))
        {
          
            while($dateNo != $row['day'])
            {
                $detailArr['profit'][] = 0;
                $i++;
                $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
                //$detailArr['dayArr'][]= $dateNo;
            }

            $detailArr['profit'][] = $row['summation']; 
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }

        while($i < $dateDiff)
        {
            $detailArr['profit'][] = 0;
            $i++;
            $dateNo = date($dateCatFormat,strtotime($sdate.' +'.$i.' '.$nextDateFormat));
            //$detailArr['dayArr'][]= $dateNo;
        }




        return json_encode(array('status' => 'success','msg' => 'Record found!!!','data' => $detailArr));
       



    }
    
    function getRoutePrefixProfitDetail($request,$session)
    {

        if(empty($session['userid']))
            return json_encode(array());

        $userId = $session['userid'];
        //include route class
        include_once('routeClass.php');
        $routeObj = new routeClass();


        $dataJson = $routeObj->getRoutePrefixProfit($request,$userId);

        $routeResult = json_decode($dataJson,TRUE);
        $routeDetail = array();

        foreach ($routeResult as $key => $value) {
            # code...

//            $routeDetail[] = array('routeName' => $value['routeName'],
//                                    'routeProfit' => $value['profit'],
//                                     'prefix' => $value['prefix']);

            $routeDetail[$value['routeName']][] = array('routeProfit' => $value['profit'],
                                                  'prefix' => $value['prefix']);

            
            // $routeDetail['routeName'][] = $value['routeName'];
            // $routeDetail['routeClosingAmt'][] =$value['routeClosingAmt'];
            // $routeDetail['currency'][] = $value['currency']; 

        }
       


       return json_encode($routeDetail);

    }



    
}
?>
