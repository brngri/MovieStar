<?php

require_once("globals.php");
require_once("db.php");
require_once("models/Movie.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");
require_once("dao/MovieDAO.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$movieDao = new MovieDAO($conn, $BASE_URL);

// Resgata o tipo do  formulário

$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário
$userData = $userDao->verifyToken();

if ($type === "create") {

    // Receber os dados do input    
    $title = filter_input(INPUT_POST, "title");
    $description = filter_input(INPUT_POST, "description");
    $trailer = filter_input(INPUT_POST, "trailer");
    $category = filter_input(INPUT_POST, "category");
    $length = filter_input(INPUT_POST, "length");

    $movie = new Movie();

    // Validação minima de dados
    if (!empty($title) && !empty($description) && !empty($category)) {

        $movie->title = $title;
        $movie->description = $description;
        $movie->trailer = $trailer;
        $movie->category = $category;
        $movie->length = $length;
        $movie->user_id = $userData->id;

        // Upload de imagem do filme
        if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

            $image = $_FILES["image"];
            $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
            $jpgArray = ["image/jpeg", "image/jpg"];

            // Checando tipo da imagem
            if (in_array($image["type"], $imageTypes)) {

                // Checa se imagem é jpg
                if (in_array($image["type"], $jpgArray)) {

                    $imageFile = imagecreatefromjpeg($image["tmp_name"]);

                } else {
                    $imageFile = imagecreatefrompng($image["tmp_name"]);
                }

                // Gerando nome da imagem
                $imageName = $movie->imageGenerateName();

                imagejpeg($imageFile, "./img/movies/" . $imageName, 100);

                $movie->image = $imageName;

            } else {

                $message->setMessage("Tipo inválido de imagem, insira png ou jpg!", "error", "back.php");

            }

        }

        $movieDao->create($movie);

    } else {
        $message->setMessage("Você precisa pelo menos adcionar: título, descrição e categoria!", "error", "back");
    }

} elseif ($type === "delete") {

    // Recebe os dados ao form 
    $id = filter_input(INPUT_POST, "id");

    $movie = $movieDao->findById($id);

    if ($movie) {

        // Verificar se o filme é do usuário
        if ($movie->user_id === $userData->id) {

            $movieDao->destroy($movie->id);

        } else {
            $message->setMessage("Informações inválidas.", "error", "index.php");
        }

    } else {
        $message->setMessage("Informações inválidas.", "error", "index.php");
    }

} elseif ($type === "update") {

    // Recebendo os inputs do formulário
    $title = filter_input(INPUT_POST, "title");
    $description = filter_input(INPUT_POST, "description");
    $trailer = filter_input(INPUT_POST, "trailer");
    $category = filter_input(INPUT_POST, "category");
    $length = filter_input(INPUT_POST, "length");
    $id = filter_input(INPUT_POST, "id");

    $movieDb = $movieDao->findById($id);

    // Verifica se o filme existe
    if ($movieDb) {

        // Verificar se o filme pertence ao usuário
        if ($movieDb->user_id === $userData->id) {

            // Verificação de dados mínimos
            if (
                !empty($title) &&
                !empty($description) &&
                !empty($category)
            ) {

                // Criar o objeto de movie, apenas com os dados que vieram
                $movieDb->title = $title;
                $movieDb->description = $description;
                $movieDb->trailer = $trailer;
                $movieDb->category = $category;
                $movieDb->length = $length;

                $image = $_FILES["image"];

                // Verifica se veio alguma imagem
                if (!empty($image["tmp_name"])) {

                    // Checando tipo da imagem
                    if (in_array($image["type"], ["image/jpeg", "image/jpg", "image/png"])) {

                        // Checa se é jpg
                        if (in_array($image["type"], ["image/jpeg", "image/jpg"])) {
                            $imageFile = imagecreatefromjpeg($image["tmp_name"]);
                        } else {
                            $imageFile = imagecreatefrompng($image["tmp_name"]);
                        }

                        $movie = new Movie();

                        $imageName = $movie->imageGenerateName();

                        imagejpeg($imageFile, "./img/movies/" . $imageName, 100);

                        $movieDb->image = $imageName;

                    } else {
                        $message->setMessage("Tipo inválido de imagem, envie jpg ou png!", "error", "dashboard.php");
                    }

                }

                $movieDao->update($movieDb);

            } else {

                $message->setMessage("Você precisa adicionar pelo menos: título, descrição e categoria.", "error", "dashboard.php");

            }

        } else {
            $message->setMessage("Erro, tente novamente mais tarde!", "error", "dashboard.php");
        }

    } else {

        $message->setMessage("Este filme não existe!", "error", "dashboard.php");

    }

}