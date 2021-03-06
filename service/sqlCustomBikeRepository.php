<?php

require_once("model/customBike.php");
require_once("model/cartItem.php");
require_once("customBikeRepository.php");
require_once("sqlContext.php");
require_once("log.php");

class SqlCustomBikeRepository implements CustomBikeRepository {

    private $context;
    private $log;

    function __construct(SqlContext $context, Log $log) {
        $this->context = $context;
        $this->log = $log;
    }

    function getAllBikes() {
        $allBikes = $this->context->executeOne($this->selectBikes());
        return $this->asCustomBikes($allBikes);
    }

    private function selectBikes(){
        return "SELECT id, name, image, category, price, description, date_added, is_highlighted FROM Bikes";
    }

    private function map($row) {
        return new CustomBike($row[1], $row[3], $row[4], $row[5], $row[2], $row[7], $row[0], $row[6]);
    }

    function getLatestBikes() {
        $amount = CustomBikeRepository::NumberOfLatest;
        $sql = $this->selectBikes() . " ORDER BY date_added DESC LIMIT $amount";
        $latestBikes = $this->context->executeOne($sql);
        return $this->asCustomBikes($latestBikes);
    }

    function getHighlightedBikes() {
        $amount = CustomBikeRepository::NumberOfHighlights;
        $sql = $this->selectBikes() . " WHERE is_highlighted = 1 ORDER BY date_added DESC LIMIT $amount";
        $highlightedBikes = $this->context->executeOne($sql);
        return $this->asCustomBikes($highlightedBikes);
    }

    function searchByName($name) {
        $sql = $this->selectBikes() . " WHERE name LIKE '%$name%'";
        $searchResults = $this->context->executeOne($sql);
        return $this->asCustomBikes($searchResults);
    }

    function searchByCategory($category) {
        $sql = $this->selectBikes() . " WHERE category = '$category'";
        $searchResults = $this->context->executeOne($sql);
        return $this->asCustomBikes($searchResults);
    }

    function searchById($id) {
        $sql = $this->selectBikes() . " WHERE id = $id";
        $searchResults = $this->context->executeOne($sql);
        return $this->asCustomBike($searchResults);
    }

    function getCategories() {
        $sql = "SELECT DISTINCT category FROM Bikes GROUP BY category";
        $categories = $this->context->executeOne($sql);
        return $this->asArray($categories);
    }

    private function asCustomBikes(mysqli_result $result){
        $bikes = array();

        $fetchResults = $result->fetch_all();

        if ($fetchResults === null)
            return null;

        $this->log->info("Fetched " . count($fetchResults) . " bikes");

        foreach ($fetchResults as $row){
            $bikes[] = $this->map($row);
        }

        return $bikes;
    }

    private function asCustomBike(mysqli_result $searchResults) {
        $row = $searchResults->fetch_row();

        if ($row !== null){
            return $this->map($row);
        }

        return null;
    }

    private function asArray(mysqli_result $result) {
        $categories = array();

        $fetchResults = $result->fetch_all();

        if ($fetchResults === null)
            return null;

        $numberOfCategories = count($fetchResults);
        $this->log->info("Fetched $numberOfCategories categories");

        foreach ($fetchResults as $category){
            $categories[$category[0]] = $category[0];
        }

        return $categories;
    }

    function delete($id) {
        $this->context->executeOne("DELETE FROM Bikes WHERE id = $id");
    }

    function add(CustomBike $bike) {
        $name = $this->context->escape_string($bike->name);
        $category = $this->context->escape_string($bike->category);
        $description = $this->context->escape_string($bike->description);
        $image = $this->context->escape_string($bike->image);
        $price = $bike->price;
        $isHighlighted = $bike->isHighlighted ? 1 : 0;

        $this->context->executeOne("INSERT INTO Bikes (name, category, price, description, image, is_highlighted) VALUES ('$name', '$category', $price, '$description', '$image', $isHighlighted)");
    }

    function update(CustomBike $bike) {
        $id = $bike->id;
        $name = $this->context->escape_string($bike->name);
        $category = $this->context->escape_string($bike->category);
        $description = $this->context->escape_string($bike->description);
        $image = $this->context->escape_string($bike->image);
        $price = $bike->price;
        $isHighlighted = $bike->isHighlighted ? 1 : 0;

        $this->context->executeOne("UPDATE Bikes SET name = '$name', category = '$category', description = '$description', image = '$image', price = $price, is_highlighted = $isHighlighted WHERE id = $id");
    }
}