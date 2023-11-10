<?php

namespace App\Jobs;

use App\Repositories\Contracts\ValueChainRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class CreateDefaultValueChains implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $shop,
        public ?Carbon $date = null,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $storeId = Arr::get($this->shop, 'store_id');

        if (! $storeId) {
            return;
        }

        /** @var \App\Repositories\Contracts\ValueChainRepository */
        $valueChainRepository = app(ValueChainRepository::class);
        $fromDate = Carbon::create(Arr::get($this->shop, 'contract_date'));
        $toDate = $this->date ?? now();
        $dateRange = $valueChainRepository->getDateTimeRange($fromDate, $toDate, ['format' => 'Y-m-d']);

        foreach ($dateRange as $yearMonthDay) {
            $valueChainRepository->handleCreateDefault($storeId, ['current_date' => $yearMonthDay]);
        }
    }
}