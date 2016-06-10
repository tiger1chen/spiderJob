<?php
namespace backend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use backend\models\Spiderlagoudata;
class LagouController extends Controller
{
	public $procNum = 8;
	public function actionStatic(){//不是经常改变的页面，凌晨一点更新数据库和redis，进行检查更新 如果更新了发送邮件给站长	
		$url = 'http://www.lagou.com';
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$result = curl_exec($ch);
		curl_close($ch);
		$pattern = '/<a.*href=\"http:\/\/www.lagou.com\/zhaopin\/([a-zA-Z]+)\/\".*>(.*?)<\/a>/i';
 		preg_match_all($pattern,$result,$arr);
 		$keyData = array_unique($arr[2]);
 		$keyData = json_encode($keyData,JSON_UNESCAPED_UNICODE);
		$redis = Yii::$app->redis;
		$redis->set("Lagou::static",$keyData);		
	}
	public function actionRun(){
		for($i = 0; $i < $this->procNum; $i++){
			$nPID = \pcntl_fork();//创建子进程
			if ($nPID == 0){
				//子进程
				$this->work();
				exit(0);
			}
		}
		// 等待子进程执行完毕，避免僵尸进程
		$n = 0;
		while ($n < $this->procNum) {
			$nStatus = -1;
			$nPID = \pcntl_wait($nStatus);
			if ($nPID > 0) {
				++$n;
			}
		}
	}
	public function work(){
		while(true){
			$nPID = \pcntl_fork();//创建子进程
			if ($nPID == 0){
				$this->actionLagou();
				exit(0);
			}
			$nStatus = -1;
			\pcntl_waitpid($nPID,$nStatus);
		}
	}	
	public function actionLagou(){//只爬取今天的全部信息
		$spiderStartTime = time();//爬虫开始时间
		set_time_limit(0);
		ignore_user_abort(true);
		$redis = Yii::$app->redis;
		/*对redis中的各数据进行初始化*/
		$keyData = $redis->get("Lagou::static");
		$keyData = json_decode($keyData,true);
		if(empty($keyData)){
			$this->actionStatic();
			$keyData = $redis->get("Lagou::static");
			$keyData = json_decode($keyData,true);
		}
		if(empty($keyData)){
			return false;
		}
		foreach($keyData as $k=>$v){//循环首页职位
			$redis->set('keyp',$v);
			$ret = $redis->incr('LagouRecord::PositionTotal');
			$count = 1;
			$flag = 1;//标记该职位可以一直循环下去
			do{
				$ch = curl_init();//初始化一个curl会话
				$kd = $v;
				$bool = 'false';
				$pn = $count;
				$data = array('first'=>$bool,'kd'=>$kd,'pn'=>$pn);//传送的数据
				$url = 'http://www.lagou.com/jobs/positionAjax.json?px=new';//第二层页面的爬取
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST, 1); 
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
				$result = json_decode($response,true);//获取某职位第某页的结果
				curl_close($ch);
				sleep(0.3);
				if(empty($result['content']['result'])){//该职位的数据为空
					break;
				}
				foreach($result['content']['result'] as $k2=>$v2){//循环某职位某页所有招聘职位信息
					$ret = $redis->incr('LagouRecord::Total');
					/*校验 是否是当天发布的 校验该数据是否已经存在了*/
					$date = date("Y-m-d",time());//今天日期
					$checkDate = date("Y-m-d",intval($v2['createTimeSort'])/1000);//职位发布日期
					if($date != $checkDate){//如果查询到的数据发布时间不是今天的了就终止
						$ret = $redis->incr('LagouRecord::TotalLost');
						$flag = 0;
						break;
					}
					$redis->set($date,1);
					$redis->set($checkDate,1);
					$indexCheck = md5($v2['createTimeSort'].$v2['positionId'].$v2['companyId']);
					$hkey = date('y/m/d',time());
					$ret = $redis->hget($hkey,$indexCheck);
					if(!empty($ret)){//如果存在则数据不进行插入
						$ret = $redis->incr('LagouRecord::TotalLost');
						break;
					}
					
					
					/*先查询职位描述*/ 
					$ch2 = curl_init();
					$url2 = "http://www.lagou.com/jobs/".$v2['positionId'].".html";
					
					curl_setopt($ch2,CURLOPT_URL,$url2);
					curl_setopt($ch2, CURLOPT_POST, 1); 
					curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
					$response = curl_exec($ch2);
					$pattern = '/<dd\s*class=\"job_bt\">.+?<\/dd>/is';
					preg_match($pattern,$response,$match);
					if(empty($match)){
						$match[0] = '';
					}
					$jobDescribe =htmlspecialchars($match[0]);
					curl_close($ch2);
					sleep(0.3);
					/*进行插入数据表操作*/
					$spiderlagoudata = new Spiderlagoudata;
					$ret = genId(1,$id);//利用id生成器生成id
					if(!$ret){
						return false;
					}
					$spiderlagoudata->Cid = $id; 				
					$spiderlagoudata->CcompanyId = intval($v2['companyId']); 				
					$spiderlagoudata->CpositionId = intval($v2['positionId']); 				
					$spiderlagoudata->CpositionName = htmlspecialchars($v2['positionName']);				
					$spiderlagoudata->CcompanyName = htmlspecialchars($v2['companyName']); 				
					$spiderlagoudata->CcompanyShortName = htmlspecialchars($v2['companyShortName']); 				
					$spiderlagoudata->CpositionFirstType =htmlspecialchars($v2['positionFirstType']); 				
					$spiderlagoudata->CindustryField = htmlspecialchars($v2['industryField']); 				
					$spiderlagoudata->Ceducation = htmlspecialchars($v2['education']); 				
					$spiderlagoudata->CworkYear = htmlspecialchars($v2['workYear']); 				
					$spiderlagoudata->CjobNature = htmlspecialchars($v2['jobNature']); 				
					$spiderlagoudata->Ccity = htmlspecialchars($v2['city']); 				
					$spiderlagoudata->CpositionAdvantage = htmlspecialchars($v2['positionAdvantage'])?htmlspecialchars($v2['positionAdvantage']):''; 				
					$spiderlagoudata->Csalary =htmlspecialchars($v2['salary']); 				
					$spiderlagoudata->CleaderName = htmlspecialchars($v2['leaderName']); 				
					$spiderlagoudata->CcompanySize = htmlspecialchars($v2['companySize']); 				
					$spiderlagoudata->CfinanceStage = htmlspecialchars($v2['financeStage']); 				
					$spiderlagoudata->CcompanyLabelList = json_encode($v2['companyLabelList'],JSON_UNESCAPED_UNICODE); 				
					$spiderlagoudata->CcreateTimeSort = intval($v2['createTimeSort'])/1000; 				
					$spiderlagoudata->CsystemCreateTime =intval(time()); 							
					$spiderlagoudata->CjobDescribe = $jobDescribe; 	
					if($spiderlagoudata->save()>0){//如果存储数据成功
							$ret = $redis->incr('LagouRecord::TotalSuccess');
							$index = md5($v2['createTimeSort'].$v2['positionId'].$v2['companyId']);
							$hkey = date('y/m/d',time());
							$redis->hset($hkey,$index,1);//利用redis建立一张校验表，防止数据重复
							/*异步爬取评论内容*/
							$msgData = array(
								'positionId'=>$v2['positionId'],
								'pageSize'=>500,
								'lagouid'=>$id
							);
							$msgData = json_encode($msgData);
							$redis->lpush("Lagou::Comment",$msgData);			
					}else{
						$ret = $redis->incr('LagouRecord::TotalFail');
					}		
							
				}
				$count++;
			}while($flag>0);
		}
		$spiderEndTime = time();
		//对本次完整爬虫进行记录扫描进行记录
		//依次代表 几个职位信息个数-没插入的职位信息个数-成功插入的职位信息-没有成功插入的职位信息-开始时间-结束时间
		$a = $redis->get('LagouRecord::PositionTotal');
		$b = $redis->get('LagouRecord::TotalLost');
		$c = $redis->get('LagouRecord::TotalSuccess');
		$d = $redis->get('LagouRecord::TotalFail');
		$complete = strval($a).'-'.strval($b).'-'.strval($c).'-'.strval($d).'-'.strval($spiderStartTime).'-'.strval($spiderEndTime);
		$redis->lpush('Lagou::Spider::statistics',$complete);
	}
	
	public function actionTest(){
		$ch = curl_init();//初始化一个curl会话
		curl_setopt($ch,CURLOPT_URL,"http://www.lagou.com/jobs/1505926.html");
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$response = curl_exec($ch);
		$pattern = '/<dd\s*class=\"job_bt\">.+?<\/dd>/is';
		preg_match($pattern,$response,$match);
		print_r($match);
		curl_close($ch);
	}
	public function actionIdtest(){
		$ret = genId(1,$id);
		if($ret){
			echo $id;
		}
		
	}
	
	public function actionTimeout(){
		set_time_limit(0);
		ignore_user_abort(true);
		do{
			$a=1;
		}while(true);
	}
	
	
	public function actionTestredisvalue(){
		$redis = Yii::$app->redis;
		$ret = $redis->get('keyp');
		echo $ret;
	}
	public function actionTestre(){
		$ch = curl_init();//初始化一个curl会话
		$kd = '热传导';
		$bool = 'false';
		$pn = 1;
		$data = array('first'=>$bool,'kd'=>$kd,'pn'=>$pn);//传送的数据
		$url = 'http://www.lagou.com/jobs/positionAjax.json?px=new';//第二层页面的爬取
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	
		$response = curl_exec($ch);	
		$result = json_decode($response,true);//获取某职位第某页的结果
		curl_close($ch);			
	//	echo $result;
		print_r($result);	
		echo 3;
	}
	
	public function actionTestcomment(){
		$ch = curl_init();//初始化一个curl会话
		$pageSize = 500;
		$positionId = 738772;
		$data = array('positionId'=>$positionId,'pageSize'=>$pageSize);//传送的数据
		$url = 'http://www.lagou.com/interview/experience/byPosition.json';//第二层页面的爬取
		$referer = 'http://www.lagou.com/jobs/738772.html';
		$headers = array('Host:www.lagou.com',$referer,'X-Requested-With:XMLHttpRequest');
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);
		$result = json_decode($response,true);		
		print_r($result);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		echo $httpCode;
		curl_close($ch);
		echo 6;
	}
	
	public function actionTestbrpop(){
		$redis = Yii::$app->redis;
		$arr = array('brpoptest');
		$count = 0;
	//	$redis->setOption(Redis::OPT_READ_TIMEOUT, 0);
		$ret = $redis->blpop('brpoptest',0);
		$redis->incr('oo');
		print_r($ret);
	}
	
	public function actionTestfork(){
		$pid = pcntl_fork();
		echo $pid;
		$redis = Yii::$app->redis;
		if($pid == 0){
			echo 'children';
			$redis->incr('gg');
		
		}else{
			echo 'parentId'.$pid;
			$redis->incr('gg');
			//exit(0);
		}
	}
	
	public function actionCli(){
		echo 3;
	}
	public function actionTestoffset(){
		$sub = 'abc';
		$pattern = '/^d$/';
		preg_match($pattern,$sub,$ar);
		if(empty($ar)){
			$ar[0] = '';
		}
		$a = htmlspecialchars($ar[0]);
		echo $a;
	}
	
	public function actionTestshutdown(){
		set_time_limit(0);
		register_shutdown_function([$this,'dealshutdown']);
	      set_error_handler([$this,'dealshutdown']);
	      set_exception_handler([$this,'dealshutdown']);		
		while(true){
			$count = 1;
		}
	}
	public function dealshutdown(){
		$redis = Yii::$app->redis;
		$redis->incr('shutdownNum');
	}
	
	public function actionTestping(){
		$a = strval(1);
		$b = strval(2);
		$c = $a.'-'.$b;
		echo $c;
	}
}