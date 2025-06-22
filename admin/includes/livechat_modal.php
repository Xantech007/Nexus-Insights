<!-- Delete Confirmation Modal -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title"><b>Delete Chat History</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="delete_chat.php">
          <input type="hidden" class="did" name="id">
          <input type="hidden" class="type" name="type">
          <p>Are you sure you want to delete the chat history for <span class="name"></span>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
      </div>
        </form>
    </div>
  </div>
</div>
