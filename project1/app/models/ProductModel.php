<?php
class ProductModel {
    public $id;
    public $name;
    public $description;
    public $price;
    public $image;

    public function __construct($id, $name, $description, $price, $image = 'default.jpg') {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->image = $image;
    }

    public function getID() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
}