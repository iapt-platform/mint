CREATE TABLE user_operation_dailys 
(
	id SERIAL PRIMARY KEY,
	user_id INTEGER NOT NULL , 
	date_at date NOT NULL , 
	date_int BIGINT NOT NULL , 
	duration BIGINT NOT NULL , 
	hit INTEGER NOT NULL DEFAULT (1),
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX user_operation_dailys_user_id ON user_operation_dailys (user_id);
CREATE INDEX user_operation_dailys_date ON user_operation_dailys (date_at);
