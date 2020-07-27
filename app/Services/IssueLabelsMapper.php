<?php

namespace App\Services;

use App\Issue;
use App\Label;

class IssueLabelsMapper
{
    /**
     * Возврачает инстанс App\Label по ИД лейбла из редмайн и ИД сервера
     *
     * @param integer $extId
     * @param integer $serverId
     * @param string|null $type
     * @return Label|null
     */
    public function getLabelByExtId(int $extId, int $serverId, ?string $type = 'label')
    {
        return Label::where([
            'ext_id' => $extId,
            'server_id' => $serverId,
            'type' => $type
        ])->first();
    }

    /**
     * По типу лейбла возвращает внешний ИД сопоставленного в правилах Mirror лейбла
     *
     * @param Issue $issue
     * @param array $labelsMap
     * @param string|null $type
     * @return integer|null
     */
    public function getLabelExtId(Issue $issue, array $labelsMap, ?string $type = 'label')
    {
        $label = $issue->enumerations()->where('type', $type)->first();
        if ($label) {
            $labelId = $this->findIdInLabels($label->id, $labelsMap);
            if ($labelId) {
                return Label::where([
                    'id' => $labelId,
                    'type' => $type
                ])->first()->ext_id;
            }
        }
        return null;
    }

    /**
     * Возвращает локальный ИД (из БД) сопоставленного лейбла
     *
     * @param integer $id
     * @param array $labelsMap
     * @return integer|null
     */
    public function findIdInLabels(int $id, array $labelsMap)
    {
        foreach ($labelsMap as $item) {
            if ($item['left_label_id'] === $id) {
                return $item['right_label_id'];
            }
        }
        return null;
    }
}