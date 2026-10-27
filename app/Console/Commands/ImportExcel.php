<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
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
                            {--provider= : Ta\'minotchi nomi (berilmasa Excel\'dagi "Дукон" qatoridan olinadi)}
                            {--category=Автозапчасти : Yangi mahsulotlar kategoriyasi}
                            {--dry-run : Bazaga yozmasdan faqat tekshirish}
                            {--force : Shu реестр avval import qilingan bo\'lsa ham qayta qo\'shish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Реестр Excel faylidan покупка yaratish (cost = Excel narxi, price = cost + ustama)';

    /**
     * Excel'dagi o'lchov birligi => [name, ShortName]
     */
    protected $units = [
        'шт'  => ['Штук', 'шт'],
        'кг'  => ['Килограмм', 'кг'],
        'м'   => ['Метр', 'м'],
        'к-т' => ['Комплект', 'к-т'],
    ];

    protected $provider_name;
    protected $registers = [];

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
        $provider_name = $this->option('provider') ?: $this->provider_name;

        if (!$items) {
            $this->error('Faylda mahsulot topilmadi');
            return 1;
        }

        if (!$provider_name) {
            $this->error('Ta\'minotchi topilmadi, --provider bilan bering');
            return 1;
        }

        if (!DB::table('warehouses')->whereNull('deleted_at')->where('id', $warehouse_id)->exists()) {
            $this->error("Ombor topilmadi: $warehouse_id");
            return 1;
        }

        $unknown_units = array_diff(array_unique(array_column($items, 'unit')), array_keys($this->units));
        if ($unknown_units) {
            $this->error("Noma'lum o'lchov birligi: " . implode(', ', $unknown_units));
            return 1;
        }

        $notes = 'Реестр ' . implode(', ', $this->registers);
        $duplicate = DB::table('purchases')
            ->join('providers', 'providers.id', '=', 'purchases.provider_id')
            ->whereNull('purchases.deleted_at')
            ->where('providers.name', $provider_name)
            ->where('purchases.notes', $notes)
            ->value('purchases.Ref');

        if ($duplicate && !$this->option('force')) {
            $this->error("Bu реестр allaqachon import qilingan: $duplicate ($notes). Qayta qo'shish uchun --force");
            return 1;
        }

        $existing = Product::whereNull('deleted_at')->whereIn('code', array_keys($items))->get()->keyBy('code');
        foreach ($existing as $code => $product) {
            $this->warn("Mavjud mahsulot (narxi o'zgarmaydi, qoldig'i oshadi): $code {$product->name}");
        }

        $grand_total = 0;
        foreach ($items as $code => $item) {
            $items[$code]['price'] = round($item['cost'] * (1 + $markup / 100), 2);
            $grand_total += $item['cost'] * $item['quantity'];
        }
        $grand_total = round($grand_total, 2);

        $this->table(
            ['Kod', 'Nomi', 'Birlik', 'Miqdor', 'Cost $', 'Price $'],
            array_map(function ($code, $item) {
                return [$code, mb_strimwidth($item['name'], 0, 60, '…'), $item['unit'], $item['quantity'], $item['cost'], $item['price']];
            }, array_keys(array_slice($items, 0, 10, true)), array_slice($items, 0, 10, true))
        );
        $this->info("Ta'minotchi: $provider_name, ombor: $warehouse_id, ustama: $markup%");
        $this->info('Mahsulotlar: ' . count($items) . ' (yangi: ' . (count($items) - $existing->count()) . ', mavjud: ' . $existing->count() . ')'
            . ', jami miqdor: ' . array_sum(array_column($items, 'quantity')) . ", покупка summasi: \$$grand_total");

        if ($this->option('dry-run')) {
            $this->comment('Dry run — bazaga hech narsa yozilmadi');
            return 0;
        }

        DB::transaction(function () use ($items, $existing, $warehouse_id, $provider_name, $grand_total, $notes) {
            $provider = Provider::whereNull('deleted_at')->where('name', $provider_name)->first()
                ?: Provider::create([
                    'user_id' => 1,
                    'name' => $provider_name,
                    'code' => (DB::table('providers')->latest('id')->value('code') ?: 0) + 1,
                ]);

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
            $purchase = new Purchase;
            $purchase->date = $now;
            $purchase->Ref = 'PO-' . date('Ymd') . '-' . date('his');
            $purchase->provider_id = $provider->id;
            $purchase->warehouse_id = $warehouse_id;
            $purchase->GrandTotal = $grand_total;
            $purchase->tax_rate = 0;
            $purchase->TaxNet = 0;
            $purchase->discount = 0;
            $purchase->discount_type = 'fixed';
            $purchase->discount_percent_total = 0;
            $purchase->shipping = 0;
            $purchase->statut = 'received';
            $purchase->payment_statut = 'unpaid';
            $purchase->paid_amount = 0;
            $purchase->currency_rate = 1;
            $purchase->notes = $notes;
            $purchase->user_id = 1;
            $purchase->save();

            $details = [];
            foreach ($items as $code => $item) {
                $product = $existing->get($code);

                if (!$product) {
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
                }

                $product_warehouse = DB::table('product_warehouse')
                    ->whereNull('deleted_at')
                    ->where('warehouse_id', $warehouse_id)
                    ->where('product_id', $product->id)
                    ->whereNull('product_variant_id');

                if ($product_warehouse->exists()) {
                    $product_warehouse->increment('qte', $item['quantity'], ['updated_at' => $now]);
                } else {
                    DB::table('product_warehouse')->insert([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse_id,
                        'qte' => $item['quantity'],
                        'manage_stock' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $details[] = [
                    'date' => $now,
                    'currency_rate' => 1,
                    'purchase_id' => $purchase->id,
                    'quantity' => $item['quantity'],
                    'cost' => $item['cost'],
                    'purchase_unit_id' => $product->unit_purchase_id ?: $unit_ids[$item['unit']],
                    'TaxNet' => 0,
                    'tax_method' => '1',
                    'discount' => 0,
                    'discount_method' => '2',
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'total' => round($item['cost'] * $item['quantity'], 2),
                    'imei_number' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($details, 200) as $chunk) {
                DB::table('purchase_details')->insert($chunk);
            }

            $this->info("Покупка yaratildi: {$purchase->Ref}, " . count($details) . " ta mahsulot, \${$grand_total}");
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
        $this->registers = [];
        $this->provider_name = null;

        foreach ($rows as $row) {
            [$num, $title, $code, $name, $unit, $qty, $cost] = array_pad($row, 8, null);

            if (is_string($title) && preg_match('/Реестр\s*(№\s*\d+)/u', $title, $m)) {
                $this->registers[] = $m[1];
            }
            if (!$this->provider_name && is_string($title) && preg_match('/Дукон\s*:\s*(.+)/u', $title, $m)) {
                $this->provider_name = trim(preg_replace('/\s+/u', ' ', $m[1]));
            }

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
}
