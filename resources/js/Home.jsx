import React, { useState, useRef } from 'react';
import Swal from 'sweetalert2';
import ReactMarkdown from 'react-markdown';
import rehypeRaw from 'rehype-raw';

function Home() {
    const [message, setMessage] = useState("");
    const [charCount, setCharCount] = useState(0);
    const [file, setFile] = useState(null);
    const [fileLabel, setFileLabel] = useState("Choose a file");
    const [isLoading, setIsLoading] = useState(false);
    const [result, setResult] = useState("");

    const textareaRef = useRef(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        const minLength = textareaRef.current.minLength;
        const maxLength = textareaRef.current.maxLength;

        if (charCount < minLength || charCount > maxLength) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `This field must contain between ${minLength} and ${maxLength} characters.`,
            });
            return;
        }
        if (!file) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a file.',
            });
            return;
        }

        setIsLoading(true);

        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while the document is being processed.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('message', message);
        formData.append('file', file);

        try {
            const response = await fetch('/submit-message', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok) {
                setResult(data.answer);
                Swal.close();
                //setMessage("");
                //setFile(null);
                //setFileLabel("Choose a file");
                //setCharCount(0);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred when sending the message.',
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred when sending the message.',
            });
        } finally {
            setIsLoading(false);
        }
    };

    const handleChange = (e) => {
        setMessage(e.target.value);
        setCharCount(e.target.value.length);
        adjustTextareaHeight(e.target);
    };

    const handleFileChange = (e) => {
        const selectedFile = e.target.files[0];
        if (selectedFile && selectedFile.size > 3 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'The file size must be less than 3MB.',
            });
            setFile(null);
            setFileLabel("Choose a file");
        } else {
            setFile(selectedFile);
            setFileLabel(selectedFile ? selectedFile.name : "Choose a file");
        }
    };

    const adjustTextareaHeight = (textarea) => {
        textarea.style.height = 'auto';
        textarea.style.height = `${textarea.scrollHeight}px`;
    };

    const minLength = textareaRef.current?.minLength || 25;
    const maxLength = textareaRef.current?.maxLength || 300;

    return (
        <div className="container mx-auto flex items-center justify-center min-h-screen relative">
            <div className="absolute top-0 z-[-2] h-screen w-screen bg-white bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.3),rgba(255,255,255,0))]"></div>
            <div className="text-center w-full max-w-2xl">
                <h1 className="text-2xl font-bold mb-4 text-gray-800">SEND FILE AS ATTACHMENT AND ASK QUESTIONS</h1>
                <form onSubmit={handleSubmit}>
                    <div className="flex flex-col items-start w-full mb-4">
                        <textarea
                            ref={textareaRef}
                            className="border p-2 w-full overflow-hidden resize-none"
                            placeholder="Ask questions"
                            value={message}
                            onChange={handleChange}
                            maxLength={maxLength}
                            minLength={minLength}
                            rows={1}
                            disabled={isLoading}
                        ></textarea>
                        <div className={`text-left text-sm ${charCount <= 0 ? '' : (charCount >= minLength ? 'text-green-500' : 'text-red-500')}`}>
                            {charCount}/{maxLength} characters {minLength > 0 && `(min: ${minLength})`}
                        </div>
                    </div>
                    <div className="flex flex-col items-start w-full mb-4">
                        <input
                            type="file"
                            id="file"
                            accept=".pdf,.txt,.docx"
                            onChange={handleFileChange}
                            className="hidden"
                            disabled={isLoading}
                        />
                        <label
                            htmlFor="file"
                            className={`border p-2 w-full text-gray-700 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer ${isLoading ? 'cursor-not-allowed' : ''}`}
                        >
                            {fileLabel}
                        </label>
                        <div className="text-left text-sm text-gray-700">Accepted file types: .pdf, .txt, .docx (max size: 3MB)</div>
                    </div>
                    <button type="submit" className="bg-blue-500 text-white px-4 py-2 mt-2 rounded-full shadow-lg hover:bg-blue-700" disabled={isLoading}>
                        Send
                    </button>
                </form>
                {result && (
                    <div className="mt-8 p-4 border rounded-lg bg-gray-100 text-left">
                        <h2 className="text-2xl font-bold mb-4">Result</h2>
                        <ReactMarkdown rehypePlugins={[rehypeRaw]}>{result}</ReactMarkdown>
                    </div>
                )}
                <p className="text-gray-700 mt-16 mb-12">The program uses <strong>php 8.2, Laravel 10.48 and React 18.3</strong> to send the file as an attachment<br />and the message to the <strong>OpenAI API (gpt-4-turbo)</strong> to get the response.</p>
                <h3 class="font-semibold">
                    <a href="https://github.com/puretalent-biz/ai-pdf-analyzer?tab=readme-ov-file" target="_blank" className="text-blue-500 underline">GitHub repository: OpenAI File Assistant with Laravel and React</a>
                </h3>
                <h3 class="font-semibold">
                    <a href="https://medium.com/@r.alexandre/laravel-10-react-18-openai-envoyer-un-fichier-pdf-et-poser-votre-question-b910e4e819cd" target="_blank" className="text-blue-500 underline">See the tutorial on Medium for more information.</a>
                </h3>
            </div>
        </div>
    );
}

export default Home;