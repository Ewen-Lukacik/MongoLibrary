<?php
use MongoDB\BSON\Regex;

require_once __DIR__ . '/vendor/autoload.php';
$searchResults = null;
$client = new MongoDB\Client("mongodb://localhost:27017");

$db = $client->selectDatabase("bibliotheque");
$livres = $db->livres;


function findBooks($livres) {
    $allBooks = $livres->find();
    return iterator_to_array($allBooks);
}

function createBook($livres){
    $title = $author = $year = null;

    if(empty($_POST["title"] || empty($_POST["author"] || empty($_POST["year"])))){
        throw new Exception("All inputs required");
    } else {
        $title = clean_input($_POST["title"]);
        $author = clean_input($_POST["author"]);
        $year = clean_input($_POST["year"]);
    
        $mongoQueryParam = ["titre" => $title, "auteur" => $author, "annee" => $year];
        return $livres->insertOne($mongoQueryParam);
    }
}

function clean_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function search($livres){
    if(empty($_POST["key"])){
        throw new Exception("Why would you search something blank");
    } 

    $key = clean_input($_POST["key"]);
    $value = clean_input($_POST["value"]);

    $mongoQueryParam = ["auteur" => new Regex($value, "i")];
    $results = $livres->find($mongoQueryParam);

    return iterator_to_array($results);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    switch($_POST["formName"]){
        case('createForm'):
            createBook($livres);
            break;
        case('searchForm'): 
                $searchResults = search($livres);
            break;

    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$allBooks = findBooks($livres);

$fields = [];
foreach($allBooks as $book){
    foreach($book as $key => $value){
        $fields[$key] = true;
    }
}
$all_fields = array_keys($fields);



?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mongo Yay</title>
</head>
<body>
    <main style="margin-inline: 10rem;">
        <h1>Library</h1>

        <section id="show-books" style="outline: 1px solid red; min-height: 25rem; margin-bottom: 5rem;">
            <h2>All books</h2>
            <div style="display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            grid-template-rows: repeat(3, 1fr);
                            grid-column-gap: 5rem;
                            grid-row-gap: 2rem;
                            align-items: center;
                    ">
                <?php foreach($allBooks as $book) {  ?>
                    <div style="width: 20rem; padding:1rem; outline: 1px solid red;" class="book">
                        <small class="id">ID: <?php echo $book->_id; ?></small>
                        <h2><?php echo $book->titre ;?> by <?php echo $book->auteur; ?></h2>
                        <small>Published in : <?php echo $book->annee; ?></small>
                        <button class="edit">Edit</button>
                    </div>
                <?php } ?>
            </section>
        </section>

        <section class="book-actions" style="display: flex; flex-direction: row; justify-content: space-between">

            <section id="create-book" style="width:50%; display: flex; align-items: center; flex-direction: column;">
                <h2>Create a new book</h2>
                <form method="post" style="display:flex; flex-direction: column; gap:1rem; width: 75%; ">
                    <input type="hidden" name="formName" value="createForm">

                    <label for="title">Book title</label>
                    <input required type="text" name="title" id="title">
                    
                    <label for="author">Book Author</label>
                    <input required type="text" name="author" id="author">
    
                    <label for="year">Year of publication</label>
                    <input required type="number" name="year" id="year">
    
                    <button type="submit">Submit book</button>
                </form>
            </section>

            <section id="search-book" style="width:50%; display: flex; align-items: center; flex-direction: column;">
                <h2>Search a book</h2>
                <form method="post" style="display:flex; flex-direction: column; gap:1rem; width: 75%; ">
                    <input type="hidden" name="formName" value="searchForm">

                    <label for="key">Search by</label>
                    <select name="key" id="key">
                        <?php foreach($all_fields as $field) {  ?>
                            <option value="<?php echo $field; ?>"><?php echo $field; ?></option>
                        <?php } ?>
                        
                    </select>

                    <label for="value">whatever you want to find</label>
                    <input type="text" name="value" id="value">

                    <button type="submit">Search</button>
                </form>

                <div class="searchResults">
                    <?php if($searchResults != null){ ?>
                        <?php foreach($searchResults as $res){ ?>
                            <div style="width: 20rem; padding:1rem; outline: 1px solid red;" class="book">
                                <small class="id">ID: <?php echo $res->_id; ?></small>
                                <h2><?php echo $res->titre ;?> by <?php echo $res->auteur; ?></h2>
                                <small>Published in : <?php echo $res->annee; ?></small>
                                <button class="edit">Edit</button>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>Aucun résultat</p>
                        <?php
                        echo '<pre>';
var_dump($searchResults);
echo '</pre>';
die;
                        ?>
                    <?php } ?>
                    
                </div>
            </section>
        </section>
    </main>
</body>
</html>


