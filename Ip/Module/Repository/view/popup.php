<div class="ipModuleRepositoryPopup">
    <div class="tabs">
        <ul>
            <li><a href="#ipModuleRepositoryTabUpload"><?php _e('Upload new', 'ipAdmin') ?></a></li>
            <li><a href="#ipModuleRepositoryTabBuy"><?php _e('Buy images', 'ipAdmin') ?></a>
        </ul>

        <a href="#" class="ipmClose ipaClose ui-dialog-titlebar-close ui-corner-all" role="button"><span class="ui-icon ui-icon-closethick"></span></a>

        <div id="ipModuleRepositoryTabUpload" class="ipmTabUpload">
            <div id="ipModuleRepositoryDragContainer" class="impContainer" >
                <div class="ipmFiles"></div>
                <div class="ipUploadProgressContainer">
                    <div class="ipmCurErrors"></div>
                    <div class="ipmBrowseButtonWrapper">
                        <span class="impDragdropNotice"><?php _e('Drag&drop files here or click a button to upload.', 'ipAdmin') ?></span>
                        <a href="#" class="ipAdminButton ipaAction ipmBrowseButton" id="ipModuleRepositoryUploadButton"><?php _e('Add new', 'ipAdmin') ?></a>
                    </div>
                    <div class="ipmBrowseButtonWrapper">
                        <span class="impDragdropNotice"><?php _e('Need more images? Browse and choose from thousands of them.', 'ipAdmin') ?></span>
                        <a href="#ipModuleRepositoryTabBuy" class="ipAdminButton ipaConfirm ipmBrowseButton" id="ipModuleRepositoryBuyButton"><?php _e('Buy images', 'ipAdmin') ?></a>
                    </div>
                </div>
                <div class="ipUploadProgressItemSample ipgHide">
                    <div class="ipUploadProgressItem">
                        <div class="ipUploadProgressbar"></div>
                        <p class="ipUploadTitle"></p>
                    </div>
                </div>
                <p class="ipmErrorSample ipgError ipgHide"></p>
            </div>
            <div class="ipmBrowser">
                <div class="ipmBrowserControls">
                    <div class="ipmBrowserSearch">
                        <form class="ipmForm" action="">
                            <input type="text" class="ipAdminInput ipmTerm" value="" placeholder="">
                            <button type="submit" class="ipmButton"><i class="icon-search"></i></button>
                        </form>
                    </div>
                </div>
                <div class="ipmBrowserContainer clearfix">
                    <h2 class="ipgHide ipmListTitle ipmRecentTitle"><?php _e('Recent files', 'ipAdmin') ?></h2>
                    <ul class="ipgHide ipmList clearfix ipmRecentList"></ul>
                </div>
            </div>
            <div class="ipgHide ipmRepositoryActions">
                <div class="ipmInner">
                    <span class="ipmTitle"><?php _e('Selected:', 'ipAdmin') ?> <strong class="ipmSelectionCount"></strong></span>
                    <a class="ipAdminButton ipaConfirm ipaSelectionConfirm" href="#"><?php _e('Confirm', 'ipAdmin') ?></a>
                    <a class="ipAdminButton ipaSelectionCancel" href="#"><?php _e('Cancel', 'ipAdmin') ?></a>
                    <a class="ipAdminButton ipaSelectionDelete" href="#"><?php _e('Delete', 'ipAdmin'); ?> <i class="icon-trash"></i></a>
                </div>
            </div>
            <?php // hidden templates for dynamic elements ?>
            <div class="ipgHide">
                <h2 class="ipmListTitleTemplate ipmListTitle"></h2>
                <ul class="ipmListTemplate ipmList clearfix"></ul>
                <ul>
                    <li class="ipmFileTemplate">
                        <i class=""></i>
                        <img src="" alt="" title="" />
                        <span></span>
                    </li>
                </ul>
            </div>
        </div>
        <div id="ipModuleRepositoryTabBuy" data-marketurl="<?php echo $marketUrl; ?>" class="ipmTabBuy">
            <div class="ipmContainer" id="ipModuleRepositoryTabBuyContainer"></div>
            <div class="ipgHide ipmLoading">
                <span class="ipmLoadingText">
                    <?php _e('Your images are being downloaded to your website. It may take some time to finish. Please wait.', 'ipAdmin') ?>
                </span>
            </div>
        </div>
    </div>
</div>