<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FakeStoreApiService;

class SyncFakeStoreCommand extends Command
{
    protected $signature = 'fakestore:sync';
    protected $description = 'Synchronize products with FakeStore API';

    public function __construct(private FakeStoreApiService $fakeStoreService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting synchronization with FakeStore API...');
        
        try {
            $result = $this->fakeStoreService->syncProducts();
            
            if ($result['success']) {
                $this->info('✅ ' . $result['message']);
                $this->table(
                    ['Total', 'Created', 'Updated'],
                    [[$result['data']['total'], $result['data']['created'], $result['data']['updated']]]
                );
                return 0;
            } else {
                $this->error('❌ ' . $result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            return 1;
        }
    }
}
