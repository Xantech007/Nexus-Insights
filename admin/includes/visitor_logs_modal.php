<!-- Delete Modal -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title"><b>Delete Visitor Logs</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="delete_visitor_logs.php" id="delete_form">
          <input type="hidden" class="did" name="id">
          <input type="hidden" class="visitor-id" name="visitor_id">
          <p>Are you sure you want to delete all logs for Visitor ID <span class="name"></span>?</p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-danger btn-flat" form="delete_form"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>
