<?php

require 'secrets.php';
$con = sqlsrv_connect('localhost', [
    'Database' => 'example_db',
    'UID' => DB_UID,
    'PWD' => DB_PWD
]);

if ($con === false) {
    echo 'Failed to connect to db: ' . sqlsrv_errors()[0]['message'];
    exit();
}

function check_err($var)
{
    if ($var === false) {
        echo 'DB failure: ' . sqlsrv_errors()[0]['message'];
        exit();
    }
}

// Method to update completed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_update_complete'])) {

        $id = $_POST['id_update_complete'];

        $stmt = sqlsrv_prepare(
            $con, "UPDATE todos SET complete='Completed' WHERE id=?",
            [$id]
        );
        check_err($stmt);

        $res = sqlsrv_execute($stmt);
        check_err($res);

        echo '<p>Successfully Updated todo item to Completed</p>';
    }
     // Method to delete entry
     if (isset($_POST['id_delete_task'])){

        $id = $_POST['id_delete_task'];

        $stmt = sqlsrv_prepare($con,
            'DELETE FROM todos WHERE id=?',
                [$id]
        );
        check_err($stmt);

        $res = sqlsrv_execute($stmt);
        check_err($res);

        echo '<p>Successfully deleted todo item</p>';
    }

    // Ammended code to check isset rather than else statement
    if (isset($_POST['title'])){
        $new_title = $_POST['title'];

        $stmt = sqlsrv_prepare(
            $con,
            'INSERT INTO todos(title) VALUES (?)',
            [$new_title]
        );
        check_err($stmt);

        $res = sqlsrv_execute($stmt);
        check_err($res);

        // success case
        echo '<p>Todo item successfully inserted</p>';
    }
}
?>

<form method="post" action="todo.php">
    <input type="text" name="title" placeholder="Add To do item">
    <button type="submit">Submit</button>
</form>


<h2>Todo list items</h2>

<style> table, th, td {
  border: 1px solid black;
}
</style>

<table>
    <tbody>
        <tr>
            <th>Task</th>
            <th>Added on</th>
            <th>Completed</th>
            <th>Mark Complete</th>
            <th>Delete Task</th>
        </tr>
        <?php
        $stmt = sqlsrv_query($con, 'SELECT id, title, created, complete FROM todos');

        while ($row = sqlsrv_fetch_array($stmt)) {
            $title = $row['title'];
            $created = $row['created']->format('j F');
            $completed = $row['complete'];
            $td_start = '<td><del>';
            $td_end = '</del></td>';
            if($completed === null){
                $completed = "To be Completed";
                $td_start = '<td>';
                $td_end = '</td>';
            }
            $id = $row['id'];
            echo '<tr>';
            echo $td_start . $title . $td_end;
            echo $td_start . $created . $td_end;
            echo $td_start . $completed . $td_end;
            echo '<td><form method="post" action="todo.php">
            <input type="hidden" name="id_update_complete" value="' . $id . '">
            <button type="submit">Done</button>
          </form></td>';
          echo '<td><form method="post" action="todo.php">
            <input type="hidden" name="id_delete_task" value="' . $id . '">
            <button type="submit">Delete</button>
          </form></td>';
            echo '</tr>';
        }

        sqlsrv_close($con);
        ?>
    </tbody>
</table>
<br /><br />

