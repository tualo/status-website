delimiter ;

create table status_website_user (
    id int  primary key,
    username varchar(255) not null,
    password varchar(255) not null,
    email varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);
alter table status_website_user add status varchar(25) default '';
alter table status_website_user add pin varchar(25) default '';


CREATE TRIGGER `trigger_status_website_user_bi_generate_id`
    BEFORE INSERT
    ON `status_website_user` FOR EACH ROW
BEGIN
    SET NEW.id = (select ifnull(max(id),150000) +1 from status_website_user);
END //  


create table status_website_user_token (
    status_website_user_id int ,
    fingerprint varchar(36),
    token longtext not null,
    primary key(status_website_user_id,fingerprint),
    key `idx_status_website_user_token_fingerprint` (fingerprint),
    valid_until timestamp not null default current_timestamp,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp,

    constraint `fk_status_website_user_token_token` FOREIGN KEY (`status_website_user_id`) REFERENCES status_website_user(`id`) 
    on delete cascade
    on update cascade
);


create table status_website_plans (
    id int  primary key,
    name varchar(255) not null,
    position int not null default 0,
    price decimal(15,5) not null default 0.0,
    valid_from date not null default '2099-12-30',
    valid_until date not null default '2099-12-31',
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

create table status_website_plan_features (
    id int  primary key,
    plan_id int not null,
    position int not null default 0,
    name varchar(255) not null,
    description varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp,
    constraint `fk_status_website_plan_features_plan_id` FOREIGN KEY (`plan_id`) REFERENCES status_website_plans(`id`) 
    on delete cascade
    on update cascade
);

create table status_website_workflows (
    id int UNSIGNED primary key,
    name varchar(255) not null,
    description varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

alter table status_website_workflows add url varchar(255) default "";


create table status_website_workflow_steps (
    workflow_id int UNSIGNED not null,
    step_id int UNSIGNED not null,
    position int UNSIGNED not null,
    name varchar(255) not null,
    description varchar(255) not null,
    
    step_type varchar(255) not null,
    method varchar(255) not null,

    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

alter table status_website_workflow_steps add primary key (workflow_id, step_id);
create index `idx_status_website_workflow_steps_workflow` on status_website_workflow_steps(`workflow_id`);
alter table status_website_workflow_steps add 
constraint `fk_status_website_workflow_steps_workflow` FOREIGN KEY (`workflow_id`) REFERENCES status_website_workflows(`id`);


create table status_website_workflow_regions (
    id int UNSIGNED primary key,
    name varchar(255) not null,
    description varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);
alter table status_website_workflow_regions add continent_key varchar(30) default "undefined";

alter table status_website_workflows add apdex_goal int UNSIGNED not null default 800;
alter table status_website_workflows add sla_goal decimal(15,5) not null default 99.0;




create table status_website_workflow_workflow_regions (
    workflow_id int UNSIGNED,
    region_id int UNSIGNED,
    primary key (workflow_id, region_id),
    constraint `fk_status_website_workflow_workflow_regions_workflow` 
    FOREIGN KEY (`workflow_id`) REFERENCES status_website_workflows(`id`),
    constraint `fk_status_website_workflow_workflow_regions_region`
    FOREIGN KEY (`region_id`) REFERENCES status_website_workflow_regions(`id`)
);

create table status_website_workflow_protocol (
    id int UNSIGNED primary key,
    name varchar(255) not null,
    description varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);
create table status_website_workflow_status (
    id int UNSIGNED primary key,
    name varchar(255) not null,
    description varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);
insert into status_website_workflow_protocol (id, name, description) values (1, 'HTTP', 'Hypertext Transfer Protocol');
insert into status_website_workflow_protocol (id, name, description) values (2, 'HTTPS', 'Hypertext Transfer Protocol Secure');
insert into status_website_workflow_status (id, name, description) values (200, 'OK', 'Standard response for successful HTTP requests');
insert into status_website_workflow_status (id, name, description) values (201, 'Created', 'The request has been fulfilled, resulting in the creation of a new resource');
insert into status_website_workflow_status (id, name, description) values (202, 'Accepted', 'The request has been accepted for processing, but the processing has not been completed');
insert into status_website_workflow_status (id, name, description) values (203, 'Non-Authoritative Information', 'The server is a transforming proxy that received a 200 OK from its origin, but is returning a modified version of the origin''s response');
insert into status_website_workflow_status (id, name, description) values (204, 'No Content', 'The server successfully processed the request and is not returning any content');

insert into status_website_workflow_protocol (id, name, description) values (0, 'Unkown', 'Unkown');
insert into status_website_workflow_status (id, name, description) values (0, 'Unkown', 'Unkown');

create table status_website_workflow_logger (
    `workflow_id` int UNSIGNED not null,
    `step_id` int UNSIGNED not null,
    `region_id` int UNSIGNED not null,
    `timestamp` timestamp not null,
    primary key (`workflow_id`, `step_id`, `region_id`, `timestamp`),

    `microseconds` int UNSIGNED not null,
    `status_code` int not null,

    `status` int UNSIGNED not null,
    `proto` int UNSIGNED not null,

    `contentlength` int not null,
    `proto_major` smallint not null,
    `proto_minor` smallint not null,

    CONSTRAINT `fk_status_website_workflow_logger_workflow_id` FOREIGN KEY (`workflow_id`) REFERENCES status_website_workflows(`id`),
    CONSTRAINT `fk_status_website_workflow_logger_step_id` FOREIGN KEY (`step_id`) REFERENCES status_website_workflow_steps(`step_id`),
    CONSTRAINT `fk_status_website_workflow_logger_region_id` FOREIGN KEY (`region_id`) REFERENCES status_website_workflow_regions(`id`),
    CONSTRAINT `fk_status_website_workflow_protocol` FOREIGN KEY (`proto`) REFERENCES status_website_workflow_protocol(`id`),
    CONSTRAINT `fk_status_website_workflow_status` FOREIGN KEY (`status`) REFERENCES status_website_workflow_status(`id`)
);

create table status_website_workflow_logger_connection_state (
    id bigint primary key,
    workflow_logger_id bigint not null,
    version int not null,
    cipher_suite int not null,
    server_name varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp,

    FOREIGN KEY (workflow_logger_id) REFERENCES status_website_workflow_logger(id)
);

/*
create table status_website_user_role (
    id int auto_increment primary key,
    user_id int not null,
    role varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

create table status_website_user_session (
    id int auto_increment primary key,
    user_id int not null,
    session_id varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

create table status_website_user_session_role (
    id int auto_increment primary key,
    user_session_id int not null,
    role varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

create table status_website_user_session_token (
    id int auto_increment primary key,
    user_session_id int not null,
    token varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

create table status_website_user_session_token_role (
    id int auto_increment primary key,
    user_session_token_id int not null,
    role varchar(255) not null,
    created_at timestamp not null default current_timestamp,
    updated_at timestamp not null default current_timestamp on update current_timestamp
);

*/