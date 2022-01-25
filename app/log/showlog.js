
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
		let delay = parseInt(iterator[2]);
		if (api.hasOwnProperty.call(api, iterator[0])) {
			let element = api[iterator[0]];
			element.times++;
			element.delay += delay;
			try{
				let hour = parseInt(iterator[1].split(':')[0]);
				element.delayHour[hour] += delay;
				if(delay>api[iterator[0]].delayMaxHour[hour]){
					api[iterator[0]].delayMaxHour[hour] = delay;
				}
				if(delay < api[iterator[0]].delayMinHour[hour]){
					api[iterator[0]].delayMinHour[hour] = delay;
				}	
				element.timesHour[hour] ++;
				
			}catch(e){

			}

		}else{
			api[iterator[0]] = {
				times:1,
				timesHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
				delay:delay,
				//一小时总执行时间
				delayHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
				//一小时最高执行时间
				delayMaxHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
				//一小时最低执行时间
				
				delayMinHour:[30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000,30000],
				//一小时平均执行时间
				delayAverageHour:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
			};
			try{
				let hour = parseInt(iterator[1].split(':')[0]);
				api[iterator[0]].delayHour[hour] = parseInt(iterator[2]);
				api[iterator[0]].delayMaxHour[hour] = parseInt(iterator[2]);
				api[iterator[0]].delayMinHour[hour] = parseInt(iterator[2]);
				api[iterator[0]].timesHour[hour] = 1;
			}catch(e){

			}

		}
	  }
	  let api_timms = new Array();
	  let api_delay = new Array();
	  let ApiDelayInHour = new Array();
	  let ApiTimesInHour = new Array();
	  let ApiAverageInHour = new Array();
	  let ohlc = new Array();
	  let volume = new Array();
	  for (const key in api) {
		  if (api.hasOwnProperty.call(api, key)) {
			  //计算每个小时的平均执行时间
			  for (let index = 0; index < api[key].delayAverageHour.length; index++) {
				  api[key].delayAverageHour[index] = api[key].delayHour[index]/api[key].timesHour[index];
			  }
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
			  });
			  ApiAverageInHour.push({
				name:key,
				data:element.delayAverageHour
			  });
			  for (let index = 1; index < api[key].delayAverageHour.length; index++) {
				api[key].delayAverageHour[index] = api[key].delayHour[index]/api[key].timesHour[index];
				if(key=="/app/uwbw/update.php"){
					ohlc.push([
						Date.UTC(2022,1,1,index,0,0,0), // the date
						api[key].delayAverageHour[index-1], // open
						api[key].delayMaxHour[index], // high
						api[key].delayMinHour[index], // low
						api[key].delayAverageHour[index] // close
					]);
			
					volume.push([
						Date.UTC(2022,1,1,index,0,0,0), // the date
						element.timesHour[index] // the volume
					]);
				}				
			  }

		  }
	  }

	  chart_1(api_timms);
	  chart_2(api_delay);
	  chart_3(ApiDelayInHour);
	  chart_3a(ApiAverageInHour);
	  chart_4(ApiTimesInHour);
	  chart_5(ohlc,volume);
	  chart_6(ohlc,volume);
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
function chart_3a(data){
	Highcharts.chart('chart-3a', {

		title: {
			text: '按照小时计算的 API 平均执行时间'
		},
	
		subtitle: {
			text: '总执行时间/次数'
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

function chart_6(ohlc,volume){
{

    // create the chart
    Highcharts.stockChart('chart-6', {


        title: {
            text: 'AAPL stock price by minute'
        },

        rangeSelector: {
            buttons: [{
                type: 'hour',
                count: 1,
                text: '1h'
            }, {
                type: 'day',
                count: 1,
                text: '1D'
            }, {
                type: 'all',
                count: 1,
                text: 'All'
            }],
            selected: 1,
            inputEnabled: false
        },

        series: [{
            name: 'AAPL',
            type: 'candlestick',
            data: ohlc,
            tooltip: {
                valueDecimals: 2
            }
        }]
    });
}
}

function chart_5(ohlc,volume){
	  // create the chart
	  groupingUnits = [[
		'week',                         // unit name
		[1]                             // allowed multiples
	], [
		'month',
		[1, 2, 3, 4, 6]
	]];
	  Highcharts.stockChart('chart-5', {


		rangeSelector: {
		  selected: 2
		},
	
		title: {
		  text: 'progress_curve'
		},
			yAxis: [{
				labels: {
					align: 'right',
					x: -3
				},
				title: {
					text: 'EXP'
				},
				height: '60%',
				lineWidth: 2,
				resize: {
					enabled: true
				}
			}, {
				labels: {
					align: 'right',
					x: -3
				},
				title: {
					text: 'action'
				},
				top: '65%',
				height: '35%',
				offset: 0,
				lineWidth: 2
			}],
	
		tooltip: {
				shared: false,
				useHTML: true,
				pointFormatter: function() {
					if(this.high){
						return '<b><a href="../">'+this.series.name + ' : ' + this.high + '&nbsp;' +'gLocal.gui.h'+ '</a><br><a href="../">' +'gLocal.gui.day_EXP' + ' : ' + Math.round((this.high - this.low)*100)/100 + '&nbsp;' +'gLocal.gui.h'+'</a></b><br/>'; 
					}
					else{
						return '<b><a href="../">'+this.series.name + ' : ' + this.y + '&nbsp;' +'gLocal.gui.times'+'</a><span style="display:none;">'+this.x+'</span></b>'; 
					}
				},
				valueDecimals: 2,//保留两位小數
				split: true
			},
	
		series: [{
		  type: 'ohlc',
		  name: 'gLocal.gui.EXP_in_total',
		  data: ohlc,
		  dataGrouping: {
			units: [[
			  'week', // unit name
			  [1] // allowed multiples
			], [
			  'month',
			  [1, 2, 3, 4, 6]
			]]
		  }
			}, {
				type: 'column',
				name: 'gLocal.gui.day_action',
				data: volume,
				yAxis: 1,
				dataGrouping: {
					units: groupingUnits
				}
			}]
	  });
}