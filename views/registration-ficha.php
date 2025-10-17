<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ficha de Inscrição</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>Ficha de Inscrição</h1>
    <h2><a href="<?= htmlspecialchars($registration->opportunity->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->opportunity->id) . htmlspecialchars($registration->opportunity->name ? ' - ' . $registration->opportunity->name : '') ?></a></h2>

    <h3>Dados do Inscrito</h3>

    <table>
        <tr><th>Número da inscrição</th><td><a href="<?= htmlspecialchars($registration->singleUrl ?? '#') ?>"><?= htmlspecialchars($registration->id) ?></a></td></tr>
        <tr><th>Nome</th><td><a href="<?= htmlspecialchars($singleUrlOwner ?? '#') ?>"><?= htmlspecialchars($nameOwner ?? '') ?></a></td></tr>

        <?php if (!empty($registration->proponentType)): ?>
        <tr><th>Tipo de Proponente</th><td><?= htmlspecialchars($registration->proponentType) ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($registration->projectName)): ?>
        <tr><th>Nome do Projeto</th><td><?= htmlspecialchars($registration->projectName) ?></td></tr>
        <?php endif; ?>

        <?php if (!empty($registration->category)): ?>
        <tr><th>Categoria</th><td><?= htmlspecialchars($registration->category) ?></td></tr>
        <?php endif; ?>

        <tr><th>Status</th><td><?= htmlspecialchars($registration->status) ?></td></tr>
        <tr><th>Data de inscrição</th><td><?= $registration->createTimestamp ? $registration->createTimestamp->format('Y-m-d H:i:s') : '' ?></td></tr>
    </table>

    <h3>Respostas dos Formulários</h3>

    <?php foreach ($answers as $formId => $form): ?>
        <?php if (isset($form['answers']) && !empty($form['answers'])): ?>
            <h4><?= htmlspecialchars($form['name'] ?? '') ?></h4>
            <table>
                <tr><th>Campo</th><th>Resposta</th></tr>
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
