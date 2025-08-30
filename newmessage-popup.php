<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <h2 class="modal-title" style="text-align: center;">New Message</h2>
      </div>
      <div class="modal-body">
        <!-- actual form -->
        <div class="panel panel-primary">
          <div class="row">
            <div class="col-lg-12">
              <!-- Advanced Tables -->
              <div class="panel panel-default">
                <div class="popup">
                  <form method="post" enctype="multipart/form-data" action="manage-messages.php">
                    <input type="hidden" name="recipient" value="<?php echo $recipientusername ?>">
                    <input type="hidden" name="sender" value="<?php echo $username ?>">
                    <input type="hidden" name="status" value="0">
                    <table>
                      <tr>
                        <td>
                          <div class="form-group">
                          <textarea cols="60" rows="6" class="form-control" name="message" id="message" placeholder="Message"></textarea>

                          </div>
                        </td>
                      </tr>
</table>
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
