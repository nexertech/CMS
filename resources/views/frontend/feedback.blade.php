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
                        <label class="block text-gray-700 text-sm font-bold mb-4 text-center">Overall Experience</label>
                        
                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 shadow-sm">
                            <div class="flex flex-row-reverse justify-center items-center gap-2 mb-4" id="star-rating">
                                <input type="radio" id="star5" name="overall_rating" value="excellent" class="sr-only" required />
                                <label for="star5" class="cursor-pointer transition-all duration-200 hover:scale-110 active:scale-90 text-gray-300" data-rating="excellent" data-text="Excellent">
                                    <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </label>

                                <input type="radio" id="star4" name="overall_rating" value="good" class="sr-only" />
                                <label for="star4" class="cursor-pointer transition-all duration-200 hover:scale-110 active:scale-90 text-gray-300" data-rating="good" data-text="Good">
                                    <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </label>

                                <input type="radio" id="star3" name="overall_rating" value="satisfied" class="sr-only" />
                                <label for="star3" class="cursor-pointer transition-all duration-200 hover:scale-110 active:scale-90 text-gray-300" data-rating="satisfied" data-text="Satisfied">
                                    <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </label>

                                <input type="radio" id="star2" name="overall_rating" value="fair" class="sr-only" />
                                <label for="star2" class="cursor-pointer transition-all duration-200 hover:scale-110 active:scale-90 text-gray-300" data-rating="fair" data-text="Fair">
                                    <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </label>

                                <input type="radio" id="star1" name="overall_rating" value="poor" class="sr-only" />
                                <label for="star1" class="cursor-pointer transition-all duration-200 hover:scale-110 active:scale-90 text-gray-300" data-rating="poor" data-text="Poor">
                                    <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                </label>
                            </div>
                            
                            <div id="rating-label" class="text-center font-bold text-lg text-gray-400 h-8 transition-all duration-300">
                                Tap to Rate
                            </div>
                        </div>

                        @error('overall_rating')
                            <p class="text-red-500 text-xs italic mt-2 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <style>
                        #star-rating label:hover,
                        #star-rating label:hover ~ label {
                            color: #fbbf24;
                        }
                        
                        #star-rating input:checked ~ label {
                            color: #f59e0b;
                        }

                        #star-rating .star-active {
                             color: #f59e0b;
                        }
                    </style>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const labels = document.querySelectorAll('#star-rating label');
                            const ratingText = document.getElementById('rating-label');
                            const inputs = document.querySelectorAll('#star-rating input');
                            
                            const colorMap = {
                                'excellent': 'text-green-600',
                                'good': 'text-blue-600',
                                'satisfied': 'text-sky-500',
                                'fair': 'text-yellow-600',
                                'poor': 'text-red-600'
                            };

                            labels.forEach(label => {
                                label.addEventListener('mouseenter', () => {
                                    if (!document.querySelector('#star-rating input:checked')) {
                                        ratingText.textContent = label.getAttribute('data-text');
                                        ratingText.className = 'text-center font-bold text-lg text-yellow-500';
                                    }
                                });

                                label.addEventListener('mouseleave', () => {
                                    const checkedInput = document.querySelector('#star-rating input:checked');
                                    if (checkedInput) {
                                        const checkedLabel = document.querySelector(`label[for="${checkedInput.id}"]`);
                                        updateLabel(checkedLabel);
                                    } else {
                                        ratingText.textContent = 'Tap to Rate';
                                        ratingText.className = 'text-center font-bold text-lg text-gray-400';
                                    }
                                });
                            });

                            inputs.forEach(input => {
                                input.addEventListener('change', () => {
                                    const label = document.querySelector(`label[for="${input.id}"]`);
                                    updateLabel(label);
                                });
                            });

                            function updateLabel(label) {
                                const text = label.getAttribute('data-text');
                                const rating = label.getAttribute('data-rating');
                                ratingText.textContent = text;
                                ratingText.className = `text-center font-bold text-lg ${colorMap[rating]}`;
                            }
                        });
                    </script>

                    <div class="mb-4">
                        <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Technician Remarks (Optional)</label>
                        <textarea id="remarks" name="remarks" rows="2"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"
                            placeholder="Enter technician remarks..."></textarea>
                        @error('remarks')
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