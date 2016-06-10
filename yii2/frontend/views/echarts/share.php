<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div id="main" style="height:400px"></div>
<?=Html::jsFile('@web/echarts/build/dist/echarts.js')?>
<script>
// 路径配置
require.config({
    paths: {
        echarts: './echarts/build/dist'
    }
});
// 使用
require(
    [
        'echarts',
        'echarts/chart/treemap' // 使用柱状图就加载bar模块，按需加载
    ],
    function (ec) {
        // 基于准备好的dom，初始化echarts图表
        var myChart = ec.init(document.getElementById('main')); 

		function createRandomItemStyle() {
		    return {
		        normal: {
		            color: 'rgb(' + [
		                Math.round(Math.random() * 160),
		                Math.round(Math.random() * 160),
		                Math.round(Math.random() * 160)
		            ].join(',') + ')'
		        }
		    };
		}        
        
	var option = {
		title : {
			text: '各网络专业占有率',
			subtext: '5月4日拉钩网数据'
		},
		tooltip : {
			trigger: 'item',
			formatter: "{b}: {c}"
		},
		toolbox: {
			show : true,
			feature : {
				mark : {show: true},
				dataView : {show: true, readOnly: false},
				restore : {show: true},
				saveAsImage : {show: true}
			}
		},
		calculable : false,
		series : [
			{
				name:'矩形图',
				type:'treemap',
				itemStyle: {
					normal: {
						label: {
							show: true,
							formatter: "{b}"
						},
						borderWidth: 1
					},
					emphasis: {
						label: {
							show: true
						}
					}
				},
				data:[
					{
						name: '网络工程',
						value: <?=$wangluogongcheng?>
					},
					{
						name: '网络技术支持',
						value: <?=$jishuzhichi?>
					},
					{
						name: '网络运维',
						value: <?=$yunwei?>
					},
					{
						name: '网络测试',
						value: <?=$ceshi?>
					},
					{
						name: '系统集成',
						value: <?=$jicheng?>
					},
					{
						name: '网络管理员',
						value: <?=$guanliyuan?>
					}
				]
			}
		]
	};
                
        // 为echarts对象加载数据 
        myChart.setOption(option); 
    }
);		
</script>
</body>
</html>
<?php $this->endPage() ?>
