<?php

namespace App\Services;

use App\Label;

/*property:"attr"
name:"status_id"
old_value:"10"
new_value:"2"
*/
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