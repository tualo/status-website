with preset as (
    select 
        {workflow_id} workflow_id,
        {region_id} region_id,
        30 buckets,
        {start_timestamp} start_datetime,
        {stop_timestamp} stop_datetime
), datas as (
select 
    ROW_NUMBER() OVER (ORDER BY timestamp) AS row_num,
    status_website_workflow_logger.*,
    ntile(preset.buckets) over (order by timestamp) as slice
from status_website_workflow_logger join preset
where 
    status_website_workflow_logger.workflow_id = preset.workflow_id
    and status_website_workflow_logger.timestamp between  preset.start_datetime  and  preset.stop_datetime
    and status_website_workflow_logger.region_id =preset.region_id
    order by timestamp
)
select 
    slice,
    round(avg(datas.microseconds)) durchschnitt ,
    group_concat(distinct(datas.status)) codes,
    concat(min(row_num),' - ', max(row_num)) zeilen,
    concat(min(timestamp),'  -  ' ,max(timestamp)) from_to from datas
    group by datas.slice
