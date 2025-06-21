<?php
require_once __DIR__ . '/../models/Worker.php';

class WorkerController {
    private $workerModel;

    public function __construct() {
        $this->workerModel = new Worker();
    }

    public function getAllWorkers() {
        return $this->workerModel->getAll();
    }

    public function getWorkerById($id) {
        return $this->workerModel->getById($id);
    }

    public function createWorker($dni, $name, $lastname, $email, $user_id) {
        return $this->workerModel->create($dni, $name, $lastname, $email, $user_id);
    }

    public function updateWorker($id, $dni, $name, $lastname, $email) {
        return $this->workerModel->update($id, $dni, $name, $lastname, $email);
    }

    public function deleteWorker($id) {
        return $this->workerModel->delete($id);
    }
}
?>