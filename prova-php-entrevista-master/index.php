<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Título Aqui</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="container">

<?php
require 'connection.php';

$connection = new Connection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $userId = $_POST["userId"];

    if ($userId) {
        $connection->updateUser($userId, $name, $email);
    } else {
        $connection->insertUser($name, $email);
        $userId = $connection->getConnection()->lastInsertId();
    }

    if (isset($_POST['colors'])) {
        $colors = $_POST['colors'];
        $connection->updateUserColors($userId, $colors);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $connection->query("DELETE FROM users WHERE id = $userId");
    $connection->query("DELETE FROM user_colors WHERE user_id = $userId");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$editUserId = isset($_GET['edit']) ? $_GET['edit'] : null;
$editUser = null;
if ($editUserId) {
    $editUser = $connection->query("SELECT * FROM users WHERE id = $editUserId")->fetch(PDO::FETCH_ASSOC);
}

$users = $connection->query("SELECT * FROM users");

echo "<div class='contain'><div class= 'container2'>";
foreach($users as $user) {
    $userColors = $connection->query("SELECT color_id FROM user_colors WHERE user_id = {$user->id}");
    $colorNames = [];
    while ($userColor = $userColors->fetch(PDO::FETCH_ASSOC)) {
        $color = $connection->query("SELECT name FROM colors WHERE id = {$userColor['color_id']}")->fetch(PDO::FETCH_ASSOC);
        $colorNames[] = $color['name'];
    }
    $colors = implode(", ", $colorNames);
    $gradient = "linear-gradient(to right, " . $colors . ")";
    $stops = [];
    $numColors = count($colorNames);
    for ($i = 0; $i < $numColors; $i++) {
        $stops[] = $colorNames[$i] . " " . (100 * $i / $numColors) . "%, " . $colorNames[$i] . " " . (100 * ($i + 1) / $numColors) . "%";
    }
    $gradient = "linear-gradient(to right, " . implode(", ", $stops) . ")";

    echo '<div class="celula">
                <div class="bloco">
                    <div class="item-id">' . $user->id . '</div>
                    <div class="item-nome">' . $user->name . '</div>
                    <div class="item-email">' . $user->email . '</div>
                    <div class="item-colors" style="background: ' . $gradient . ';"></div>
                </div>
                <a href="?edit=' . $user->id . '"><div class="button-edit"><img src="assets/images/edit.png" alt="Editar"></div></a>
                <a href="' . $_SERVER['PHP_SELF'] . '?delete=' . $user->id . '"><div class="button-delete"><img src="assets/images/delete.png" alt="Deletar"></div></a>
        </div>';
}
echo "</div>";

?>
<div class="form">
    <h2><?php echo $editUser ? 'Editar Usuário' : 'Inserir Novo Usuário'; ?></h2>
    <form id="userForm" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <input type="hidden" id="userId" name="userId" value="<?php echo $editUser ? $editUser['id'] : ''; ?>">
        <input type="text" name="name" placeholder="Nome" id="name" value="<?php echo $editUser ? $editUser['name'] : ''; ?>" required><br><br>
        <input type="email" name="email" placeholder="Email" id="email" value="<?php echo $editUser ? $editUser['email'] : ''; ?>" required><br><br>
        <div class='blocos'>
        <?php
            $colors = $connection->query("SELECT * FROM colors");
            foreach ($colors as $color) {
                $checked = '';
                if ($editUser) {
                    $userColorsResult = $connection->query("SELECT color_id FROM user_colors WHERE user_id = {$editUser['id']} AND color_id = {$color->id}");
                    if ($userColorsResult->fetch(PDO::FETCH_ASSOC)) {
                        $checked = 'checked';
                    }
                }
                echo "<input type='checkbox' name='colors[]' value='{$color->id}' $checked<br>";
            }
        ?>
        </div>
        <br><input type="submit" value="<?php echo $editUser ? 'Salvar' : 'Inserir'; ?>">
    </form>
</div>
</div>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var checkboxes = document.querySelectorAll("input[type='checkbox']");
        var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
        if (!checkedOne) {
            e.preventDefault();
            alert('Por favor, selecione pelo menos uma cor.');
        }
    });
</script>
</body>
</html>
