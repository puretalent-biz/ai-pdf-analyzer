<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\OpenAiService;


class AssistantController extends Controller
{
    /**
     * Display the assistant view.
     * @param Request $request The request object.
     * @return \Illuminate\Contracts\View\View
     */
    public function submitMessage(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'message' => 'required|string|min:5|max:300',
                'file' => 'required|file|mimetypes:text/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'messages' => $e->errors()
            ], 422);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getPathName();
            $fileExtension = $file->getClientOriginalExtension();
            $mimieType = $file->getMimeType();

            $openAiService = new OpenAiService('gpt-4-turbo', $fileExtension);
            $file_id = $openAiService->uploadFile($filePath);
            $answer = '';

            if ($file_id) {
                $assistant_id = $openAiService->createAssistant($file_id);
                $thread_id = $openAiService->createThread($assistant_id, $file_id);
                $openAiService->addMessageToThread($thread_id, $request->message);
                $run_id = $openAiService->runAssistant($thread_id, $assistant_id);

                do {
                    $status = $openAiService->checkRunStatus($thread_id, $run_id);
                    sleep(1);
                } while ($status->status == 'in_progress');

                $response = $openAiService->getAssistantResponse($thread_id);
                if (isset($response->data[0]->content[0]->text->value)) {
                    $extractedText = $response->data[0]->content[0]->text->value;
                    $answer = Str::markdown($extractedText);
                }
            }

            return response()->json([
                'success' => true, 
                'answer' => $answer
            ]);
        } else {
            return response()->json([
                'error' => true
            ]);
        }
    }
}
