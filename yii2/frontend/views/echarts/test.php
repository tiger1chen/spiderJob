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
        'echarts/chart/wordCloud' // 使用柱状图就加载bar模块，按需加载
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
        
		var	option = {
			    title: {
			        text: '公司福利标签',
			        link: 'http://www.stackoverflow.com'
			    },
			    tooltip: {
			        show: true
			    },
			    series: [{
			        name: 'Google Trends',
			        type: 'wordCloud',
			        size: ['80%', '80%'],
			        textRotation : [0, 45, 90, -45],
			        textPadding: 0,
			        autoSize: {
			            enable: true,
			            minSize: 14
			        },
			         data:[  
			         		{
				                name: "福利标签",
				                value: <?=$count?>,
				                itemStyle: {
				                    normal: {
				                        color: 'black'
				                    }
				                }
				            },
			         		<?php foreach($companyLabeList as $k=>$v): ?>
			         		{
			         			name:"<?=$k?>",
			         			value:<?=$v?>,
			         			itemStyle: createRandomItemStyle()
			         		},
			         	   <?php endforeach; ?>
			         	  ]
			    }]
			};
        // 为echarts对象加载数据 
        myChart.setOption(option); 
    }
);		
</script>
</body>
</html>
<?php $this->endPage() ?>
