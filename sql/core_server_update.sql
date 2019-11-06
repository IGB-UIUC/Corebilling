# Run this file to update the database for the core server update
alter table `groups`
    add column netid varchar(255);

create table `user_groups`
(
    `user_id`  int(10) unsigned not null,
    `group_id` int(10) unsigned not null,
    primary key (`user_id`, `group_id`)
) engine = InnoDB
  default charset = latin1;
insert into user_groups (user_id, group_id) select id as user_id, group_id from users;

alter table `users` drop column group_id;