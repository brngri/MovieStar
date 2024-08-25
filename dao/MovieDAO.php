<?php

require_once("models/Movie.php");
require_once("models/Message.php");

// Review DAO
require_once("dao/ReviewDAO.php");

class MovieDAO implements MovieDAOInterface
{
    private $conn;
    private $url;
    private $message;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
    }

    public function buildMovie($data)
    {

        $movie = new Movie();
        $movie->id = $data["id"];
        $movie->title = $data["title"];
        $movie->description = $data["description"];
        $movie->image = $data["image"];
        $movie->trailer = $data["trailer"];
        $movie->category = $data["category"];
        $movie->length = $data["length"];
        $movie->user_id = $data["user_id"];

        // Recebe as ratings do filme
        $reviewDao = new ReviewDAO($this->conn, $this->url);

        $rating = $reviewDao->getRating($movie->id);

        $movie->rating = $rating;

        return $movie;

    }
    public function findAll()
    {
    }
    public function getLatestMovies()
    {
        $movies = [];
        $statement = $this->conn->query("SELECT * FROM movies ORDER BY id DESC");
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $moviesArray = $statement->fetchAll();
            foreach ($moviesArray as $movie) {
                $movies[] = $this->buildMovie($movie);
            }
        }

        return $movies;
    }
    public function getMoviesByCategory($category)
    {

        $movies = [];
        $statement = $this->conn->prepare("SELECT * FROM movies WHERE category = :category ORDER BY id DESC");
        $statement->bindParam(":category", $category);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $moviesArray = $statement->fetchAll();
            foreach ($moviesArray as $movie) {
                $movies[] = $this->buildMovie($movie);
            }
        }

        return $movies;

    }
    public function getMoviesByUserId($id)
    {

        $movies = [];
        $statement = $this->conn->prepare("SELECT * FROM movies WHERE user_id = :user_id");
        $statement->bindParam(":user_id", $id);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $moviesArray = $statement->fetchAll();
            foreach ($moviesArray as $movie) {
                $movies[] = $this->buildMovie($movie);
            }
        }

        return $movies;

    }
    public function findById($id) {

        $movie = [];
  
        $stmt = $this->conn->prepare("SELECT * FROM movies
                                      WHERE id = :id");
  
        $stmt->bindParam(":id", $id);
  
        $stmt->execute();
  
        if($stmt->rowCount() > 0) {
  
          $movieData = $stmt->fetch();
  
          $movie = $this->buildMovie($movieData);
  
          return $movie;
  
        } else {
  
          return false;
  
        }
  
      }
    public function findByTitle($title){

        $movies = [];
        $statement = $this->conn->prepare("SELECT * FROM movies WHERE title like :title");
        $statement->bindValue(":title", '%'.$title.'%');
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $moviesArray = $statement->fetchAll();
            foreach ($moviesArray as $movie) {
                $movies[] = $this->buildMovie($movie);
            }
        }

        return $movies;

    }
    public function create(Movie $movie)
    {

        $statement = $this->conn->prepare("INSERT INTO movies(title, description, image, trailer, category, length, user_id) VALUES (:title, :description, :image, :trailer, :category, :length, :user_id)");

        $statement->bindParam(":title", $movie->title);
        $statement->bindParam(":description", $movie->description);
        $statement->bindParam(":image", $movie->image);
        $statement->bindParam(":trailer", $movie->trailer);
        $statement->bindParam(":category", $movie->category);
        $statement->bindParam(":length", $movie->length);
        $statement->bindParam(":user_id", $movie->user_id);

        $statement->execute();

        // Menssagem de sucesso por adicionar filme
        $this->message->setMessage("Filme adicionado com sucesso!", "success", "index.php");

    }
    public function update(Movie $movie)
    {
        $statement = $this->conn->prepare("UPDATE movies SET title = :title, description = :description, image = :image,
                category = :category, trailer = :trailer, length = :length WHERE id = :id");

        $statement->bindParam(":title", $movie->title);
        $statement->bindParam(":description", $movie->description);
        $statement->bindParam(":image", $movie->image);
        $statement->bindParam(":category", $movie->category);
        $statement->bindParam(":trailer", $movie->trailer);
        $statement->bindParam(":length", $movie->length);
        $statement->bindParam(":id", $movie->id);

        $statement->execute();

        // Menssagem de sucesso por remover filme
        $this->message->setMessage("Filme atualizado com sucesso!", "success", "dashboard.php");


    }
    public function destroy($id)
    {

        $statement = $this->conn->prepare("DELETE FROM movies WHERE id =:id");
        $statement->bindParam(":id", $id);
        $statement->execute();

        // Menssagem de sucesso por adicionar filme
        $this->message->setMessage("Filme removido com sucesso!", "success", "dashboard.php");

    }

}