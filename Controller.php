<?php
namespace DownloadAllFiles;

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use MapasCulturais\i;
use MapasCulturais\App;

class Controller extends \MapasCulturais\Controllers\EntityController {

    public function __construct()
    {
        parent::__construct();
        $this->entityClassName = '\DownloadAllFiles\download-registration';

    }

    /**
     * Exporta todas as inscrições e seus anexos em um arquivo ZIP
     * Inclui a ficha de inscrição (ficha.pdf) dentro de cada pasta
     */
    public function GET_createAllZipFiles()
    {
        $app = App::i();
        $this->requireAuthentication();

        $opportunityId = $this->data['opportunityId'] ?? null;
        if (!$opportunityId) {
            $this->errorJson(i::__("ID da oportunidade não enviado"), 400);
        }

        $opportunity = $app->repo('Opportunity')->find(['id' => $opportunityId]);
        if (!$opportunity) {
            $this->errorJson(i::__("Fase não encontrada"), 404);
        }

        if (!$opportunity->isFirstPhase) {
            $opportunity = $app->repo("Opportunity")->findOneBy(['id' => $opportunity->parent->id]);
            if (!$opportunity)
                $this->errorJson(i::__("Fase não encontrada"), 404);
        }

        $opportunity->checkPermission('@control');

        // Diretório temporário para exportação
        $zip = new \ZipArchive();
        $zipName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $opportunity->id . '-' . $opportunity->name);
        $tmpZipPath = sys_get_temp_dir() . '/' . uniqid($zipName) . '.zip';
        if ($zip->open($tmpZipPath, \ZipArchive::CREATE) !== true) {
            $this->errorJson(i::__("Erro ao criar o ZIP"), 500);
        }

        // Copia os anexos e acumula as respostas
        $fields = [];
        $phase = $opportunity;
        while(!!$phase) {
            $registrations = $phase->getAllRegistrations();

            foreach ($registrations as $registration) {

                if (!isset($fields[$registration->number])) // Usa-se o Number e não Id pois o Id muda conforme a fase
                    $fields[$registration->number] = [];

                if (!isset($fields[$registration->number][$phase->id]))
                    $fields[$registration->number][$phase->id] = [];

                $fields[$registration->number][$phase->id]["name"] = $phase->name;
                $fields[$registration->number][$phase->id]["status"] = $registration->getEvaluationResultString() ?? i::__("Pendente");
                $fields[$registration->number][$phase->id]["answers"] = $this->getAnswers($registration);

                if (!$registration->files) continue;

                foreach ($registration->files as $key => $file) {
                    if (is_array($file)) $file = $file[0];
                    if (!$file || !file_exists($file->path)) continue;

                    $parts = explode(' - ', $file->path, 3);
                    $fileName = count($parts) >= 3 ? $parts[2] : $file->path;

                    $pathInZip = $registration->number . '/'. i::__("anexos") . '-' . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $phase->name) . '/' . $fileName;
                    $zip->addFile($file->path, $pathInZip);
                }
            }
            $phase = $phase->nextPhase;
        }
        // Diretório temporário para exportação
        $basePath = sys_get_temp_dir() . '/export_' . uniqid();
        mkdir($basePath, 0777, true);

        //Gera as fichas de inscrição em PDF
        $registrations = $app->repo('Registration')->findBy(['opportunity' => $opportunity]) ?? [];
        foreach ($registrations as $registration) {
            $fileName = $basePath . '/' . $registration->number . '.pdf';
            $this->exportRegistrationToPdf($registration, $fields[$registration->number], $fileName);
            $pathInZip = $registration->number . '/' . $registration->number . '.pdf';
            $zip->addFile($fileName, $pathInZip);
        }

        $zip->close();

        if (!file_exists($tmpZipPath)) {
            @unlink($tmpZipPath);
            $this->errorJson(i::__("Nenhum anexo encontrado"), 500);
        }

        // Cabeçalhos para forçar download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipName) . '.zip"');
        header('Content-Length: ' . filesize($tmpZipPath));
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        readfile($tmpZipPath);
        unlink($tmpZipPath);
        $this->deleteFolder($basePath);
        exit;
    }

    private function getAnswers($registration)
    {
        $app = App::i();
        $app->disableAccessControl();

        json_encode($registration); // POR FAVOR nao remover essa lina. Ela faz com que o objeto seja completamente carregado e seja possível acessar os metadados. Só altere caso saiba fazer o carregamento corretamente.

        // Recupera campos da ficha usando getMetadata()
        $fieldsValues = method_exists($registration, 'getMetadata') ? $registration->getMetadata() : (isset($registration->metadata) ? $registration->metadata : []) ;
        $fieldsConfigurations = $registration->opportunity->getRegistrationFieldConfigurations();
        $filesConfigurations = $registration->opportunity->getRegistrationFileConfigurations();

        $fields = [];
        foreach ($fieldsConfigurations as $conf) {
            $key = 'field_' . $conf->id;
            $label = $conf->title ?? $conf->fieldName ?? $key;

            $fields[$label] = isset($fieldsValues[$key]) && $fieldsValues[$key] ? $fieldsValues[$key] : (isset($registration->$key) && $registration->$key ? $registration->$key : ''); // Se retornar '' é pq nao tem o campo respondido
        }

        foreach ($filesConfigurations as $conf) {
            $key = 'rfc_' . $conf->id;
            $label = $conf->title ?? $conf->groupName ?? $key;
            $path = "";

            if (isset($registration->files[$key])){
                $parts = explode(' - ', $registration->files[$key]->path, 3);
                $fileName = count($parts) >= 3 ? $parts[2] : $registration->files[$key]->path;

                $path = '/anexos-' . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $registration->opportunity->name) . '/' . $fileName;
            }
            $fields[$label] = $path;
        }
        $app->enableAccessControl();

        return $fields;
    }

    /**
     * Gera o PDF da ficha de inscrição e salva em $folder/ficha.pdf
     */
    private function exportRegistrationToPdf($registration, $answers, $fileName)
    {
        // Variáveis para acessar no template
        $nameOwner = $registration->owner->name;
        $singleUrlOwner = $registration->owner->singleUrl;

        // Gera HTML com template da ficha
        ob_start();
        include __DIR__ . '/views/registration-ficha.php';
        $html = ob_get_clean();

        // Converte em PDF usando Dompdf
        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Salva o PDF
        file_put_contents($fileName, $pdf->output());
    }

    /**
     * Remove uma pasta temporária recursivamente
     */
    private function deleteFolder($folder)
    {
        if (!is_dir($folder)) return;
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $path = "$folder/$file";
            is_dir($path) ? $this->deleteFolder($path) : unlink($path);
        }
        rmdir($folder);
    }
}
