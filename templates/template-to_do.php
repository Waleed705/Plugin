<?php
/**
 * Template Name: To-Do
 */
get_header();
?>
<div class="todo-container">
    <h2>To-Do List</h2>
    <form id="todo-form">
        <div class="add-input">
            <input type="text" id="task-input" placeholder="Add a new task..." required>
            <span id="add-error" class="error-message"></span>
            <button id="add-button" type="submit">Add</button>
        </div>
        <ul id="task-list"></ul>
        <button id="logout-button" type="button">Logout</button>
    </form>

    <style>
        .todo-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            margin: 20px auto;
        }
        .add-input {
            display: flex;
            margin-bottom: 20px;
        }
        #task-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        #add-button {
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        li {
            background: #f8f8f8;
            margin: 10px 0;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 5px;
        }
        .completed {
            text-decoration: line-through;
            color: #999;
        }
        .delete-button, .update-button {
            background: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 5px 10px;
            margin-left: 5px;
        }
    </style>
</div>
<script>
jQuery(document).ready(function($) {
    loadTasks();

    $('#add-button').on('submit', function(e) {
        e.preventDefault();
        const task = $('#task-input').val();
        $('#add-error').text('');
        if (task) {
            $.post(ajax_object.ajaxurl, {
                action: 'add_task',
                task: task
            }, function(response) {
                console.log(response)
                debugger;
                if (response.success.data) {
                    loadTasks();
                    $('#task-input').val('');
                } else {
                    $('#add-error').text(response.data.message);
                }
            });
        }
    });

    $(document).on('click', '.delete-button', function() {
        const taskId = $(this).data('id');
        $.post(ajax_object.ajaxurl, {
            action: 'delete_task',
            id: taskId
        }, function(response) {
            if (response.success) {
                loadTasks();
            }
        });
    });

    $(document).on('click', '.update-button', function() {
        const taskId = $(this).data('id');
        const taskText = $(this).siblings('.task-text').text();
        const newTask = prompt("Update task:", taskText);
        if (newTask) {
            $.post(ajax_object.ajaxurl, {
                action: 'update_task',
                task_id: taskId,
                task: newTask
            }, function(response) {
                if (response.success) {
                    loadTasks();
                } else {
                    alert(response.data.message);
                }
            });
        }
    });

    $(document).on('click', '.task-text', function() {
        const taskId = $(this).parent().data('id');
        const completed = $(this).parent().hasClass('completed') ? 0 : 1;
        $.post(ajax_object.ajaxurl, {
            action: 'toggle_task',
            id: taskId,
            completed: completed
        }, function(response) {
            if (response.success) {
                loadTasks();
            }
        });
    });

    $('#logout-button').on('click', function() {
        $.post(ajax_object.ajaxurl, { action: 'logout_user' }, function(response) {
            if (response.success) {
                window.location.href = response.data.redirect;
            }
        });
    });

    function loadTasks() {
        $('#task-list').empty();
        $.post(ajax_object.ajaxurl, { action: 'load_tasks' }, function(response) {
            if (response.success) {
                $('#task-list').append(response.data);
            } else {
                alert('Failed to load tasks.');
            }
        });
    }
});
</script>
<?php
get_footer();
?>
