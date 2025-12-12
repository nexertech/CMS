<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Feedback - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Complaint Feedback</h1>
                <p class="mt-2 text-sm text-gray-600">Please rate your experience for Complaint #{{ $complaint->id }}
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(isset($already_submitted) && $already_submitted)
                <div class="text-center py-8">
                    <div class="text-5xl mb-4">✅</div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Feedback Received</h2>
                    <p class="text-gray-600">Thank you! Your feedback has already been recorded for this complaint.</p>
                </div>
            @elseif(!session('success'))
                <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">Complaint Details</h3>
                    <p class="text-sm text-blue-800"><span class="font-medium">Title:</span> {{ $complaint->title }}</p>
                    <p class="text-sm text-blue-800"><span class="font-medium">Category:</span>
                        {{ ucfirst($complaint->category) }}</p>
                    <p class="text-sm text-blue-800"><span class="font-medium">Assigned To:</span>
                        {{ $complaint->assignedEmployee->name ?? 'Unassigned' }}</p>
                </div>

                <form action="{{ route('frontend.feedback.submit', $complaint->id) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="submitted_by" class="block text-gray-700 text-sm font-bold mb-2">Your Name</label>
                        <input type="text" id="submitted_by" name="submitted_by" required
                            value="{{ old('submitted_by', $complaint->client->client_name ?? '') }}"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                            placeholder="Enter your name">
                        @error('submitted_by')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Overall Experience</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="overall_rating" value="excellent" class="peer sr-only" required>
                                <div
                                    class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition-all text-center">
                                    <div class="text-2xl mb-1">😃</div>
                                    <div class="text-sm font-medium text-gray-700">Excellent</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="overall_rating" value="good" class="peer sr-only">
                                <div
                                    class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-all text-center">
                                    <div class="text-2xl mb-1">🙂</div>
                                    <div class="text-sm font-medium text-gray-700">Good</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="overall_rating" value="average" class="peer sr-only">
                                <div
                                    class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition-all text-center">
                                    <div class="text-2xl mb-1">😐</div>
                                    <div class="text-sm font-medium text-gray-700">Average</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="overall_rating" value="poor" class="peer sr-only">
                                <div
                                    class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 transition-all text-center">
                                    <div class="text-2xl mb-1">😞</div>
                                    <div class="text-sm font-medium text-gray-700">Poor</div>
                                </div>
                            </label>
                        </div>
                        @error('overall_rating')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="comments" class="block text-gray-700 text-sm font-bold mb-2">Comments (Optional)</label>
                        <textarea id="comments" name="comments" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                            placeholder="Any additional feedback..."></textarea>
                        @error('comments')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-center">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                            Submit Feedback
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    <div class="text-center mt-8 text-gray-500 text-xs">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</body>

</html>