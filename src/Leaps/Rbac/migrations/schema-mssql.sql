/**
 * Database schema required by \Leaps\Rbac\DbManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 2.0
 */

drop table [auth_assignment];
drop table [auth_item_child];
drop table [auth_item];
drop table [auth_rule];

create table [auth_rule]
(
    [name]  varchar(64) not null,
    [data]  text,
    [created_at]           integer,
    [updated_at]           integer,
    primary key ([name])
);

create table [auth_item]
(
   [name]                 varchar(64) not null,
   [type]                 integer not null,
   [description]          text,
   [rule_name]            varchar(64),
   [data]                 text,
   [created_at]           integer,
   [updated_at]           integer,
   primary key ([name]),
   foreign key ([rule_name]) references [auth_rule] ([name]) on delete set null on update cascade
);

create index [idx-auth_item-type] on [auth_item] ([type]);

create table [auth_item_child]
(
   [parent]               varchar(64) not null,
   [child]                varchar(64) not null,
   primary key ([parent],[child]),
   foreign key ([parent]) references [auth_item] ([name]) on delete cascade on update cascade,
   foreign key ([child]) references [auth_item] ([name]) on delete cascade on update cascade
);

create table [auth_assignment]
(
   [item_name]            varchar(64) not null,
   [user_id]              varchar(64) not null,
   [created_at]           integer,
   primary key ([item_name], [user_id]),
   foreign key ([item_name]) references [auth_item] ([name]) on delete cascade on update cascade
);
