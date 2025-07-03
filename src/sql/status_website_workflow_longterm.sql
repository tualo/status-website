CREATE TABLE `status_website_workflow_logger_longterm` (
  `workflow_id` int(10) unsigned NOT NULL,
  `step_id` int(10) unsigned NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `microseconds` int(10) unsigned NOT NULL,
  `status_code` int(11) NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `proto` int(10) unsigned NOT NULL,
  `contentlength` int(11) NOT NULL,
  `proto_major` smallint(6) NOT NULL,
  `proto_minor` smallint(6) NOT NULL,
  PRIMARY KEY (`workflow_id`,`step_id`,`region_id`,`timestamp`),
  KEY `idx_status_website_workflow_logger_longterm_workflow_id` (`workflow_id`)
) //


CREATE OR REPLACE PROCEDURE `proc_fix_null_open`()
BEGIN
    for rec in ( select id from blg_hdr_rechnung where offen is null and id>202300000 )
    do
        call recaluclateheader('rechnung',rec.id);
    end for;
END //



CREATE OR REPLACE PROCEDURE `proc_fix_null_kontoauszug`()
BEGIN
    for rec in ( select * from kontoauszuege_belege where id in (
select id from kontoauszuege where rechnungsnummer is null and id in (select id from kontoauszuege_belege)
) )
    do
        update kontoauszuege_belege set rechnungsnummer = rec.belegnummer where id = rec.id;
    end for;
END //

CREATE OR REPLACE PROCEDURE `proc_move_status_website_workflow_logger_longterm`()
BEGIN
    set @index=0;
    for record in (
        select * from status_website_workflow_logger 
        where  `timestamp` < now() + interval -2 month 
        order by `timestamp`
        limit 500000
    ) do
        set @index=@index+1;
        select @index, record.timestamp;
        start transaction ;
        insert ignore into status_website_workflow_logger_longterm
        (
            workflow_id,
            step_id,
            region_id,
            `timestamp`,
            microseconds,
            status_code,
            status,
            proto,
            contentlength,
            proto_major,
            proto_minor
        )
        select 
        workflow_id,
            step_id,
            region_id,
            `timestamp`,
            microseconds,
            status_code,
            status,
            proto,
            contentlength,
            proto_major,
            proto_minor

        from status_website_workflow_logger where workflow_id = record.workflow_id and region_id = record.region_id and `timestamp` = record.timestamp;
        delete from status_website_workflow_logger where workflow_id = record.workflow_id and region_id = record.region_id and `timestamp` = record.timestamp;
        commit;
    end for;
END //