<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\Controller;
use frontend\models\Test;

/**
 * Site controller
 */
class SiteController extends Controller
{
   public function actionSay($message='Hello'){
		$redis = Yii::$app->redis;
		$b = $redis->HGET('ab','a');
		echo $b;
		if(empty($b)){
			echo 'kong';
		}
		if($b === false){
			echo 'false';
		}
		//$a = $redis->get('w');
		//echo $a;
   }
}
