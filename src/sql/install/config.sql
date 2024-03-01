create or replace view view_status_website_workflow_config as

select 

    concat('workflow_',region_name,'_',workflow_id) `key`,
    json_object(
        'workflow',workflow,
        'workflow_id',workflow_id,
        'region_name',region_name,
        'region_id',region_id,
        'url',url,
        'steps', JSON_ARRAYAGG(
            step
            order by step_position
        )
    ) cnf

from (
select

wf.id workflow_id,
wf.name workflow,
wf.url,
r.name region_name,
wfr.region_id region_id,
wfs.position step_position,

json_object(
    'step_id',wfs.step_id,
    'step_type',ifnull(wfs.step_type,'ping_html'),
    'method',wfs.method
) step


from 
status_website_workflows wf 
join status_website_workflow_workflow_regions wfr on wfr.workflow_id = wf.id
join status_website_workflow_regions r on wfr.region_id = r.id 
join status_website_workflow_steps wfs on  wf.id = wfs.workflow_id

) s

group by workflow_id,region_id