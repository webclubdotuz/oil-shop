<?php

namespace App\Console\Commands;

use App\Models\Adjustment;
use App\Models\AdjustmentDetail;
use App\Models\Product;
use App\Models\product_warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Generate2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $adjustment_details = AdjustmentDetail::all();

        foreach ($adjustment_details as $adjustment_detail) {
            $product_warehouse = product_warehouse::where('product_id', '=', $adjustment_detail->product_id)
                ->where('warehouse_id', '=', $adjustment_detail->adjustment->warehouse_id)
                ->first();


            if ($product_warehouse) {
                echo $product_warehouse->product->name . " " . $product_warehouse->qte . PHP_EOL;
                $product_warehouse->qte += $adjustment_detail->quantity;
                $product_warehouse->save();
            }

            echo $adjustment_detail->quantity . PHP_EOL;
        }
    }
}
