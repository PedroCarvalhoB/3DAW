<?php

// index.php

require_once 'GerenciadorDisciplinas.php';

$gerenciador = new GerenciadorDisciplinas();
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'adicionar':
                $gerenciador->criarDisciplina($_POST['nome'], $_POST['codigo'], (int)$_POST['cargaHoraria']);
                $mensagem = 'Disciplina adicionada com sucesso!';
                break;
            case 'atualizar':
                $gerenciador->atualizarDisciplina($_POST['id'], $_POST['nome'], $_POST['codigo'], (int)$_POST['cargaHoraria']);
                $mensagem = 'Disciplina atualizada com sucesso!';
                break;
            case 'excluir':
                $gerenciador->excluirDisciplina($_POST['id']);
                $mensagem = 'Disciplina excluída com sucesso!';
                break;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$disciplinas = $gerenciador->obterDisciplinas();

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Disciplinas</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        .container-formulario { margin-bottom: 2rem; border: 1px solid #ccc; padding: 1rem; border-radius: 8px; }
        .container-formulario h3 { margin-top: 0; }
        label, input { display: block; margin-bottom: 0.5rem; }
        input[type="text"], input[type="number"] { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        button { padding: 0.5rem 1rem; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-excluir { display: inline; }
    </style>
</head>
<body>

    <h1>Gerenciador de Disciplinas</h1>

    <div class="container-formulario">
        <h3>Adicionar Nova Disciplina</h3>
        <form action="" method="post">
            <input type="hidden" name="acao" value="adicionar">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
            <label for="codigo">Código:</label>
            <input type="text" id="codigo" name="codigo" required>
            <label for="cargaHoraria">Carga Horária:</label>
            <input type="number" id="cargaHoraria" name="cargaHoraria" required>
            <button type="submit">Adicionar</button>
        </form>
    </div>

    <?php if ($mensagem): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <h2>Lista de Disciplinas</h2>
    <?php if (empty($disciplinas)): ?>
        <p>Nenhuma disciplina cadastrada.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Carga Horária</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disciplinas as $disciplina): ?>
                    <tr>
                        <td><?= htmlspecialchars($disciplina->id) ?></td>
                        <td><?= htmlspecialchars($disciplina->nome) ?></td>
                        <td><?= htmlspecialchars($disciplina->codigo) ?></td>
                        <td><?= htmlspecialchars($disciplina->cargaHoraria) ?></td>
                        <td>
                            <a href="#" onclick="mostrarFormularioEdicao('<?= htmlspecialchars($disciplina->id) ?>', '<?= htmlspecialchars($disciplina->nome) ?>', '<?= htmlspecialchars($disciplina->codigo) ?>', '<?= htmlspecialchars($disciplina->cargaHoraria) ?>')">Editar</a> |
                            <form action="" method="post" class="form-excluir" onsubmit="return confirm('Tem certeza que deseja excluir esta disciplina?');">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($disciplina->id) ?>">
                                <button type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="container-formulario" id="formularioEdicao" style="display:none;">
        <h3>Editar Disciplina</h3>
        <form action="" method="post">
            <input type="hidden" name="acao" value="atualizar">
            <input type="hidden" id="edicao-id" name="id">
            <label for="edicao-nome">Nome:</label>
            <input type="text" id="edicao-nome" name="nome" required>
            <label for="edicao-codigo">Código:</label>
            <input type="text" id="edicao-codigo" name="codigo" required>
            <label for="edicao-cargaHoraria">Carga Horária:</label>
            <input type="number" id="edicao-cargaHoraria" name="cargaHoraria" required>
            <button type="submit">Atualizar</button>
            <button type="button" onclick="esconderFormularioEdicao()">Cancelar</button>
        </form>
    </div>

    <script>
        function mostrarFormularioEdicao(id, nome, codigo, cargaHoraria) {
            document.getElementById('edicao-id').value = id;
            document.getElementById('edicao-nome').value = nome;
            document.getElementById('edicao-codigo').value = codigo;
            document.getElementById('edicao-cargaHoraria').value = cargaHoraria;
            document.getElementById('formularioEdicao').style.display = 'block';
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }

        function esconderFormularioEdicao() {
            document.getElementById('formularioEdicao').style.display = 'none';
        }
    </script>

</body>
</html>