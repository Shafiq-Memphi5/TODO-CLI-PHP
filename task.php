<?php
// Path to JSON file
define('TASK_FILE', __DIR__ . '/task.json');

// Load tasks
function loadTasks()
{
    if (!file_exists(TASK_FILE)) {
        return [];
    }
    $json = file_get_contents(TASK_FILE);
    $tasks = json_decode($json, true);
    return $tasks ?: [];
}

// Save tasks
function saveTasks($tasks)
{
    file_put_contents(TASK_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Get current ISO timestamp
function nowISO()
{
    return gmdate('Y-m-d\TH:i:s\Z');
}

// Get next ID
function nextId($tasks)
{
    if (empty($tasks))
        return 1;
    $ids = array_column($tasks, 'id');
    return max($ids) + 1;
}
// Print usage
function printHelp()
{
    echo <<<HELP
Usage:
  php task.php add <description>
  php task.php update <id> <new description>
  php task.php delete <id>
  php task.php mark-in-progress <id>
  php task.php mark-done <id>
  php task.php list [todo|in-progress|done]

HELP;
}

$argv = $_SERVER['argv'];
$argc = $_SERVER['argc'];
if ($argc < 2) {
    printHelp();
    exit(1);
}
$command = $argv[1];
$args = array_slice($argv, 2);

// LISTING
if (strtolower($command) === 'list') {
    $tasks = loadTasks();
    $filter = $args[0] ?? 'all';

    if ($filter !== 'all' && !in_array($filter, ['todo', 'in-progress', 'done'])) {
        echo "Invalid list filter.\n";
        exit(1);
    }

    $filtered = ($filter === 'all') ? $tasks : array_filter($tasks, fn($t) => $t['status'] === $filter);

    if (empty($filtered)) {
        echo "No tasks found.\n";
        exit;
    }

    foreach ($filtered as $t) {
        echo "[{$t['id']}] ({$t['status']}) {$t['description']}\n";
    }
    exit;
}

// ADDING
if (strtolower($command) === 'add') {
    if (count($args) < 1 && count($args) >= 2) {
        echo "Error: Description required.\n";
        exit(1);
    }

    $description = implode(' ', $args);
    $tasks = loadTasks();

    $task = [
        'id' => nextId($tasks),
        'description' => $description,
        'status' => 'todo',
        'createdAt' => nowISO(),
        'updatedAt' => nowISO()
    ];

    $tasks[] = $task;
    saveTasks($tasks);

    echo "Task added successfully (ID: {$task['id']})\n";
    exit;
}

// DELETING
if (strtolower($command) === 'delete') {
    if (count($args) < 1) {
        echo "Error: ID required.\n";
        exit(1);
    }

    $id = (int) $args[0];
    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as $index => $task) {
        if ($task['id'] === $id) {
            unset($tasks[$index]);
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "Error: Task with ID $id not found.\n";
        exit(1);
    }

    // Reindex array
    $tasks = array_values($tasks);
    saveTasks($tasks);

    echo "Task with ID $id deleted successfully.\n";
    exit;
}

// UPDATING
if (strtolower($command) === 'update') {
    if (count($args) < 2) {
        echo "Usage: php task.php update <id> <new description>\n";
        exit(1);
    }

    $id = (int) $args[0];
    $newDesc = implode(' ', array_slice($args, 1));

    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as &$task) {
        if ($task['id'] === $id) {
            $task['description'] = $newDesc;
            $task['updatedAt'] = nowISO();
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "Task with ID $id not found.\n";
        exit(1);
    }

    saveTasks($tasks);
    echo "Task $id updated successfully.\n";
    exit;
}

// UPDATE STATUS
if (in_array($command, ['in-progress', 'done'])) {
    if (count($args) < 1) {
        echo "Usage: php task.php $command <id>\n";
        exit(1);
    }

    $id = (int) $args[0];
    $newStatus = $command === 'done' ? 'done' : 'in-progress';

    $tasks = loadTasks();
    $found = false;

    foreach ($tasks as &$task) {
        if ($task['id'] === $id) {
            $task['status'] = $newStatus;
            $task['updatedAt'] = nowISO();
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "Task with ID $id not found.\n";
        exit(1);
    }

    saveTasks($tasks);
    echo "Task $id marked as $newStatus.\n";
    exit;
}

echo "Unknown command: $command\n";
printHelp();
exit(1);
?>