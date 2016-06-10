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
        'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
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
				text: '各家公司给予网络工程师的薪资待遇',
				subtext: '月薪'
			},
			tooltip : {
				trigger: 'axis'
			},
			legend: {
				data:[
					'5k以下','5-8k','8-10k','10-15k',
					'15-20k','20-30k','30k以上'
				]
			},
			toolbox: {
				show : true,
				feature : {
					mark : {show: true},
					dataView : {show: true, readOnly: false},
					magicType : {show: true, type: ['line', 'bar']},
					restore : {show: true},
					saveAsImage : {show: true}
				}
			},
			calculable : true,
			grid: {y: 70, y2:30, x2:20},
			xAxis : [
				{
					type : 'category',
					data : ['不限经验','应届毕业生','一年以下','1-3年','3-5年','5-10年','10年以上']
				},
				{
					type : 'category',
					axisLine: {show:false},
					axisTick: {show:false},
					axisLabel: {show:false},
					splitArea: {show:false},
					splitLine: {show:false},
					data : ['Line','Bar','Scatter','K','Map']
				}
			],
			yAxis : [
				{
					type : 'value',
					axisLabel:{formatter:'{value} 家'}
				}
			],
			series : [
				{
					name:'5k以下',
					type:'bar',
					itemStyle: {normal: {color:'rgba(193,35,43,1)', label:{show:true}}},
					data:<?=$five?>
				},
				{
					name:'5-8k',
					type:'bar',
					itemStyle: {normal: {color:'rgba(181,195,52,1)', label:{show:true,textStyle:{color:'#27727B'}}}},
					data:<?=$fiveE?>
				},
				{
					name:'8-10k',
					type:'bar',
					itemStyle: {normal: {color:'rgba(252,206,16,1)', label:{show:true,textStyle:{color:'#E87C25'}}}},
					data:<?=$eightT?>
				},
				{
					name:'10-15k',
					type:'bar',
					xAxisIndex:1,
					itemStyle: {normal: {color:'rgba(193,35,43,0.5)', label:{show:true,formatter:function(p){return p.value > 0 ? (p.value +'\n'):'';}}}},
					data:<?=$tenF?>
				},
				{
					name:'15-20k',
					type:'bar',
					xAxisIndex:1,
					itemStyle: {normal: {color:'rgba(181,195,52,0.5)', label:{show:true}}},
					data:<?=$fifteeT?>
				},
				{
					name:'20-30k',
					type:'bar',
					xAxisIndex:1,
					itemStyle: {normal: {color:'rgba(181,195,52,0.5)', label:{show:true}}},
					data:<?=$twentyT?>
				},				
				{
					name:'30k以上',
					type:'bar',
					xAxisIndex:1,
					itemStyle: {normal: {color:'rgba(252,206,16,0.5)', label:{show:true,formatter:function(p){return p.value > 0 ? (p.value +'+'):'';}}}},
					data:<?=$thirty?>
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
