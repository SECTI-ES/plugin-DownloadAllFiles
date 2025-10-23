<?php
use MapasCulturais\i;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= i::__("Ficha de Inscrição") ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1><?= i::__("Ficha de Inscrição") ?></h1>
    <h2><a href="<?= htmlspecialchars($registration->opportunity->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->opportunity->id) . htmlspecialchars($registration->opportunity->name ? ' - ' . $registration->opportunity->name : '') ?></a></h2>

    <h3><?= i::__("Dados do Inscrito") ?></h3>

    <table>
        <tr><th><?= i::__("Número da inscrição") ?></th><td><a href="<?= htmlspecialchars($registration->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->id) ?></a></td></tr>
        <tr><th><?= i::__("Nome") ?></th><td><a href="<?= htmlspecialchars($singleUrlOwner ?? '#') ?>"><?= htmlspecialchars($nameOwner ?? '') ?></a></td></tr>

        <?php if (!empty($registration->proponentType)): ?>
        <tr><th><?= i::__("Tipo de Proponente") ?></th><td><?= htmlspecialchars($registration->proponentType) ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($registration->projectName)): ?>
        <tr><th><?= i::__("Nome do Projeto") ?></th><td><?= htmlspecialchars($registration->projectName) ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($registration->category)): ?>
        <tr><th><?= i::__("Categoria") ?></th><td><?= htmlspecialchars($registration->category) ?></td></tr>
        <?php endif; ?>

        <tr><th><?= i::__("Data de inscrição") ?></th><td><?= $registration->createTimestamp ? $registration->createTimestamp->format('Y-m-d H:i:s') : '' ?></td></tr>
    </table>

    <h3><?= i::__("Respostas dos Formulários por Fase") ?></h3>

    <?php foreach ($answers as $formId => $form): ?>
        <?php if (isset($form['answers']) && !empty($form['answers'])): ?>
            <h4><?= htmlspecialchars($form['name'] ?? ($formId ?? "")) ?></h4>
            <h5><?= i::__("Status") ?>: <?= $form['status'] ?></h5>
            <table>
                <tr><th><?= i::__("Campo") ?></th><th><?= i::__("Resposta") ?></th></tr>
                <?php foreach ($form['answers'] as $key => $value): ?>
                    <tr>
                        <td><?= htmlspecialchars($key) ?></td>
                        <td><?= htmlspecialchars(is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
