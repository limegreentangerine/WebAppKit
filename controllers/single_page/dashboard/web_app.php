<?php

namespace Concrete\Package\WebApp\Controller\SinglePage\Dashboard;

use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use File;
use Symfony\Component\HttpFoundation\Response;

class WebApp extends DashboardPageController
{
    protected $helpers = [
        'form',
        'form/color',
        'concrete/asset_library',
    ];

    protected Liaison $config;

    protected Package $pkg;

    /**
     * @return array<string>
     */
    public array $iconDimensions = [
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
    public array $iosLaunchScreenDimensions = [
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
    public array $displayMethods = [
        '' => 'Choose a display method...',
        'fullscreen' => 'Fullscreen',
        'standalone' => 'Standalone',
        'minimal-ui' => 'Minimal UI',
        'browser' => 'Browser',
    ];

    protected function validateSubmit(array $post): void
    {
        $vstrings = $this->app->make('helper/validation/strings');

        if (!$vstrings->notempty($post['name'])) {
            $this->error->add('Please enter a name', 'name');
        }

        if ($post['iconFile'] == 0) {
            $this->error->add('Icon required', 'iconFile');
        }

        if ($post['launchFile'] == 0) {
            $this->error->add('Launchscreen required', 'launchFile');
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
        $data['name'] = $this->config->get('web_app.name');
        if ($this->config->get('web_app.short_name') !== null) {
            $data['short_name'] = $this->config->get('web_app.short_name');
        }
        if ($this->config->get('web_app.categories') !== null) {
            $data['categories'] = $this->config->get('web_app.categories');
        }
        if ($this->config->get('web_app.description') !== null) {
            $data['description'] = $this->config->get('web_app.description');
        }
        if ($this->config->get('web_app.display') !== null) {
            $data['display'] = $this->config->get('web_app.display');
        }
        if ($this->config->get('web_app.theme_color') !== null) {
            $data['theme_color'] = $this->config->get('web_app.theme_color');
        }
        if ($this->config->get('web_app.background_color') !== null) {
            $data['background_color'] = $this->config->get('web_app.background_color');
        }

        foreach ($this->config->get('web_app.icons') as $size => $path) {
            $data['icons'][] = [
                'src' => $path,
                'type' => 'image/png',
                'sizes' => $size
            ];
        }

        // create new manifest and write json encoded data to file
        $manifest = fopen($fileName, 'w') or throw new UserMessageException('Unable to open manifest file!');
        $encodedData = str_replace('\/', '/', json_encode($data)); // str_replace to remove escaped url slashes
        fwrite($manifest, $encodedData);
        fclose($manifest);
    }

    public function on_start()
    {
        parent::on_start();

        $this->pkg = Core::make(PackageService::class)->getClass('web_app');
        $this->set('pkg', $this->pkg);

        $this->config = $this->pkg->getFileConfig();;
        $iconFile = File::getByID($this->config->get('web_app.iconFile'));
        $launchFile = File::getByID($this->config->get('web_app.launchFile'));

        $this->set('iconFile', $iconFile ? $iconFile : null);
        $this->set('launchFile', $launchFile ? $launchFile : null);
    }

    public function save()
    {
        $post = $this->post();

        if (!$this->token->validate('webapp_settings_submit')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $this->validateSubmit($post);

        if (!$this->error->has()) {
            // status
            $this->config->save('web_app.activate', ($post['activate']) ? true : false);

            // basic
            $this->config->save('web_app.name', $post['name']);
            if ($post['short_name'] !== '') {
                $this->config->save('web_app.short_name', $post['short_name']);
            }
            if ($post['categories'] !== '') {
                $this->config->save('web_app.categories', explode(',', $post['categories']));
            }
            if ($post['description'] !== '') {
                $this->config->save('web_app.description', $post['description']);
            }
            if ($post['display'] !== '') {
                $this->config->save('web_app.display', $post['display']);
            }

            // colours
            if ($post['theme_color'] !== '') {
                $this->config->save('web_app.theme_color', $post['theme_color']);
            }
            if ($post['background_color'] !== '') {
                $this->config->save('web_app.background_color', $post['background_color']);
            }

            $this->config->save('web_app.iconFile', $post['iconFile']);
            $this->config->save('web_app.launchFile', $post['launchFile']);

            $this->generateSiteManifest();

            $this->flash('success', t('Web App settings saved. ' . DIR_BASE . '/site.webmanifest generated.'));
            return $this->buildRedirect('/dashboard/web_app');
        }
        $this->set('errors', $this->error);
        $this->set('formContent', $post);
    }

    public function generate(string $type): ?Response
    {
        $ih = Core::make('helper/image');
        $ih->setThumbnailsFormat('png');

        match ($type) {
            'icons' => (function () use ($ih) {
                $iconPaths = [];
                $iconFile = File::getByID($this->config->get('web_app.iconFile'));
                if (!$iconFile) {
                    $this->error->add('Icon not found');
                    return;
                }

                $validationSizes = explode('x', end($this->iconDimensions));
                $validationWidth = $validationSizes[0];
                $validationHeight = $validationSizes[1];

                if ((int) $validationWidth > (int) $iconFile->getAttribute('width')) {
                    $this->error->add(t('Base icon not at least %s in width', $validationWidth));
                    return;
                }

                if ((int) $validationHeight > (int) $iconFile->getAttribute('height')) {
                    $this->error->add(t('Base icon not at least %s in height', $validationHeight));
                    return;
                }

                foreach ($this->iconDimensions as $size) {
                    $sizes = explode('x', $size);
                    $generatedThumbnail = $ih->processThumbnail(false, $iconFile, $sizes[0], $sizes[1], false);
                    $iconPaths[$size] = $generatedThumbnail->src;
                }

                $this->config->save('web_app.icons', $iconPaths);
                return;
            })(),
            'launchscreens' => (function () use ($ih) {
                $launchscreenPaths = [];
                $launchFile = File::getByID($this->config->get('web_app.launchFile'));
                if (!$launchFile) {
                    $this->error->add('Launchscreen not found');
                    return;
                }

                $validationSizes = explode('x', end($this->iosLaunchScreenDimensions));
                $validationWidth = $validationSizes[0];
                $validationHeight = $validationSizes[1];

                if ((int) $validationWidth > (int) $launchFile->getAttribute('width')) {
                    $this->error->add(t('Base launchscreen not at least %s in width', $validationWidth));
                    return;
                }

                if ((int) $validationHeight > (int) $launchFile->getAttribute('height')) {
                    $this->error->add(t('Base launchscreen not at least %s in height', $validationHeight));
                    return;
                }

                foreach ($this->iosLaunchScreenDimensions as $size) {
                    $sizes = explode('x', $size);
                    $generatedThumbnail = $ih->processThumbnail(false, $launchFile, $sizes[0], $sizes[1], false);
                    $launchscreenPaths[$sizes[0]] = $generatedThumbnail->src;
                }

                $this->config->save('web_app.launchscreens', $launchscreenPaths);
                return;
            })(),
            default => (function() {
                $this->error->add(t('Invalid image generation type.'));
                return;
            })()
        };

        if (!$this->error->has()) {
            $this->flash('success', t('%s generated successfully', ucfirst($type)));
            return $this->buildRedirect('/dashboard/web_app');
        }

        $this->set('errors', $this->error);
        return null;
    }

    public function manifest()
    {
        $this->generateSiteManifest();
        $this->flash('success', t('/site.webmanifest file created'));
        return $this->buildRedirect('/dashboard/web_app');
    }
}
