<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="newvotehead" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">    
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">New Votehead</h2>
      </div>

      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <table class="table">
            <tr>
              <td><label for="votehead">Votehead Name:</label></td>
              <td>
                <input type="text" class="form-control" name="votehead" id="votehead" required placeholder="Votehead Name" value="<?php echo $votehead; ?>">
              </td>
            </tr>
            <tr>
              <td><label for="description">Description:</label></td>
              <td>
              <textarea class="form-control" name="description" id="description" rows="3" placeholder="Description"><?php echo htmlentities($description); ?></textarea>

              </td>
              </tr>
              <tr>
              <td>
                <label>Is Fee Payment?</label>
              </td>
              <td>
                <select name="isfeepayment" class="form-control" required>
                  <option value="">-- Select Option --</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </td>
              </tr>
              <tr>
               <td>
                <label>Is Fee Treatment Calculations?</label>
              </td>
               <td>
                <select name="isfeetreatmentcalculations" class="form-control" required>
                  <option value="">-- Select Option --</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
