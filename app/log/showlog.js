
function getData(filename){
	$.get("../../tmp/log/"+filename,
	function(data,status){
	  
	//生成数据数组
	  let rowData= data.split('\n');
	  let arrData = new Array();
	  for (const iterator of rowData) {
		arrData.push(iterator.split(","));
	  }
	  console.log(arrData);
	  let api = new Object;
	  let delayInMinute
	  
	  //遍历所有数据
	  for (const iterator of arrData) {
		if (api.hasOwnProperty.call(api, iterator[0])) {
			let element = api[iterator[0]];
			element.times++;
			element.delay += parseInt(iterator[2]);
			try{
				let hour = parseInt(iterator[1].split(':')[0]);
				element.delayHour[hour] += parseInt(iterator[2]);
				element.timesHour[hour] ++;
				
			}catch(e){

			}

		}else{
			api[iterator[0]] = {
				times:1,
				timesHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
				delay:parseInt(iterator[2]),
				delayHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
			};
			try{
				let hour = parseInt(iterator[1].split(':')[0]);
				api[iterator[0]].delayHour[hour] = parseInt(iterator[2]);
				api[iterator[0]].timesHour[hour] = 1;
			}catch(e){

			}

		}
	  }
	  let api_timms = new Array();
	  let api_delay = new Array();
	  let ApiDelayInHour = new Array();
	  let ApiTimesInHour = new Array();
	  for (const key in api) {
		  if (Object.hasOwnProperty.call(api, key)) {
			  const element = api[key];
			  api_timms.push({
				  name:key,
				  y:element.times
			  });
			  api_delay.push({
				name:key,
				y:element.delay
			  });
			  ApiDelayInHour.push({
				name:key,
				data:element.delayHour
			  });
			  ApiTimesInHour.push({
				name:key,
				data:element.timesHour
			  })
		  }
	  }

	  chart_1(api_timms);
	  chart_2(api_delay);
	  chart_3(ApiDelayInHour);
	  chart_4(ApiTimesInHour);
	});
  
}

function chart_1(data){
	// Make monochrome colors
var pieColors = (function () {
    var colors = [],
        base = Highcharts.getOptions().colors[0],
        i;

    for (i = 0; i < 10; i += 1) {
        // Start out with a darkened base color (negative brighten), and end
        // up with a much brighter color
        colors.push(Highcharts.color(base).brighten((i - 3) / 7).get());
    }
    return colors;
}());

// Build the chart
Highcharts.chart('chart-1', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'API 执行次数'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            colors: pieColors,
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                distance: -50,
                filter: {
                    property: 'percentage',
                    operator: '>',
                    value: 4
                }
            }
        }
    },
    series: [{
        name: 'Share',
        data: data
    }]
});
}

function chart_2(data){
	// Make monochrome colors
var pieColors = (function () {
    var colors = [],
        base = Highcharts.getOptions().colors[0],
        i;

    for (i = 0; i < 10; i += 1) {
        // Start out with a darkened base color (negative brighten), and end
        // up with a much brighter color
        colors.push(Highcharts.color(base).brighten((i - 3) / 7).get());
    }
    return colors;
}());

// Build the chart
Highcharts.chart('chart-2', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'API 累积执行时间'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            colors: pieColors,
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                distance: -50,
                filter: {
                    property: 'percentage',
                    operator: '>',
                    value: 4
                }
            }
        }
    },
    series: [{
        name: 'Share',
        data: data
    }]
});
}

//按照小时计算的api 执行时间
function chart_3(data){
	Highcharts.chart('chart-3', {

		title: {
			text: '按照小时计算的 API 执行时间'
		},
	
		subtitle: {
			text: 'Source: thesolarfoundation.com'
		},
	
		yAxis: {
			title: {
				text: '执行时间'
			}
		},
	
		xAxis: {
			categories: [
				'8',
				'9',
				'10',
				'11',
				'12',
				'13',
				'14',
				'15',
				'16',
				'17',
				'18',
				'19',
				'20',
				'21',
				'22',
				'23',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7'
			],
			accessibility: {
				rangeDescription: 'Range: 0 to 23'
			}
		},
	
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
	
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
				pointStart: 0
			}
		},
	
		series: data,
	
		responsive: {
			rules: [{
				condition: {
					maxWidth: 500
				},
				chartOptions: {
					legend: {
						layout: 'horizontal',
						align: 'center',
						verticalAlign: 'bottom'
					}
				}
			}]
		}
	
	});
}

function chart_4(data){
	Highcharts.chart('chart-4', {
		chart: {
			type: 'column'
		},
		title: {
			text: 'API 执行次数'
		},
		subtitle: {
			text: 'Source: WorldClimate.com'
		},
		xAxis: {
			categories: [
				'8',
				'9',
				'10',
				'11',
				'12',
				'13',
				'14',
				'15',
				'16',
				'17',
				'18',
				'19',
				'20',
				'21',
				'22',
				'23',
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7'
			],
			crosshair: true
		},
		yAxis: {
			min: 0,
			title: {
				text: '执行次数'
			}
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle'
		},
		tooltip: {
			headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
				'<td style="padding:0"><b>{point.y} 次</b></td></tr>',
			footerFormat: '</table>',
			shared: true,
			useHTML: true
		},
		plotOptions: {
			column: {
				pointPadding: 0.2,
				borderWidth: 0
			}
		},
		series: data
	});
}