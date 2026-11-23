-- Air Asia HW4: exact original entity/attribute names and relationships.
-- From /workspaces/IS6465-codyschlawin/air-asia, migrate/seed safely with:
--   php airasia-hw2/database/setup.php
-- This CLI script preserves existing cards/accounts and migrates old FKs.
-- For a new empty database only, schema.sql and seed.sql can be imported:
--   cd airasia-hw2/database
--   sqlite3 rewards.db < schema.sql
--   sqlite3 rewards.db < seed.sql
-- Inspect from this database folder:
--   sqlite3 rewards.db
-- At sqlite>: .tables, .schema ACCOUNT, .schema USER, then SQL queries:
--   SELECT accountId, userId, accountType, points FROM ACCOUNT;
--   SELECT userId, userName, firstName, lastName, role FROM USER;
--   SELECT COUNT(*) FROM GIFTCARD;
--   .quit
-- Do not select or display password hashes as profile information.
PRAGMA foreign_keys = ON;
CREATE TABLE IF NOT EXISTS USER (
  userId INTEGER PRIMARY KEY AUTOINCREMENT,
  userName VARCHAR(100) NOT NULL COLLATE NOCASE UNIQUE
    CHECK (length(trim(userName)) BETWEEN 1 AND 100),
  password VARCHAR(100) NOT NULL CHECK (length(password) BETWEEN 60 AND 100),
  firstName VARCHAR(100) NOT NULL
    CHECK (length(trim(firstName)) BETWEEN 1 AND 100),
  lastName VARCHAR(100) NOT NULL
    CHECK (length(trim(lastName)) BETWEEN 1 AND 100),
  role VARCHAR(100) NOT NULL CHECK (role IN ('Admin', 'Customer'))
);
CREATE TABLE IF NOT EXISTS GIFTCARD (
  cardId INTEGER PRIMARY KEY AUTOINCREMENT,
  cardName VARCHAR(100) NOT NULL
    CHECK (length(trim(cardName)) BETWEEN 1 AND 100),
  cardType VARCHAR(100) NOT NULL
    CHECK (length(trim(cardType)) BETWEEN 1 AND 100),
  cardValue FLOAT(10) NOT NULL
    CHECK (typeof(cardValue) IN ('integer', 'real')
      AND cardValue > 0 AND cardValue <= 1.7976931348623157e308),
  points INT(50) NOT NULL CHECK (typeof(points) = 'integer' AND points > 0)
);
CREATE TABLE IF NOT EXISTS ACCOUNT (
  accountId INTEGER PRIMARY KEY AUTOINCREMENT,
  userId INT(11) NOT NULL REFERENCES USER(userId) ON DELETE RESTRICT,
  accountType VARCHAR(100) NOT NULL
    CHECK (length(trim(accountType)) BETWEEN 1 AND 100),
  points INT(50) NOT NULL CHECK (typeof(points) = 'integer' AND points >= 0)
);
CREATE TABLE IF NOT EXISTS REDEMPTION (
  redeemId INTEGER PRIMARY KEY AUTOINCREMENT,
  date VARCHAR(100) NOT NULL,
  accountId INT(11) NOT NULL REFERENCES ACCOUNT(accountId) ON DELETE RESTRICT,
  cardId INT(11) NOT NULL REFERENCES GIFTCARD(cardId) ON DELETE RESTRICT,
  pointsRedeemed INT(50) NOT NULL
    CHECK (typeof(pointsRedeemed) = 'integer' AND pointsRedeemed > 0)
);
CREATE INDEX IF NOT EXISTS account_user ON ACCOUNT(userId);
CREATE INDEX IF NOT EXISTS redemption_account ON REDEMPTION(accountId);
