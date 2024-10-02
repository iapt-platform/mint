CREATE TABLE sent_historaies
(
    id SERIAL PRIMARY KEY,
	sent_uid VARCHAR (36), 
	user_uid VARCHAR (36), 
	content TEXT, 
	landmark VARCHAR (64),
	date BIGINT,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX sent_historaies_sent_uid ON sent_historaies (sent_uid);
