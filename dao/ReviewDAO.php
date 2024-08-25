<?php 

    require_once("models/Review.php");
    require_once("models/Message.php");
    require_once("dao/UserDAO.php");

    Class ReviewDAO implements ReviewDAOInterface{

    private $conn;
    private $url;

    private $message;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
    }

    public function buildReview($data){

        $reviewObject = new Review();
        $reviewObject->id = $data["id"];
        $reviewObject->rating = $data["rating"];
        $reviewObject->review = $data["review"];
        $reviewObject->user_id = $data["user_id"];
        $reviewObject->movies_id = $data["movies_id"];
        
        return $reviewObject;

    }
    public function create(Review $review){
        $statement = $this->conn->prepare("INSERT INTO reviews(rating, review, movies_id, user_id) VALUES (:rating, :review, :movies_id, :user_id)");

        $statement->bindParam(":rating", $review->rating);
        $statement->bindParam(":review", $review->review);
        $statement->bindParam(":movies_id", $review->movies_id);
        $statement->bindParam(":user_id", $review->user_id);

        $statement->execute();

        // Menssagem de sucesso por adicionar filme
        $this->message->setMessage("Crítica adicionado com sucesso!", "success", "index.php");

    }

    public function getMoviesReview($id){
        $reviews = [];
        $statement = $this->conn->prepare("SELECT * FROM reviews WHERE movies_id = :movies_id");
        $statement->bindParam(":movies_id", $id);

        $statement->execute();

        if($statement->rowCount() > 0) {
  
            $reviewsData = $statement->fetchAll();

            $userDao = new UserDAO($this->conn, $this->url);

            foreach ($reviewsData as $review) {

                $reviewObject = $this->buildReview($review);

                // Chamar dados do usuário

                $user = $userDao->findById($reviewObject->user_id);

                $reviewObject->user = $user;

                $reviews[] = $reviewObject;
            }
        }

        return $reviews;

    }
    public function hasAlreadyReviewed($id, $userId){
        $statement = $this->conn->prepare("SELECT * FROM reviews WHERE movies_id = :movies_id AND user_id = :user_id");
        $statement->bindParam(":movies_id",$id);
        $statement->bindParam(":user_id",$userId);

        $statement->execute();

        if($statement->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }
    public function getRating($id){
        $statement = $this->conn->prepare("SELECT * FROM reviews WHERE movies_id = :movies_id");
        $statement->bindParam(":movies_id", $id);
        $statement->execute();

        if($statement->rowCount() > 0){
            $rating = 0;
            $reviews = $statement->fetchAll();

            foreach($reviews as $review){
                $rating += $review["rating"];
            }

            $rating = $rating / count($reviews);

        }else{
            $rating = "Não avaliado";
        }

        return $rating;

    }
}