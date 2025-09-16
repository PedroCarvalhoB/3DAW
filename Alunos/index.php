<?php
/**
 * Sistema CRUD de Alunos
 * Desenvolvido seguindo boas práticas de POO
 */

// ==================== CLASSES DO SISTEMA ====================

/**
 * Classe responsável pela conexão com o banco de dados
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'crud_alunos';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Método para estabelecer conexão com o banco
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "<div class='alert alert-danger'>Erro de conexão: " . $e->getMessage() . "</div>";
        }
        return $this->conn;
    }
}

/**
 * Classe modelo para representar um Aluno
 */
class Aluno {
    private $id;
    private $nome;
    private $idade;
    private $email;

    // Getters e Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getIdade() { return $this->idade; }
    public function setIdade($idade) { $this->idade = $idade; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
}

/**
 * Classe controladora para operações CRUD
 */
class AlunoController {
    private $conn;
    private $table_name = "alunos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->criarTabelaSeNaoExiste();
    }

    /**
     * Cria a tabela de alunos se não existir
     */
    private function criarTabelaSeNaoExiste() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                idade INT NOT NULL,
                email VARCHAR(100),
                data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->conn->exec($query);
        } catch(PDOException $e) {
            echo "<div class='alert alert-danger'>Erro ao criar tabela: " . $e->getMessage() . "</div>";
        }
    }

    /**
     * Validação dos dados do aluno
     * @param Aluno $aluno
     * @return array
     */
    private function validarAluno(Aluno $aluno) {
        $erros = [];

        // Validar nome (apenas letras e espaços)
        if (empty($aluno->getNome())) {
            $erros[] = "Nome é obrigatório";
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $aluno->getNome())) {
            $erros[] = "Nome deve conter apenas letras e espaços";
        }

        // Validar idade (número inteiro positivo)
        if (empty($aluno->getIdade())) {
            $erros[] = "Idade é obrigatória";
        } elseif (!filter_var($aluno->getIdade(), FILTER_VALIDATE_INT) || $aluno->getIdade() <= 0) {
            $erros[] = "Idade deve ser um número inteiro positivo";
        }

        // Validar email (se fornecido)
        if (!empty($aluno->getEmail()) && !filter_var($aluno->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $erros[] = "Formato de e-mail inválido";
        }

        return $erros;
    }

    /**
     * Criar novo aluno
     * @param Aluno $aluno
     * @return array
     */
    public function criar(Aluno $aluno) {
        $erros = $this->validarAluno($aluno);
        
        if (!empty($erros)) {
            return ['sucesso' => false, 'erros' => $erros];
        }

        try {
            $query = "INSERT INTO {$this->table_name} (nome, idade, email) VALUES (:nome, :idade, :email)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nome', $aluno->getNome());
            $stmt->bindValue(':idade', $aluno->getIdade());
            $stmt->bindValue(':email', $aluno->getEmail());

            return ['sucesso' => $stmt->execute(), 'id' => $this->conn->lastInsertId()];
        } catch(PDOException $e) {
            return ['sucesso' => false, 'erros' => [$e->getMessage()]];
        }
    }

    /**
     * Listar todos os alunos
     * @return array
     */
    public function listar() {
        try {
            $query = "SELECT * FROM {$this->table_name} ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    /**
     * Buscar aluno por ID
     * @param int $id
     * @return Aluno|null
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $dados = $stmt->fetch();
            
            if ($dados) {
                $aluno = new Aluno();
                $aluno->setId($dados['id']);
                $aluno->setNome($dados['nome']);
                $aluno->setIdade($dados['idade']);
                $aluno->setEmail($dados['email']);
                return $aluno;
            }
            return null;
        } catch(PDOException $e) {
            return null;
        }
    }

    /**
     * Atualizar aluno existente
     * @param Aluno $aluno
     * @return array
     */
    public function atualizar(Aluno $aluno) {
        $erros = $this->validarAluno($aluno);
        
        if (!empty($erros)) {
            return ['sucesso' => false, 'erros' => $erros];
        }

        try {
            $query = "UPDATE {$this->table_name} SET nome = :nome, idade = :idade, email = :email WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nome', $aluno->getNome());
            $stmt->bindValue(':idade', $aluno->getIdade());
            $stmt->bindValue(':email', $aluno->getEmail());
            $stmt->bindValue(':id', $aluno->getId());

            return ['sucesso' => $stmt->execute()];
        } catch(PDOException $e) {
            return ['sucesso' => false, 'erros' => [$e->getMessage()]];
        }
    }

    /**
     * Excluir aluno por ID
     * @param int $id
     * @return bool
     */
    public function excluir($id) {
        try {
            $query = "DELETE FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}

// ==================== PROCESSAMENTO DAS AÇÕES ====================

$alunoController = new AlunoController();
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'listar';
$id = $_POST['id'] ?? $_GET['id'] ?? null;
$mensagem = '';
$erros = [];

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($acao === 'criar') {
        $aluno = new Aluno();
        $aluno->setNome($_POST['nome']);
        $aluno->setIdade($_POST['idade']);
        $aluno->setEmail($_POST['email']);
        
        $resultado = $alunoController->criar($aluno);
        
        if ($resultado['sucesso']) {
            $mensagem = "Aluno cadastrado com sucesso!";
        } else {
            $erros = $resultado['erros'];
        }
    } elseif ($acao === 'atualizar') {
        $aluno = new Aluno();
        $aluno->setId($id);
        $aluno->setNome($_POST['nome']);
        $aluno->setIdade($_POST['idade']);
        $aluno->setEmail($_POST['email']);
        
        $resultado = $alunoController->atualizar($aluno);
        
        if ($resultado['sucesso']) {
            $mensagem = "Aluno atualizado com sucesso!";
        } else {
            $erros = $resultado['erros'];
        }
    }
} else {
    if ($acao === 'excluir' && $id) {
        if ($alunoController->excluir($id)) {
            $mensagem = "Aluno excluído com sucesso!";
        } else {
            $erros = ["Erro ao excluir aluno."];
        }
    }
}

// Buscar aluno para edição
$alunoEdicao = null;
if ($acao === 'editar' && $id) {
    $alunoEdicao = $alunoController->buscarPorId($id);
    if (!$alunoEdicao) {
        $erros = ["Aluno não encontrado."];
    }
}

// Listar todos os alunos
$alunos = $alunoController->listar();

// ==================== INTERFACE GRÁFICA ====================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema CRUD de Alunos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card h2 {
            color: #6a11cb;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus {
            border-color: #6a11cb;
            outline: none;
            box-shadow: 0 0 0 2px rgba(106, 17, 203, 0.2);
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #868f96 0%, #596164 100%);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistema CRUD de Alunos</h1>
            <p>Cadastro e gerenciamento de alunos</p>
        </header>

        <!-- Mensagens de feedback -->
        <?php if ($mensagem): ?>
            <div class="alert alert-success">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?php echo $erro; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulário de cadastro/edição -->
        <div class="card">
            <h2><?php echo $alunoEdicao ? 'Editar Aluno' : 'Cadastrar Novo Aluno'; ?></h2>
            <form method="POST">
                <input type="hidden" name="acao" value="<?php echo $alunoEdicao ? 'atualizar' : 'criar'; ?>">
                <?php if ($alunoEdicao): ?>
                    <input type="hidden" name="id" value="<?php echo $alunoEdicao->getId(); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" 
                           value="<?php echo $alunoEdicao ? $alunoEdicao->getNome() : ''; ?>" 
                           required pattern="[A-Za-zÀ-ÿ\s]+" 
                           title="Apenas letras e espaços são permitidos">
                </div>
                
                <div class="form-group">
                    <label for="idade">Idade:</label>
                    <input type="number" id="idade" name="idade" 
                           value="<?php echo $alunoEdicao ? $alunoEdicao->getIdade() : ''; ?>" 
                           required min="1" 
                           title="Apenas números inteiros positivos">
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail (opcional):</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo $alunoEdicao ? $alunoEdicao->getEmail() : ''; ?>">
                </div>
                
                <button type="submit" class="btn">
                    <?php echo $alunoEdicao ? 'Atualizar' : 'Cadastrar'; ?>
                </button>
                
                <?php if ($alunoEdicao): ?>
                    <a href="?" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Lista de alunos -->
        <div class="card">
            <h2>Alunos Cadastrados</h2>
            
            <?php if (count($alunos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>E-mail</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td><?php echo $aluno['id']; ?></td>
                                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                <td><?php echo $aluno['idade']; ?></td>
                                <td><?php echo htmlspecialchars($aluno['email'] ?? 'Não informado'); ?></td>
                                <td class="actions">
                                    <a href="?acao=editar&id=<?php echo $aluno['id']; ?>" class="btn">Editar</a>
                                    <a href="?acao=excluir&id=<?php echo $aluno['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este aluno?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    Nenhum aluno cadastrado ainda. Use o formulário acima para cadastrar o primeiro aluno.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>