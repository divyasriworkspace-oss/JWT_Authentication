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
    public function index()
    {
        $patients = $this->patient->all();

        Response::json(200, "Patient list", $patients);
    }

    // Create a new patient record.
    public function store($request)
    {
        $body = $request['body'];

        $this->validate($body);

        $this->patient->create($body);

        Response::json(201, "Patient created");
    }

    // Update an existing patient by id.
    public function update($id, $request)
    {
        $body = $request['body'];

        $this->validate($body);

        $this->patient->update($id, $body);

        Response::json(200, "Patient updated");
    }

    // Delete a patient by id.
    public function delete($id)
    {
        $this->patient->delete($id);

        Response::json(200, "Patient deleted");
    }
}