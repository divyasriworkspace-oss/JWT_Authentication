<?php

// Handles protected patient CRUD endpoints.
class PatientController
{
    // Patient model for database operations.
    private $patient;

    // Inject database connection and initialize the model.
    public function __construct($db)
    {
        $this->patient = new Patient($db);
    }

     // Resolve authenticated user id from middleware-attached request payload.
    private function getUserId($request)
    {
        if (empty($request['user']['user_id'])) {
            Response::json(401, "Unauthorized");
        }
        //Get the logged-in user's user_id from the request and return it as an integer.
        return (int)$request['user']['user_id'];
    }
    // Validate required patient payload fields.
    private function validate($data)
    {
        if (
            empty($data['name']) ||
            empty($data['age']) ||
            empty($data['gender']) ||
            empty($data['phone'])
        ) {
            Response::json(400, "Required fields missing");
        }
    }

   // Return all patients.
    public function index($request)
    {
        $userId = $this->getUserId($request);

        $patients = $this->patient->allByUser($userId);

        Response::json(200, "Patient list", $patients);
    }

    // Create a new patient record.
    public function store($request)
    {
        $body = $request['body'];
        $userId = $this->getUserId($request);

        $this->validate($body);

        $this->patient->create($body,$userId);

        Response::json(201, "Patient created");
    }

    // Update an existing patient by id.
    public function update($id, $request)
    {
        $body = $request['body'];
        $userId = $this->getUserId($request);

        $this->validate($body);

         if (!$this->patient->existsByIdAndUser((int)$id, $userId)) {
            Response::json(404, "Patient not found");
        }

        $this->patient->updateByUser((int)$id, $body, $userId);

        Response::json(200, "Patient updated");
    }

    // Delete a patient by id.
    public function delete($id,$request)
    {   
        $userId = $this->getUserId($request);
        
        if (!$this->patient->existsByIdAndUser((int)$id, $userId)) {
            Response::json(404, "Patient not found");
        }

        $this->patient->deleteByUser((int)$id, $userId);

        Response::json(200, "Patient deleted");
    }
}
