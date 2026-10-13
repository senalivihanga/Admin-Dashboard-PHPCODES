<?php
require_once __DIR__ . '/../models/Offer.php';
require_once __DIR__ . '/AuthController.php';

class OfferController {
    private $auth;
    private $offerModel;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->offerModel = new Offer();
    }

    public function getOffers() {
        return $this->offerModel->getByNic($_SESSION['nic']);
    }

    public function respond() {
        $id = filter_var($_POST['offer_id'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['response'] ?? '';
        if (!$id || !in_array($action, ['accept', 'postpone', 'deny'], true)) {
            $_SESSION['offer_message'] = 'Choose a valid offer response.';
        } else {
            $nic = $_SESSION['nic'];
            $saved = match ($action) {
                'accept' => $this->offerModel->accept($id, $nic),
                'postpone' => $this->offerModel->postpone($id, $nic),
                'deny' => $this->offerModel->deny($id, $nic)
            };
            $_SESSION['offer_message'] = $saved ? 'Your offer response was saved.' : 'The offer could not be updated.';
        }
        redirect('/offer/respond');
    }
}
