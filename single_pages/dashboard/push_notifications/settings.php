<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($form) && isset($this)) { ?>
    <fieldset>
        <legend><?php echo t('VAPID Keys'); ?></legend>

        <div class="form-group">
            <?php
                echo $form->label('publicKey', t('Public Key'));
    echo $form->text('publicKey', (isset($vapidKeys)) ? $vapidKeys->getPublicKey() : null, [ 'readonly' => 'readonly', 'disabled' => 'disabled' ]);
    ?>
        </div>

        <div class="form-group">
            <?php
        echo $form->label('privateKey', t('Private Key'));
    echo $form->password('privateKey', (isset($vapidKeys)) ? $vapidKeys->getPrivateKey() : null, [ 'readonly' => 'readonly', 'disabled' => 'disabled' ]);
    ?>
        </div>
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
