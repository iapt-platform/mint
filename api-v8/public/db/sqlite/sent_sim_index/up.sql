
-- 表：sent_sim_index
CREATE TABLE sent_sim_index 
(
    id SERIAL PRIMARY KEY,
	sent_id INTEGER,
	count INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

