<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($form) && isset($this) && isset($view) && isset($token)) { ?>
    <?php if (isset($vapidKeys)) { ?>
        <div class="ccm-dashboard-header-buttons">
            <button data-launch-dialog="regenerate-dialog" class="btn btn-success">
                <?php echo t('Regenerate Keys'); ?>
            </button>

            <div style="display: none" data-dialog="regenerate-dialog" class="ccm-ui">
                <form data-dialog-form="regenerate-form" method="POST" action="<?php echo $view->action('regenerate_keys'); ?>">
                    <?php echo $token->output('regenerate-vapid-keys'); ?>
                    <p><?php echo t('Are you sure you want to regenerate your VAPID keys?'); ?></p>
                    <p><strong><?php echo t('WARNING: this operation can not be undone!'); ?></strong></p>
                </form>
                <div class="dialog-buttons">
                    <button class="btn btn-secondary pull-left" data-dialog-action="cancel"><?php echo t('Cancel'); ?></button>
                    <button class="btn btn-primary pull-right" data-dialog-action="submit"><?php echo t('Regenerate'); ?></button>
                </div>
            </div>

            <script>
                $(function() {
                    var $dialog = $('div[data-dialog="regenerate-dialog"]');
                    $('[data-launch-dialog="regenerate-dialog"]').on('click', function(e) {
                        e.preventDefault();
                        jQuery.fn.dialog.open({
                            element: $dialog,
                            modal: true,
                            width: 420,
                            title: <?php echo json_encode(t('Confirm Regeneration')); ?>,
                            height: 'auto'
                        });
                    });

                    ConcreteEvent.subscribe('AjaxFormSubmitSuccess', function(e, data) {
                        if (data.form === 'regenerate-form') {
                            window.location.href = <?php echo json_encode((string) \URL::to('/dashboard/push_notifications/settings')); ?>;
                        }
                    });
                });
            </script>
        </div>
    <?php } ?>

    <form method="post" class="mb-4" action="<?php echo $view->action('save'); ?>">
        <?php echo $token->output('push_notification_settings'); ?>

        <fieldset>
            <legend><?php echo t('Status'); ?></legend>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="activate" name="activate" class="form-check-input" value="1" <?php echo (isset($formContent) && isset($formContent['activate'])) ? 'checked' : ((isset($config) && $config->get('push_notifications.activate') == true) ? 'checked' : ''); ?> />
                    <label for="activate" class="form-check-label"><?php echo t('Activate Push Notifications'); ?></label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Subject Line'); ?></legend>

            <div class="form-group">
                <?php echo $form->label('subject', t('Broadcast subject')); ?>
                <div class="float-end">
                    <span class="text-muted small"><?php echo t('Required'); ?></span>
                </div>
                <?php echo $form->text('subject', (isset($formContent)) ? $formContent['subject'] : ((isset($config)) ? $config->get('push_notifications.subject') : 'devrow@limegreentangerine.co.uk')); ?>
                <div class="help-block"><?php echo t('Must be a valid email address.'); ?></div>
            </div>

        </fieldset>

        <?php echo $form->submit('save', t('Save'), ['class' => 'btn btn-primary']); ?>
    </form>

    <hr />

    <fieldset>
        <legend><?php echo t('VAPID Keys'); ?></legend>

        <?php if (isset($vapidKeys)) { ?>
            <div class="form-group">
                <?php echo $form->label('publicKey', t('Public Key')); ?>
                <?php echo $form->text('publicKey', $vapidKeys->getPublicKey() ?? null, [ 'readonly' => 'readonly', 'disabled' => 'disabled' ]); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('privateKey', t('Private Key')); ?>
                <?php echo $form->password('privateKey', $vapidKeys->getPrivateKey() ?? null, [ 'readonly' => 'readonly', 'disabled' => 'disabled' ]); ?>
            </div>
        <?php } else { ?>
            <div class="alert alert-info"><?php echo t('VAPID keys missing, try <a href="%s">generating</a> them', $this->action('generate_keys')); ?></div>
        <?php } ?>
    </fieldset>

    <?php if (!isset($vapidKeys)) { ?>
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo $this->action('generate_keys'); ?>" class="btn btn-primary float-end">
                    <?php echo t('Generate Keys'); ?>
                </a>
            </div>
        </div>
    <?php } ?>
<?php } ?>
