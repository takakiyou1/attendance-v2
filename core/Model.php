<?php
// core/Model.php
require_once __DIR__ . '/../config/database.php';

class Model {
    protected $pdo;
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
}
