delimiter //

CREATE  TRIGGER `status_website_workflow_regions_bi_id`
    BEFORE INSERT
    ON `status_website_workflow_regions` FOR EACH ROW
BEGIN
    set new.id = (select ifnull(max(id),30000) + 1  from status_website_workflow_regions);
    
END //


CREATE  TRIGGER `status_website_workflows_bi_id`
    BEFORE INSERT
    ON `status_website_workflows` FOR EACH ROW
BEGIN
    set new.id = (select ifnull(max(id),60000) + 1 from status_website_workflows);
    
END //


CREATE  TRIGGER `status_website_workflow_steps_bi_id`
    BEFORE INSERT
    ON `status_website_workflow_steps` FOR EACH ROW
BEGIN
    set new.step_id = (select ifnull(max(step_id),160000) + 1 from status_website_workflow_steps);
    
END //
