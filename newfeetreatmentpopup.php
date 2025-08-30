<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="newfeetreatment" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">    
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">New Fee Treatment</h2>
      </div>

      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <table class="table">
            <tr>
              <td><label for="feetreatment">FeeTreatment Name:</label></td>
              <td>
                <input type="text" class="form-control" name="feetreatment" id="feetreatment" required placeholder="Treatment Name">
              </td>
            </tr>
            <tr>
              <td><label for="feetreatmentrate">Fee TreatmentRate:</label></td>
              <td>
              <input type="text" class="form-control" name="feetreatmentrate" id="feetreatmentrate"  placeholder="Fee Treatment Rate">
              </td>
            </tr>    
              <tr>
              <td><label for="transporttreatmentrate">Transport TreatmentRate:</label></td>
              <td>
              <input type="text" class="form-control" name="transporttreatmentrate" id="transporttreatmentrate"  placeholder="Transport Treatment Rate">
              </td>
            </tr>   
            <tr>
              <td>
                <button type="submit" name="submitfeetreatment" class="btn btn-primary">Submit</button>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
