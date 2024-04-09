<?php

class Connection {

    private $databaseFile;
    private $connection;

    public function __construct(){
        $this->databaseFile = realpath(__DIR__ . "/database/db.sqlite");
        $this->connect();
    }

    private function connect(){
        return $this->connection = new PDO("sqlite:{$this->databaseFile}");
    }

    public function getConnection(){
        return $this->connection ?: $this->connection = $this->connect();
    }

    public function query($query){
        $result      = $this->getConnection()->query($query);

        $result->setFetchMode(PDO::FETCH_INTO, new stdClass);

        return $result;
    }

    public function insertUser($name, $email){
        $query = "INSERT INTO users (name, email) VALUES (:name, :email)";
        $statement = $this->getConnection()->prepare($query);
        $statement->bindParam(':name', $name);
        $statement->bindParam(':email', $email);
        $statement->execute();
    }

    public function updateUser($userId, $name, $email){
        $query = "UPDATE users SET name = :name, email = :email WHERE id = :userId";
        $statement = $this->getConnection()->prepare($query);
        $statement->bindParam(':name', $name);
        $statement->bindParam(':email', $email);
        $statement->bindParam(':userId', $userId);
        $statement->execute();
    }

    public function updateUserColors($userId, $colors){
        $this->query("DELETE FROM user_colors WHERE user_id = $userId");
        $query = "INSERT INTO user_colors (user_id, color_id) VALUES (:userId, :colorId)";
        $statement = $this->getConnection()->prepare($query);
        foreach ($colors as $colorId) {
            $statement->bindParam(':userId', $userId);
            $statement->bindParam(':colorId', $colorId);
            $statement->execute();
        }
    }



}