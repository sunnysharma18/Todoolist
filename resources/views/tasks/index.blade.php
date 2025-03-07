<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light">

    <div class="container mt-4">
        <div class="d-flex align-items-center mb-3">
            <input type="checkbox" id="showAllTasks" class="me-2">
            <label for="showAllTasks" class="fw-bold">Show All Tasks</label>
        </div>

        <div class="input-group mb-3">
            <input type="text" id="taskInput" class="form-control" placeholder="Project # To Do">
            <button class="btn btn-success" id="addTaskButton">Add</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th></th>
                    <th>Task</th>
                    <th>Created At</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="taskList">
                @foreach($tasks as $task)
                <tr class="task-item {{ $task->completed ? 'd-none' : '' }}" data-id="{{ $task->id }}">
                    <td>
                        <input type="checkbox" class="task-checkbox" {{ $task->completed ? 'checked' : '' }}>
                    </td>
                    <td class="task-text {{ $task->completed ? 'text-decoration-line-through text-muted' : '' }}">
                        {{ $task->task }}
                    </td>
                    <td>{{ $task->created_at->diffForHumans() }}</td>
                    <td>
                        @php
                            $profileImage = optional($task->user)->profile_image ?? 'default.png';
                        @endphp
                       <img src="{{ asset('storage/upload/download.jpeg') }}" class="rounded-circle" width="30" onerror="this.src='{{ asset('storage/default.png') }}'">

                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm delete-task">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

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
            data: {
                task: task,
                _token: getCsrfToken()
            },
            success: function (response) {
                if (response.success) {
                    let profileImage = response.task.user?.profile_image ?? 'default.png';
                    let taskRow = `
                        <tr class="task-item" data-id="${response.task.id}">
                            <td><input type="checkbox" class="task-checkbox"></td>
                            <td class="task-text">${response.task.task}</td>
                            <td>${response.task.created_at}</td>
                            <td>
                               <img src="{{ asset('storage/upload/download.jpeg') }}" class="rounded-circle" width="30" onerror="this.src='{{ asset('storage/default.png') }}'">

                            </td>
                            <td><button class="btn btn-danger btn-sm delete-task">Delete</button></td>
                        </tr>`;
                    $('#taskList').append(taskRow);
                    $('#taskInput').val('');
                } else {
                    alert('Task already exists!');
                }
            },
            error: function (xhr) {
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
                    $(checkbox).closest('tr').toggleClass('d-none');
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

    $('#addTaskButton').click(addTask);

    $('#taskList').on('click', '.task-checkbox', function () {
        toggleTask($(this).closest('tr').attr('data-id'), this);
    });

    $('#taskList').on('click', '.delete-task', function () {
        deleteTask($(this).closest('tr').attr('data-id'), $(this).closest('tr'));
    });

    $('#taskInput').keypress(function (e) {
        if (e.which === 13) {
            addTask();
        }
    });

    $('#showAllTasks').change(function () {
        $('.task-item').toggleClass('d-none', !$(this).is(':checked'));
    });
});
</script>

</body>
</html>
