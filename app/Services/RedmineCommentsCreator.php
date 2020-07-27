<?php

namespace App\Services;

use App\Label;

class RedmineCommentsCreator
{
    const AVAILABLE_ATTRS = [
        'status_id' => [
            'label_type' => 'status',
            'name' => 'Статус'
        ],
        'priority_id' => [
            'label_type' => 'priority',
            'name' => 'Приоритет'
        ],
        'tracker_id' => [
            'label_type' => 'tracker',
            'name' => 'Трекер'
        ],
    ];

    /**
     * Возвращает строку "сымитированного" комментария (созданного из истории изменения статусов, трекеров и пр.)
     *
     * @param array $details
     * @param integer $serverId
     * @return string
     */
    public function createFromJournalDetails(array $details, int $serverId)
    {
        $notes = [];
        foreach ($details as $item) {
            if ( $item['property'] === 'attr' && key_exists($item['name'], self::AVAILABLE_ATTRS) ) {
                $attr = self::AVAILABLE_ATTRS[$item['name']];
                $label = Label::where([
                    'type' => $attr['label_type'],
                    'ext_id' => $item['new_value'],
                    'server_id' => $serverId
                ])->first();
                $notes[] = 'Параметр ' . $attr['name'] . ' изменился на *' . $label['name'] . "*\n";
            }
        }
        return implode($notes);
    }
}