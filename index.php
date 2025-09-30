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