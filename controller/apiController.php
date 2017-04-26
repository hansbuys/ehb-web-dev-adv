<?php
require_once(SERVICE_PATH . "/customBikeRepository.php");
require_once(SERVICE_PATH . "/purchaseRepository.php");

class ApiController {

    private $customBikes;
    private $purchases;

    function __construct(CustomBikeRepository $bikes, PurchaseRepository $purchases) {
        $this->customBikes = $bikes;
        $this->purchases = $purchases;
    }

    public function buy($id) {
        $bike = $this->customBikes->searchById($id);
        if ($bike !== NULL) {
            $this->addToCart($bike);

            echo json_encode($bike);
        } else {
            header('X-PHP-Response-Code: 404', true, 404);
            throw new Exception("Custom bike with id " . $bike->id . " not found.");
        }
    }

    public function count(){
        echo $this->purchases->getNumberOfItemsInCart();
    }

    private function addToCart(CustomBike $bike) {
        $this->purchases->addToCart($bike);

    }
}