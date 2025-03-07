<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="text-center mb-4">To-Do List</h2>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="input-group mb-3">
        <input type="text" id="taskInput" class="form-control" placeholder="Enter Task">
        <button class="btn btn-primary me-3" id="addTaskButton">Add Task</button>
    </div>

    <button class="btn btn-secondary mb-3" id="showAllTasks">Show All Tasks</button>

    <ul id="taskList" class="list-group">
        @foreach($tasks as $task)
        <li class="list-group-item d-flex justify-content-between align-items-center task-item {{ $task->completed ? 'd-none' : '' }}" data-id="{{ $task->id }}">
            <div>
                <input type="checkbox" class="task-checkbox" {{ $task->completed ? 'checked' : '' }}>
                <span class="task-text {{ $task->completed ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->task }}</span>
            </div>
            <button class="btn btn-danger btn-sm delete-task">Delete</button>
        </li>
        @endforeach
    </ul>

</div>

<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function addTask() {
    let task = $('#taskInput').val().trim();
    if (!task) {
        alert('Task cannot be empty!');
        return;
    }

    $.ajax({
        url: '/tasks',
        type: 'POST',
        data: { task: task },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            console.log('Success:', response);
            if (response.success) {
                $('#taskList').append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center task-item" data-id="${response.task.id}">
                        <div>
                            <input type="checkbox" class="task-checkbox">
                            <span class="task-text">${response.task.task}</span>
                        </div>
                        <button class="btn btn-danger btn-sm delete-task">Delete</button>
                    </li>
                `);
                $('#taskInput').val('');
            } else {
                alert('Task already exists!');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            alert('Error: ' + xhr.responseText);
        }
    });
}


function toggleTask(id, checkbox) {
    $.ajax({
        url: `/tasks/${id}`,
        type: 'PATCH',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        success: function (response) {
            if (response.success) {
                let taskItem = $(checkbox).closest('.task-item');
                let taskText = taskItem.find('.task-text');

                taskText.toggleClass('text-decoration-line-through text-muted');

                if ($(checkbox).is(':checked')) {
                    taskItem.addClass('d-none'); // Hide completed tasks
                } else {
                    taskItem.removeClass('d-none'); // Show uncompleted tasks
                }
            }
        }
    });
}

    function deleteTask(id, taskItem) {
        if (!confirm('Are you sure you want to delete this task?')) return;

        $.ajax({
            url: `/tasks/${id}`,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            success: function (response) {
                if (response.success) {
                    $(taskItem).remove();
                }
            }
        });
    }

    $('#addTaskButton').click(function () {
        addTask();
    });

    $('#taskList').on('click', '.task-checkbox', function () {
        let taskItem = $(this).closest('li');
        let taskId = taskItem.attr('data-id');
        toggleTask(taskId, this);
    });

    $('#taskList').on('click', '.delete-task', function () {
        let taskItem = $(this).closest('li');
        let taskId = taskItem.attr('data-id');
        deleteTask(taskId, taskItem);
    });

    $('#taskInput').keypress(function (e) {
        if (e.which === 13) {
            addTask();
        }
    });

    $('#showAllTasks').click(function () {
    $('.task-item').removeClass('d-none'); // Show all tasks
});

});
</script>
</body>
</html>

