<?php

namespace App\Filament\Resources\BoningResource\Pages;

use App\Filament\Resources\BoningResource;
use App\Models\Boning;
use App\Models\BoningItem;
use App\Models\BeefStock;
use App\Models\BeefStockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Grade;
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
use Filament\Notifications\Notification;
use Carbon\Carbon;

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
            'warehouse_id' => 1,
            'grade_id' => 1,
            'ph_level' => session('last_ph_' . $this->record->id),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('warehouse_id')
                            ->hiddenLabel()
                            ->placeholder('Warehouse')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->required()
                            ->extraAttributes(['tabindex' => '-1'])
                            ->extraInputAttributes(['tabindex' => '-1']),

                        Forms\Components\Select::make('product_id')
                            ->hiddenLabel()
                            ->placeholder('Product')
                            ->options(Product::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->autofocus()
                            ->extraAttributes(['class' => 'product-select-container']),

                        Forms\Components\Select::make('grade_id')
                            ->hiddenLabel()
                            ->placeholder('Grade')
                            ->options(Grade::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->extraAttributes(['tabindex' => '-1'])
                            ->extraInputAttributes(['tabindex' => '-1']),

                        Forms\Components\DatePicker::make('pack_date')
                            ->hiddenLabel()
                            ->placeholder('Pack Date')
                            ->required()
                            ->extraAttributes(['tabindex' => '-1'])
                            ->extraInputAttributes(['tabindex' => '-1']),

                        Forms\Components\DatePicker::make('exp_date')
                            ->hiddenLabel()
                            ->placeholder('Exp Date')
                            ->extraAttributes(['tabindex' => '-1'])
                            ->extraInputAttributes(['tabindex' => '-1']),

                        Forms\Components\Grid::make(2)->schema([

                            Forms\Components\TextInput::make('qty_pcs_combined')
                                ->hiddenLabel()
                                ->placeholder('Weight/Pcs (e.g. 22.5/8)')
                                ->required()
                                ->extraInputAttributes([
                                    'id' => 'qty_input_field',
                                    'class' => 'text-2xl font-black text-center text-primary-600',
                                    'oninput' => "this.value = this.value.replace(/,/g, '.');",
                                    'onkeydown' => "if(event.key === 'Enter') { event.preventDefault(); document.getElementById('submit_btn_label').click(); }"
                                ]),

                            Forms\Components\TextInput::make('ph_level')
                                ->hiddenLabel()
                                ->numeric()
                                ->step(0.1)
                                ->minValue(5.4)
                                ->maxValue(5.7)
                                ->placeholder('PH (5.4 - 5.7)')
                                ->extraInputAttributes([
                                    'onkeydown' => "if(event.key === ','){ event.preventDefault(); this.value = this.value + '.'; } else if(event.key === 'Enter'){ event.preventDefault(); document.getElementById('submit_btn_label').click(); }"
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
            ->paginated([10, 25, 50, 'all'])
            ->columns([
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->weight('bold')
                    ->size('sm')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->size('sm')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary')
                    ->url(fn($record) => route('print.label', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Qty')
                    ->size('sm')
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', '')),

                Tables\Columns\TextColumn::make('grade_id')
                    ->label('Grade')
                    ->formatStateUsing(fn($state) => in_array($state, [1, 3]) ? 'C' : 'F')
                    ->badge()
                    ->color(fn($state) => in_array($state, [1, 3]) ? 'info' : 'danger'),

                Tables\Columns\TextColumn::make('qty_pcs')
                    ->label('Pcs')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Author')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->time('H:i:s')
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Filter Product')
                    ->options(Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Filter Grade')
                    ->options(Grade::where('is_active', true)->pluck('name', 'id'))
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Hapus Data')
                    ->requiresConfirmation()
                    ->action(function (BoningItem $record) {

                        DB::transaction(function () use ($record) {
                            BeefStockMovement::create([
                                'product_id' => $record->product_id,
                                'warehouse_id' => $record->warehouse_id,
                                'condition' => $record->grade_id,
                                'barcode' => $record->barcode,
                                'transaction_type' => 'VOID_BONING',
                                'reference_document' => $record->boning->doc_no ?? 'DELETED',
                                'weight_in' => -$record->weight,
                                'pcs_in' => -$record->qty_pcs,
                                'created_by' => Auth::id(),
                            ]);

                            BeefStock::where('barcode', $record->barcode)->delete();

                            $record->delete();
                        });

                        Notification::make()
                            ->title('Data dibatalkan dan dihapus dari stok!')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function create(): void
    {
        $formData = $this->form->getState();

        // Simpan pH ke session biar gak hilang pas refresh
        if (isset($formData['ph_level'])) {
            session(['last_ph_' . $this->record->id => $formData['ph_level']]);
        }

        $combinedInput = $formData['qty_pcs_combined'];
        $parts = explode('/', $combinedInput);

        $weight = (float) trim($parts[0]);
        $pcs = isset($parts[1]) && trim($parts[1]) !== '' ? (int) trim($parts[1]) : 1;

        try {
            $insertedItem = DB::transaction(function () use ($formData, $weight, $pcs) {

                // ==========================================
                // RACIKAN BARCODE 25 DIGIT (GS1 STYLE)
                // ==========================================

                $origin = '1';
                $dateStr = Carbon::parse($formData['pack_date'])->format('dmy');

                $product = Product::find($formData['product_id']);
                $productCode = $product ? $product->code : '000000';

                $gradeId = $formData['grade_id'];
                $weightStr = str_pad(round($weight * 100), 4, '0', STR_PAD_LEFT);
                $pcsStr = str_pad($pcs, 2, '0', STR_PAD_LEFT);
                $phStr = isset($formData['ph_level']) ? str_pad(round($formData['ph_level'] * 10), 2, '0', STR_PAD_LEFT) : '00';

                // COUNTER HARIAN DENGAN WITH_TRASHED (Termasuk yang di-soft delete)
                $prefix = $origin . $dateStr;
                $latestItem = BoningItem::withTrashed()
                    ->where('barcode', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $counter = 1;
                if ($latestItem && strlen($latestItem->barcode) === 25) {
                    $lastCounter = (int) substr($latestItem->barcode, -3);
                    $counter = $lastCounter + 1;
                }
                $counterStr = str_pad($counter, 3, '0', STR_PAD_LEFT);

                $barcode = $origin . $dateStr . $productCode . $gradeId . $weightStr . $pcsStr . $phStr . $counterStr;

                // ==========================================

                $item = BoningItem::create([
                    'boning_id' => $this->record->id,
                    'product_id' => $formData['product_id'],
                    'warehouse_id' => $formData['warehouse_id'],
                    'grade_id' => $formData['grade_id'],
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
                    'grade_id' => $formData['grade_id'],
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
                    'condition' => $formData['grade_id'],
                    'barcode' => $barcode,
                    'transaction_type' => 'IN_BONING',
                    'reference_document' => $this->record->doc_no,
                    'weight_in' => $weight,
                    'pcs_in' => $pcs,
                    'created_by' => Auth::id(),
                ]);

                return $item;
            });

            Notification::make()
                ->title('Successfully Added')
                ->success()
                ->send();

            $this->form->fill([
                'warehouse_id' => $formData['warehouse_id'],
                'product_id' => $formData['product_id'],
                'grade_id' => $formData['grade_id'],
                'pack_date' => $formData['pack_date'],
                'exp_date' => $formData['exp_date'] ?? null,
                'ph_level' => $formData['ph_level'] ?? null,
                'qty_pcs_combined' => null,
            ]);

            $this->dispatch('refreshTable');

            if ($insertedItem) {
                $printUrl = route('print.label', ['id' => $insertedItem->id]);
                $this->dispatch('auto-print', url: $printUrl);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Masuk Database!')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
