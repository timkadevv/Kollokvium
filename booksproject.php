<?php
$db = new PDO('sqlite:books.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    genre TEXT NOT NULL,
    year INTEGER,
    pages INTEGER
)
");

$action = $_GET['action'] ?? 'list';

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare("INSERT INTO books(title,author,genre,year,pages) VALUES(?,?,?,?,?)");
    $stmt->execute([$_POST['title'], $_POST['author'], $_POST['genre'], $_POST['year'], $_POST['pages']]);
    header("Location: ?");
    exit;
}

if ($action == 'delete') {
    $stmt = $db->prepare("DELETE FROM books WHERE id=?");
    $stmt->execute([$_GET['id']]);
    header("Location: ?");
    exit;
}

if ($action == 'edit_save') {
    $stmt = $db->prepare("UPDATE books SET title=?,author=?,genre=?,year=?,pages=? WHERE id=?");
    $stmt->execute([
        $_POST['title'],
        $_POST['author'],
        $_POST['genre'],
        $_POST['year'],
        $_POST['pages'],
        $_GET['id']
    ]);
    header("Location: ?");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Books</title>
</head>
<body>

<h1>Библиотека</h1>

<a href="?">Список книг</a> |
<a href="?action=add_form">Добавить книгу</a> |
<a href="?action=queries">Запросы</a>

<hr>

<?php
if ($action == 'list') {

    $books = $db->query("SELECT * FROM books")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Название</th><th>Автор</th><th>Действия</th></tr>";

    foreach ($books as $book) {
        echo "<tr>";
        echo "<td>{$book['id']}</td>";
        echo "<td>{$book['title']}</td>";
        echo "<td>{$book['author']}</td>";
        echo "<td>
        <a href='?action=view&id={$book['id']}'>Открыть</a>
        <a href='?action=edit&id={$book['id']}'>Изменить</a>
        <a href='?action=delete&id={$book['id']}'>Удалить</a>
        </td>";
        echo "</tr>";
    }

    echo "</table>";
}

if ($action == 'add_form') {
?>
<form method="post" action="?action=add">
Название:<br><input name="title"><br><br>
Автор:<br><input name="author"><br><br>
Жанр:<br><input name="genre"><br><br>
Год:<br><input type="number" name="year"><br><br>
Страниц:<br><input type="number" name="pages"><br><br>
<button>Сохранить</button>
</form>
<?php } ?>

<?php
if ($action == 'view') {
    $stmt = $db->prepare("SELECT * FROM books WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>{$book['title']}</h2>";
    echo "<p>Автор: {$book['author']}</p>";
    echo "<p>Жанр: {$book['genre']}</p>";
    echo "<p>Год: {$book['year']}</p>";
    echo "<p>Страниц: {$book['pages']}</p>";
}

if ($action == 'edit') {
    $stmt = $db->prepare("SELECT * FROM books WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form method="post" action="?action=edit_save&id=<?= $book['id'] ?>">
Название:<br><input name="title" value="<?= $book['title'] ?>"><br><br>
Автор:<br><input name="author" value="<?= $book['author'] ?>"><br><br>
Жанр:<br><input name="genre" value="<?= $book['genre'] ?>"><br><br>
Год:<br><input type="number" name="year" value="<?= $book['year'] ?>"><br><br>
Страниц:<br><input type="number" name="pages" value="<?= $book['pages'] ?>"><br><br>
<button>Обновить</button>
</form>
<?php } ?>

<?php
if ($action == 'queries') {

    $newBooks = $db->query("SELECT * FROM books WHERE year > 2020")->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Книги после 2020 года</h2><ul>";
    foreach ($newBooks as $b) {
        echo "<li>{$b['title']} ({$b['year']})</li>";
    }
    echo "</ul>";

    $max = $db->query("SELECT * FROM books ORDER BY pages DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if ($max) {
        echo "<h2>Книга с самым большим количеством страниц</h2>";
        echo "<p>{$max['title']} — {$max['pages']} стр.</p>";
    }
}
?>

</body>
</html>
