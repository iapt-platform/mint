CREATE TABLE user_operation_frames 
(
	id SERIAL PRIMARY KEY, 
	user_id INTEGER NOT NULL , 
	duration BIGINT NOT NULL , 
	hit INTEGER NOT NULL DEFAULT (1), 
	timezone BIGINT NOT NULL DEFAULT (0),
	op_start BIGINT  NOT NULL , 
	op_end BIGINT  NOT NULL , 
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX user_operation_frames_user_id ON user_operation_frames (user_id);
