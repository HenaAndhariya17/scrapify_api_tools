📦 Scrapify ApiTools

A Laravel package for **API requests**, **web scraping**, and **dynamic data exporting** using `PhpSpreadsheet`.

---

## 📥 Installation

Install via Composer:

```bash
composer require scrapify-dev/api-tools
```

The service provider is auto-discovered by Laravel.
If you need to register manually, add to `config/app.php`:

```php
'providers' => [
    Scrapify\ApiTools\ApiToolsServiceProvider::class,
],
```

---

## ⚙️ Requirements

* **PHP:** `>=8.0`
* **Laravel:** `^9.0` | `^10.0` | `^11.0` | `^12.0`
* **Dependencies:**

  * `phpoffice/phpspreadsheet:^4.5`
  * `symfony/dom-crawler:^6.0`
  * `league/html-to-markdown:^4.0`
  * `illuminate/support` *(as per Laravel version)*

---

## 📚 Features

* **ApiService** → Make robust HTTP API calls with authentication & custom headers.
* **ApiScrapeService** → Scrape HTML, Markdown, specific elements, or full-page screenshots.
* **DynamicExport** → Export data dynamically to Excel or other formats.

---

## 1️⃣ ApiService

Make HTTP requests to external APIs.

### **Usage**

```php
use Scrapify\ApiTools\ApiService;

$apiService = new ApiService();

try {
    $response = $apiService->callApi(
        'https://api.example.com/data', // Endpoint
        'GET',                          // Method
        ['param1' => 'value1'],          // Payload
        'Bearer',                        // Auth type
        ['token' => 'your_auth_token'],  // Auth data
        ['X-Custom-Header' => 'MyValue'] // Headers
    );

    $data = $response->json();
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

---

## 2️⃣ ApiScrapeService

Scrape websites & extract data.

### **Supported output types**

* `html` → Raw HTML
* `markdown` → HTML to Markdown
* `screenshot` → Full-page screenshot
* `specific` → Extract specific data (links, emails, images, phones, metadata, headings)

### **Usage**

```php
use Scrapify\ApiTools\ApiScrapeService;

$scraper = new ApiScrapeService();

// Extract links & emails
$result = $scraper->scrape(
    'https://example.com',
    'specific',
    ['link', 'email']
);

// Screenshot
$screenshot = $scraper->scrape(
    'https://example.com',
    'screenshot'
);
```

---

## 3️⃣ DynamicExport

Prepare and export tabular data.

### **Usage**

```php
use Scrapify\ApiTools\Exports\DynamicExport;

$rows = [
    ['John Doe', 'john@example.com'],
    ['Jane Smith', 'jane@example.com'],
];

$headings = ['Name', 'Email'];

$export = new DynamicExport($rows, $headings);

// Get arrays
$headingsArray = $export->getHeadings();
$rowsArray = $export->getRows();
```

---

## 📊 Example: Export to Excel

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Scrapify\ApiTools\Exports\DynamicExport;

// Prepare export
$export = new DynamicExport($rows, $headings);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Headings
foreach ($export->getHeadings() as $col => $heading) {
    $colLetter = Coordinate::stringFromColumnIndex($col + 1);
    $sheet->setCellValue($colLetter . '1', $heading);
}

// Rows
foreach ($export->getRows() as $rowIndex => $row) {
    foreach ($row as $colIndex => $value) {
        $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($colLetter . ($rowIndex + 2), $value);
    }
}

// Save file
$writer = new Xlsx($spreadsheet);
$writer->save('exported_data.xlsx');
```
