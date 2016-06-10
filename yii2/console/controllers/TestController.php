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
		// �ȴ��ӽ���ִ����ϣ����⽩ʬ����
		$n = 0;
		while ($n < 2) {
			$nStatus = -1;
			$nPID = \pcntl_wait($nStatus);
			if ($nPID > 0) {
				++$n;
			}
		}
	}
//����1���� ����redis�����Ӳ��Ե�ʱ�� Ϊʲô�������� ������������� incr����Ŀһ������Ϊredis�ﵽ��ƿ��
//���ͨ��sleep(1)�ᷢ�� �������Ǿ��𵽶���̵�Ч�� ��Ϊredis��û�е���ƿ������ʱʹ��redis���������

//����2����Ҳ��Ч�� oh yeah ���ڸ㶮��~~~~~~~~~~
	
	public function actionWork($str){
		while(true){
			$pid = pcntl_fork();
			if($pid == 0){
				sleep(1);
				$redis = Yii::$app->redis;
			/**����1**/
			//	$ret = $redis->incr($str);
			
			/**����2**/
				$ret = $redis->incr("ceshic");
				exit(0);
			}				
			$status = -1;
			pcntl_waitpid($pid,$status);
		}
	}
	
	public function actionKeyp(){
		$spiderStartTime = time();//���濪ʼʱ��
		set_time_limit(0);
		ignore_user_abort(true);
		$redis = Yii::$app->redis;
		/*��redis�еĸ����ݽ��г�ʼ��*/
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
		$p = '���ܲ�';
		$redis = Yii::$app->redis;
		$redis->set('keyp',$p);
	}
}