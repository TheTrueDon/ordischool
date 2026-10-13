<?php
declare(strict_types=1);

namespace OrdiSchool\Planning;

final class TimetableConflictDetector
{
    /** @param list<array<string, mixed>> $lessons */
    public static function detect(array $lessons): array
    {
        $conflicts = [];
        $count = count($lessons);
        for ($i = 0; $i < $count; $i++) {
            $left = $lessons[$i];
            for ($j = $i + 1; $j < $count; $j++) {
                $right = $lessons[$j];
                if ((int) $left['weekday'] !== (int) $right['weekday']) {
                    continue;
                }
                $leftStart = (int) $left['start_period'];
                $rightStart = (int) $right['start_period'];
                $leftEnd = $leftStart + (int) $left['duration_periods'] - 1;
                $rightEnd = $rightStart + (int) $right['duration_periods'] - 1;
                if ($leftStart > $rightEnd || $rightStart > $leftEnd) {
                    continue;
                }
                $sameClass = (int) $left['class_id'] === (int) $right['class_id'];
                $sameTeacher = (int) $left['teacher_user_id'] === (int) $right['teacher_user_id'];
                $leftRoom = isset($left['room_id']) && $left['room_id'] !== '' ? (int) $left['room_id'] : null;
                $rightRoom = isset($right['room_id']) && $right['room_id'] !== '' ? (int) $right['room_id'] : null;
                $sameRoom = $leftRoom !== null && $rightRoom !== null && $leftRoom === $rightRoom;
                if ($sameClass || $sameTeacher || $sameRoom) {
                    $conflicts[] = [
                        'left' => $left,
                        'right' => $right,
                        'class_conflict' => $sameClass,
                        'teacher_conflict' => $sameTeacher,
                        'room_conflict' => $sameRoom,
                    ];
                }
            }
        }
        return $conflicts;
    }
}
