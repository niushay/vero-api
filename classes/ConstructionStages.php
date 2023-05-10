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
        $data->validateData();

        $durationUnit = $data->durationUnit ?? 'DAYS';
        $status = $data->status ?? 'NEW';
        $duration = $data->calculateDuration($data->startDate, $data->endDate, $durationUnit);

		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $duration,
			'durationUnit' => $durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

    /**
     * Updates the specified ConstructionStage item with the provided data.
     *
     * This method is responsible for editing the ConstructionStage item identified by the given ID.
     * It takes the updated data provided by the user in the form of a ConstructionStagesCreate object
     * and applies the changes to the item.
     *
     * @param int $id The unique identifier of the ConstructionStage item to be updated.
     * @param ConstructionStagesCreate $data An object containing the updated field values for the ConstructionStage item.
     * @return void
     *
     * @throws PDOException If there is an error executing the SQL statement.
     */
    public function update(ConstructionStagesCreate $data, int $id): void
    {
        try {
            $data->validateStatus();

            $constructionItem = $this->getSingle($id);
            if (empty($constructionItem)) {
                response(['message' => 'Record not found.'], false, 404);
            }

            $start_date = $data->startDate ?? $constructionItem[0]['startDate'];
            $end_date = $data->endDate ?? $constructionItem[0]['endDate'];
            $duration_unit = $data->durationUnit ?? $constructionItem[0]['durationUnit'];
            $duration = $data->calculateDuration($start_date, $end_date, $duration_unit);

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
                'start_date' => $start_date,
                'end_date' => $end_date,
                'duration' => $duration ?? null,
                'durationUnit' => $duration_unit,
                'color' => $data->color ?? null,
                'externalId' => $data->externalId ?? null,
                'status' => $data->status ?? null,
            ]);

            $constructionItem = $this->getSingle($id);
            response([
                'message' => 'Record updated successfully.',
                'data' => $constructionItem,
            ]);
        } catch (PDOException $e) {
            response(['message' => $e->getMessage()], false, 500);
        }
    }

    /**
     * Delete a construction stage.
     *
     * This method updates the status of the construction stage with the specified ID to 'DELETED'.
     *
     * @param int $id The ID of the construction stage to delete.
     *
     * @return void
     *
     * @throws PDOException If there is an error executing the SQL statement.
     */
    public function delete(int $id): void
    {
        try {
            $constructionItem = $this->getSingle($id);
            if (empty($constructionItem)) {
                $response = ['message' => 'Record not found.'];
                response($response, true, 404);
            }

            $stmt = $this->db->prepare("
            UPDATE construction_stages SET
                status = 'DELETED'
            WHERE id = :id
            ");
            $stmt->execute(['id' => $id]);

            $response = ['message' => 'Record deleted successfully.'];
            response($response);
        } catch (PDOException $e) {
            $error = ['message' => $e->getMessage()];
            response($error, false, 500);
        }
    }

}