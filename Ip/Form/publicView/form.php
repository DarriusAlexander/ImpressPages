<?php
/** @var $form \Ip\Form */
?>
<form <?php echo $form->getClassesStr(); ?> <?php echo $form->getAttributesStr(); ?> method="<?php echo $form->getMethod(); ?>" action="<?php echo $form->getAction(); ?>" enctype="multipart/form-data">
    <?php foreach ($form->getPages() as $pageKey => $page) { ?>
        <?php foreach ($page->getFieldsets() as $fieldsetKey => $fieldset) { ?>
        <fieldset>
            <?php if ($fieldset->getLabel()) { ?>
                <legend><?php echo esc($fieldset->getLabel()); ?></legend>
            <?php } ?>
            <?php foreach ($fieldset->getFields() as $fieldKey => $field) { ?>
                <?php 
                    switch ($field->getLayout()) {
                        case \Ip\Form\Field::LAYOUT_DEFAULT:
                            echo $this->subview('field.php', array('field' => $field, 'environment' => $environment))->render()."\n";
                            break;
                        case \Ip\Form\Field::LAYOUT_BLANK:
                        default:
                            echo $field->render($this->getDoctype())."\n";
                            break;
                    }
                ?>
            <?php } ?>
        </fieldset>
        <?php } ?>
    <?php } ?>
</form>
