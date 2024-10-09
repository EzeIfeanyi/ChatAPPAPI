<?php

namespace Presentation\Responses;

class ApiResponse {
    public string $status;
    public $data;
    public string $message;

    public function __construct(string $status, $data = null, string $message = '') {
        $this->status = $status;
        $this->data = $data;
        $this->message = $message;
    }

    // Convert the response to a JSON string
    public function toJson(): string {
        return json_encode([
            'status' => $this->status,
            'data' => $this->data,
            'message' => $this->message
        ]);
    }
}
