<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use frontend\models\Spiderlagoudata;

/**
 * Site controller
 */
class EchartsController extends Controller
{
	public function actionTest(){
		$connection  = Yii::$app->db;
		$sql = "select a.CcompanyLabelList from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络工程%' ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$result     = $command->queryAll();
		$companyLabeList = array();//公司标签
		foreach($result as $key=>$value){
			if(empty($value['CcompanyLabelList'])){
				continue;
			}
			$tmp = array();//对数据设置为空
			$tmp = json_decode($value['CcompanyLabelList']);
			foreach($tmp as $m=>$n){
				if(array_key_exists($n,$companyLabeList)){//如果存在该key值
					++$companyLabeList[$n];
				}else{
					$companyLabeList[$n]=1;
				}
			}
		}
		$count = 0;
		foreach($companyLabeList as $k=>$v){
			if($v<=4){//过小的数据进行排除
				unset($companyLabeList[$k]);
				continue;
			}
			$count +=$v;
		}
		return $this->render('test',['companyLabeList'=>$companyLabeList,'count'=>$count]);
	}
	
	public function actionCity(){
		$connection  = Yii::$app->db;
		$sql = "select a.Ccity from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络工程%' ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$result     = $command->queryAll();
		$city = array();//公司标签
		foreach($result as $key=>$value){
			if(array_key_exists($value['Ccity'],$city)){//如果存在该key值
				++$city[$value['Ccity']];
			}else{
					$city[$value['Ccity']]=1;
			}			
		}
		return $this->render('city',['city'=>$city]);		
	}
	
	
	public function actionWorkyear(){
		$connection = Yii::$app->db;
		$sql = "select a.CworkYear,a.Csalary from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络工程%' ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$result     = $command->queryAll();
		
		/*对数据进行初始化*/
		$five = array();
		$fiveE = array();
		$eightT = array();
		$tenF = array();
		$fifteeT = array();
		$twentyT = array();
		$thirty = array();
		foreach($result as $k=>$v){
			if(strpos($v['Csalary'],'-')){
				list($minSalary,$maxSalary) = explode('-',$v['Csalary']);
			}else{
				$minSalary = rtrim($v['Csalary'],'以上');
				$maxSalary = 80;
			}
			$minSalary = rtrim($minSalary,'k');
			$maxSalary = rtrim($maxSalary,'k');
			
			if($minSalary<5 || $maxSalary<5){
				$this->sumCompany($v['CworkYear'],$five);
			}
			
			if(($minSalary>=5 && $minSalary<8)||($maxSalary>=5&&$maxSalary<8)){
				$this->sumCompany($v['CworkYear'],$fiveE);
			}
			
			if(($minSalary>=8 && $minSalary<10)||($maxSalary>=8 && $maxSalary<10)){
				$this->sumCompany($v['CworkYear'],$eightT);
			}
			
			if(($minSalary>=10 && $minSalary<15)||($maxSalary>=10&&$maxSalary<15)){
				$this->sumCompany($v['CworkYear'],$tenF);
			}
			if(($minSalary>=15 && $minSalary<20)||($maxSalary>=15&&$maxSalary<20)){
				$this->sumCompany($v['CworkYear'],$fifteeT);
			}
			if(($minSalary>=20 && $minSalary<30)||($maxSalary>=20&&$maxSalary<30)){
				$this->sumCompany($v['CworkYear'],$twentyT);
			}			
			if($minSalary>=30||$maxSalary>=30){
				$this->sumCompany($v['CworkYear'],$thirty);
			}				
		}
		$five = json_encode($five);
		$fiveE = json_encode($fiveE);
		$eightT = json_encode($eightT);
		$tenF = json_encode($tenF);
		$fifteeT = json_encode($fifteeT);
		$twentyT = json_encode($twentyT);
		$thirty = json_encode($thirty);		
		return $this->render('workyear',['five'=>$five,'fiveE'=>$fiveE,'eightT'=>$eightT,'tenF'=>$tenF,'fifteeT'=>$fifteeT,'twentyT'=>$twentyT,'thirty'=>$thirty]);
	}
	
	
	public function actionShare(){
		$connection = Yii::$app->db;
		$sql = "select count(*) as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络工程%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000 ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);	
		$wangluogongcheng     = $command->queryAll();
		$sql = "select count(*) as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络技术支持%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000 ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$jishuzhichi     = $command->queryAll();
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络运维工程师%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000 ) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);	
		$yunwei     = $command->queryAll();
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络测试%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$ceshi     = $command->queryAll();
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%系统集成%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$jicheng     = $command->queryAll();		
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%网络管理员%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);	
		$guanliyuan     = $command->queryAll();		
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%售前技术支持%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);
		$shouqianjishuzhichi     = $command->queryAll();
		$sql = "select count(*)  as count from by_spiderlagoudata as a  inner join (select Cid from by_spiderlagoudata  where CpositionName like '%售前售后技术支持%' and CcreateTimeSort> 1462312800  and CcreateTimeSort<1462374000) as b on a.Cid=b.Cid";
		$command = $connection->createCommand($sql);	
		$shouhoujishuzhichi     = $command->queryAll();
		$jishuzhichi = $jishuzhichi+$shouqianjishuzhichi+$shouhoujishuzhichi;
		return $this->render('share',['wangluogongcheng'=>$wangluogongcheng[0]['count'],'jishuzhichi'=>$jishuzhichi[0]['count'],'ceshi'=>$ceshi[0]['count'],'guanliyuan'=>$guanliyuan[0]['count'],'yunwei'=>$yunwei[0]['count'],'jicheng'=>$jicheng[0]['count']]);
	}
	
	public function sumCompany($workyear,&$arr){
			if(empty($arr)){
				for($i=0;$i<7;$i++){
					$arr[$i] = 0;
				}
			}
			switch($workyear){
				case '不限':
					++$arr[0];
					break;
				case '应届毕业生':
					++$arr[1];
					break;
				case '1年以下':
					++$arr[2];
					break;
				case '1-3年':
					++$arr[3];
					break;
				case '3-5年':
					++$arr[4];
					break;
				case '5-10年':
					++$arr[5];
					break;
				case '10年以上':
				    ++$arr[6];
					break;
				default:
					$arr[7] = $workyear;
					break;
			}
	}
}