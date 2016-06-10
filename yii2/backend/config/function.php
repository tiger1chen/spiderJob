<?php
/*IDÉú³ÉÆ÷*/
function genId($type,&$id){
	if($type == 1){
		$key = "LagouData";
	}
	$redis = Yii::$app->redis;
	$ret = $redis->incr($key);
	if($ret === false){
		return false;
	}
	if($type == 1){
		$id = $ret+10000;
	}
	return true;
}