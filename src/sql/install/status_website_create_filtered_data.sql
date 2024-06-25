
CREATE OR REPLACE PROCEDURE `status_website_create_filtered_data`(
    IN workflow_id INT,
    IN region_id INT,
    IN start_date DATETIME,
    IN end_date DATETIME,
    IN grouping_deep varchar(255)
)
BEGIN
    DECLARE grouping_deep_sql longtext;

    if grouping_deep = 'minute' then
        SET grouping_deep_sql = ', year(timestamp), month(timestamp), day(timestamp), hour(timestamp), minute(timestamp)';
    elseif grouping_deep = 'hour' then
        SET grouping_deep_sql = ', year(timestamp), month(timestamp), day(timestamp), hour(timestamp)';
    elseif grouping_deep = 'day' then
        SET grouping_deep_sql = ', year(timestamp), month(timestamp), day(timestamp)';
    elseif grouping_deep = 'month' then
        SET grouping_deep_sql = ', year(timestamp), month(timestamp)';
    elseif grouping_deep = 'year' then
        SET grouping_deep_sql = ', year(timestamp)';
    else
        SET grouping_deep_sql = '';
    end if;


    SET @sql = CONCAT('
        CREATE OR REPLACE TEMPORARY TABLE status_website_workflow_logger_filtered as
        select 
            workflow_id,
            region_id,
            year(timestamp) year,
            month(timestamp) month,
            day(timestamp) day,
            hour(timestamp) hour,
            minute(timestamp) minute,
            min(timestamp) min_timestamp,
            max(timestamp) max_timestamp,
            avg(microseconds) microseconds,
            min(status_code) min_status_code,
            max(status_code) max_status_code,
            count(*) samples
        from 
            status_website_workflow_logger 
        where 
            workflow_id = ', workflow_id, ' 
            ', if(region_id is null,'',concat('and region_id = ', region_id, '')),'
            and timestamp between ', quote(start_date), ' and ', quote(end_date), '
        group by 
            workflow_id,
            region_id
            ', grouping_deep_sql, '
    ');

    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

END