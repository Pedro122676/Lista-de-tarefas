# Lista-de-tarefas
Lista de tarefas


**Lista de Tarefas com PHP, PDO e AJAX**
Este é um projeto simples de lista de tarefas (To-Do List) que demonstra o uso de PHP com PDO para manipulação do banco de dados, e AJAX com jQuery para operações assíncronas (CRUD), oferecendo uma experiência de usuário moderna sem recarregar a página.

**Funcionalidades**
Adicionar novas tarefas (título e descrição opcional).

Listar todas as tarefas, ordenadas por status (não concluídas primeiro).

Marcar/Desmarcar tarefas como concluídas com um clique.

Excluir tarefas.

Interface responsiva e moderna utilizando Bootstrap 5 e ícones Font Awesome.

Utiliza SweetAlert2 para notificações amigáveis ao usuário.

**Tecnologias Utilizadas**
Backend: PHP 

Banco de Dados: MySQL

Driver de BD: PHP Data Objects (PDO)

Frontend: HTML5, CSS3, JavaScript

Bibliotecas:

Bootstrap 5

jQuery (para AJAX)

Font Awesome

SweetAlert2

**Banco de Dados- XAMPP** **todolist_db.sql**
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/09/2025 às 17:33
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `todolist_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `concluida` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

**Configuração PHP- config.php**

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


**index.php**

<?php

require_once 'config.php';


$tarefas = getTarefas();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tarefas com PHP, PDO e AJAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; } 
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); border-radius: .25rem; }
        .card-header { background-color: #ffffff; border-bottom: 1px solid rgba(0,0,0,.125); }
        .todo-list .form-check-label.done { text-decoration: line-through; color: #6c757d; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-center mb-4 text-primary"><i class="fas fa-tasks"></i> Lista de Tarefas</h1>

            <div class="card card-primary card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title">Nova Tarefa</h3>
                </div>
                <div class="card-body">
                    <form id="form-add-tarefa" class="mb-3">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição (Opcional)</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary float-end">
                            <i class="fas fa-plus"></i> Adicionar Tarefa
                        </button>
                    </form>
                </div>
            </div>

            <div class="card" id="card-tarefas">
                <div class="card-header">
                    <h3 class="card-title">Minhas Tarefas</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="todo-list list-group list-group-flush" id="lista-tarefas">
                        <?php if (count($tarefas) > 0): ?>
                            <?php foreach ($tarefas as $tarefa): ?>
                                <?php $is_done = $tarefa['concluida'] ? 'done' : ''; ?>
                                <li class="list-group-item d-flex align-items-center todo-item-<?= $tarefa['id'] ?> <?= $is_done ?>" data-id="<?= $tarefa['id'] ?>">
                                    <div class="form-check me-3">
                                        <input class="form-check-input check-status" type="checkbox" data-id="<?= $tarefa['id'] ?>" <?= $tarefa['concluida'] ? 'checked' : '' ?>>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-bold <?= $is_done ?>"><?= htmlspecialchars($tarefa['titulo']) ?></p>
                                        <?php if (!empty($tarefa['descricao'])): ?>
                                            <small class="text-muted <?= $is_done ?>"><?= nl2br(htmlspecialchars($tarefa['descricao'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm delete-btn ms-3" data-id="<?= $tarefa['id'] ?>" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted" id="empty-list-message">
                                Nenhuma tarefa cadastrada.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer clearfix">
                    <p class="mb-0 text-muted float-end">Total de Tarefas: <span id="total-tarefas"><?= count($tarefas) ?></span></p>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

 
    $('#form-add-tarefa').on('submit', function(e) {
        e.preventDefault();

        var titulo = $('#titulo').val().trim();
        if (titulo === '') {
             Swal.fire('Atenção', 'O título da tarefa não pode ser vazio.', 'warning');
            return;
        }

        $.ajax({
            url: 'config.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'add',
                titulo: titulo,
                descricao: $('#descricao').val()
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Sucesso!', response.message, 'success');
                    $('#form-add-tarefa')[0].reset(); 
    
                    location.reload(); 
                } else {
                    Swal.fire('Erro!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Erro de Conexão', 'Não foi possível se comunicar com o servidor.', 'error');
            }
        });
    });


    $(document).on('change', '.check-status', function() {
        var taskId = $(this).data('id');
        var listItem = $('.todo-item-' + taskId);
        
    
        listItem.toggleClass('done');
        listItem.find('.fw-bold, .text-muted').toggleClass('done');

        $.ajax({
            url: 'config.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'toggle_status',
                id: taskId
            },
            success: function(response) {
                if (response.success) {
 
                    if (response.novo_status == 1) {
                        $('#lista-tarefas').append(listItem);
                    } else {
           
                        var firstNotDone = $('#lista-tarefas > li:not(.done):first');
                        if(firstNotDone.length) {
                             listItem.insertBefore(firstNotDone);
                        } else {
                
                             $('#lista-tarefas').prepend(listItem);
                        }
                    }
                    Swal.fire('Atualizado!', response.message, 'success');

                } else {
             
                    listItem.toggleClass('done');
                    listItem.find('.fw-bold, .text-muted').toggleClass('done');
                    $(this).prop('checked', !$(this).prop('checked'));
                    Swal.fire('Erro!', response.message, 'error');
                }
            },
            error: function() {
         
                listItem.toggleClass('done');
                listItem.find('.fw-bold, .text-muted').toggleClass('done');
                $(this).prop('checked', !$(this).prop('checked'));
                Swal.fire('Erro de Conexão', 'Não foi possível atualizar o status.', 'error');
            }
        });
    });

   
    $(document).on('click', '.delete-btn', function() {
        var taskId = $(this).data('id');
        var listItem = $('.todo-item-' + taskId);

        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'config.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'delete',
                        id: taskId
                    },
                    success: function(response) {
                        if (response.success) {
                            listItem.remove();
                            
                         
                            var total = parseInt($('#total-tarefas').text()) - 1;
                            $('#total-tarefas').text(total);
                            if (total === 0) {
                                $('#lista-tarefas').html('<li class="list-group-item text-center text-muted" id="empty-list-message">Nenhuma tarefa cadastrada.</li>');
                            }
                            
                            Swal.fire('Excluído!', response.message, 'success');
                        } else {
                            Swal.fire('Erro!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Erro de Conexão', 'Não foi possível excluir a tarefa.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
