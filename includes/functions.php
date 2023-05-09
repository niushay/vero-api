<?php

if (!function_exists('response'))
{
    /**
     * @param array $dataArray
     * @param bool $success
     * @param int $responseCode
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