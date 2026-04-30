DELETE s1 FROM shift s1
INNER JOIN shift s2
WHERE s1.shift_id > s2.shift_id
AND s1.student_id = s2.student_id
AND s1.day_of_week = s2.day_of_week
AND s1.start_time = s2.start_time;