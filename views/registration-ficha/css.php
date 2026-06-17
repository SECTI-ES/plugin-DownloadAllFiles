<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Controllers\EntityController\Controller $this
 */

use MapasCulturais\i;

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
                padding-top: 40px;
            }

            a {
                color: var(--mc-black);
                text-decoration: none;
            }

            .header h1 {
                font-size: 33px;
                font-weight: bold;
                width: fit-content;
                white-space: nowrap;
            }

            .header h2 {
                margin: 10px 0 15px 0;
                font-size: 22px;
                font-weight: normal;
                opacity: 0.95;
            }

            .register, .section {
                width: 100%;
                padding-bottom: 15px;
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
                margin-top: 10px;
                height: 80px;
            }

            .table-border {
                border: 3px solid var(--mc-black);
                overflow: hidden;
                border-radius: 25px;
                z-index: 2;
            }
            .table {
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
                padding: 15px;
                margin-bottom: 20px;
                background-color: var(--mc-black);
                border-radius: 26px;
            }

            .table-phases-header {
                color: var(--mc-white);
                font-size: 20px;
                font-weight: bold;
                padding: 0 5% 0 5%;
                height: 40px;
            }

            .status-box{
                font-size: 14px;
                font-weight: normal;
                padding-top: 4px;
            }

            .step {
                page-break-inside: avoid;
                margin-bottom: 5px;
            }

            .step-title {
                background-color: var(--mc-primary-500);
                text-align: center;
                width: 181px;
                padding: 5px 3px 3px 3px;
                border: 3px solid var(--mc-black);
                border-radius: 20px;
                margin-top: 5px;
                margin-bottom: -42px;
                height: 70px;
            }
            .table-step-header th {
                background: var(--mc-secondary-500);
                color: var(--mc-white);
                font-weight: bold;
                border-bottom: 3px solid var(--mc-black);
                padding: 10px;
            }

            .table-step-body {
                background: var(--mc-white);
                color: var(--mc-black);
            }

            .table-step-body td {
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-word;
                white-space: normal;
                padding: 8px;
            }

            .status {
                padding: 10px;
                font-size: 12px;
                border-radius: 8px;
            }
            .status-10 {  /* Selecionado */
                background: var(--mc-success-500);
                color: var(--mc-white);
            }

            .status-1 {  /* Pendente */
                background: var(--mc-gray-700);
                color: var(--mc-white);
            }

            .status-8 {  /* Suplente */
                background: var(--mc-warning-500);
                color: var(--mc-black);
            }

            .status-3 {  /* Não Selecionada */
                background: var(--mc-danger-500);
                color: var(--mc-white);
            }

            .status-2, .status--1 {  /* Inválida ou Nulo - purple */
                background: purple;
                color: var(--mc-white);
            }

            .status-0 {  /* Rascunho */
                background: var(--mc-gray-500);
                color: var(--mc-white);
            }

            .field {
                text-align: center;
                width: 30%;
            }

            .page-content {
                margin: 30px 80px 20px 80px;
            }

            .footer-generated {
                position: fixed;
                right: 30px;
                bottom: 20px;

                font-size: 10px;
                color: var(--mc-black);
            }

            /* ===== CONTROLE DE QUEBRA DE PÁGINA (PDF) ===== */

            /* Cabeçalho da fase nunca fica separado */
            .page-break-avoid {
                page-break-inside: avoid;
                page-break-after: avoid;
            }

            /* Tabela pode quebrar ENTRE linhas */
            table {
                page-break-inside: auto;
                width: 100%;
            }

            thead {
                display: table-row-group;
            }

            @page {
                margin: 0;
                padding: 0;
                border: none;
            }
        </style>
    </head>
