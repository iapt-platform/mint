-- migrate:up
create table t2(id integer);

-- migrate:down
drop table t2;

