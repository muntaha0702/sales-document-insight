<x-app-layout>
    <x-slot name="header">
        <!-- Removed the Dashboard title -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Upload CSV File</h1>
        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{ route('csv.upload.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" accept=".csv" class="mb-4 p-2 border rounded">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
        </form>
        <a href="{{ route('csv.visualize') }}" class="mt-4 inline-block text-blue-500">View Visualizations</a>
    </div>

    <a href="{{ route('see.data')}}">See whole data</a>
</body>
</html>
</x-app-layout>