<?php

class ConstructionStagesCreate
{
	public $data;
	public $name;
	public $startDate;
	public $endDate;
	public $duration;
	public $durationUnit;
	public $color;
	public $externalId;
	public $status;

	public function __construct($data) {
        $this->data = $data;

		if(is_object($data)) {

			$vars = get_object_vars($this);

			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {

					$this->$name = $data->$name;
				}
			}
		}
	}

    public function validateStatus()
    {
        $statuses = ["NEW", "PLANNED", "DELETED"];

        if (isset($this->data->status) && !in_array($this->data->status, $statuses)) {
            $error = [
                'success' => false,
                'message' => 'Invalid status value. Status must be NEW, PLANNED or DELETED.'
            ];

            response($error, false, 400);
        }
        return true;
    }
}