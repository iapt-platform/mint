
-- 表：sent_sim_index
CREATE TABLE sent_sim_indexs
(
    id SERIAL PRIMARY KEY,
	sent_id INTEGER,
	count INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX sent_sim_indexs_sent_id ON sent_sim_indexs (sent_id);
