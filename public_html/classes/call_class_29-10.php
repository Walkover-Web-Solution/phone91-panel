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
	function Call($paramarg) {
            /***
             * @Author Rahul <rahul@hostnsoft.com> 
             * * @createdDate 08-08-13
             * @details developed by vikas use to hit a url for twoway calling
             */
            //callServerUrl is defined in definepath page.
                $connect_url = CALLSERVERURL."clicktocall2.php"; // Do not change	
		$param["src_no"] = $paramarg["source"];
		$param["des_no"]=$paramarg["dest"];
		$param["username"] = $paramarg["login"]; 
		$param["password"] = $paramarg["password"]; 
		$param["type"]="1";
		$request="";
		foreach($param as $key=>$val){ 
		$request.= $key."=".urlencode($val);
		$request.= "&";
		}
		$request = substr($request, 0, strlen($request)-1);
		$url2 = $connect_url."?".$request;
		$ch = curl_init($url2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$curl_scraped_page = curl_exec($ch);
		curl_close($ch);
		return $curl_scraped_page;
	}
        
	function callResponse($param)
	{
		/***
                * @Author Rahul <rahul@hostnsoft.com> 
                * * @createdDate 08-08-13
                * @details grep active call status of a provided call uniqueId or if call is completed then search in call table
                 * currencly function not validating user as uniqueId is very random so we are not putiing condition of user check.   here
                */
		$uniqueId=$param["uniqueId"] ;
		$table = '91_currentCalls';
		$this->db->select('*')->from($table)->where("uniqueId = '" . $uniqueId. "' ");
		$resCheck = $this->db->execute();
		$count =$resCheck->num_rows ;
			
		if($count>0){
			while ($get_userinfo= $resCheck->fetch_array(MYSQL_ASSOC) ) {				
			$status = $get_userinfo['status'];
			return json_encode(array("status" => "success", "msg" => $status));
			}
		}
		else{
			
			$table = '91_calls';
			$this->db->select('*')->from($table)->where("uniqueId = '" . $uniqueId . "' ");
			//echo $this->db->getQuery();
			$resCheck = $this->db->execute();
			//var_dump($resCheck2);
			$count =$resCheck->num_rows ;	

			if($count>0){
				while ($get_userinfo= $resCheck->fetch_array(MYSQL_ASSOC) ) {				
					$status = $get_userinfo['reason'];
					return json_encode(array("status" => "success", "msg" => $status));
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
        function seeCallRate($parm,$id_tariff){
        
           #check for valid contact no or not  
           if (!preg_match("/^[0-9]{8,15}$/", $parm["source"])){  
            return json_encode(array("status"=>"error","msg"=>" Source Number are not valid!"));
            }   
           
//           #check for valid contact no or not  
           if (!preg_match("/^[0-9]{5,15}$/", $parm["destination"])){  
            return json_encode(array("status"=>"error","msg"=>" Destination Number are not valid!"));
            } 
            
           $source = $parm["source"];
           $dest = $parm["destination"];
           $callrate_src =  $this->getCallRate($source,$id_tariff);
           $callrate_des =  $this->getCallRate($dest,$id_tariff);
           $callrate = $callrate_src + $callrate_des;
           
            #get currency name 
            include_once CLASS_DIR.'plan_class.php';
            $planObj = new plan_class();
            #call function manageClients and return json data clientJson
            $curency=$planObj->getOutputCurrency($id_tariff);
            $funobj = new fun();
            $currencyName = $funobj->getCurrencyViaApc($curency,1);
           
            return json_encode(array("rate"=>$callrate,"currencyName"=>$currencyName));
            
//           if($callrate > 0)
//           {
//               return json_encode(array("rate"=>$callrate));
//           }
//           else
//              return json_encode(array("rate"=>$callrate));
           
           
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
