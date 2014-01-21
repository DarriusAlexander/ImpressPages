<div class="ipsAddAdministratorModal modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php _e('Add new administrator', 'ipAdmin') ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $createForm ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'ipAdmin') ?></button>
                <button type="button" class="ipsAdd btn btn-primary"><?php _e('Add', 'ipAdmin') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
