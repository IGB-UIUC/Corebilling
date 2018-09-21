-- Run these sql commands to refactor the access_control table
delete from access_control where resource_type_id=2;
delete from access_control where participant_type_id=0;
alter table access_control drop column resource_type_id;
alter table access_control drop column participant_type_id;
alter table access_control drop column `id`;
delete from access_control where permission=0;
alter table access_control drop column permission;
alter table access_control rename column `participant_id` to `user_id`;
alter table access_control rename column `resource_id` to `device_id`;
alter table access_control add primary key (user_id,device_id);
drop table pages;