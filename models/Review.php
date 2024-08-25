<?php 

class Review{
    public $id;
    public $rating;
    public $review;
    public $user_id;
    public $movies_id;


}

interface ReviewDAOInterface{
    public function buildReview($data);
    public function create(Review $review);

    public function getMoviesReview($id);
    public function hasAlreadyReviewed($id, $user_id);
    public function getRating($id);
}