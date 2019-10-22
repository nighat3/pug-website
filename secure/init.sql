-- TODO: Put ALL SQL in between `BEGIN TRANSACTION` and `COMMIT`
BEGIN TRANSACTION;

-- TODO: create tables

-- CREATE TABLE `examples` (
-- 	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
-- 	`name`	TEXT NOT NULL
-- );


-- Users Table
CREATE TABLE users(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	username TEXT NOT NULL UNIQUE,
	image_id FLOAT NOT NULL,
	password TEXT NOT NULL
);


CREATE TABLE sessions (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id INTEGER NOT NULL,
	session TEXT NOT NULL UNIQUE
);


CREATE TABLE images(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id FLOAT NOT NULL,
	file_name TEXT NOT NULL,
	file_ext TEXT NOT NULL,
	desc TEXT NOT NULL,
	citation TEXT
);


CREATE TABLE image_tags(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	image_id INTEGER NOT NULL,
	tag_id INTEGER
);


CREATE TABLE tags(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	name TEXT NOT NULL
);

-- TODO: initial seed data

-- TODO: FOR HASHED PASSWORDS, LEAVE A COMMENT WITH THE PLAIN TEXT PASSWORD!

INSERT INTO users (id, username, image_id,  password) VALUES (1, 'na295', 2, '$2y$10$Xpsfb5wADObAAhOvShqMRu8G82Y937BRLdI3n7fP4PtaPxERkyW5C'); -- password: pineapples

INSERT INTO users (id, username, image_id, password) VALUES (2, 'an1035', 1, '$2y$10$qH.dn2ordM06.MrF8cekRuxgoSg9EXabvI8mF67D44eQQQhn0UW6q'); -- password: cinnamon

-- Represents unique images. Images must be added to this table first and then updated in image_tags.
INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (1, 2, 'james.png', 'png', 'A baby pug on Instagram', 'https://www.instagram.com/p/Bnp9KqDClcx/');
INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (2, 2, 'peaches.png', 'png','Pug waiting for a Treat!', 'https://www.instagram.com/p/BnyrXNPnYMu/');
INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (3, 1, 'helmut.png', 'png', 'A pug by the poolside', 'https://www.instagram.com/p/Bj7JSVtHgu8/');
INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (4, 1, 'bed.png', 'png', 'Comfortable pug bed available on Amazon and Costco websites', 'https://www.amazon.com/JOYELF-Orthopedic-Removable-Washable-Squeaker/dp/B0734Y6KQS/ref=sr_1_7?keywords=dog+bed&qid=1554916345&s=gateway&sr=8-7');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (5, 1, 'james2.png', 'png', 'Cute pug', 'https://www.instagram.com/p/Bv06FbknJ-w/');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (6, 2, 'james3.png', 'png', 'new puppy', 'https://www.instagram.com/p/BtK1_8Vnqo3/');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (7, 1, 'james4.png', 'png', 'Breeder  Winning',  'https://www.instagram.com/p/Bu17YsUn2nj/');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (8, 2, 'james5.png', 'png', 'newborn puppy breeder', 'https://www.instagram.com/p/Bu1dnxGnMSK/');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (9, 1, 'james6.png', 'png', 'beggin strips','https://www.amazon.com/Purina-Beggin-Strips-Hickory-Flavor/dp/B004IN2PHG/ref=asc_df_B004IN2PHG/?tag=hyprod-20&linkCode=df0&hvadid=167152134657&hvpos=1o3&hvnetw=g&hvrand=12419540808327804105&hvpone=&hvptwo=&hvqmt=&hvdev=c&hvdvcmdl=&hvlocint=&hvlocphy=9005779&hvtargid=pla-299906014499&psc=1');

INSERT INTO images (id, user_id, file_name, file_ext, desc, citation) VALUES (10, 2, 'james7.png', 'png', 'lamb chewies for pugs','https://www.amazon.com/Sojos-Simply-Freeze-Treats-4-Ounce/dp/B00K4E3G62/ref=pd_day0_hl_0_5/134-3765261-2117400?_encoding=UTF8&pd_rd_i=B00K4E3G62&pd_rd_r=80825608-5bc9-11e9-a328-111adf3de4ca&pd_rd_w=SsF0S&pd_rd_wg=EinKk&pf_rd_p=ad07871c-e646-4161-82c7-5ed0d4c85b07&pf_rd_r=MYHNK22WJ1DDYARVP817&psc=1&refRID=MYHNK22WJ1DDYARVP817');




-- Tags and images not unique in this table. Multiple images can have multiple tags. image_id references images.Id and tag_id references tags.id.

-- This record has id of 1, corresponds to james.png and shows 'pug' tag.
INSERT INTO image_tags (id, image_id, tag_id) VALUES (1, 1, 1);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (2, 1, 2);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (3, 1, 3);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (4, 10, 4);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (5, 9, 4);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (6, 3, 2);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (7, 4, 3);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (8, 5, 1);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (9, 6, 5);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (10, 7, 5);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (11, 8, 1);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (12, 2, 4);
INSERT INTO image_tags (id, image_id, tag_id) VALUES (13, 2, 1);


-- Represent unique tags. Tags must be added to this table first and then image_tags has to be updated.
INSERT INTO tags (id, name) VALUES (1, 'pug');
INSERT INTO tags (id, name) VALUES (2, 'puggle');
INSERT INTO tags (id, name) VALUES (3, 'lifestyle');
INSERT INTO tags (id, name) VALUES (4, 'food');
INSERT INTO tags (id, name) VALUES (5, 'breeder');


COMMIT;
