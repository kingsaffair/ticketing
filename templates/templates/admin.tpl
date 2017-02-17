{extends file="main.tpl"}
{block "head"}
	<link rel="stylesheet" href="{resource src='css/admin.css'}" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
	<script type="text/javascript" src="{resource src='scripts/highcharts.js'}"></script>
	<script type="text/javascript">
		var chart;
		$(document).ready(function() {
			chart = new Highcharts.Chart({
				chart: {
					renderTo: 'summarygraph',
					defaultSeriesType: 'line',
					marginTop: 50,
					marginBottom: 40,
				},
				title: {
					text: null
				},
				xAxis: {
					type: 'datetime',
					tickWidth: 0,
					gridLineWidth: 1,
					//min: 1200000000000,
					labels: {
						align: 'left',
						x: 3,
						y: 15 
					}
				},
				yAxis: {
					title: {
						text: null
					},
					min: 0,
					plotLines: [{
						value: 0,
						width: 1,
						color: '#808080'
					}]
				},
				tooltip: {
					formatter: function() {
							return '<b>'+ this.series.name +'</b>: ' + this.y + '<br/>'+
							Highcharts.dateFormat('%e %B %Y  %H:%M',this.x) + '-' + Highcharts.dateFormat('%H:%M',this.x+{$graph['step']}000);
					}, 
					crosshairs: true
				},
				legend: {
					align: 'left',
					verticalAlign: 'top',
					y: 0,
					floating: true,
					borderWidth: 0
				},
				series: [{
					name: 'Order\'s Created',
					data: [
{foreach $graph['orders'] as $time => $val}
					[{$time}000,{$val}],
{/foreach}
					]
				},{
					name: 'Ticket\'s Created',
					data: [
{foreach $graph['tickets'] as $time => $val}
					[{$time}000,{$val}],
{/foreach}
					]
				}]
			});
			
			
		});
			
	</script>
{/block}
{block "body"}
{nocache}
<h4>Summary</h4>
<div class="summary">
<dl class="totals">
	<dt>Total Tickets Sold</dt>
	<dd>{$totals['total']} / {$totals['total_limit']}</dd>
	<dt>Total QueueJump Upgrades Sold</dt>
	<dd>{$totals['premium']} / {$totals['premium_limit']}</dd>
	<dt>Tickets on the Waiting List</dt>
	<dd>{$totals['waiting']}</dd>
	<dt>Number of orders</dt>
	<dd>{$totals['orders']}</dd>
	<dt>Average tickets per Order</dt>
	<dd>{$totals['average_tickets']}</dd>
	<dt>Orders paid by Bank Transfer</dt>
	<dd>{$totals['cheque']}</dd>
	<dt>Orders paid by College Bill</dt>
	<dd>{$totals['college_bill']}</dd>
</dl>
<dl class="totals">
	<dt>Total price of Tickets Sold</dt>
	<dd>&pound;{number_format($totals['total_cost'])}</dd>
	<dt>Total money for Charity</dt>
	<dd>&pound;{number_format($totals['charity'])}</dd>
	<dt>Total revenue (inc VAT)</dt>
	<dd>&pound;{number_format($totals['profit'])}</dd>
	<dt>Total revenue (ex VAT)</dt>
	<dd>&pound;{number_format($totals['profitexvat'])}</dd>
	<dt>Total VAT paid to HMRC</dt>
	<dd>&pound;{number_format($totals['vat'])}</dd>
	<dt>Money from Bank Transfers</dt>
	<dd>&pound;{number_format($totals['chequem'])}</dd>
	<dt>Money from College Bills</dt>
	<dd>&pound;{number_format($totals['college_billm'])}</dd>
</dl>
</div>
<h4>Summary Graph</h4>
<div id="summarygraph"></div>
{/nocache}
{/block}
