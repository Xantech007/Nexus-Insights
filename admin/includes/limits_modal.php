<!-- Edit Limits Modal -->
<div class="modal fade" id="editLimits">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"><b>Edit Deposit and Withdrawal Limits</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="limits_edit.php">
                    <input type="hidden" class="limitid" name="id" value="1">
                    <div class="form-group">
                        <label for="edit_min_deposit" class="col-sm-3 control-label">Minimum Deposit</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" class="form-control" id="edit_min_deposit" name="min_deposit" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_max_deposit" class="col-sm-3 control-label">Maximum Deposit</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" class="form-control" id="edit_max_deposit" name="max_deposit" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_min_withdraw" class="col-sm-3 control-label">Minimum Withdrawal</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" class="form-control" id="edit_min_withdraw" name="min_withdraw" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_max_withdraw" class="col-sm-3 control-label">Maximum Withdrawal</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" class="form-control" id="edit_max_withdraw" name="max_withdraw" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
