-- Coursework fixtures only. Password values below are PHP-generated hashes.
-- Grading login: bsmith / mysecret (Admin), pjones / acrobat (Customer).
-- Existing IDs/rows are preserved; setup does not reset balances or cards.
-- Existing HW3 account userIds 1-5 now link to these USER records.
INSERT INTO USER (userId, userName, password, firstName, lastName, role) VALUES
  (1, 'bsmith', '$2y$12$AOi7vil9RTlsxZfwXasOTeCw/dndR6uqNWPsvzZO/Z2SVa3nnKW5u', 'Bill', 'Smith', 'Admin'),
  (2, 'pjones', '$2y$12$qAmE0Ai6x75iDg/xwbT3oughGUDalZXfwh8S3CQWyWOUA1foUrq7e', 'Pat', 'Jones', 'Customer'),
  (3, 'sample3', '$2y$12$C0ivQb8UKdJjpfZAAyaINO8gRt06q8QvwqiOR3ci2rdH9Gh9Axswa', 'Casey', 'Taylor', 'Customer'),
  (4, 'sample4', '$2y$12$SBGQPKpyXkENfmWUvtugP.qruu.zjzY7uEb7mXAizumIeqLB8Zsp2', 'Jordan', 'Lee', 'Customer'),
  (5, 'sample5', '$2y$12$BRz5HkXOWO.DaixGYzjg.OLpgodYDtDUmh4NqqMMxv/X.37CHQkg6', 'Alex', 'Morgan', 'Customer')
ON CONFLICT(userId) DO NOTHING;

-- Fictional coursework data, not official prices or rewards offers.
-- Fixed seed IDs make routine reruns nonduplicating and preserve existing rows.
-- A deleted seed record is reintroduced only if seed.sql is deliberately rerun.
INSERT INTO ACCOUNT (accountId, userId, accountType, points) VALUES
  (1, 1, 'Rewards', 12000),
  (2, 2, 'Rewards', 18000),
  (3, 3, 'Rewards', 9000),
  (4, 4, 'Rewards', 22000),
  (5, 5, 'Rewards', 15000)
ON CONFLICT(accountId) DO NOTHING;

-- CARD_TYPES: Retail, Dining, Entertainment, Travel (existing HW2 choices).
INSERT INTO GIFTCARD (cardId, cardName, cardType, cardValue, points) VALUES
  (1, 'Amazon Gift Card', 'Retail', 25.00, 2000),
  (2, 'Starbucks Gift Card', 'Dining', 15.00, 1500),
  (3, 'iTunes Gift Card', 'Entertainment', 25.00, 2500),
  (4, 'Best Buy Gift Card', 'Retail', 25.00, 2000),
  (5, 'Walmart Gift Card', 'Retail', 20.00, 1800),
  (6, 'Target Gift Card', 'Retail', 25.00, 2000),
  (7, 'Air Asia Travel Gift Card', 'Travel', 50.00, 5000),
  (8, 'Nike Gift Card', 'Retail', 50.00, 4500),
  (9, 'Adidas Gift Card', 'Retail', 50.00, 4500),
  (10, 'IKEA Gift Card', 'Retail', 50.00, 4000),
  (11, 'Sephora Gift Card', 'Retail', 25.00, 2500),
  (12, 'H&M Gift Card', 'Retail', 25.00, 2200),
  (13, 'Chipotle Gift Card', 'Dining', 20.00, 2000),
  (14, 'Panera Bread Gift Card', 'Dining', 20.00, 1800),
  (15, 'Dunkin Gift Card', 'Dining', 10.00, 1000),
  (16, 'AMC Theatres Gift Card', 'Entertainment', 25.00, 2500),
  (17, 'Regal Cinemas Gift Card', 'Entertainment', 25.00, 2400),
  (18, 'PlayStation Store Gift Card', 'Entertainment', 25.00, 2500),
  (19, 'Xbox Gift Card', 'Entertainment', 25.00, 2500),
  (20, 'Hotels.com Gift Card', 'Travel', 50.00, 5000)
ON CONFLICT(cardId) DO NOTHING;
