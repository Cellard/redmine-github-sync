<?php

namespace App\Services;

use App\Issue;
use App\Label;

class IssueLabelsMapper
{
    public function getLabelByExtId(int $extId, $serverId, ?string $type = 'label')
    {
        return Label::where([
            'ext_id' => $extId,
            'server_id' => $serverId,
            'type' => $type
        ])->first();
    }

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