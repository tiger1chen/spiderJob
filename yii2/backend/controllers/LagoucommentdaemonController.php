<?php
namespace backend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use backend\models\Spiderlagoucomment;
class LagoucommentdaemonController extends Controller
{
	public $procNum = 2;
	public function actionRun(){
		$redis = Yii::$app->redis;
		for($i = 0; $i < $this->procNum; $i++){
			$nPID = \pcntl_fork();//创建子进程
			$redis->lpush('runPID',$nPID);
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
			$redis = Yii::$app->redis;
			$redis->incr('mainProcess');
			$redis->lpush('mainProcessId',$nPID);
			$nPID = \pcntl_wait($nStatus);
			$redis->lpush('runPID2',$nPID);
			if ($nPID > 0) {
				++$n;
			}
		}
	}
	public function work(){
		while(true){
			$nPID = \pcntl_fork();//创建子进程
			$redis = Yii::$app->redis;
			$redis->lpush("nPID1",$nPID);
			$redis->incr('mm');
			if ($nPID == 0){
				$redis = Yii::$app->redis;
				$arr = array();
				$delMsg = $redis->brpop('Lagou::Comment',0);
				$delMsg = (array)json_decode($delMsg[1]);
				$this->actionLagoucomment($delMsg);
				$redis->incr("jincheng");
				exit(0);
			}
			//$nStatus = -1;

			//$pwaitid = \pcntl_waitpid($nPID,$nStatus);
		}
		$redis->incr("jincheng4");
	}

	public function actionLagoucomment($conditionData){
		$ch = curl_init();//初始化一个curl会话
		$pageSize = $conditionData['pageSize'];
		$positionId = $conditionData['positionId'];
		$data = array('pageSize'=>$pageSize,'positionId'=>$positionId);//传送的数据
		$url = 'http://www.lagou.com/interview/experience/byPosition.json';
		$referer = 'http://www.lagou.com/jobs/'.$positionId.'.html';
		$headers = array('Host:www.lagou.com',$referer,'X-Requested-With:XMLHttpRequest');
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);		
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);
		$result = json_decode($response,true);
		curl_close($ch);
		$redis = Yii::$app->redis;
		$redis->incr('Spiderlagoucomment::run');
		$redis->lpush('runData',$response);
		if(!empty($result['content']['data']['data']['result'])){
			foreach($result['content']['data']['data']['result'] as $k=>$v){
				$redis->incr('Spiderlagoucomment::run::have');
				$spiderlagoucomment = new Spiderlagoucomment;
				$spiderlagoucomment->CcommentId = intval($v['id']); 	
				$spiderlagoucomment->CpositionId = intval($v['positionId']); 	
				$spiderlagoucomment->Clagouid = intval($conditionData['lagouid']); 	
				$spiderlagoucomment->CuserName = $v['username']; 	
				$spiderlagoucomment->CisAnonymous = $v['isAnonymous']=='false'; 	
				$spiderlagoucomment->CisInterview = $v['isInterview']=='false'; 	
				$spiderlagoucomment->CusefulCount = intval($v['usefulCount']); 	
				$spiderlagoucomment->CmyScore = intval($v['myScore']); 	
				$spiderlagoucomment->CdescribeScore = intval($v['describeScore']); 	
				$spiderlagoucomment->CinterviewerScore = intval($v['interviewerScore']); 	
				$spiderlagoucomment->CcompanyScore = intval($v['companyScore']); 	
				$spiderlagoucomment->CcomprehensiveScore = $v['comprehensiveScore']; 	
				$spiderlagoucomment->Ccontent = $v['content']; 	
				$spiderlagoucomment->CcompanyName = $v['companyName']; 	
				$spiderlagoucomment->CpositionName = $v['positionName']; 	
				$spiderlagoucomment->CpositionType = $v['positionType']; 	
				$spiderlagoucomment->CcompanyId = intval($v['companyId']); 	
				$spiderlagoucomment->Ctags = json_encode($v['tags'],JSON_UNESCAPED_UNICODE); 	
				$spiderlagoucomment->CcreateTime = $v['createTime']; 	
				$spiderlagoucomment->CsystemCreateTime = intval(time()); 	
				$spiderlagoucomment->ClastModifyTime = intval(time()); 	
				$spiderlagoucomment->CnoInterviewReason = $v['noInterviewReason']; 	
				if($spiderlagoucomment->save()>0){
					$redis = Yii::$app->redis;
					$redis->incr("commentRecordSuccess");
					return;
				}else{
					$redis = Yii::$app->redis;
					$redis->lpush("commentRecordFail:commentId",$v['id']);
					return;
				}
			}
		}else{
			$redis->incr('Spiderlagoucomment::run::none');
			return;
		}		
		return;
	}
}