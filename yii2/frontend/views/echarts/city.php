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
        'echarts/chart/pie',
        'echarts/chart/map'
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
				text: '网络工程全国分布图',
				subtext: '数据来自拉钩网'
			},
			tooltip : {
				trigger: 'item'
			},
			legend: {
				x:'right',
				selectedMode:false,
				data:['北京','上海','广东']
			},
			dataRange: {
				orient: 'horizontal',
				min: 0,
				max: 55000,
				text:['高','低'],           // 文本，默认为数值文本
				splitNumber:0
			},
			toolbox: {
				show : true,
				orient: 'vertical',
				x:'right',
				y:'center',
				feature : {
					mark : {show: true},
					dataView : {show: true, readOnly: false}
				}
			},
			series : [
				{
					name: '2011全国GDP分布',
					type: 'map',
					mapType: 'china',
					mapLocation: {
						x: 'left'
					},
					selectedMode : 'multiple',
					itemStyle:{
						normal:{label:{show:true}},
						emphasis:{label:{show:true}}
					},
					data:[
						{name:'西藏', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'青海', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'宁夏', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'海南', value:<?=isset($city['海口'])?$city['海口']*1000:1?>},
						{name:'甘肃', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'贵州', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'新疆', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'云南', value:<?=isset($city['昆明'])?$city['昆明']*1000:1?>},
						{name:'重庆', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'吉林', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'山西', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'天津', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'江西', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'广西', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'陕西', value:<?=isset($city['西安'])?$city['西安']*1000:1?>},
						{name:'黑龙江', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'内蒙古', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'安徽', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'北京', value:<?=$city['北京']*1000?>, selected:true},
						{name:'福建', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'上海', value:<?=$city['上海']*1000?>, selected:true},
						{name:'湖北', value:<?=isset($city['武汉'])?$city['武汉']*1000:1?>},
						{name:'湖南', value:<?=isset($city['长沙'])?$city['长沙']*1000:1?>},
						{name:'四川', value:<?=isset($city['成都'])?$city['成都']*1000:1?>},
						{name:'辽宁', value:<?=isset($city['大连'])?$city['大连']*1000:1?>},
						{name:'河北', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'河南', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'浙江', value:<?=isset($city['杭州'])?$city['杭州']*1000:1?>},
						{name:'山东', value:<?=isset($city['西藏'])?$city['西藏']:1?>},
						{name:'江苏', value:<?=isset($city['苏州'])?$city['苏州']*1000:1?>},
						{name:'广东', value:<?=($city['广州']+$city['珠海']+$city['深圳'])*1000?>, selected:true}
					]
				},
				{
					name:'2011全国GDP对比',
					type:'pie',
					roseType : 'area',
					tooltip: {
						trigger: 'item',
						formatter: "{a} <br/>{b} : {c} ({d}%)"
					},
					center: [document.getElementById('main').offsetWidth - 250, 225],
					radius: [30, 120],
					data:[
						{name: '北京', value: <?=$city['北京']?>},
						{name: '上海', value:<?=$city['上海']?>},
						{name: '广州', value: <?=$city['广州']?>},
						{name: '深圳', value:<?=$city['深圳']?>}
						
					]
				}
			],
			animation: false
		};
var ecConfig = require('echarts/config');
myChart.on(ecConfig.EVENT.MAP_SELECTED, function (param){
    var selected = param.selected;
    var mapSeries = option.series[0];
    var data = [];
    var legendData = [];
    var name;
    for (var p = 0, len = mapSeries.data.length; p < len; p++) {
        name = mapSeries.data[p].name;
        //mapSeries.data[p].selected = selected[name];
        if (selected[name]) {
            data.push({
                name: name,
                value: mapSeries.data[p].value
            });
            legendData.push(name);
        }
    }
    option.legend.data = legendData;
    option.series[1].data = data;
    myChart.setOption(option, true);
})
                    
        // 为echarts对象加载数据 
        myChart.setOption(option); 
    }
);		
</script>
</body>
</html>
<?php $this->endPage() ?>
