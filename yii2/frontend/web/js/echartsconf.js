
    // ·������
    require.config({
        paths: {
            echarts: './echarts/build/dist'
        }
    });
   // ʹ��
    require(
        [
            'echarts',
            'echarts/chart/bar' // ʹ����״ͼ�ͼ���barģ�飬�������
        ],
        function (ec) {
            // ����׼���õ�dom����ʼ��echartsͼ��
            var myChart = ec.init(document.getElementById('main')); 
            
            var option = {
                tooltip: {
                    show: true
                },
                legend: {
                    data:['����']
                },
                xAxis : [
                    {
                        type : 'category',
                        data : ["����","��ë��","ѩ����","����","�߸�Ь","����"]
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : [
                    {
                        "name":"����",
                        "type":"bar",
                        "data":[5, 20, 40, 10, 10, 20]
                    }
                ]
            };
    
            // Ϊecharts����������� 
            myChart.setOption(option); 
        }
    );		
