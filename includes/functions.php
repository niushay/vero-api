<?php

if (!function_exists('response'))
{
    /**
     * Returns a JSON-formatted response with the specified data, success status, and response code.
     *
     * This method is responsible for generating a standardized JSON response that includes the given data,
     * success status, and HTTP response code. It is useful for providing consistent API responses.
     *
     * @param array $dataArray An associative array containing the data to be included in the response.
     * @param bool $success A boolean value indicating whether the operation was successful or not.
     * @param int $responseCode The HTTP response code to be sent with the response.
     * @return void
     */
    function response(array $dataArray = [], bool $success = true, int $responseCode = 200)
    {
        $response = [
            'success' => $success
        ];

        http_response_code($responseCode);
        header('Content-Type: application/json');
        if($dataArray){
            $response = array_merge($response, $dataArray);
        }
        echo json_encode($response);
        exit;
    }
}