<h1>Upload Spreadsheet</h1>

<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import">
	<div class="row" style="min-height:4em;">
	<div class="form-group">
		<label for="spreadsheet" class="col-sm-2 control-label">Excel File</label>
		<div class="col-sm-8">
			<span class="btn btn-default btn-file" id="browse-button">
				<span class="glyphicon glyphicon-open" aria-hidden="true"></span> Browse
				<input type="file" name="spreadsheet" id="spreadsheet" required="required"
				  accept="application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.oasis.opendocument.spreadsheet">
			</span>
			<span id="filename"></span>
		</div>
	</div>
	</div>
	<div class="row">
	<div class="form-group">
		<div class="col-sm-10">
			<div class="btn-group" role="group">
				<button class="btn btn-primary" disabled="disabled" type="submit" id="upload-button">
					<span class="glyphicon glyphicon-import" aria-hidden="true"></span> Upload
				</button>
			</div>
		</div>
	</div>
	</div>
</form>

<script type="text/javascript">
$(document).on('change', '.btn-file :file', function() {
	var input = $(this),
		numFiles = input.get(0).files ? input.get(0).files.length : 1,
		label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
	input.trigger('fileselect', [numFiles, label]);
});

$(document).ready( function() {
	$('.btn-file :file').on('fileselect', function(event, numFiles, label) {
		$('#filename').text(label);
		$('#browse-button').fadeOut(50);
		$('#upload-button').removeAttr('disabled');
	});
});
</script>