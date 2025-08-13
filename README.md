Scrapify ApiTools Library
A Laravel package to handle external API requests, web scraping, and data exporting.

📦 Installation

This package can be installed via Composer.
composer require scrapify-dev/api-tools


Service Provider

The package's service provider will be automatically discovered by Laravel. If you need to register it manually, add the following to your config/app.php:
'providers' => [
    // ...
    Scrapify\ApiTools\ApiToolsServiceProvider::class,
],


⚙️ Core Components

This library consists of several core classes designed for specific tasks.

ApiService

This class is used for making robust calls to external APIs. It supports various HTTP methods and authentication types.
Usage
The callApi() method is the main entry point.
$apiService = new Scrapify\ApiTools\ApiService();

try {
    $response = $apiService->callApi(
        'https://api.example.com/data',
        'GET',
        ['param1' => 'value1'],
        'Bearer',
        ['token' => 'your_auth_token'],
        ['X-Custom-Header' => 'MyValue']
    );

    // Get the JSON response body
    $data = $response->json();
} catch (\Exception $e) {
    // Handle the API call error
    echo $e->getMessage();
}


ApiScrapeService

This class provides a simple way to scrape a URL and extract specific data in different formats (Markdown, HTML, specific fields, or a screenshot).
Usage
The scrape() method is the main entry point. It requires a URL, an output type, and optional specific options.
$scraper = new Scrapify\ApiTools\ApiScrapeService();

// Scrape links and emails from a page
$result = $scraper->scrape(
    'https://example.com',
    'specific',
    ['link', 'email']
);

// Get a screenshot of the page
$screenshot = $scraper->scrape(
    'https://example.com',
    'screenshot'
);


Supported outputType values:

markdown: Converts the page's HTML to Markdown.
screenshot: Captures a full-page screenshot and returns its URL.
specific: Extracts specific data points like link, email, image, phone, metadata, and heading.
html: Returns the raw HTML content of the page.
DynamicExport
This class is designed to handle dynamic data exports, particularly with the phpoffice/phpspreadsheet library. It structures data and headings for easy export.
Usage
The class is instantiated with arrays for rows and headings.
use Scrapify\ApiTools\Exports\DynamicExport;

$rows = [
    ['John Doe', 'john@example.com'],
    ['Jane Smith', 'jane@example.com'],
];
$headings = ['Name', 'Email'];

$export = new DynamicExport($rows, $headings);

// You can now use $export->getHeadings() and $export->getRows()
// with a library like PhpOffice\PhpSpreadsheet to create a file.


📊 Example: Exporting Data to Excel

To export data to an Excel file, you can combine the DynamicExport class with the phpoffice/phpspreadsheet library.
Note: The following example assumes you have phpoffice/phpspreadsheet installed and configured.
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Scrapify\ApiTools\Exports\DynamicExport;

// Assuming $rows and $headings have been prepared
$export = new DynamicExport($rows, $headings);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headings
foreach ($export->getHeadings() as $col => $heading) {
    $columnLetter = Coordinate::stringFromColumnIndex($col + 1);
    $sheet->getCell($columnLetter . '1')->setValue($heading);
}

// Set rows
foreach ($export->getRows() as $rowIndex => $row) {
    foreach ($row as $colIndex => $value) {
        $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->getCell($columnLetter . ($rowIndex + 2))->setValue($value);
    }
}

$writer = new Xlsx($spreadsheet);
$filename = 'exported_data.xlsx';
$tempFile = tempnam(sys_get_temp_dir(), $filename);
$writer->save($tempFile);

return response()->download($tempFile, $filename)->deleteFileAfterSend(true);


