-- Creator:       MySQL Workbench 8.0.12/ExportSQLite Plugin 0.1.0
-- Author:        Mazin
-- Caption:       New Model
-- Project:       Name of the project
-- Changed:       2018-12-19 22:02
-- Created:       2018-12-19 22:02
PRAGMA foreign_keys = OFF;

CREATE TABLE "country"(
  "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL CHECK("id">=0),
  "name" VARCHAR(20) NOT NULL,
  "details" TEXT NOT NULL
);

CREATE TABLE "user"(
  "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL CHECK("id">=0),
  "country_id" INTEGER NOT NULL CHECK("country_id">=0),
  "type" TEXT NOT NULL CHECK("type" IN('admin', 'author')),
  "name" VARCHAR(20) NOT NULL,
  CONSTRAINT "fk_user_country_id"
    FOREIGN KEY("country_id")
    REFERENCES "country"("id")
);
CREATE INDEX "user.country_id" ON "user" ("country_id");
CREATE TABLE "comment"(
  "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL CHECK("id">=0),
  "article_id" INTEGER NOT NULL CHECK("article_id">=0),
  "user_id" INTEGER NOT NULL CHECK("user_id">=0),
  "content" VARCHAR(100) NOT NULL,
  CONSTRAINT "fk_comment_article_id"
    FOREIGN KEY("article_id")
    REFERENCES "article"("id"),
  CONSTRAINT "fk_comment_user_id"
    FOREIGN KEY("user_id")
    REFERENCES "user"("id")
);
CREATE INDEX "comment.article_id" ON "comment" ("article_id");
CREATE INDEX "comment.user_id" ON "comment" ("user_id");
CREATE TABLE "article"(
  "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL CHECK("id">=0),
  "user_id" INTEGER NOT NULL CHECK("user_id">=0) DEFAULT '0',
  "published_at" DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  "title" VARCHAR(100) NOT NULL DEFAULT '',
  "content" TEXT NOT NULL,
  CONSTRAINT "fk_article_user_id"
    FOREIGN KEY("user_id")
    REFERENCES "user"("id")
);
CREATE INDEX "article.user_id" ON "article" ("user_id");

INSERT INTO `country` (`id`, `name`, `details`) VALUES
(1, 'Slovakia', '{"name": "Slovensko", "pop": 5456300, "gdp": 90.75}'),
(2, 'Canada', '{"name": "Canada", "pop": 37198400, "gdp": 1592.37}'),
(3, 'Germany', '{"name": "Deutschland", "pop": 82385700, "gdp": 3486.12}');

INSERT INTO `user` (`id`, `country_id`, `type`, `name`) VALUES
(1, 1, 'admin', 'Marek'),
(2, 1, 'author', 'Robert'),
(3, 2, 'admin', 'Chris'),
(4, 2, 'author', 'Kevin');

INSERT INTO `comment` (`id`, `article_id`, `user_id`, `content`) VALUES
(1, 1, 1, 'comment 1.1'),
(2, 1, 2, 'comment 1.2'),
(3, 2, 1, 'comment 2.1'),
(4, 5, 4, 'cömment 5.4'),
(5, 6, 2, 'ਟਿੱਪਣੀ 6.2');

INSERT INTO `article` (`id`, `user_id`, `published_at`, `title`, `content`) VALUES
(1, 1, '2011-12-10 12:10:00', 'article 1', 'content 1'),
(2, 2, '2011-12-20 16:20:00', 'article 2', 'content 2'),
(3, 1, '2012-01-04 22:00:00', 'article 3', 'content 3'),
(4, 4, '2018-07-07 15:15:07', 'artïcle 4', 'content 4'),
(5, 3, '2018-10-01 01:10:01', 'article 5', 'content 5'),
(6, 3, '2019-01-21 07:00:00', 'სარედაქციო 6', '함유량 6');

PRAGMA foreign_keys = ON;



