LOAD DATA INFILE 'C:/ProgramData/MySQL/MySQL Server 8.0/Uploads/shifts.csv'
INTO TABLE shift
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
-- Assign CSV columns to variables (for the times) and standard columns
(student_id, day_of_week, shift_id, @raw_start, @raw_end, location)
SET 
    -- Clean and convert Start Time
    start_time = CASE 
        WHEN TRIM(@raw_start) = 'Noon' THEN '12:00:00'
        ELSE STR_TO_DATE(REPLACE(@raw_start, '.', ''), '%l:%i %p')
    END,
    
    -- Clean and convert End Time
    end_time = CASE 
        WHEN TRIM(@raw_end) = 'Noon' THEN '12:00:00'
        ELSE STR_TO_DATE(REPLACE(@raw_end, '.', ''), '%l:%i %p')
    END;