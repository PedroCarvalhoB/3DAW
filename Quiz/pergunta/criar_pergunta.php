<?php

define('FilePerg','perguntas.txt');

function carregarPerguntas() { 
    // Verifica se o arquivo existe
    if (!file_exists(FilePerg)) { 
        return []; // Retorna array vazio se não existir
    } 
    $perguntas = []; // Inicializa array de disciplinas
    $handle = fopen(FilePerg, 'r'); // Abre o arquivo para leitura
    // Lê cada linha do CSV
    while (($dados = fgetcsv($handle)) !== FALSE) { 
        // Armazena os dados no array usando o ID como chave
        $perguntas[$dados[0]] = ['id' => $dados[0], 'pergunta' => $dados[1], 'tipoResposta' => $dados[2], 'certa' => $dados[3]]; 
    } 
    fclose($handle); // Fecha o arquivo
    return $perguntas; // Retorna o array de disciplinas
} 

// Função para salvar disciplinas no arquivo CSV
function salvarPerguntas($perguntas) { 
    $handle = fopen(FilePerg, 'w'); // Abre o arquivo para escrita (sobrescreve)
    foreach ($perguntas as $pergunta) { 
        fputcsv($handle, $pergunta); // Escreve cada disciplina no arquivo CSV
    } 
    fclose($handle); // Fecha o arquivo
} 

function proximoId($perguntas) { 
    // Se houver disciplinas, encontra o maior ID e adiciona 1, caso contrário retorna 1
    return count($perguntas) > 0 ? max(array_keys($perguntas)) + 1 : 1; 
} 

// Obtém a ação a ser realizada a partir da URL (padrão: 'listar')
$acao = $_GET['acao'] ?? 'listar'; 
// Obtém o ID a partir da URL (se existir)
$id = $_GET['id'] ?? null; 
$mensagem = ''; // Inicializa variável para mensagens
$erros = []; // Inicializa array para erros
$perguntas = carregarPerguntas(); // Carrega as disciplinas do arquivo

// Processamento de formulários (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    // Obtém e limpa os dados do formulário
    $nome = trim($_POST['nome'] ?? ''); 
    $codigo = trim($_POST['codigo'] ?? ''); 
    $carga_horaria = trim($_POST['carga_horaria'] ?? ''); 
    $id_form = $_POST['id'] ?? null; 
    
    // Validações
    if (empty($nome)) $erros[] = 'O nome é obrigatório.'; 
    if (empty($codigo)) $erros[] = 'O código é obrigatório.'; 
    if (!is_numeric($carga_horaria) || $carga_horaria <= 0) $erros[] = 'A carga horária deve ser um número positivo.'; 
    
    // Se não houver erros
    if (count($erros) === 0) { 
        if ($acao === 'criar') { 
            // Cria uma nova disciplina
            $novoId = proximoId($disciplinas); 
            $disciplinas[$novoId] = ['id' => $novoId, 'nome' => $nome, 'codigo' => $codigo, 'carga_horaria' => $carga_horaria]; 
            $mensagem = 'Disciplina adicionada com sucesso!'; 
        } elseif ($acao === 'editar' && isset($disciplinas[$id_form])) { 
            // Edita uma disciplina existente
            $disciplinas[$id_form] = ['id' => $id_form, 'nome' => $nome, 'codigo' => $codigo, 'carga_horaria' => $carga_horaria]; 
            $mensagem = 'Disciplina atualizada com sucesso!'; 
        } 
        salvarDisciplinas($disciplinas); // Salva as alterações no arquivo
        // Redireciona para evitar reenvio do formulário
        header('Location: ' . $_SERVER['PHP_SELF'] . '?mensagem=' . urlencode($mensagem)); 
        exit; 
    } 
} 

?>

