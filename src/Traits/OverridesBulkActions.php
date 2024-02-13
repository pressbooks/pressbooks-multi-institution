<?php

namespace PressbooksMultiInstitution\Traits;

use PressbooksMultiInstitution\Models\Institution;

trait OverridesBulkActions
{
    public function get_bulk_actions(): array
    {
        return Institution::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('Unassigned', 'pressbooks-multi-institution'), 0)
            ->toArray();
    }

    /**
     * Displays the bulk action dropdown.
     * This has been overridden to customize the dropdown.
     *
     * @since 3.1.0
     *
     * @param string $which The location of the bulk actions: 'top' or 'bottom'.
     *                      This is designated as optional for backward compatibility.
     * @return void
     */
    protected function bulk_actions($which = ''): void
    {
        if (is_null($this->_actions)) {
            $this->_actions = $this->get_bulk_actions();

            $two = '';
        } else {
            $two = '2';
        }

        if (empty($this->_actions)) {
            return;
        }

        echo app('Blade')->render('PressbooksMultiInstitution::table.bulk-actions', [
            'actions' => $this->get_bulk_actions(),
            'two' => $two,
            'which' => $which,
        ]);
    }
}
