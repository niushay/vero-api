<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

    /**
     * @param $id
     * @param ConstructionStagesCreate $data
     * @return void
     */
    public function update(ConstructionStagesCreate $data, $id)
    {
        $data->validateStatus();

        $stmt = $this->db->prepare("
                UPDATE construction_stages SET
                    name = IFNULL(:name, name),
                    start_date = IFNULL(:start_date, start_date),
                    end_date = IFNULL(:end_date, end_date),
                    duration = IFNULL(:duration, duration),
                    durationUnit = IFNULL(:durationUnit, durationUnit),
                    color = IFNULL(:color, color),
                    externalId = IFNULL(:externalId, externalId),
                    status = IFNULL(:status, status)
                WHERE id = :id
                ");
        $stmt->execute([
            'id' => $id,
            'name' => $data->name ?? null,
            'start_date' => $data->startDate ?? null,
            'end_date' => $data->endDate ?? null,
            'duration' => $data->duration ?? null,
            'durationUnit' => $data->durationUnit ?? null,
            'color' => $data->color ?? null,
            'externalId' => $data->externalId ?? null,
            'status' => $data->status ?? null,
        ]);

        // Check if the query was successful
        if ($stmt->rowCount() === 0) {
            $error = ['message' => 'Record not found.'];
            response($error, false, 404);
        }

        $constructionItem = $this->getSingle($id);
        $response = [
            'message' => 'Record updated successfully.',
            'data' => $constructionItem
        ];

        response($response);
    }
}