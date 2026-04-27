ALTER TABLE tutor_course 
MODIFY COLUMN student_id VARCHAR(50);

INSERT INTO tutor_course (student_id, course_code)
SELECT student_id, 'CMSC 201' 
FROM tutors;