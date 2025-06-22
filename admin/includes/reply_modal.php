<!-- Reply Modal -->
<div class="modal fade" id="reply">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title"><b>Send Message</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="livechat.php">
          <input type="hidden" class="rid" name="id">
          <input type="hidden" class="type" name="type">
          <p>Sending message to <span class="name"></span></p>
          <div class="form-group">
            <label for="message" class="control-label">Message</label>
            <textarea name="message" id="message" class="form-control" rows="4" placeholder="Type your message..." required></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-send"></i> Send</button>
      </div>
        </form>
    </div>
  </div>
</div>
