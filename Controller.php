<?php
namespace DownloadAllFiles;

use MapasCulturais\App;

class Controller extends \MapasCulturais\Controllers\EntityController {

    public function __construct()
    {
        parent::__construct();
        $this->entityClassName = '\DownloadAllFiles\download-registration';

    }

    public function GET_createAllZipFiles() {
        $app = App::i();
        $this->requireAuthentication();
        $phases = [];

        $opportunityId = $this->data['opportunityId'] ?? null;
        if (!$opportunityId)
            $this->errorJson("ID da oportunidade não enviado", 400);

        $opportunity = $app->repo("Opportunity")->findOneBy(['id' => $opportunityId]);
        if (!$opportunity)
            $this->errorJson("Fase não encontrada", 404);

        if (!$opportunity->parent) {
            $phases = $app->repo("Opportunity")->findBy(['parent' => $opportunity->id]);
        } else {
            $opportunity = $app->repo("Opportunity")->findOneBy(['id' => $opportunity->parent->id]);
            if (!$opportunity)
                $this->errorJson("Fase não encontrada", 404);

            $phases = $app->repo("Opportunity")->findBy(['parent' => $opportunity->id]);
        }

        $opportunity->checkPermission('@control');

        $zip = new \ZipArchive();
        $fileName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $opportunity->id . '-' . $opportunity->name) . '.zip';
        $tmpZipPath = sys_get_temp_dir() . '/' . uniqid('zip_') . '.zip';

        if ($zip->open($tmpZipPath, \ZipArchive::CREATE) !== true)
            $this->errorJson("Erro ao criar o ZIP", 500);

        foreach([$opportunity, ...$phases] as $phase) {
            $registrations = $app->repo('Registration')->findBy(['opportunity' => $phase]) ?? [];

            foreach ($registrations as $registration) {
                if (!$registration->files) continue;

                foreach ($registration->files as $key => $file) {
                    if (is_array($file)) $file = $file[0];
                    if (!$file || !file_exists($file->path)) continue;

                    $pathInZip = $registration->number . '/' . $phase->name . '/' . basename($file->path);
                    $zip->addFile($file->path, $pathInZip);
                }
            }
        }

        $zip->close();

        if (!file_exists($tmpZipPath)) {
            @unlink($tmpZipPath);
            $this->errorJson("Nenhum anexo encontrado", 400);
        }

        // Cabeçalhos para forçar download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Content-Length: ' . filesize($tmpZipPath));
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        readfile($tmpZipPath);
        unlink($tmpZipPath);
        exit;
    }
}
