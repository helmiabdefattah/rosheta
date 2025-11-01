<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Medicine;

class FetchAllApiResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:fetch-all-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all results from dwaprices.com API with intelligent recursive search';

    protected $apiUrl = 'https://dwaprices.com/routing.php';
    protected $headers = [
        'Cookie' => 'PHPSESSID=0fkeqr5t57v9k44qfskiab4u5f; _ga=GA1.1.706465259.1761605357; _ga_VEDDXTY9WX=GS2.1.s1761605357$o1$g1$t1761605992$j26$l0$h0'
    ];

    protected $seenIds = [];
    protected $searchCount = 0;
    protected $targetCount = 30104;
    protected $maxDepth = 5;
    protected $savedCount = 0;
    protected $itemsToSave = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting intelligent recursive search...');
        
        // Define all characters to search
        $chars = array_merge(
            range('a', 'z'),
            range(0, 9),
            ['ا', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي'],
            [' ', '.']
        );
        
        $this->info('Starting recursive search with base characters...');
        
        foreach ($chars as $char) {
            $this->searchRecursive((string)$char, 1);
            
            // Progress update every 10 searches
            if ($this->searchCount % 10 == 0) {
                $savedCount = Medicine::count();
                $remaining = $this->targetCount - $savedCount;
                $this->info("Search count: {$this->searchCount} | Saved: {$savedCount} | Remaining: {$remaining}");
            }
        }
        
        $savedCount = Medicine::count();
        $remaining = $this->targetCount - $savedCount;
        
        if ($remaining > 0) {
            $this->info("\nStill need to find {$remaining} more items.");
            $this->info("Trying empty search and ID-based searches...");
            
            // Try empty search
            $this->searchRecursive('', 1);
            
            // Try ID-based searches
            for ($id = 1; $id <= $this->targetCount; $id += 100) {
                $this->searchRecursive((string)$id, 1);
                if ($id % 5000 == 0) {
                    $savedCount = Medicine::count();
                    $remaining = $this->targetCount - $savedCount;
                    $this->info("Searched up to ID {$id}... Found {$savedCount} items (Remaining: {$remaining})");
                }
            }
        }
        
        // Save any remaining items in batch
        if (!empty($this->itemsToSave)) {
            $this->saveBatch();
        }
        
        $finalCount = Medicine::count();
        $this->info("\n✓ Search complete! Total items saved: {$finalCount}");
        
        if ($finalCount >= $this->targetCount) {
            $this->info('✓ Perfect! Found all target items!');
        } else {
            $this->warn("Found {$finalCount} items, target was {$this->targetCount}");
        }
        
        return Command::SUCCESS;
    }
    
    protected function searchRecursive($term, $depth = 1)
    {
        if ($depth > $this->maxDepth) {
            return;
        }
        
        $results = $this->searchApi($term);
        $items = $results['items'];
        $totalCount = $this->extractTotalCount($results['metadata']);
        
        // If we can't get count from metadata, use the returned items count
        if ($totalCount == 0 && count($items) > 0) {
            $totalCount = count($items);
        }
        
        $this->searchCount++;
        
        // Collect new items for batch saving
        $newItems = 0;
        foreach ($items as $item) {
            $itemId = $item['id'] ?? md5(json_encode($item));
            if (!isset($this->seenIds[$itemId])) {
                $this->seenIds[$itemId] = true;
                $this->itemsToSave[] = $item;
                $newItems++;
            }
        }
        
        // Batch save every 50 items
        if (count($this->itemsToSave) >= 50) {
            $this->saveBatch();
        }
        
        // Show progress for meaningful searches
        if ($newItems > 0 || $totalCount > 100) {
            $this->info("Search '{$term}': Metadata {$totalCount} | Returned " . count($items) . " | +{$newItems} new");
        }
        
        // If total count > 100, dig deeper with more characters
        if ($totalCount > 100 && $depth < $this->maxDepth) {
            $this->digDeeper($term, $depth);
        }
        
        // Reduced delay to 50ms for speed
        // usleep(50000);
    }
    
    protected function digDeeper($baseTerm, $depth)
    {
        // Reduce character set for faster digging (most common characters)
        $appendChars = ['a', 'e', 'i', 'o', 'u', 'b', 'c', 'd', 'f', 'g', 'h', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'w', 
                        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,'.'];
        
        foreach ($appendChars as $char) {
            $newTerm = $baseTerm . $char;
            $this->searchRecursive($newTerm, $depth + 1);
            
            // Check if we've reached target
            if ($this->savedCount >= $this->targetCount) {
                return;
            }
        }
    }
    
    protected function saveBatch()
    {
        if (empty($this->itemsToSave)) {
            return;
        }
        
        foreach ($this->itemsToSave as $item) {
            try {
                Medicine::updateOrCreate(
                    ['api_id' => $item['id'] ?? md5(json_encode($item))],
                    $this->getInsertData($item)
                );
            } catch (\Exception $e) {
                // Skip individual errors
            }
        }
        
        $this->savedCount += count($this->itemsToSave);
        $this->itemsToSave = [];
    }
    
    protected function extractTotalCount($metadata)
    {
        // If metadata is a direct number (like in the example response)
        if (is_numeric($metadata)) {
            return (int)$metadata;
        }
        
        // Try to find total count in metadata array
        if (is_array($metadata)) {
            // Look for common metadata fields at root level
            $possibleFields = [
                'total', 'total_count', 'count', 'totalResults', 'total_results', 
                'totalCount', 'results_total', 'totalItems', 'num_results',
                'total_results_count', 'totalCount', 'results_count'
            ];
            
            foreach ($possibleFields as $field) {
                if (isset($metadata[$field]) && is_numeric($metadata[$field])) {
                    return (int)$metadata[$field];
                }
            }
            
            // Try to find count in any key that contains 'total' or 'count'
            foreach ($metadata as $key => $value) {
                $keyLower = strtolower($key);
                if ((stripos($keyLower, 'total') !== false || stripos($keyLower, 'count') !== false || stripos($keyLower, 'num') !== false) && $keyLower !== 'data') {
                    if (is_numeric($value)) {
                        return (int)$value;
                    }
                }
            }
            
            // Check if metadata itself is an array with numeric keys (count might be in array elements)
            if (isset($metadata[0]) && is_array($metadata[0])) {
                // This is likely the data array, skip it
                return count($metadata);
            }
        }
        
        // Return 0 if we can't find count, which means we won't dig deeper
        return 0;
    }
    
    protected function searchApi($searchTerm)
    {
        try {
            $response = Http::asForm()->withHeaders($this->headers)->post($this->apiUrl, [
                'search' => '2',
                'searchq' => $searchTerm,
                'order_by' => 'id ASC',
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Try to extract items from different possible response structures
                $items = [];
                $metadata = null;
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $items = $data['data'];
                    // Extract metadata separately
                    if (isset($data['metadata'])) {
                        // Metadata is a single number in this API
                        $metadata = $data['metadata'];
                    }
                } elseif (isset($data['items']) && is_array($data['items'])) {
                    $items = $data['items'];
                    $metadata = [];
                    foreach ($data as $key => $value) {
                        if ($key !== 'items') {
                            $metadata[$key] = $value;
                        }
                    }
                } elseif (isset($data['results']) && is_array($data['results'])) {
                    $items = $data['results'];
                    $metadata = [];
                    foreach ($data as $key => $value) {
                        if ($key !== 'results') {
                            $metadata[$key] = $value;
                        }
                    }
                } elseif (is_array($data)) {
                    // If data is an array, check if first element is numeric (indexed array)
                    $keys = array_keys($data);
                    if (isset($keys[0]) && is_numeric($keys[0])) {
                        $items = $data;
                        $metadata = [];
                    } else {
                        // It's an associative array, use as both items and metadata
                        $items = $data;
                        $metadata = $data;
                    }
                }
                
                return [
                    'items' => is_array($items) ? $items : [],
                    'metadata' => $metadata !== null ? $metadata : 0,
                ];
            }
        } catch (\Exception $e) {
            // Silently continue on errors
        }
        
        return ['items' => [], 'metadata' => []];
    }
    
    protected function getInsertData($item)
    {
        return [
            'api_id' => $item['id'] ?? null,
            'name' => $item['name'] ?? null,
            'arabic' => $item['arabic'] ?? null,
            'shortage' => $item['shortage'] ?? null,
            'date_updated' => $item['Date_updated'] ?? null,
            'imported' => $item['imported'] ?? null,
            'percentage' => $item['percentage'] ?? null,
            'pharmacology' => $item['pharmacology'] ?? null,
            'route' => $item['route'] ?? null,
            'in_eye' => $item['inEye'] ?? false,
            'units' => $item['units'] ?? null,
            'small_unit' => $item['small_unit'] ?? null,
            'sold_times' => isset($item['sold_times']) ? (int)$item['sold_times'] : 0,
            'dosage_form' => $item['dosage_form'] ?? null,
            'barcode' => $item['barcode'] ?? null,
            'barcode2' => $item['barcode2'] ?? null,
            'qrcode' => $item['qrcode'] ?? null,
            'anyupdatedate' => $item['anyupdatedate'] ?? null,
            'new_added_date' => $item['new_added_date'] ?? null,
            'updtime' => $item['updtime'] ?? null,
            'fame' => isset($item['fame']) ? ($item['fame'] == '1' || $item['fame'] === '1') : false,
            'cosmo' => isset($item['cosmo']) ? ($item['cosmo'] == '1' || $item['cosmo'] === '1') : false,
            'dose' => $item['dose'] ?? null,
            'repeated' => isset($item['repeated']) ? ($item['repeated'] == '1' || $item['repeated'] === '1') : false,
            'price' => isset($item['price']) ? (float)$item['price'] : null,
            'oldprice' => isset($item['oldprice']) ? (float)$item['oldprice'] : null,
            'newprice' => isset($item['newprice']) ? (float)$item['newprice'] : null,
            'active_ingredient' => $item['active'] ?? null,
            'similars_id' => $item['similars_id'] ?? null,
            'img' => $item['img'] ?? null,
            'description' => $item['description'] ?? null,
            'uses' => $item['uses'] ?? null,
            'company' => $item['company'] ?? null,
            'reported' => isset($item['reported']) ? ($item['reported'] == '1' || $item['reported'] === '1') : false,
            'reporter_name' => $item['reporterName'] ?? null,
            'visits' => isset($item['visits']) ? (int)$item['visits'] : 0,
            'sim_visits' => isset($item['sim_visits']) ? (int)$item['sim_visits'] : 0,
            'ind_visits' => isset($item['ind_visits']) ? (int)$item['ind_visits'] : 0,
            'composition_visits' => isset($item['composition_visits']) ? (int)$item['composition_visits'] : 0,
            'order_visits' => isset($item['order_visits']) ? (int)$item['order_visits'] : 0,
            'app_visits' => isset($item['app_visits']) ? (int)$item['app_visits'] : 0,
            'shares' => isset($item['shares']) ? (int)$item['shares'] : 0,
            'last_visited' => $item['last_visited'] ?? null,
            'lastupdatingadmin' => $item['lastupdatingadmin'] ?? null,
            'awhat_visits' => isset($item['awhat_visits']) ? (int)$item['awhat_visits'] : 0,
            'report_msg' => $item['reportMSG'] ?? null,
            'found_pharmacies_ids' => $item['found_pharmacies_ids'] ?? null,
            'availability' => isset($item['availability']) ? (int)$item['availability'] : 0,
            'noimgid' => $item['noimgid'] ?? null,
            'raw_data' => $item,
            'batch_number' => 1,
        ];
    }
}
