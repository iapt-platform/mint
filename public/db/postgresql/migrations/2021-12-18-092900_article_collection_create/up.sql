CREATE TABLE article_collection 
(
	id SERIAL PRIMARY KEY, 
	collect_id VARCHAR (36) NOT NULL, 
	article_id VARCHAR (36) NOT NULL, 
	level INTEGER, 
	title VARCHAR (128), 
	children INTEGER DEFAULT (0),
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX article_collection_collect_id ON article_collection (collect_id);
CREATE INDEX article_collection_article_id ON article_collection (article_id);
CREATE INDEX article_collection_order ON article_collection (order);