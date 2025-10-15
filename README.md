Task Tracker CLI app in PHP

Arguments:
    <description>: Name of the Task
    <id>: Task number
    <todo>: Task is yet to be done
    <in-progress>: Task is being done
    <done>: Task has been done

Commands:
    add: Add new task
    update: Edit the name of task
    delete: Remove the task
    in-progress/done: Mark task as in progress/done
    list: Lists all task done, in progress and to be done

Usage:
  php task.php add <description>
  php task.php update <id> <new description>
  php task.php delete <id>
  php task.php in-progress <id>
  php task.php done <id>
  php task.php list [todo|in-progress|done]