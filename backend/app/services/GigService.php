<?php

require_once __DIR__ . '/../dao/GigDao.php';

Class GigService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new GigDao();
    }

    public function get_gig_by_id($id) {
        return $this->dao->get_gig_by_id($id);
    }

    public function add_gig($gig){
        if (!isset($gig['created_at']) || empty($gig['created_at'])) {
            $gig['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->dao->add_gig($gig);
    }

    public function get_gigs(){
                $gigs = $this->dao->get_gigs();
        foreach ($gigs as &$gig) {
            $gig['is_locked'] = $this->dao->is_gig_locked($gig['id']);
        }
        return $gigs;
    }

    public function delete_gig($id){
        if ($this->dao->is_gig_locked($id)) {
            Flight::halt(403, "Cannot delete this gig. A freelancer is approved and not yet paid.");
        }
        return $this->dao->delete_gig($id);
    }

    public function search_gigs($searchTerm){
        return $this->dao->search_gigs($searchTerm);
    }

    public function update_gig($data){
        return $this->dao->update_gig($data);
    }

    public function getAllGigs($excludeUserId = null) {
    return $this->dao->getAll($excludeUserId);
    }

   public function updateGig($id, $title, $price, $status, $gig_image_url = null) {
    if ($this->dao->is_gig_locked($id)) {
        Flight::halt(403, "Gig is locked because a freelancer has been approved but not paid.");
    }
    return $this->dao->updateGig($id, $title, $price, $status, $gig_image_url);
    }


    public function getAllWithFilters(array $filters) {
        return $this->dao->getAllWithFilters($filters);
    }

    public function getGigByIdWithUser($gigId)
        {
            return $this->dao->getGigByIdWithUser($gigId);
        }


   public function apply_to_gig($gig_id, $user_id, $message, $cv_filename = null) {
        return $this->dao->apply_to_gig($gig_id, $user_id, $message, $cv_filename);
    }


    public function get_applications_for_gig($gig_id) {
        return $this->dao->get_applications_for_gig($gig_id);
    }

    public function approve_applicant($gig_id, $user_id) {
        return $this->dao->approve_applicant($gig_id, $user_id);
    }

    public function get_application_status($gig_id, $user_id) {
        return $this->dao->get_application_status($gig_id, $user_id);
    }

    public function pay_freelancer($gig_id, $payer_id, $freelancer_id) {
        $gig = $this->dao->get_gig_by_id($gig_id);
        if (!$gig) {
            return ['success' => false, 'error' => 'Gig not found'];
        }

        $amount = $gig['price'];

        $payer_balance = $this->dao->get_user_balance($payer_id);
        $freelancer_balance = $this->dao->get_user_balance($freelancer_id);

        if ($payer_balance < $amount) {
            return ['success' => false, 'error' => 'Insufficient funds'];
        }

        $this->dao->update_user_balance($payer_id, $payer_balance - $amount);
        $this->dao->update_user_balance($freelancer_id, $freelancer_balance + $amount);

        $this->dao->record_transaction([
            'sender_id' => $payer_id,
            'receiver_id' => $freelancer_id,
            'gig_id' => $gig_id,
            'amount' => $amount,
            'status' => 'completed',
            'transaction_date' => date('Y-m-d H:i:s')
        ]);

        $this->dao->mark_application_paid($gig_id, $freelancer_id);

        return ['success' => true];
    }

    public function reject_application($gig_id, $user_id) {
        return $this->dao->updateApplicationStatus($gig_id, $user_id, 'rejected');
    }

    public function is_gig_locked($gig_id) {
        return $this->dao->is_gig_locked($gig_id);
    }






}
