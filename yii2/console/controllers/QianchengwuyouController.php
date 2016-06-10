<?php
namespace console\controllers;
 

use Yii;
use yii\base\InvalidParamException;
use backend\models\Qianchengwuyou;
use yii\console\Controller;
 
class QianchengwuyouController extends Controller{
	$url = "http://search.51job.com/jobsearch/search_result.php?fromJs=1&jobarea=000000%2C00&funtype=0000&industrytype=00&keyword=%E7%BD%91%E7%BB%9C%E5%B7%A5%E7%A8%8B&keywordtype=0&lang=c&stype=2&postchannel=0000&fromType=1&confirmdate=9";
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$response = curl_exec($ch);
	$result = json_decode($response,true);
	curl_close($ch);
	print_r($result,true);
}




