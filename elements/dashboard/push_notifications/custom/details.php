<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($v) && isset($token) && isset($view) && $v['id']) { ?>
    <div class="ccm-dashboard-header-buttons">
        <button data-launch-dialog="delete-dialog" class="btn btn-danger"><?php echo t('Delete'); ?></button>
    </div>

    <div style="display: none" data-dialog="delete-dialog" class="ccm-ui">
        <form data-dialog-form="delete-form" method="POST" action="<?php echo $view->action('delete', $v['id']); ?>">
            <?php echo $token->output('delete-custom-id-' . $v['id']); ?>
            <p><?php echo t('Are you sure you want to permanently delete this push notification?'); ?></p>
            <p><strong><?php echo t('WARNING: this operation can not be undone!'); ?></strong></p>
        </form>
        <div class="dialog-buttons">
            <button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel'); ?></button>
            <button class="btn btn-danger pull-right" data-dialog-action="submit"><?php echo t('Delete'); ?></button>
        </div>
    </div>

    <script>
    $(function() {
        var $dialog = $('div[data-dialog="delete-dialog"]');
        $('[data-launch-dialog="delete-dialog"]').on('click', function(e) {
            e.preventDefault();
            jQuery.fn.dialog.open({
                element: $dialog,
                modal: true,
                width: 420,
                title: <?php echo json_encode(t('Confirm Delete')); ?>,
                height: 'auto'
            });
        });
        ConcreteEvent.subscribe('AjaxFormSubmitSuccess', function(e, data) {
            if (data.form === 'delete-form') {
                window.location.href = <?php echo json_encode((string) \URL::to('/dashboard/push_notifications/custom')); ?>;
            }
        });
    });
    </script>
<?php } ?>

<?php if (isset($view) && isset($token) && isset($form) && isset($h)) { ?>
    <form method="post" action="<?php echo $view->action('save'); ?>">
        <?php echo $token->output('submit') ?>
        <?php echo (isset($entity)) ? $form->hidden('id', $entity->getID()) : ''; ?>

        <div class="form-group">
            <?php
                echo $form->label('title', t('Title'));
    echo $form->text('title', $v['title'] ?? null);
    ?>
        </div>

        <div class="form-group">
            <?php
        echo $form->label('description', t('Description'));
    echo $form->text('description', $v['description'] ?? null);
    ?>
        </div>

        <div class="form-group">
            <?php
        echo $form->label('link', t('Link'));
    echo $h['ps']->selectPage('link', $v['link'] ?? false);
    ?>
        </div>

        <div class="form-group">
            <?php
        echo $form->label('sendDate', t('Send Date'));
    echo $h['dth']->datetime('sendDate', $v['sendDate'] ?? null);
    ?>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo \URL::to('/dashboard/push_notifications/custom'); ?>" class="btn btn-secondary float-start"><?php echo t('Cancel'); ?></a>
                <?php echo $form->submit('save', (isset($entity)) ? t('Update') : t('Create'), ['class' => 'btn btn-primary float-end']); ?>
            </div>
        </div>
    </form>
<?php } ?>
