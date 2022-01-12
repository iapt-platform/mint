<?PHP
include "../pcdl/html_head.php";
?>

	<body class="wiki_body" onload="wiki_index_init()">
	<style>
	.term_link,.term_link_new{
		color: blue;
		padding-left: 2px;
		padding-right: 2px;
	}
	.term_link_new{
		color:red;
	}
	#search_result{
		position: absolute;
		background: var(--bg-color);
    	box-shadow: 8px 8px 20px var(--border-shadow);
		max-width: 95%;
		width: 24em;
		z-index: 20;
	}
	chapter{
		color: blue;
		text-decoration: none;
		cursor: pointer;
	}
	chapter:hover{
		color: blue;
		text-decoration: underline;
	}
	.fun_block {
    color: var(--tool-color);
    width: 95%;
	margin-top:3em;
	margin-left:auto;
	margin-right:auto;
    max-width: 30em;
    margin-bottom: 20px;
    box-shadow: 2px 2px 10px 2px var(--shadow-color);
    border-radius: 8px;
}
	.wiki_body{
		align-items: center;
	}
	.search_toolbar{
			height: initial;
			padding: 1em 1em;
			background-color: var(--tool-bg-color1);
			border-bottom: none;
		}
		#index_list{
			display:flex;
		}
		.wiki_search_list li{
			padding:0 1em;
			line-height:1.8em;
		}
		.wiki_search_list li:hover {
			background-color: beige;
		}
	</style>
	<style  media="screen and (max-width:800px)">
		#index_list{
			display:block;
		}
	</style>
	<?php
    require_once("../pcdl/head_bar.php");
	?>
        <script src="../../node_modules/highcharts/highcharts.js"></script>
        <script src="../../node_modules/highcharts/modules/exporting.js"></script>
        <script src="../../node_modules/highcharts/modules/data.js"></script>
        <script src="../../node_modules/highcharts/modules/series-label.js"></script>
        <script src="../../node_modules/highcharts/modules/oldie.js"></script>
		<script src="../term/term.js"></script>
		<script src="../term/note.js"></script>
		<script src="wiki.js"></script>

		<div id="container" style="min-width:400px;height:200px;"></div>

			
			<div id="wiki_search" style="width:100%;">

			</div>

	<!-- tool bar begin-->
	<div id='search_toolbar' class="search_toolbar">
			<div style="display:flex;justify-content: space-between;">
				<div ></div>
				<div>
					<div>
						<input id="wiki_search_input" type="input" placeholder="<?php echo $_local->gui->search;?>" style="width: 40em;max-width: 100%;font-size:140%;padding: 0.3em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onkeyup="wiki_search_keyup(event,this)"/>
					</div>
					<div id="search_result">
					</div>	
                </div>
                <div >
				<ul>
					<li>人人皆可编辑</li>
					<li>引用圣典原文</li>
					<li><a href="wiki.php?word=:new"><?php echo $_local->gui->new_technic_term; ?></a></li>

				</ul>
				</div>
			</div>
	</div>	
	<!--tool bar end -->



		<div id="index_list">
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
					<div>最近搜索</div>
				</div>
			</div>
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
					<div>优质词条</div>
				</div>
			</div>
			<div style="flex:3;margin:12px;">
				<div class="card" style="padding:10px;">
					<div>我的贡献</div>
					<div>新手指南</div>
				</div>
			</div>
		</div>
        <script>
		$.get('../term/update_analytics.php', function (csvStr) {
			console.log(csvStr);
			csvStr = csvStr.replace(/\n\n/g, '\n');
			Highcharts.chart('container', {

			chart: {
				scrollablePlotArea: {
					minWidth: 700
				}
			},

			data: {
				//csvURL: 'https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/analytics.csv',
				csv: csvStr
			},

			title: {
				text: '圣典百科百日更新'
			},

			subtitle: {
				text: ''
			},

			xAxis: {
				tickInterval: 7 * 24 * 3600 * 1000, // one week
				tickWidth: 0,
				gridLineWidth: 1,
				labels: {
					align: 'left',
					x: 3,
					y: -3
				}
			},

			yAxis: [{ // left y axis
				title: {
					text: null
				},
				labels: {
					align: 'left',
					x: 3,
					y: 16,
					format: '{value:.,0f}'
				},
				showFirstLabel: false
			}, { // right y axis
				linkedTo: 0,
				gridLineWidth: 0,
				opposite: true,
				title: {
					text: null
				},
				labels: {
					align: 'right',
					x: -3,
					y: 16,
					format: '{value:.,0f}'
				},
				showFirstLabel: false
			}],

			legend: {
				align: 'left',
				verticalAlign: 'top',
				borderWidth: 0
			},

			tooltip: {
				shared: true,
				crosshairs: true
			},

			plotOptions: {
				series: {
					cursor: 'pointer',
					point: {
						events: {
							click: function (e) {
								hs.htmlExpand(null, {
									pageOrigin: {
										x: e.pageX || e.clientX,
										y: e.pageY || e.clientY
									},
									headingText: this.series.name,
									maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) + ':<br/> ' +
										this.y + ' sessions',
									width: 200
								});
							}
						}
					},
					marker: {
						lineWidth: 1
					}
				}
			},

			series: [{
				name: 'All sessions',
				lineWidth: 4,
				marker: {
					radius: 4
				}
			}, {
				name: 'New users'
			}]
			});
		});
        </script>
	</body>
</html>