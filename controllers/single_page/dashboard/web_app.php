<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard;

use Core;
use File;
use Concrete\Core\Package\Package;
use Concrete\Core\Site\Config\Liaison;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\Utility\Service\Validation\Numbers;
use Concrete\Core\Page\Controller\DashboardPageController;

class WebApp extends DashboardPageController
{
    protected $helpers = [
        'form',
        'form/color',
        'concrete/asset_library',
    ];

    /**
     * @return array<string>
     */
    protected array $iconDimensions = [
        '48x48',
        '72x72',
        '96x96',
        '144x144',
        '168x168',
        '192x192',
        '256x256',
        '512x512',
    ];

    /**
     * @return array<string>
     */
    protected array $iosLaunchScreenDimensions = [
        '640x1136',
        '750x1294',
        '1242x2148',
        '1125x2436',
        '1536x2048',
        '1668x2224',
        '2048x2732',
    ];

    /**
     * @return array<string, string>
     */
    protected array $displayMethods = [
        '' => 'Choose a display method...',
        'fullscreen' => 'Fullscreen',
        'standalone' => 'Standalone',
        'minimal-ui' => 'Minimal UI',
        'browser' => 'Browser',
    ];

    protected Liaison $siteConfig;

    protected Package $pkg;

    protected function validateSubmit(array $post): void
    {
        $vstrings = $this->app->make('helper/validation/strings');

        if (!$vstrings->notempty($post['name'])) {
            $this->error->add('Please enter a name', 'name');
        }

        if ($post['iosHomeFID'] == 0) {
            $this->error->add('iPhone Bookmark Icon required', 'iosHomeFID');
        }
    }

    protected function generateSiteManifest(): void
    {
        // remove current manifest
        $fileName = DIR_BASE . '/site.webmanifest';
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        // empty data array
        $data = [];

        // add settings to data array
        $config = $this->pkg->getFileConfig();
        $data['name'] = $config->get('web_app.name');
        if ($config->get('web_app.short_name') !== null) {
            $data['short_name'] = $config->get('web_app.short_name');
        }
        if ($config->get('web_app.categories') !== null) {
            $data['categories'] = $config->get('web_app.categories');
        }
        if ($config->get('web_app.description') !== null) {
            $data['description'] = $config->get('web_app.description');
        }
        if ($config->get('web_app.display') !== null) {
            $data['display'] = $config->get('web_app.display');
        }
        if ($config->get('web_app.theme_color') !== null) {
            $data['theme_color'] = $config->get('web_app.theme_color');
        }
        if ($config->get('web_app.background_color') !== null) {
            $data['background_color'] = $config->get('web_app.background_color');
        }

        foreach ($config->get('web_app.icons') as $size => $fID) {
            if ($fID > 0) {
                $file = File::getByID($fID);
                if ($file) {
                    $version = $file->getApprovedVersion();
                    $size = ($size == 'default') ? '57x57' : $size;
                    $data['icons'][] = [
                        'src' => $version->getRelativePath(),
                        'type' => $version->getMimeType(),
                        'sizes' => '57x57',
                    ];
                }
            }
        }

        // create new manifest and write json encoded data to file
        $manifest = fopen($fileName, 'w') or die('Unable to open file!');
        $encodedData = str_replace('\/', '/', json_encode($data)); // str_replace to remove escaped url slashes
        fwrite($manifest, $encodedData);
        fclose($manifest);
    }

    public function on_start()
    {
        parent::on_start();


        $this->siteConfig = Core::make('site')->getSite()->getConfigRepository();
        $fid = (int) $this->siteConfig->get('misc.iphone_home_screen_thumbnail_fid');
        $file = $this->entityManager->find(FileEntity::class, $fid);
        $this->set('iosHome', $file ? $file : null);

        $this->pkg = Core::make(PackageService::class)->getClass('web_app');
        $this->set('pkg', $this->pkg);

        $this->set('iosLaunchScreenDimensions', $this->iosLaunchScreenDimensions);
        $this->set('iconDimensions', $this->iconDimensions);
        $this->set('displayMethod', $this->displayMethods);
    }

    public function save()
    {
        $post = $this->post();

        if (!$this->token->validate('webapp_settings_submit')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $this->validateSubmit($post);

        if (!$this->error->has()) {
            // save iphone thumbnail
            $valn = $this->app->make(Numbers::class);
            $ios_fid = $post['iosHomeFID'];
            $this->siteConfig->save('misc.iphone_home_screen_thumbnail_fid', $valn->integer($ios_fid, 1) ? (int) $ios_fid : null);

            // set all package settings
            $config = $this->pkg->getFileConfig();

            // status
            $config->save('web_app.activate', ($post['activate']) ? true : false);

            // basic
            $config->save('web_app.name', $post['name']);
            if ($post['short_name'] !== '') {
                $config->save('web_app.short_name', $post['short_name']);
            }
            if ($post['categories'] !== '') {
                $config->save('web_app.categories', explode(',', $post['categories']));
            }
            if ($post['description'] !== '') {
                $config->save('web_app.description', $post['description']);
            }
            if ($post['display'] !== '') {
                $config->save('web_app.display', $post['display']);
            }

            // colours
            if ($post['theme_color'] !== '') {
                $config->save('web_app.theme_color', $post['theme_color']);
            }
            if ($post['background_color'] !== '') {
                $config->save('web_app.background_color', $post['background_color']);
            }

            // icons
            $config->save('web_app.icons.default', (int) $post['iosHomeFID']);
            foreach ($post['icons'] as $size => $fID) {
                $config->save('web_app.icons.' . $size, (int) $fID);
            }

            // launchscreen images
            foreach ($post['launchscreen'] as $size => $fID) {
                $config->save('web_app.launchscreens.' . $size, (int) $fID);
            }

            $this->generateSiteManifest();

            $this->flash('success', t('Web App settings saved. ' . DIR_BASE . '/site.webmanifest generated.'));
            return $this->buildRedirect('/dashboard/web_app');
        }
        $this->set('errors', $this->error);
        $this->set('formContent', $post);

    }
}
