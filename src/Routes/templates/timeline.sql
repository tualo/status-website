with vorabfrage as (
  select
    timestamp,
    workflow_id,
    (
      if(
        status_code between 200
        and 399,
        1,
        0
      )
    ) s,
    LAST_VALUE(status_code) OVER (
      PARTITION BY workflow_id,
      region_id,
      year(timestamp),
      month(timestamp),
      day(timestamp),
      hour(timestamp)
      /*
       ,minute(timestamp)
       */
      ORDER BY
        timestamp ROWS BETWEEN CURRENT ROW
        and 1 FOLLOWING
    ) next_status,
    FIRST_VALUE(status_code) OVER (
      PARTITION BY workflow_id,
      region_id,
      year(timestamp),
      month(timestamp),
      day(timestamp),
      hour(timestamp)
      /*
       ,minute(timestamp)
       */
      ORDER BY
        timestamp ROWS BETWEEN 1 PRECEDING
        AND CURRENT ROW
    ) prev_status,
    LAST_VALUE(timestamp) OVER (
      PARTITION BY workflow_id,
      region_id,
      year(timestamp),
      month(timestamp),
      day(timestamp),
      hour(timestamp)
      /*
       ,minute(timestamp)
       */
      ORDER BY
        timestamp ROWS BETWEEN CURRENT ROW
        and 1 FOLLOWING
    ) next_ts,
    region_id,
    status_code,
    microseconds
  from
    status_website_workflow_logger
  where
    workflow_id = {workflow_id}
    and timestamp between {start_timestamp} and {stop_timestamp}
    and region_id = {region_id}
),
status_typ_frage as (
  select
    vorabfrage.*,
    if(
      status_code between 200
      and 399,
      'normal',
      if (
        next_status between 200
        and 399,
        if (
          prev_status between 200
          and 399,
          'ping-fehler',
          'ausfall'
        ),
        'ausfall'
      )
    ) status_typ
  from
    vorabfrage
),
status_typ_rank as (
  select
    DENSE_RANK() OVER (
      PARTITION BY status_typ
      ORDER BY
        timestamp
    ) rank,
    status_typ_frage.*
  from
    status_typ_frage
),
flanken as (
select
  status_typ_rank.*,
  if (rank=1,1,
  if (status_typ<>
  first_VALUE(status_typ) OVER (
      PARTITION BY 
        workflow_id,
        region_id
      ORDER BY
        timestamp  
      ROWS BETWEEN  1 PRECEDING and CURRENT ROW 
  ),1,0)) flanke
from
  status_typ_rank
order by
  timestamp
),
clustered_data as (
select
    flanken.*,
    @running_flanke := @running_flanke + flanken.flanke AS cluster_id
FROM   flanken
    JOIN (SELECT @running_flanke := 0) r
)

select 
    cluster_id,
    min(timestamp) start_timestamp,
    max(timestamp) stop_timestamp,
    status_typ,
    avg(microseconds) microseconds
from 
    clustered_data
group by cluster_id
    

