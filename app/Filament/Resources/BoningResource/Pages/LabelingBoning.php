<?php

namespace App\Filament\Resources\BoningResource\Pages;

use App\Filament\Resources\BoningResource;
use App\Models\Boning;
use App\Models\BoningItem;
use App\Models\BeefStock;
use App\Models\BeefStockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\MaxWidth;

class LabelingBoning extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $resource = BoningResource::class;
    protected static string $view = 'filament.resources.boning-resource.pages.labeling-boning';

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return '';
    }

    public Boning $record;
    public ?array $data = [];

    public function mount(Boning $record): void
    {
        $this->record = $record;
        $this->form->fill([
            'pack_date' => now()->format('Y-m-d'),
            'condition' => 'CHILL',
            'warehouse_id' => 1,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // 1. WAREHOUSE (Paling Atas)
                        Forms\Components\Select::make('warehouse_id')
                            ->hiddenLabel()
                            ->placeholder('Warehouse')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->required()
                            ->extraInputAttributes(['tabindex' => 4]),

                        // 2. PRODUCT
                        Forms\Components\Select::make('product_id')
                            ->hiddenLabel()
                            ->placeholder('Product')
                            ->options(Product::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->autofocus()
                            ->extraInputAttributes(['tabindex' => 1]),

                        // 3. GRADE
                        Forms\Components\Select::make('condition')
                            ->hiddenLabel()
                            ->placeholder('Grade')
                            ->options([
                                'CHILL' => 'CHILL',
                                'FROZEN' => 'FROZEN',
                                'GRADE A' => 'GRADE A'
                            ])
                            ->required()
                            ->extraInputAttributes(['tabindex' => 5]),

                        // 4. PACK DATE
                        Forms\Components\DatePicker::make('pack_date')
                            ->hiddenLabel()
                            ->placeholder('Pack Date')
                            ->required()
                            ->extraInputAttributes(['tabindex' => 6]),

                        // 5. EXP DATE (Bisa Kosong)
                        Forms\Components\DatePicker::make('exp_date')
                            ->hiddenLabel()
                            ->placeholder('Exp Date')
                            ->extraInputAttributes(['tabindex' => 7]),

                        // 6. QTY/PCS & PH LEVEL (Paling Bawah)
                        Forms\Components\Grid::make(2)->schema([

                            // Qty tetep text karena butuh masukin garis miring (/)
                            Forms\Components\TextInput::make('qty_pcs_combined')
                                ->hiddenLabel()
                                ->placeholder('Weight/Pcs (e.g. 22.5/8)')
                                ->required()
                                ->extraInputAttributes([
                                    'tabindex' => 2,
                                    'class' => 'text-2xl font-black text-center text-primary-600',
                                    'oninput' => "this.value = this.value.replace(/,/g, '.');"
                                ]),

                            // PH LEVEL kembali jadi NUMBER sejati dengan min/max HTML!
                            Forms\Components\TextInput::make('ph_level')
                                ->hiddenLabel()
                                ->numeric() // BALIK JADI NUMBER!
                                ->step(0.1)
                                ->minValue(5.4) // Ngunci batas bawah di browser
                                ->maxValue(5.7) // Ngunci batas atas di browser
                                ->placeholder('PH (5.4 - 5.7)')
                                ->extraInputAttributes([
                                    'tabindex' => 8,
                                    // SAKTI: Kalau user kepencet koma, langsung dipotong & diganti titik
                                    'onkeydown' => "if(event.key === ','){ event.preventDefault(); this.value = this.value + '.'; }"
                                ]),

                        ]),
                    ])->columns(1)
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BoningItem::query()->where('boning_id', $this->record->id))
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('barcode')->label('Barcode')->weight('bold'),
                Tables\Columns\TextColumn::make('product.name')->label('Product Name'),
                Tables\Columns\TextColumn::make('weight')->label('Qty')->suffix(' Kg'),
                Tables\Columns\TextColumn::make('qty_pcs')->label('Pcs'),
                Tables\Columns\TextColumn::make('created_at')->label('Time')->time('H:i:s'),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->icon('heroicon-o-printer')->color('success')
                    ->url(fn($record) => url("/print_labelboning.php?idlabelboning={$record->id}"))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public function create(): void
    {
        $formData = $this->form->getState();

        // LOGIC PECAH QTY DAN PCS (Pisahin pake garis miring '/')
        $combinedInput = $formData['qty_pcs_combined'];
        $parts = explode('/', $combinedInput);

        $weight = (float) trim($parts[0]);
        // Kalau user cuma masukin "22.5" (tanpa /), otomatis pcs dihitung 1
        $pcs = isset($parts[1]) && trim($parts[1]) !== '' ? (int) trim($parts[1]) : 1;

        DB::transaction(function () use ($formData, $weight, $pcs) {
            $barcode = 'LBL-' . $this->record->id . '-' . date('YmdHis') . rand(10, 99);

            BoningItem::create([
                'boning_id' => $this->record->id,
                'product_id' => $formData['product_id'],
                'warehouse_id' => $formData['warehouse_id'],
                'condition' => $formData['condition'],
                'weight' => $weight,
                'qty_pcs' => $pcs,
                'ph_level' => $formData['ph_level'] ?? null,
                'pack_date' => $formData['pack_date'],
                'exp_date' => $formData['exp_date'] ?? null,
                'barcode' => $barcode,
                'created_by' => Auth::id(),
            ]);

            BeefStock::create([
                'barcode' => $barcode,
                'product_id' => $formData['product_id'],
                'warehouse_id' => $formData['warehouse_id'],
                'condition' => $formData['condition'],
                'weight' => $weight,
                'qty_pcs' => $pcs,
                'ph_level' => $formData['ph_level'] ?? null,
                'pack_date' => $formData['pack_date'],
                'exp_date' => $formData['exp_date'] ?? null,
                'origin' => 'BONING',
                'status' => 'IN_STOCK',
            ]);

            BeefStockMovement::create([
                'product_id' => $formData['product_id'],
                'warehouse_id' => $formData['warehouse_id'],
                'barcode' => $barcode,
                'transaction_type' => 'IN_BONING',
                'reference_document' => $this->record->doc_no,
                'weight_in' => $weight,
                'pcs_in' => $pcs,
                'created_by' => Auth::id(),
            ]);
        });

        // EFEK SESSION-LIKE: Ingat semua pilihan SEBELUMNYA, KECUALI Kotak Qty
        $this->form->fill([
            'warehouse_id' => $formData['warehouse_id'],
            'product_id' => $formData['product_id'],
            'condition' => $formData['condition'],
            'pack_date' => $formData['pack_date'],
            'exp_date' => $formData['exp_date'] ?? null,
            'ph_level' => $formData['ph_level'] ?? null,

            // Cuma ini doang yang kita kosongin buat input barang berikutnya
            'qty_pcs_combined' => null,
        ]);

        $this->dispatch('refreshTable');
    }
}
