-- Your SQL goes here

CREATE TABLE books 
(
	id SERIAL PRIMARY KEY,
	book INTEGER, 
	paragraph INTEGER, 
	title TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);