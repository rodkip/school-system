<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="newotherpayitems" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">    
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">New Other Payments Items</h2>
      </div>

      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="username" value="<?php echo $username ?>">
          <table class="table">
            <tr>
              <td><label for="otherpayitemname">Item Name:</label></td>
              <td>
                <input type="text" class="form-control" name="otherpayitemname" id="otherpayitemname" required placeholder="Item Name" value="<?php echo $otherpayitemname; ?>">
              </td>
            </tr>
            <tr>
              <td><label for="description">Description:</label></td>
              <td>
                <input type="text" class="form-control" name="description" id="description" placeholder="Description" value="<?php echo $description; ?>">
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
