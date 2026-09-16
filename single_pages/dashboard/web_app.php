<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($token) && isset($view) && isset($form_color) && isset($concrete_asset_library)) { ?>
    <form method="post" action="<?php echo $view->action('save'); ?>">
        <?php echo $token->output('webapp_settings_submit') ?>

        <fieldset>
            <legend><?php echo t('Status'); ?></legend>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="activate" name="activate" class="form-check-input" value="1" <?php echo (isset($formContent) && isset($formContent['activate'])) ? 'checked' : ((isset($pkg) && $pkg->getFileConfig()->get('web_app.activate') == true) ? 'checked' : ''); ?> />
                    <label for="activate" class="form-check-label"><?php echo t('Make this site web app capable'); ?></label>
                </div>

                <div class="help-block">
                    <a href="https://www.gartner.com/en/information-technology/glossary/mobile-web-applications#:~:text=Mobile%20Web%20applications%20refer%20to,be%20installed%20on%20the%20device.&text=Mobile%20Web%20applications%20differ%20from,the%20underlying%20platform%20for%20deployment." target="_blank">
                        <?php echo t('What does this mean?'); ?>
                    </a>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Basic Information'); ?></legend>

            <div class="form-group">
                <?php echo $form->label('name', t('Name')); ?>
                <div class="float-end">
                    <span class="text-muted small"><?php echo t('Required'); ?></span>
                </div>
                <?php echo $form->text('name', (isset($formContent)) ? $formContent['name'] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.name') : ''), [ 'required' => 'required' ]); ?>
            </div>

            <div class="form-group">
                <?php echo $form->label('short_name', t('Short Name')); ?>
                <?php echo $form->text('short_name', (isset($formContent)) ? $formContent['short_name'] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.short_name') : '')); ?>
            </div>

            <div class="form-group">
                <?php
                    echo $form->label('categories', t('Categories'));
    echo $form->text('categories', (isset($formContent) && is_array($formContent['categories'])) ? implode(',', $formContent['categories']) : ((isset($pkg) && is_array($pkg->getFileConfig()->get('web_app.categories'))) ? implode(',', $pkg->getFileConfig()->get('web_app.categories')) : ''));
    ?>
                <div class="help-block"><?php echo t('Insert as a comma (,) seperated list.'); ?></div>
            </div>

            <div class="form-group">
                <?php
        echo $form->label('description', t('Description'));
    echo $form->textarea('description', (isset($formContent)) ? $formContent['description'] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.description') : ''));
    ?>
            </div>

            <div class="form-group">
                <?php
        echo $form->label('display', t('Display'));
    echo (string) $form->select('display', $displayMethods ?? [], (isset($formContent)) ? $formContent['display'] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.display') : ''));
    ?>

                <div class="help-block">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col" style="white-space: nowrap;"><?php echo t('Display Mode'); ?></th>
                                <th scope="col" style="white-space: nowrap;"><?php echo t('Description'); ?></th>
                                <th scope="col" style="white-space: nowrap;"><?php echo t('Fallback Display Mode'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code><?php echo t('fullscreen'); ?></code></td>
                                <td>
                                    <?php echo t('All of the available display area is used and no user agent <a href="%s" target="_blank">chrome</a> is shown.', 'https://developer.mozilla.org/en-US/docs/Glossary/Chrome'); ?>
                                </td>
                                <td><code><?php echo t('standalone'); ?></code></td>
                            </tr>
                            <tr>
                                <td><code><?php echo t('standalone'); ?></code></td>
                                <td>
                                    <?php echo t('The application will look and feel like a standalone application. This can include the application having a different window, its own icon in the application launcher, etc. In this mode, the user agent will exclude UI elements for controlling navigation, but can include other UI elements such as a status bar.'); ?>
                                </td>
                                <td><code><?php echo t('minimal'); ?>-ui</code></td>
                            </tr>
                            <tr>
                                <td><code><?php echo t('minimal'); ?>-ui</code></td>
                                <td>
                                    <?php echo t('The application will look and feel like a standalone application, but will have a minimal set of UI elements for controlling navigation. The elements will vary by browser.'); ?>
                                </td>
                                <td><code><?php echo t('browser'); ?></code></td>
                            </tr>
                            <tr>
                                <td><code><?php echo t('browser'); ?></code></td>
                                <td>
                                    <?php t('The application opens in a conventional browser tab or new window, depending on the browser and platform. This is the default.'); ?>
                                </td>
                                <td><?php echo t('(None)'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Colours'); ?></legend>

            <div class="form-group">
                <span style="margin-right:10px;">
                    <?php echo $form_color->output('theme_color', (isset($formContent)) ? $formContent['theme_color'] : ((isset($pkg) && $pkg->getFileConfig()->get('web_app.theme_color') !== null) ? $pkg->getFileConfig()->get('web_app.theme_color') : ''), [ 'preferredFormat' => 'hex' ]); ?>
                </span>

                <?php echo $form->label('theme_color', t('Theme Colour')); ?>
            </div>

            <div class="form-group">
                <span style="margin-right:10px;">
                    <?php echo $form_color->output('background_color', (isset($formContent)) ? $formContent['background_color'] : ((isset($pkg) && $pkg->getFileConfig()->get('web_app.background_color') !== null) ? $pkg->getFileConfig()->get('web_app.background_color') : ''), [ 'preferredFormat' => 'hex' ]); ?>
                </span>

                <?php echo $form->label('background_color', t('Background Colour')); ?>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Icons'); ?></legend>

            <div class="form-group">
                <?php echo $form->label('iosHomeFID', t('iPhone Thumbnail')); ?>
                <div class="float-end">
                    <span class="text-muted small"><?php echo t('Required'); ?></span>
                </div>
                <?php echo $concrete_asset_library->image('ccm-iphone-file', 'iosHomeFID', t('Choose File'), $iosHome ?? false, ['filters' => [['field' => 'extension', 'extension' => 'png']]]); ?>
                <div class="help-block"><?php echo t('iPhone home screen icons should be 57x57 and be in the .png format with a non transparent background.') ?></div>
            </div>

            <?php foreach ($iconDimensions ?? [] as $i) { ?>
                <div class="form-group">
                    <?php
            echo $form->label($i, t('Icon: (' . $i . ')'));
                echo $concrete_asset_library->image($i, 'icons[' . $i . ']', t('Choose image for ' . $i), (isset($formContent)) ? $formContent['icons'][$i] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.icons.' . $i) : ''));
                ?>
                </div>
            <?php } ?>
        </fieldset>

        <fieldset>
            <legend><?php echo t('Launchscreen Images'); ?></legend>

            <?php foreach ($iosLaunchScreenDimensions ?? [] as $ls) { ?>
                <div class="form-group">
                    <?php
                    echo $form->label($ls, t('Screen Size: (' . $ls . ')'));
                echo $concrete_asset_library->image($ls, 'launchscreen[' . $ls . ']', t('Choose image for ' . $ls), (isset($formContent)) ? $formContent['launchscreen'][$ls] : ((isset($pkg)) ? $pkg->getFileConfig()->get('web_app.launchscreen.' . $ls) : ''));
                ?>
                </div>
            <?php } ?>

        </fieldset>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <?php echo $form->submit('save', t('Save Settings'), ['class' => 'btn btn-primary float-end']); ?>
            </div>
        </div>
    </form>
<?php } ?>
