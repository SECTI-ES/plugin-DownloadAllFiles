<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Controllers\EntityController\Controller $this
 */

use MapasCulturais\i;

date_default_timezone_set('America/Sao_Paulo');
?>

    <body>
        <div class="footer-generated">
            Ficha Gerada em: <?= date('d/m/Y H:i:s') ?>
        </div>
        <div class="page-content">
            <div class="header">
                <table width="100%">
                    <tr>
                        <td align="left" class="title">
                            <h1><?= i::__("Ficha de Inscrição") ?></h1>
                            <h2><a href="<?= htmlspecialchars($registration->opportunity->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->opportunity->id) . htmlspecialchars($registration->opportunity->name ? ' - ' . $registration->opportunity->name : '') ?></a></h2>
                        </td>
                        <td class="logo" align="right">
                            <img src="file://<?= $this->filterAndSelectFiles(["logo"], "icon", "png")[0]; ?>" alt="Logo not Found" style="width: 190px;">
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
                                        <td align="left" style="padding-top: 3px;">
                                            <?= htmlspecialchars($form['name'] ?? ($formId ?? "")) ?>
                                        </td>
                                        <td align="right">
                                            <div class="status-box">
                                                Status:
                                                <span class="status status-<?= isset($form['status']) ? $this->statusName($form['status'], true) : 'Rascunho' ?>">
                                                    <?= isset($form['status']) ? $this->statusName($form['status']) : 'Rascunho' ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="table-border">
                                <table class="table table-phases-body">
                                    <thead>
                                        <tr class="table-header">
                                            <th><?= i::__("Campo") ?></th>
                                            <th><?= i::__("Resposta") ?></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($form['answers'] as $key => $value): ?>
                                            <tr>
                                                <td class="field"><?= htmlspecialchars($key) ?></td>
                                                <td><?= htmlspecialchars(is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
</html>
