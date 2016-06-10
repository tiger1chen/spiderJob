<?php
namespace console\controllers;
use Yii;
use yii\base\InvalidParamException;
use yii\console\Controller;
 
class TestController extends Controller{
	public function actionTes(){
		echo "b";
	}
	
	public function actionMultiprocess(){
		
		for($i=0;$i<2;$i++){
			$pid = pcntl_fork();
			if($pid == 0){
				$key = getmypid();
				$str = 'twoProcess'.$key;				
				$this->actionWork($str);
				exit(0);
			}
		}		
		// 等待子进程执行完毕，避免僵尸进程
		$n = 0;
		while ($n < 2) {
			$nStatus = -1;
			$nPID = \pcntl_wait($nStatus);
			if ($nPID > 0) {
				++$n;
			}
		}
	}
//试验1表明 当用redis来增加测试的时候 为什么两个进程 和三个进程最后 incr的数目一样是因为redis达到了瓶颈
//如果通过sleep(1)会发现 这样他们就起到多进程的效果 因为redis还没有到达瓶颈。此时使用redis是有意义的

//试验2增加也有效果 oh yeah 终于搞懂了~~~~~~~~~~
	
	public function actionWork($str){
		while(true){
			$pid = pcntl_fork();
			if($pid == 0){
				sleep(1);
				$redis = Yii::$app->redis;
			/**试验1**/
			//	$ret = $redis->incr($str);
			
			/**试验2**/
				$ret = $redis->incr("ceshic");
				exit(0);
			}				
			$status = -1;
			pcntl_waitpid($pid,$status);
		}
	}
	
	public function actionKeyp(){
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
		$keyValue = $redis->get('keyp');
		foreach ($keyData as $k=>$v){
			if($v == $keyValue){
				break;
			}
			array_shift($keyData);
		}
		
		print_r($keyData);
	}
	public function actionStatic(){
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
	public function actionSet(){
		$p = '副总裁';
		$redis = Yii::$app->redis;
		$redis->set('keyp',$p);
	}
}