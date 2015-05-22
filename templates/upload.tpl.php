<div class="row">
  <h1>Upload Excel File</h1>
  <form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label for="exampleInputFile">Excel File</label>
      <input type="file" name="spreadsheet" id="speadsheet">
      <p class="help-block">Select excel file to upload.</p>
    </div>
    <input type="hidden" name="action" value="import">
    <button class="btn btn-default" type="submit">Upload</button>
  </form>
</div>