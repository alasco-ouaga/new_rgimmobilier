<?php

namespace Botble\RealEstate\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\RealEstate\Enums\ConsultStatusEnum;
use Botble\RealEstate\Models\Consult;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;

class ConsultTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Consult::class)
            ->addActions([
                EditAction::make()->route('consult.edit'),
                DeleteAction::make()->route('consult.destroy'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('name', function (Consult $item) {
                if (! $this->hasPermission('consult.edit')) {
                    return BaseHelper::clean($item->name);
                }

                return Html::link(route('consult.edit', $item->id), BaseHelper::clean($item->name));
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'phone',
                'email',
                'created_at',
                'ip_address',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            IdColumn::make(),
            NameColumn::make(),
            Column::make('email')
                ->title(trans('core/base::tables.email'))
                ->alignLeft(),
            Column::make('phone')
                ->title(trans('plugins/real-estate::consult.phone')),
            Column::make('ip_address')
                ->title(trans('plugins/real-estate::consult.ip_address')),
            CreatedAtColumn::make(),
            StatusColumn::make(),
        ];
    }

    public function bulkActions(): array
    {
        return [
            DeleteBulkAction::make()->permission('consult.destroy'),
        ];
    }

    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title' => trans('core/base::tables.name'),
                'type' => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => ConsultStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', ConsultStatusEnum::values()),
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'datePicker',
            ],
        ];
    }
}
