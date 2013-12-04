<div class="ipAdminWidgetControls">
    <?php if (!empty($optionsMenu)) { ?>
        <div class="ip">
        <div class="btn-group ipaButton">
            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">
                <span class="caret"><?php _e('Settings', 'ipAdmin') ?></span>
                <span class="sr-only"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <?php foreach($optionsMenu as $menuItem) { ?>
                    <li><a href="#"><?php echo esc($menuItem['title']) ?></a></li>
                <?php } ?>
            </ul>
        </div>
        </div>
    <?php } ?>
    <a href="#" class="ipaButton ipActionWidgetMove"><span><?php _e('Move', 'ipAdmin') ?></span></a>
    <a href="#" class="ipaButton ipActionWidgetDelete"><span><?php _e('Delete', 'ipAdmin') ?></span></a>
</div>
<div class="ipAdminWidgetMoveIcon"></div>