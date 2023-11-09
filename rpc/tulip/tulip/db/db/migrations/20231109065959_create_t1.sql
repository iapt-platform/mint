-- migrate:up
create table t1(id integer);

-- migrate:down
drop table t1;

