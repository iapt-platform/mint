
-- 表：sent_sim
CREATE TABLE sent_sim 
(
    id SERIAL PRIMARY KEY,
	sent1 INTEGER, 
	sent2 INTEGER, 
	sim REAL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 索引：sent
CREATE INDEX sent_sim_sent1 ON sent_sim (sent1);

