<?php

namespace SleepingOwl\Admin\Form\Element;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\FormElement;
use SleepingOwl\Admin\Contracts\FormElementInterface;

class Columns extends FormElement
{
    /**
     * @var Collection
     */
    protected $columns;

    public function __construct()
    {
        $this->columns = new Collection();
    }

    public function initialize()
    {
        parent::initialize();

        $this->applyCallbackToItems(function (FormElementInterface $item) {
            $item->initialize();
        });
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Collection $columns
     *
     * @return $this
     */
    public function setColumns(Collection $columns)
    {
        $this->columns = collect($columns);

        return $this;
    }

    public function addColumn(Closure $callback)
    {
        $this->columns->push(
            new Collection($callback())
        );

        return $this;
    }

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        parent::setModel($model);

        $this->applyCallbackToItems(function (FormElementInterface $item) use ($model) {
            $item->setModel($model);
        });

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'columns' => $this->getColumns(),
        ];
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        $rules = parent::getValidationRules();

        foreach ($this->getColumns() as $columnItems) {
            foreach ($columnItems as $item) {
                if ($item instanceof FormElementInterface) {
                    $rules += $item->getValidationRules();
                }
            }
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function getValidationMessages()
    {
        $messages = [];

        foreach ($this->getColumns() as $columnItems) {
            foreach ($columnItems as $item) {
                if ($item instanceof NamedFormElement) {
                    $messages += $item->getValidationMessages();
                }
            }
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getValidationLabels()
    {
        $labels = [];

        foreach ($this->getColumns() as $columnItems) {
            foreach ($columnItems as $item) {
                if ($item instanceof NamedFormElement) {
                    $labels += $item->getValidationLabels();
                }
            }
        }

        return $labels;
    }

    public function save()
    {
        parent::save();

        $this->applyCallbackToItems(function (FormElementInterface $item) {
            $item->save();
        });
    }

    /**
     * @param Closure $callback
     */
    protected function applyCallbackToItems(Closure $callback)
    {
        foreach ($this->getColumns() as $columnItems) {
            foreach ($columnItems as $item) {
                if ($item instanceof FormElementInterface) {
                    call_user_func($callback, $item);
                }
            }
        }
    }
}
