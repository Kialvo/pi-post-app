@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Website Scraper</h1>
        <form id="scraper-form" class="bg-white p-6 rounded-lg shadow-md w-full md:w-1/2">
            <label for="csv_file" class="block text-lg font-medium mb-2">Upload CSV File:</label>
            <input type="file" id="csv_file" class="block w-full border border-gray-300 rounded-lg p-2" accept=".csv" required />
            <button type="button" id="start-scraping" class="w-full bg-blue-500 text-white font-bold py-2 rounded hover:bg-blue-600 mt-4">
                Start Scraping
            </button>
        </form>

        <div class="bg-gray-800 text-white p-4 rounded-lg shadow-md mt-6 h-64 overflow-y-auto">
            <h2 class="text-lg font-bold mb-2">Scraper Results:</h2>
            <div id="results-content" class="space-y-2"></div>
        </div>
    </div>

    <script>
        const resultsContent = document.getElementById('results-content');
        const startButton = document.getElementById('start-scraping');
        const fileInput = document.getElementById('csv_file');

        startButton.addEventListener('click', function () {
            const file = fileInput.files[0];
            if (!file) {
                alert('Please upload a file first.');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', file);

            startButton.disabled = true;
            resultsContent.innerHTML = "Uploading file and starting scraping process...";

            fetch('{{ route('scraper.upload') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resultsContent.innerHTML = `<div class="text-red-500">${data.error}</div>`;
                    } else {
                        resultsContent.innerHTML = data.results.map(result => `<div>${result}</div>`).join('');
                    }
                })
                .catch(error => {
                    resultsContent.innerHTML = `<div class="text-red-500">Error: ${error.message}</div>`;
                })
                .finally(() => {
                    startButton.disabled = false;
                });
        });
    </script>


@endsection
