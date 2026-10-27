<?php

namespace App\Console\Commands;

use App\Models\Adjustment;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-excel
                            {file : Excel fayl yo\'li}
                            {--markup=30 : Sotish narxiga ustama (%)}
                            {--warehouse=1 : Ombor ID}
                            {--category=Автозапчасти : Kategoriya nomi}
                            {--dry-run : Bazaga yozmasdan faqat tekshirish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Реестр Excel faylidan mahsulotlarni import qilish (cost = Excel narxi, price = cost + ustama)';

    /**
     * Excel'dagi o'lchov birligi => [name, ShortName]
     */
    protected $units = [
        'шт'  => ['Штук', 'шт'],
        'кг'  => ['Килограмм', 'кг'],
        'м'   => ['Метр', 'м'],
        'к-т' => ['Комплект', 'к-т'],
    ];

    public function handle()
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("Fayl topilmadi: $file");
            return 1;
        }

        $markup = (float) $this->option('markup');
        $warehouse_id = (int) $this->option('warehouse');
        $items = $this->readRows($file);

        if (!$items) {
            $this->error('Faylda mahsulot topilmadi');
            return 1;
        }

        $existing = DB::table('products')
            ->whereNull('deleted_at')
            ->whereIn('code', array_keys($items))
            ->pluck('code')
            ->toArray();

        foreach ($existing as $code) {
            $this->warn("O'tkazib yuborildi (kod mavjud): $code {$items[$code]['name']}");
            unset($items[$code]);
        }

        $unknown_units = array_diff(array_unique(array_column($items, 'unit')), array_keys($this->units));
        if ($unknown_units) {
            $this->error("Noma'lum o'lchov birligi: " . implode(', ', $unknown_units));
            return 1;
        }

        $total_cost = 0;
        foreach ($items as $code => $item) {
            $items[$code]['price'] = round($item['cost'] * (1 + $markup / 100), 2);
            $total_cost += $item['cost'] * $item['quantity'];
        }

        $this->table(
            ['Kod', 'Nomi', 'Birlik', 'Miqdor', 'Cost $', 'Price $'],
            array_map(function ($code, $item) {
                return [$code, mb_strimwidth($item['name'], 0, 60, '…'), $item['unit'], $item['quantity'], $item['cost'], $item['price']];
            }, array_keys(array_slice($items, 0, 10, true)), array_slice($items, 0, 10, true))
        );
        $this->info('Mahsulotlar: ' . count($items) . ', jami miqdor: ' . array_sum(array_column($items, 'quantity'))
            . ', jami tannarx: $' . round($total_cost, 2) . ", ustama: $markup%");

        if ($this->option('dry-run')) {
            $this->comment('Dry run — bazaga hech narsa yozilmadi');
            return 0;
        }

        if (!$items) {
            return 0;
        }

        DB::transaction(function () use ($items, $warehouse_id, $file) {
            $category = Category::whereNull('deleted_at')->firstOrCreate(
                ['name' => $this->option('category')],
                ['code' => str_pad(Category::count() + 1, 3, '0', STR_PAD_LEFT)]
            );

            $unit_ids = [];
            foreach (array_unique(array_column($items, 'unit')) as $short) {
                [$name, $short_name] = $this->units[$short];
                $unit_ids[$short] = Unit::whereNull('deleted_at')->firstOrCreate(
                    ['ShortName' => $short_name],
                    ['name' => $name, 'base_unit' => null, 'operator' => '*', 'operator_value' => 1]
                )->id;
            }

            $now = now();
            $adjustment = Adjustment::create([
                'user_id' => 1,
                'warehouse_id' => $warehouse_id,
                'Ref' => $this->getNumberOrder(),
                'date' => $now,
                'items' => count($items),
                'notes' => 'Import: ' . basename($file),
            ]);

            foreach ($items as $code => $item) {
                $product = new Product;
                $product->type = 'is_single';
                $product->code = $code;
                $product->Type_barcode = 'CODE128';
                $product->name = $item['name'];
                $product->category_id = $category->id;
                $product->cost = $item['cost'];
                $product->price = $item['price'];
                $product->unit_id = $unit_ids[$item['unit']];
                $product->unit_sale_id = $unit_ids[$item['unit']];
                $product->unit_purchase_id = $unit_ids[$item['unit']];
                $product->TaxNet = 0;
                $product->tax_method = '1';
                $product->image = 'no_image.png';
                $product->save();

                DB::table('product_warehouse')->insert([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse_id,
                    'qte' => $item['quantity'],
                    'manage_stock' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('adjustment_details')->insert([
                    'product_id' => $product->id,
                    'adjustment_id' => $adjustment->id,
                    'quantity' => $item['quantity'],
                    'type' => 'add',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->info("Import qilindi: " . count($items) . " ta mahsulot, Adjustment {$adjustment->Ref}");
        });

        return 0;
    }

    /**
     * Реестр qatorlarini o'qiydi: № | Код папки | Код | Товар номи | Улч. бирл. | Микд | Сотиш нархи $ | Сумма.
     * Bir nechta bo'limdagi bir xil kodli mahsulotlar miqdori qo'shiladi.
     */
    protected function readRows($file)
    {
        $rows = IOFactory::load($file)->getActiveSheet()->toArray(null, true, false, false);
        $items = [];

        foreach ($rows as $row) {
            [$num, , $code, $name, $unit, $qty, $cost] = array_pad($row, 8, null);
            if (!is_numeric($num) || !$code || !is_numeric($qty) || !is_numeric($cost)) {
                continue;
            }

            $code = trim($code);
            if (isset($items[$code])) {
                if ((float) $items[$code]['cost'] !== (float) $cost) {
                    $this->warn("Kod $code turli narxda: {$items[$code]['cost']} va $cost — birinchisi olindi");
                }
                $items[$code]['quantity'] += $qty;
                continue;
            }

            $items[$code] = [
                'name' => trim(preg_replace('/\s+/u', ' ', $name)),
                'unit' => trim($unit),
                'quantity' => $qty,
                'cost' => (float) $cost,
            ];
        }

        return $items;
    }

    protected function getNumberOrder()
    {
        $last = DB::table('adjustments')->latest('id')->first();

        if ($last) {
            $nwMsg = explode('_', $last->Ref);
            return $nwMsg[0] . '_' . ($nwMsg[1] + 1);
        }

        return 'AD_1111';
    }
}
