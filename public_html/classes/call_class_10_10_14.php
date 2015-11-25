<?php
/**
 * @Author Rahul <rahul@hostnsoft.com>
 * @createdDate 03-06-13
 * @package Phone91
 * @name call_class
 * @details Class contains all function related to make call and call history
 * 
 */
include dirname(dirname(__FILE__)).'/config.php';
class call_class extends fun
{
	function Call($paramarg) 
        {
            /***
             * @Author Rahul <rahul@hostnsoft.com> 
             * * @createdDate 08-08-13
             * @details developed by vikas use to hit a url for twoway calling
             */
            
            $startTimTracker = date(DATEFORMAT);
            //call tracker
            $trackId = $this->callTracker(null,$startTimTracker,'','user reached at call function',$paramarg,__FILE__,__LINE__);
            
             $startTimTracker = date(DATEFORMAT);
            //callServerUrl is defined in definepath page.
            $connect_url = CALLSERVERURL."clicktocall2.php"; // Do not change	
            $param["src_no"] = $paramarg["source"];
            $param["des_no"]=$paramarg["dest"];
            $param["username"] = $paramarg["login"]; 
            $param["password"] = $paramarg["password"]; 
            $param["type"]="1";
            $request="";
            
            
            foreach($param as $key=>$val)
            { 
                $request.= $key."=".urlencode($val);
                $request.= "&";
            }
            
            $request = substr($request, 0, strlen($request)-1);
            
            $url2 = $connect_url."?".$request;
            
            $trackMsg = 'Curl detail for calling';
            
            $trackDtl['url'] = $url2;
            
            $trackDtl['callDetails'] = $paramarg;
             //call tracker
            $trackId = $this->callTracker($trackId,$startTimTracker,'',$trackMsg,$trackDtl,__FILE__,__LINE__);
            
//            $url2 = urlencode($url2);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url2);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $curl_scraped_page = curl_exec($ch);
            curl_close($ch);
            return $curl_scraped_page;
	}
        
	function callResponse($param,$userId)
	{
		/***
                * @Author Rahul <rahul@hostnsoft.com> 
                * * @createdDate 08-08-13
                * @details grep active call status of a provided call uniqueId or if call is completed then search in call table
                 * currencly function not validating user as uniqueId is very random so we are not putiing condition of user check.   here
                */
		$uniqueId=trim($param["uniqueId"]) ;
                
                
                if(preg_match(NOTNUM_REGX,$uniqueId) || empty($uniqueId))
                    return json_encode(array("status" => "error", "msg" => "Error Invalid call id please enter proper value"));    
                
                if(preg_match(NOTNUM_REGX,$userId) || empty($userId))
                    return json_encode(array("status" => "error", "msg" => "Invalid user please login again"));    
                
		$table = '91_currentCalls';
                $condition = "uniqueId = '" . $uniqueId. "' and id_client =".$userId;
		$this->db->select('status,call_start')->from($table)->where($condition);
		$resCheck = $this->db->execute();
		$count =$resCheck->num_rows ;
			
		if($count>0){
			while ($get_userinfo= $resCheck->fetch_array(MYSQL_ASSOC) ) {				
			$status = $get_userinfo['status'];
			$datetime1 = date('Y-m-d H:i:s',strtotime($get_userinfo['call_start']));
			$datetime2 = date('Y-m-d H:i:s');

			$ans = (strtotime($datetime2) - strtotime($datetime1) - strtotime(date('H:i:s','5:30:00')) );

			$timeYet = gmdate('H:i:s',$ans);
			//calculate time
			return json_encode(array("status" => "success", "msg" => $status,'timeYet' => $timeYet));
			}
		}
		else{
			
			$table = '91_calls';
			$this->db->select('*')->from($table)->where($condition);
			//echo $this->db->getQuery();
			$resCheck = $this->db->execute();
			//var_dump($resCheck2);
			$count =$resCheck->num_rows ;	

			if($count>0){
				while ($get_userinfo= $resCheck->fetch_array(MYSQLI_ASSOC) ) {				
					$status = $get_userinfo['status'];
					$datetime1 = date('Y-m-d H:i:s',strtotime($get_userinfo['call_start']));
					$datetime2 = date('Y-m-d H:i:s');

					$ans = (strtotime($datetime2) - strtotime($datetime1)-strtotime(date('H:i:s','5:30:00')) );

					$timeYet = gmdate('H:i:s',$ans);
					
					return json_encode(array("status" => "success", "msg" => $status,'timeYet' => $timeYet));
				}
			}
			else {
				return json_encode(array("status" => "error", "msg" =>  "status not exist for this ID\n"));;
			}
		}
	}
	function uniqueId($id_client)
	{
		$random = rand(100000000,1000000000);
		return $id_client.$random.date('dm');
	}
        
        
        #modified by sudhir padney (sudhir@hostnsoft.com)
        #modification date 05-08-2013
        #function use for show call rates 
        function seeCallRate($parm,$tariffId){
        
           #check for valid contact no or not  
           if (!preg_match(PHNNUM_REGX, $parm["source"]) || empty($parm["source"])){  
               $this->msg = "Source Number are not valid!";
               $this->status = "error";
               $this->code = "330";
            return 0;
            }   
           
//           #check for valid contact no or not  
           if (!preg_match(PHNNUM_REGX, $parm["destination"]) || empty($parm["destination"])){  
               $this->msg = "Destination Number are not valid!";
               $this->status = "error";
               $this->code = "331";
               return 0;
            } 
            
           if (preg_match(NOTNUM_REGX, $tariffId) || empty($tariffId)){  
               $this->msg = "Invalid tariff id please provide a valid tariff Id!";
               $this->status = "error";
               $this->code = "332";
               return 0;
            } 
            
           $source = $parm["source"];
           $dest = $parm["destination"];
           $callrate_src =  $this->getCallRate($source,$tariffId);
           $callrate_des =  $this->getCallRate($dest,$tariffId);
           $callrate = $callrate_src + $callrate_des;
           
            #get currency name 
            include_once CLASS_DIR.'plan_class.php';
            $planObj = new plan_class();
            #call function manageClients and return json data clientJson
            $curency=$planObj->getOutputCurrency($tariffId);
            if(!$curency){
               $this->msg = "Error fetching output currency";
               $this->status = "error";
               $this->code = "333";
               return 0;
            }
            $currencyName = $planObj->getCurrencyViaApc($curency,1);
            if(!$currencyName)
            {
               $this->msg = "Error fetching  currency details";
               $this->status = "error";
               $this->code = "333";
               return 0;
            }
            $data = array("rate"=>$callrate,"currencyName"=>$currencyName);
            $this->setData($data);
            
            return true;
        }
        
        function getCallRate($number,$id_tariff)
	{
		$i=strlen($number);
		$prefix='';
		for($i;$i>0;$i--)
		{
			$number=substr($number,0, $i);
			$prefix.= "'".$number."',";
		}
		$prefix=substr($prefix, 0,strlen($prefix)-1);
                
                $table = "91_tariffs";
                $this->db->select('voiceRate,prefix')->from($table)->where("tariffId='".$id_tariff."' AND prefix in (".$prefix.") order by length(prefix) desc limit 1");
                $this->db->getQuery();
                
                $result = $this->db->execute();

                if ($result->num_rows > 0) {
                         $row = $result->fetch_array(MYSQL_ASSOC);                             
                           extract($row);
                               return $voiceRate;
                     }
	}
        
        function getMyCall($userId,$start,$limit){
            
            /***
             * @Author Rahul <rahul@hostnsoft.com> 
             * * @createdDate 08-08-13
             * @details developed by vikas use to hit a url for twoway calling
             */
            
            $table = '91_calls';
            $this->db->select('*')->from($table)->where("id_client = '" . $userId . "' ")->limit($limit)->offset($start);;
            //echo $this->db->getQuery();
            $resCheck = $this->db->execute();
            //var_dump($resCheck2);
            $count =$resCheck->num_rows ;	

            if($count>0){
                    while ($get_userinfo= $resCheck->fetch_array(MYSQL_ASSOC) ) {				
//                            $status = $get_userinfo['reason'];
                            $callInfo[]=$get_userinfo;
                    }
            }
            else {
                    return json_encode(array("status" => "error", "msg" =>  "status not exist for User\n"));;
            }
             return json_encode(array("status" => "success", "msg" => "","callData"=>$callInfo));
        }
        
        

}//end of class
?>
