<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($view) && isset($token) && isset($form)) { ?>
    <form method="post" action="<?php echo $view->action('save'); ?>">
        <?php echo $token->output('set_publish_notification_types'); ?>

        <?php if (isset($types)) { ?>
            <fieldset>
                <legend><?php echo t('Available Page Types'); ?></legend>

                <div class="help-block"><?php echo t('Selecting page types from this list will add them to the automatic push on publish feature. This will only apply to the default template type to avoid issues with news listing pages for example.'); ?></div>

                <?php foreach ($types as $index => $pt) {
                    $pageTypeID = is_object($pt) && method_exists($pt, 'getPageTypeID') ? $pt->getPageTypeID() : $index;
                    $pageTypeName = is_object($pt) && method_exists($pt, 'getPageTypeName') ? $pt->getPageTypeName() : $pt;
                    ?>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="type__<?php echo $index; ?>" name="types[]" class="form-check-input" value="<?php echo $pageTypeID; ?>" <?php echo ((isset($formContent) && isset($formContent['types'])) && in_array($pageTypeID, $formContent['types'])) ? 'checked' : ((isset($publish) && in_array($pageTypeID, $publish)) ? 'checked' : ''); ?> />
                            <label for="type__<?php echo $index; ?>" class="form-check-label"><?php echo $pageTypeName; ?></label>
                        </div>
                    </div>
                <?php } ?>
            </fieldset>
        <?php } else { ?>
            <div class="alert alert-info"><?php echo t('No page types available'); ?></div>
        <?php } ?>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <?php echo $form->submit('save', t('Save'), ['class' => 'btn btn-primary float-end']); ?>
            </div>
        </div>
    </form>
<?php } ?>
