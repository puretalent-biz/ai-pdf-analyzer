<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAiService
{
    protected $api_key;
    protected $model;
    protected $file_extension;

    /**
     * Create a new OpenAiService instance.
     * @param string $model The model to use for the assistant.
     * @param string $file_extension The file extension to use for the assistant.
     */
    public function __construct($model, $file_extension = null)
    {
        $this->api_key = config('services.openai.api_key');
        $this->model = $model;
        $this->file_extension = Str::upper($file_extension);
    }

    /**
     * Delete the assistant by its ID.
     * @param string $assistant_id The ID of the assistant to delete.
     * @return bool True if the deletion was successful, false otherwise.
     */
    public function deleteAssistant($assistant_id)
    {
        try {
            $client = new Client;
            $response = $client->delete('https://api.openai.com/v1/assistants/' . $assistant_id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]);

            Log::info('Assistant deleted: ', ['assistant_id' => $assistant_id]);
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting assistant: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * List all assistants.
     * @return array|false An array of assistants, or false if an error occurred.
     */
    public function listAssistants()
    {
        try {
            $client = new Client;
            $response = $client->get('https://api.openai.com/v1/assistants', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]);

            $response_json = json_decode($response->getBody());
            
            Log::info('Assistants list: ', (array) $response_json->data);

            // Assuming the response contains a 'data' field with the list of assistants.
            return $response_json->data; 

        } catch (Exception $e) {
            Log::error('Error listing assistants: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create an assistant.
     * @param string $file_id The ID of the file.
     * @return string|false The ID of the assistant, or false if an error occurred.
     */
    public function createAssistant($file_id)
    {
        try {
            $client = new Client;
            $response = $client->post('https://api.openai.com/v1/assistants', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'model' => $this->model,
                    'name' => 'file_analyzer',

                    'description' => "You are an assistant designed to analyze and interpret the content of {$this->file_extension} files. Your main task is to read the file, extract the textual content, and analyze it to solve the problem mentioned.",

                    'instructions' => "Please read the content of the {$this->file_extension} file and extract the text. If the file contains images, use Optical Character Recognition (OCR) to read the content of the images and extract the text. If the file contains base64 encoded text, decode it using base64 decryption. If the file contains readable text, extract it. After extracting the text, analyze it to address the problem mentioned. Format your output concisely, with only the solution, using as few words as possible.",

                    'tools' => [
                        ['type' => 'code_interpreter']
                    ],
                    'tool_resources' => [
                        'code_interpreter' => [
                            'file_ids' => [$file_id]
                        ]
                    ],
                    'temperature' => 0.02,
                    'top_p' => 1
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Assistant created: ', (array) $response_json);
            return $response_json->id;
        } catch (Exception $e) {
            Log::info('Error Assistant: ', $e->getMessage());
            return false;
        }
    }    

    /**
     * Create a thread for the assistant.
     * @param string $assistant_id The ID of the assistant.
     * @param string $file_id The ID of the file.
     * @return string|false The ID of the thread, or false if an error occurred.
     */
    public function createThread($assistant_id, $file_id)
    {
        try {
            $client = new Client;
            $response = $client->post('https://api.openai.com/v1/threads', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Read the content of a {$this->file_extension} file and extract the text. If the {$this->file_extension} file contains images, use optical character recognition to read the content of the images and extract the text. If the {$this->file_extension} file contains encrypted text, decode the encrypted text using base64 decryption. Otherwise, if the {$this->file_extension} file contains readable text, extract the text. After extracting the text, analyze it to solve the problem mentioned. Format the output concisely with only the solution, using the least number of words.",
                            'attachments' => [
                                [
                                    'file_id' => $file_id,
                                    'tools' => [
                                        ['type' => 'code_interpreter']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Thread created: ', (array) $response_json);
            return $response_json->id;
        } catch (Exception $e) {
            Log::info('Error Thread (createThread): ', $e->getMessage());
            return false;
        }
    }

    /**
     * Add a message to the thread.
     * @param string $thread_id The ID of the thread.
     * @param string $message The message to add.
     * @return string|false The ID of the message, or false if an error occurred.
     */
    public function addMessageToThread($thread_id, $message)
    {
        try {
            $client = new Client;
            $response = $client->post('https://api.openai.com/v1/threads/' . $thread_id . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'role' => 'user',
                    'content' => $message
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Message added to thread: ', (array) $response_json);
            return $response_json->id;
        } catch (Exception $e) {
            Log::info('Error Message (addMessageToThread): ', $e->getMessage());
            return false;
        }
    }

    /**
     * Run the assistant on the thread.
     * @param string $thread_id The ID of the thread.
     * @param string $assistant_id The ID of the assistant.
     * @return string|false The ID of the run, or false if an error occurred.
     */
    public function runAssistant($thread_id, $assistant_id)
    {
        try {
            $client = new Client;
            $response = $client->post('https://api.openai.com/v1/threads/' . $thread_id . '/runs', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'assistant_id' => $assistant_id
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Assistant run: ', (array) $response_json);
            return $response_json->id;
        } catch (Exception $e) {
            Log::info('Error Assistant (runAssistant): ', $e->getMessage());
            return false;
        }
    }
   
    /**
     * Check the status of the assistant run.
     * @param string $thread_id The ID of the thread.
     * @param string $run_id The ID of the run.
     * @return object|false The status of the run, or false if an error occurred.
     */
    public function checkRunStatus($thread_id, $run_id)
    {
        try {
            $client = new Client;
            $response = $client->get('https://api.openai.com/v1/threads/' . $thread_id . '/runs/' . $run_id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Run status (checkRunStatus): ', (array) $response_json);
            return $response_json;
        } catch (Exception $e) {
            Log::info('Error Run (checkRunStatus): ', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the response from the assistant.
     * @param string $thread_id The ID of the thread.
     * @return object|false The response from the assistant, or false if an error occurred.
     */
    public function getAssistantResponse($thread_id)
    {
        try {
            $client = new Client;
            $response = $client->get('https://api.openai.com/v1/threads/' . $thread_id . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Run status (getAssistantResponse): ', (array) $response_json);
            return $response_json;
        } catch (Exception $e) {
            Log::info('Error Assistant (getAssistantResponse): ', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload a file to the OpenAI API.
     * @param string $filePath The path of the file to upload.
     * @param string $purpose The purpose of the file (default is 'assistants').
     * @return string|false The ID of the uploaded file, or false if an error occurred.
     */
    public function uploadFile($filePath, $purpose = 'assistants')
    {
        try {
            if (!is_string($filePath)) {
                throw new Exception("Invalid file path: must be a string.");
            }
    
            $client = new Client;
            $response = $client->post('https://api.openai.com/v1/files', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                    [
                        'name' => 'purpose',
                        'contents' => $purpose,
                    ]
                ],
            ]);
    
            $response_json = json_decode($response->getBody());
            Log::info('Uploaded file response: ', (array) $response_json);

            return $response_json->id;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * List all files in the OpenAI storage.
     * @return array|false An array of files, or false if an error occurred.
     */
    public function listAllFiles()
    {
        try {
            $client = new Client;
            $response = $client->get('https://api.openai.com/v1/files', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                ]
            ]);

            $response_json = json_decode($response->getBody());
            Log::info('Files list: ', (array) $response_json->data);

            return $response_json->data; // Assuming the response contains a 'data' field with the list of files.
        } catch (Exception $e) {
            Log::error('Error listing files: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file in the OpenAI storage.
     * @param string $file_id The ID of the file to delete.
     * @return bool True if the file was successfully deleted, false otherwise.
     */
    public function deleteFile($file_id)
    {
        try {
            $client = new Client;
            $response = $client->delete('https://api.openai.com/v1/files/' . $file_id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                Log::info("File {$file_id} deleted successfully.");
                return true;
            } else {
                Log::warning("Failed to delete file {$file_id}");
                return false;
            }
        } catch (Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Query the OpenAI API for chat completions.
     * @param string $prompt The prompt to send to the API.
     * @return string|false The response from the API, or false if an error occurred.
     */
    public function queryChatCompletion($prompt)
    {
        try {
            $client = new Client;

            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->api_key,
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ],
            ]);

            $response_json = json_decode($response->getBody());

            foreach ($response_json->choices as $choice) {
                return $choice->message->content;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
