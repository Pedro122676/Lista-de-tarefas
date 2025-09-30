<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'todolist_db');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 


function connectDB() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        
        die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
    }
}


$pdo = connectDB();


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json'); 
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($_POST['action']) {
            case 'add':
                $titulo = trim($_POST['titulo']);
                $descricao = trim($_POST['descricao']);

                if (empty($titulo)) {
                    throw new Exception("O título da tarefa não pode ser vazio.");
                }

                $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao) VALUES (?, ?)");
                $stmt->execute([$titulo, $descricao]);
                $response['success'] = true;
                $response['message'] = "Tarefa adicionada com sucesso!";
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = ?");
                $stmt->execute([$id]);
                $response['success'] = true;
                $response['message'] = "Tarefa excluída com sucesso!";
                break;

            case 'toggle_status':
                $id = (int)$_POST['id'];
 
                $stmt = $pdo->prepare("UPDATE tarefas SET concluida = 1 - concluida WHERE id = ?");
                $stmt->execute([$id]);


                $stmt_status = $pdo->prepare("SELECT concluida FROM tarefas WHERE id = ?");
                $stmt_status->execute([$id]);
                $novo_status = $stmt_status->fetchColumn();

                $response['success'] = true;
                $response['message'] = "Status da tarefa atualizado.";
                $response['novo_status'] = $novo_status; 
                break;

            default:
                $response['message'] = "Ação inválida.";
        }
    } catch (Exception $e) {
        $response['message'] = "Erro: " . $e->getMessage();
    }

    echo json_encode($response);
    exit; 
}


function getTarefas() {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM tarefas ORDER BY concluida ASC, data_criacao DESC");
    return $stmt->fetchAll();
}
?>