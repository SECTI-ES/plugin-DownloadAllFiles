<?php
namespace DownloadAllFiles;

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

use MapasCulturais\App;
use Exception;


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
        $phases = [];

        $opportunityId = $this->data['opportunityId'] ?? null;
        if (!$opportunityId) {
            $this->errorJson("ID da oportunidade não enviado", 400);
        }

        $opportunity = $app->repo('Opportunity')->find(['id' => $opportunityId]);
        if (!$opportunity) {
            $this->errorJson("Fase não encontrada", 404);
        }
        // $opportunity->isFirstPhase;
        // $phase->nextPhase;
        if (!$opportunity->parent) {
            $phases = $app->repo("Opportunity")->findBy(['parent' => $opportunity->id]);
        } else {
            $opportunity = $app->repo("Opportunity")->findOneBy(['id' => $opportunity->parent->id]);
            if (!$opportunity)
                $this->errorJson("Fase não encontrada", 404);

            $phases = $app->repo("Opportunity")->findBy(['parent' => $opportunity->id]);
        }

        $opportunity->checkPermission('@control');

        // Diretório temporário para exportação
        $zip = new \ZipArchive();
        $zipName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $opportunity->id . '-' . $opportunity->name);
        $tmpZipPath = sys_get_temp_dir() . '/' . uniqid($zipName) . '.zip';
        if ($zip->open($tmpZipPath, \ZipArchive::CREATE) !== true) {
            $this->errorJson("Erro ao criar o ZIP", 500);
        }

        // Copia os anexos e acumula as respostas
        $fields = [];
        foreach([$opportunity, ...$phases] as $phase) {
            $registrations = $app->repo('Registration')->findBy(['opportunity' => $phase]) ?? [];
            // $registrations = $entity->getAllRegistrations();
            foreach ($registrations as $registration) {

                if (!isset($fields[$registration->number])) // Usa-se o Number e não Id pois o Id muda conforme a fase
                    $fields[$registration->number] = [];

                if (!isset($fields[$registration->number][$phase->id]))
                    $fields[$registration->number][$phase->id] = [];

                $fields[$registration->number][$phase->id]["name"] = $phase->name;
                $fields[$registration->number][$phase->id]["answers"] = $this->getAnswers($registration);

                if (!$registration->files) continue;

                foreach ($registration->files as $key => $file) {
                    if (is_array($file)) $file = $file[0];
                    if (!$file || !file_exists($file->path)) continue;

                    $pathInZip = $registration->number . '/' . $phase->name . '/' . basename($file->path);
                    $zip->addFile($file->path, $pathInZip);
                }
            }
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
            $this->errorJson("Nenhum anexo encontrado", 500);
        }

        // Cabeçalhos para forçar download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
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
        $fieldsConfigurations = $registration->opportunity->registrationFieldConfigurations;

        $fields = [];
        foreach ($fieldsConfigurations as $conf) {
            $key = 'field_' . $conf->id;
            $label = $conf->title ?? $conf->fieldName ?? $key;

            $fields[$label] = isset($fieldsValues[$key]) && $fieldsValues[$key] ? $fieldsValues[$key] : (isset($registration->$key) && $registration->$key ? $registration->$key : '');
        }
        $app->enableAccessControl();

        return $fields;
    }

    /**
     * Resolve CSS variables (--var) para uso com Dompdf.
     *
     * Suporta:
     * - :root { --x: valor; }
     * - html { --x: valor; }
     * - var(--x)
     * - var(--x, fallback)
     */
    function resolveCssVariables(string $html): string
    {
        $variables = [];

        // 1️⃣ Extrai TODOS os blocos <style>
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $styles)) {
            foreach ($styles[1] as $css) {

                // 2️⃣ Captura todas as variáveis CSS (--x: valor;)
                if (preg_match_all(
                    '/(--[a-zA-Z0-9\-_]+)\s*:\s*([^;]+);/',
                    $css,
                    $defs,
                    PREG_SET_ORDER
                )) {
                    foreach ($defs as $def) {
                        $variables[$def[1]] = trim($def[2]);
                    }
                }
            }
        }

        // Nenhuma variável encontrada → retorna original
        if (empty($variables)) {
            return $html;
        }

        // 3️⃣ Substitui var(--x) e var(--x, fallback)
        $html = preg_replace_callback(
            '/var\(\s*(--[a-zA-Z0-9\-_]+)\s*(?:,\s*([^)]+))?\)/',
            function ($matches) use ($variables) {
                $name = $matches[1];
                $fallback = $matches[2] ?? '';

                return $variables[$name] ?? $fallback;
            },
            $html
        );

        // 4️⃣ Remove apenas as definições --x: valor; (mantém o resto do CSS)
        $html = preg_replace(
            '/--[a-zA-Z0-9\-_]+\s*:\s*[^;]+;/',
            '',
            $html
        );

        return $html;
    }

    /**
     * Filtra arquivos selecionados, pegando apenas o primeiro de cada grupo duplicado
     * Padrão de arquivo: <nome>.<dist>.<id>.<extensão>
     * Exemplo: main.prod.1.css, main.prod.2.css -> pega apenas main.prod.1.css
     *
     * @param array $selectedNames Array com nomes base desejados ['main', 'tables', 'forms']
     * @param string $Folder Caminho da pasta com os arquivos
     * @return array Array de caminhos dos arquivos filtrados
     */
    function filterAndSelectFiles($selectedNames, $folder, $extension = "*") {
        $webFolder = "/assets/$folder/";
        $fullFolder = $_SERVER['DOCUMENT_ROOT'] . $webFolder;

        $result = [];

        if (!is_dir($fullFolder) || empty($selectedNames)) {
            return $result;
        }

        // Pega todos os arquivos da extensão especificada
        $files = glob(rtrim($fullFolder, '/') . '/*.' . $extension);
        sort($files, SORT_NATURAL);

        // Cria um mapa de arquivos por nome base
        $filesByName = [];
        foreach ($files as $filePath) {
            if (!is_file($filePath) || !is_readable($filePath)) continue;

            $fileName = basename($filePath); // ex: main.prod.1.css

            // Extrai o nome base (primeira parte antes do primeiro ponto)
            $baseName = explode('.', $fileName)[0]; // ex: main

            // Armazena apenas o primeiro arquivo de cada nome base
            if (in_array($baseName, $selectedNames) && !isset($filesByName[$baseName])) {
                $filesByName[$baseName] = $filePath;
                $result[] = $filePath;
            }
        }

        return $result;
    }

    function statusName($status) {
        if($status === 10) {
            return i::__('Selecionada');
        } elseif($status === 8) {
            return i::__('Suplente');
        } elseif($status === 3) {
            return i::__('Não selecionada');
        } elseif($status === 2) {
            return i::__('Inválida');
        } elseif($status === 1) {
            return i::__('Pendente');
        } else {
            return i::__('Rascunho');
        }
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


        // Gera PDF
        try {
            $html = $this->resolveCssVariables($html);
            // error_log("HTML gerado para PDF: \n" . $html);
            // echo $html;
            // exit;

            // Converte em PDF usando Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', $_SERVER['DOCUMENT_ROOT']);

            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            file_put_contents($fileName, $pdf->output());
        } catch (\Exception $e) {
            error_log("Erro ao gerar PDF: " . $e->getMessage());
            $this->errorJson("Erro ao gerar PDF: " . $e->getMessage(), 500);
        }
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
