<?php
namespace DownloadAllFiles;

use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin {

    public function __construct($config = []) {
        parent::__construct($config);
    }

    public function _init() {
        $app = App::i();

        $app->hook('template(<<*>>.<<*>>.registration-list-actions-entity-table):end', function($entity) {
            $this->part('download/download-files', [
                'entity' => $entity
            ]);
        });
    }

    public function register() {
        $app = App::i();
        $app->registerController('download-registration', Controller::class);
    }
}