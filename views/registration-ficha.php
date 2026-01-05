<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Controllers\EntityController\Controller $this
 */

use MapasCulturais\i;
use MapasCulturais\app;

$app = App::i();
$theme = $app->view;

date_default_timezone_set('America/Sao_Paulo');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?= i::__("Ficha de Inscrição") ?></title>

        <?php
        $files = $this->filterAndSelectFiles(['theme-BaseV2'], "css");

        echo "<style>\n";

        foreach ($files as $filePath) {
            if (!is_file($filePath) || !is_readable($filePath))
                continue;

            $cssContent = file_get_contents($filePath);
            if ($cssContent === false)
                continue;

            // Captura TODOS os blocos :root { ... }
            if (preg_match_all('/:root\s*\{([^}]*)\}/is', $cssContent, $matches)) {
                foreach ($matches[0] as $rootBlock) {
                    echo $rootBlock . "\n";
                }
            }
        }

        echo "</style>\n";
        ?>

        <style>
            /* RESET BÁSICO */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html {
                width: 100%;
                height: 100%;
                /* background: linear-gradient(155deg, var(--mc-secondary-500) 0%, var(--mc-white) 50%, var(--mc-primary-500) 100%); */
                font-family: DejaVu Sans, sans-serif;
                font-size: 16px;
                color: var(--mc-black);
            }

            body {
                background-color: var(--mc-white);
            }

            a {
                color: var(--mc-black);
                text-decoration: none;
            }
            .header {
                margin-bottom: 30px;
            }

            .header h1 {
                font-size: 33px;
                font-weight: bold;
                width: fit-content;
                white-space: nowrap;
            }

            .header h2 {
                margin: 10px 0 5px 0;
                font-size: 22px;
                font-weight: normal;
                opacity: 0.95;
            }

            .register, .section {
                width: 100%;
                padding-bottom: 30px;
            }

            .register-header {
                background: var(--mc-primary-500);
                color: var(--mc-white);
                font-weight: bold;
                font-size: 20px;
                border: 3px solid var(--mc-black);
                border-bottom: 0;
                width: 256px;
                padding: 15px 0 15px 0;
                text-align: center;
                border-top-left-radius: 26px;
                border-top-right-radius: 26px;
                margin-bottom: -47px;
                height: 80px;
            }

            .table-border {
                border: 3px solid var(--mc-black);
                overflow: hidden;
                border-radius: 25px;
                z-index: 2;
            }
            .register-table, .table-phases-body {
                border-collapse: collapse;
                width: 100%;
            }

            .register-table th,
            .register-table td {
                padding: 13px;
            }

            /* linhas */
            .table tr + tr td,
            .table tr + tr th {
                border-top: 3px solid var(--mc-black);
            }

            /* colunas */
            .table th + th,
            .table td + td,
            .table th + td {
                border-left: 3px solid var(--mc-black);
            }

            td, th {
               vertical-align: middle;
            }

            .title {
               width: 70%;
            }
            .logo {
                width: 30%;
                text-align: right;
            }
            .logo img {
                margin-top: -39px;
            }

            .register-table th {
                background: var(--mc-secondary-500);
                color: var(--mc-white);
                font-weight: bold;
                width: 230px;
            }

            .register-table td {
                background: var(--mc-white);
            }

            .table-phases {
                width: 100%;
                overflow: hidden;
                padding-bottom: 30px;
            }

            .table-phases-header {
                background-color: var(--mc-black);
                color: var(--mc-white);
                font-size: 20px;
                font-weight: bold;
                padding: 2% 5% 0 7%;
                height: 73px;

                z-index: -2;
                border-top-left-radius: 26px;
                border-top-right-radius: 26px;
                margin-bottom: -20px;
            }

            .status-box{
                font-size: 14px;
                font-weight: normal;
                padding-top: 5px;
            }

            .table-phases-body th {
                background-color: var(--mc-secondary-500);
                color: var(--mc-white);
                font-size: 16px;
                padding: 11px;
            }

            .table-phases-body td {
                font-size: 14px;
                background-color: var(--mc-white);
                color: var(--mc-black);
                padding: 13px;
            }

            .table-phases-body tr,

            .status {
                padding: 10px;
                font-size: 12px;
                border-radius: 8px;
            }

            .status-Selecionada {
                background: var(--mc-success-500);
                color: var(--mc-white);
            }

            .status-Pendente {
                background: var(--mc-gray-700);
                color: var(--mc-white);
            }

            .status-Suplente {
                background: var(--mc-warning-500);
                color: var(--mc-white);
            }

            /* Não Selecionada */
            .status-Não {
                background: var(--mc-danger-500);
                color: var(--mc-white);
            }

            .status-Inválida {
                background: var(--mc-danger-500);
                color: var(--mc-white);
            }

            .status-Rascunho {
                background: var(--mc-gray-500);
                color: var(--mc-white);
            }

            .field {
                text-align: center;
                width: 30%;
            }

            .page-content {
                margin: 80px;
            }

            .footer-generated {
                position: fixed;
                right: 30px;
                bottom: 20px;

                font-size: 10px;
                color: var(--mc-black);
            }
        </style>
    </head>
    <body>
        <div class="page-content">
            <div class="header">
                <table width="100%">
                    <tr>
                        <td align="left" class="title">
                            <h1><?= i::__("Ficha de Inscrição") ?></h1>
                            <h2><a href="<?= htmlspecialchars($registration->opportunity->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->opportunity->id) . htmlspecialchars($registration->opportunity->name ? ' - ' . $registration->opportunity->name : '') ?></a></h2>
                        </td>
                        <td class="logo" align="right">
                                <img src="file://<?= $this->filterAndSelectFiles(["logo"], "img")[0]; ?>" alt="<?= $this->filterAndSelectFiles(["logo"], "img")[0]; ?>" style="width: 190px;">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="register">
                <div class="register-header">Dados do Inscrito</div>
                <div class="table-border">
                    <table class="table register-table">
                        <?php if (!empty($registration->id)): ?>
                        <tr>
                            <th><?= i::__("Número da inscrição") ?></th>
                            <td><a href="<?= htmlspecialchars($registration->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->id) ?></a></td>
                        </tr>
                        <?php endif; ?>

                        <?php if (!empty($nameOwner)): ?>
                        <tr>
                            <th><?= i::__("Nome") ?></th>
                            <td><a href="<?= htmlspecialchars($singleUrlOwner ?? '#') ?>"><?= htmlspecialchars($nameOwner ?? '') ?></a></td>
                        </tr>

                        <?php endif; ?>


                        <?php if (!empty($registration->proponentType)): ?>
                        <tr>
                            <th><?= i::__("Tipo de Proponente") ?></th>
                            <td><?= htmlspecialchars($registration->proponentType) ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if (!empty($registration->projectName)): ?>
                        <tr>
                            <th><?= i::__("Nome do Projeto") ?></th>
                            <td><?= htmlspecialchars($registration->projectName) ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if (!empty($registration->category)): ?>
                        <tr>
                            <th><?= i::__("Categoria") ?></th>
                            <td><?= htmlspecialchars($registration->category) ?></td>
                        </tr>
                        <?php endif; ?>

                        <tr>
                            <th><?= i::__("Data de inscrição") ?></th>
                            <td><?= $registration->createTimestamp ? $registration->createTimestamp->format('d/m/Y H:i') : '' ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="section">
                <div class="header"><h2><?= i::__("Respostas dos Formulários por Fase") ?></h2></div>
                    <?php foreach ($answers as $formId => $form): ?>
                        <?php if (isset($form['answers']) && !empty($form['answers'])): ?>
                            <div class="table-phases">
                                <div class="table-phases-header">
                                    <table width="100%">
                                        <tr>
                                            <td align="left" style="margin-top: 1%;">
                                                <?= htmlspecialchars($form['name'] ?? ($formId ?? "")) ?>
                                            </td>
                                            <td align="right">
                                                <div class="status-box">
                                                    Status da Avaliação:
                                                    <span class="status status-<?= isset($form['status']) ? $form['status'] : (  isset($registration->status) ? $this->statusName($registration->status) : 'Rascunho' ) ?>">
                                                        <?= isset($form['status']) ? $form['status'] : ( isset($registration->status) ? $this->statusName($registration->status) : 'Rascunho' ) ?>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="table-border">
                                    <table class="table table-phases-body">
                                        <tr class="table-header">
                                            <th><?= i::__("Campo") ?></th>
                                            <th><?= i::__("Resposta") ?></th>
                                        </tr>

                                        <?php foreach ($form['answers'] as $key => $value): ?>
                                            <tr>
                                                <td class="field"><?= htmlspecialchars($key) ?></td>
                                                <td><?= htmlspecialchars(is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <div class="footer-generated">
            Ficha Gerada em: <?= date('d/m/Y H:i:s') ?>
        </div>
    </body>
</html>
