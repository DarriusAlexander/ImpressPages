<div class="ipAdminPanelContainer">
    <div class="ipAdminPanel">
        <div class="ipAdminControls ipmMobileHide">
            <div class="ipAdminWidgetsSearch clearfix">
                <div class="ipaControls">
                    <span class="ipaArrow"></span>
                    <input type="text" class="ipAdminInput ipaInput" value="<?php _e('Search widgets', 'ipAdmin') ?>" />
                    <a href="#" class="ipaButton"></a>
                </div>
            </div>
            <a href="#" class="ipAdminButton ipaOptions" title="<?php _e('Advanced', 'ipAdmin') ?>"><span>&nbsp;</span></a>
            <a href="<?php echo \Ip\Internal\UrlHelper::getCurrentUrl(); ?>" target="_blank" class="ipAdminButton ipaPreview ipsPreview" title="<?php _e('Preview', 'ipAdmin') ?>"><span>&nbsp;</span></a>
            <a href="#" class="ipAdminButton ipaConfirm ipActionPublish" title="<?php _e('Publish', 'ipAdmin') ?>"><?php _e('Publish', 'ipAdmin') ?></a>
            <div class="ipAdminRevisions">
                <a href="#" class="ipAdminButton ipaRevisions"><span>&nbsp;</span></a>
                <div class="ipaDropdownBlock">
                    <a href="#" class="ipAdminButton ipaSave ipActionSave" title="<?php _e('Save', 'ipAdmin') ?>"><?php _e('Save now', 'ipAdmin') ?></a>
                    <ul>
    <?php foreach ($revisions as $revisionKey => $revision){
              $revisionClass = '';
              if ($revision['revisionId'] == $currentRevision['revisionId']) {
                  $revisionClass .= $revisionClass ? ' ' : '';
                  $revisionClass .= 'ipaActive';
              }
              if ($revision['published']) {
                  $revisionClass .= $revisionClass ? ' ' : '';
                  $revisionClass .= 'ipaPublished';
              }
    ?>
                        <li<?php echo $revisionClass ? ' class="'.$revisionClass.'"' : ''; ?>>
                            <a href="<?php echo $managementUrls[$revisionKey]; ?>">
                                <strong><?php echo (int)$revision['revisionId'] ?></strong> - <?php echo date("Y-m-d H:i", $revision['created']); echo $revision['published'] ? ' '.$this->esc(__('Published', 'ipAdmin')) . ' ' : ''; ?>
                            </a>
                        </li>
    <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="ipAdminWidgets">
    <?php if(!$manageableRevision){ ?>
            <div class="ipAdminWidgetsDisable">
                <p>
                    <?php echo $this->escPar('Content.older_revision_preview', array ('date' => date("Y-m-d H:i", $currentRevision['created']))) ?>
                    <a href="#" class="ipActionPublish"><?php _e('Publish this revision', 'ipAdmin') ?></a>
                    <a href="#" class="ipActionSave"><?php _e('Duplicate and edit this revision', 'ipAdmin') ?></a>
                </p>
            </div>
    <?php } ?>
            <a href="#" class="ipAdminWidgetsScroll ipaLeft"></a>
            <a href="#" class="ipAdminWidgetsScroll ipaRight"></a>
            <div class="ipAdminWidgetsContainer">
    <?php $scrollWidth = count($widgets)*87; // to keep all elements on one line ?>
                <ul<?php echo ' style="width: '.$scrollWidth.'px;"'; ?>>
    <?php foreach ($widgets as $widgetKey => $widget) { ?>
                    <li>
                        <div id="ipAdminWidgetButton-<?php echo $widget->getName(); ?>" class="ipActionWidgetButton">
                            <a href="#">
                                <span class="ipaTitle"><span><?php echo htmlspecialchars($widget->getTitle()); ?></span></span>
                                <img src="<?php echo ipEsc($widget->getIcon()) ?>" alt="<?php echo htmlspecialchars($widget->getTitle()); ?>" />
                            </a>
                        </div>
                    </li>
    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="ipAdminErrorContainer"></div>
        <div class="ipAdminErrorSample">
            <p class="ipAdminError"></p>
        </div>
    </div>

</div>
<a href="#" class="ipAdminButton ipaConfirm ipActionPublish ipmMobileShow" title="<?php _e('Publish', 'ipAdmin') ?>"><?php _e('Publish', 'ipAdmin') ?></a>
