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

    /**
     * Validates the status value of the current object.
     *
     * This method checks whether the status value of the current object is valid. The valid status values are "NEW",
     * "PLANNED", and "DELETED". If the status value is invalid, an error response is returned with a 400 HTTP status code.
     *
     * @return bool Returns true if the status value is valid, or false if an error response is returned.
     */
    public function validateStatus()
    {
        $statuses = ["NEW", "PLANNED", "DELETED"];

        if (isset($this->data->status) && !in_array($this->data->status, $statuses)) {
            $error = [
                'message' => 'Invalid status value. Status must be NEW, PLANNED or DELETED.'
            ];

            response($error, false, 400);
        }
        return true;
    }

    /**
     * Validates the posted data against a set of rules.
     *
     * This method checks each field in the posted data against a set of rules to ensure that they meet the required
     * criteria. If a field fails validation, an error response is returned with a message indicating the reason for the failure.
     *
     * @return bool
     */
    function validateData(): bool
    {
        $name = $this->data->name ?? null;
        $start_date = $this->data->startDate ?? null;
        $end_date = $this->data->endDate ?? null;
        $durationUnit = $this->data->durationUnit ?? 'DAYS';
        $color = $this->data->color ?? null;
        $externalId = $this->data->externalId ?? null;
        $status = $this->data->status ?? 'NEW';

        // Validate name
        if ($name === null || strlen($name) > 255) {
            $error = [
                'message' => 'Name is required and must be a maximum of 255 characters in length.'
            ];
            response($error, false, 400);
        }

        // Validate start_date
        if ($start_date === null || !DateTime::createFromFormat(DateTimeInterface::ISO8601, $start_date)) {
            $error = [
                'message' => 'Start date must be a valid date and time in ISO8601 format (e.g. 2022-12-31T14:59:00Z).'
            ];
            response($error, false, 400);
        }

        // Validate end_date
        if ($end_date !== null) {
            $start = DateTime::createFromFormat(DateTimeInterface::ISO8601, $start_date);
            $end = DateTime::createFromFormat(DateTimeInterface::ISO8601, $end_date);
            if (!$end || $end <= $start) {
                $error = [
                    'message' => 'End date must be a valid date and time that is later than the start date.'
                ];
                response($error, false, 400);
            }
        }

        // Validate durationUnit
        $validDurationUnits = ['HOURS', 'DAYS', 'WEEKS'];
        if (!in_array($durationUnit, $validDurationUnits)) {
            $error = [
                'message' => 'Duration unit must be one of HOURS, DAYS, or WEEKS.'
            ];
            response($error, false, 400);
        }

        // Validate color
        if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $error = [
                'message' => 'Color must be a valid HEX color code (e.g. #FF0000).'
            ];
            response($error, false, 400);
        }

        // Validate externalId
        if ($externalId !== null && strlen($externalId) > 255) {
            $error = [
                'message' => 'External ID must be a maximum of 255 characters in length.'
            ];
            response($error, false, 400);
        }

        // Validate status
        $validStatuses = ['NEW', 'PLANNED', 'DELETED'];
        if ($status === null || !in_array($status, $validStatuses)) {
            $error = [
                'message' => 'Status is required and must be one of NEW, PLANNED, or DELETED.'
            ];
            response($error, false, 400);
        }
        return true;
    }

    /**
     * Calculates the duration between two dates in the specified duration unit.
     *
     * @param string $durationUnit The duration unit (HOURS, DAYS, or WEEKS).
     * @return float|null The duration in the specified unit, or null if the duration unit is invalid.
     */
    function calculateDuration($start_date, $end_date, string $durationUnit): ?float
    {
        $start_date = new DateTime($start_date);
        $end_date = isset($end_date) ? new DateTime($end_date) : null;

        if ($end_date === null) {
            return null;
        }
        $interval = $end_date->diff($start_date);

        switch ($durationUnit) {
            case 'HOURS':
                $duration = $interval->h + $interval->days * 24;;
                break;
            case 'WEEKS':
                $duration = $interval->days / 7 * 24;
                break;
            default:
                $duration = $interval->days * 24;
                break;
        }

        return round($duration);
    }

}