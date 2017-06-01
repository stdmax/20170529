
<table id="table"></table>

<script type="text/javascript">
	(function () {
		table = sortTable('table');

		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function() {
			if (4 == this.readyState
				&& 200 == this.status) {
				dataRows = JSON.parse(this.responseText, function(dataKey, dataValue) {
					if ('date' == dataKey) {
						dataValue = new Date(dataValue);
					} else if ('numeric' == dataKey) {
						dataValue = parseFloat(dataValue);
					}

					return dataValue;
				});
				table.setDataRows(dataRows);
				table.show();
			}
		};
		xhr.open('GET', '<?php echo $url ?>?action=data', true);
		xhr.send();
	}) ();
</script>
