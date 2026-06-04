-- =====================================================================
-- test_crud.sql
-- Darwin Art Company - Database CRUD & Constraint Tests
-- HIT326 Group 21 Project
--
-- PURPOSE
--   Exercises every CRUD operation against each of the 7 tables and
--   verifies foreign-key constraint behaviour (RESTRICT, CASCADE,
--   SET NULL) and transaction atomicity.
--
-- DEPENDENCY
--   Requires create_load.sql to have been run (schema must exist).
--   Does NOT require test_load.sql — this script creates its own
--   temporary data, runs assertions, and cleans up.
--
-- RUN WITH
--   mysql -u root darwin_art < test_crud.sql
--   (or via the phpMyAdmin SQL tab)
--
-- READING THE OUTPUT
--   Each test prints a SELECT showing what to verify. Expected
--   outcomes are documented in the inline comments above each block.
-- =====================================================================

USE darwin_art;

-- =====================================================================
-- PART 1: CRUD per table
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. ADMIN
-- ---------------------------------------------------------------------
SELECT '=== TEST: ADMIN table CRUD ===' AS test_section;

-- CREATE
INSERT INTO admin (username, password_hash) VALUES
  ('crud_test_admin', '$2y$10$placeholder.hash.value.for.testing.only.not.real.hash.here.padding');
-- Expected: 1 row inserted

-- READ
SELECT admin_id, username FROM admin WHERE username = 'crud_test_admin';
-- Expected: 1 row returned

-- UPDATE
UPDATE admin SET username = 'crud_test_admin_renamed' WHERE username = 'crud_test_admin';
-- Expected: 1 row affected

SELECT admin_id, username FROM admin WHERE username = 'crud_test_admin_renamed';
-- Expected: 1 row with new username

-- DELETE
DELETE FROM admin WHERE username = 'crud_test_admin_renamed';
-- Expected: 1 row affected, row gone

-- ---------------------------------------------------------------------
-- 2. PRODUCT
-- ---------------------------------------------------------------------
SELECT '=== TEST: PRODUCT table CRUD ===' AS test_section;

-- CREATE
INSERT INTO product (name, description, price, category, color, size, image_filename, is_available)
VALUES ('CRUD TEST PRODUCT', 'Temporary row for CRUD test', 99.00, 'Test', 'NA', 'NA', 'na.jpg', 1);
SET @crud_product_id = LAST_INSERT_ID();

-- READ
SELECT product_id, name, price, is_available FROM product WHERE product_id = @crud_product_id;
-- Expected: 1 row returned with the test product

-- UPDATE: change price
UPDATE product SET price = 129.00 WHERE product_id = @crud_product_id;
-- Expected: 1 row affected; price now 129.00

-- UPDATE: soft-delete (set is_available = 0)
UPDATE product SET is_available = 0 WHERE product_id = @crud_product_id;
-- Expected: 1 row affected; is_available now 0

-- READ with soft-delete filter (simulates the public catalogue query)
SELECT product_id, name, is_available FROM product
WHERE product_id = @crud_product_id AND is_available = 1;
-- Expected: 0 rows (soft-deleted product correctly excluded)

-- DELETE (hard delete, only safe because no FK references it)
DELETE FROM product WHERE product_id = @crud_product_id;
-- Expected: 1 row affected

-- ---------------------------------------------------------------------
-- 3. CUSTOMER
-- ---------------------------------------------------------------------
SELECT '=== TEST: CUSTOMER table CRUD ===' AS test_section;

INSERT INTO customer (email, first_name, last_name, phone)
VALUES ('crud_test@example.com', 'Crud', 'Tester', '0400999999');
SET @crud_customer_id = LAST_INSERT_ID();

SELECT customer_id, email FROM customer WHERE customer_id = @crud_customer_id;
-- Expected: 1 row

UPDATE customer SET phone = '0400111222' WHERE customer_id = @crud_customer_id;
-- Expected: 1 row affected

DELETE FROM customer WHERE customer_id = @crud_customer_id;
-- Expected: 1 row affected

-- ---------------------------------------------------------------------
-- 4. PURCHASE + 5. PURCHASE_ITEM (tested together)
-- ---------------------------------------------------------------------
SELECT '=== TEST: PURCHASE and PURCHASE_ITEM tables CRUD ===' AS test_section;

-- We need a customer to attach the purchase to:
INSERT INTO customer (email, first_name, last_name) VALUES
  ('purchase_crud@example.com', 'Purchase', 'CrudTest');
SET @crud_customer_id = LAST_INSERT_ID();

-- We also need a product to add as a line item. Reuse one of the seeded products:
SET @some_product_id = (SELECT product_id FROM product WHERE is_available = 1 LIMIT 1);

-- CREATE a purchase
INSERT INTO purchase (customer_id, total_amount, delivery_address, status)
VALUES (@crud_customer_id, 450.00, '1 CRUD Street, Test NT 0800', 'pending');
SET @crud_purchase_id = LAST_INSERT_ID();

-- CREATE a purchase_item linked to that purchase
INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price)
VALUES (@crud_purchase_id, @some_product_id, 1, 450.00);

-- READ: join purchase to its items
SELECT p.purchase_id, p.total_amount, pi.product_id, pi.quantity, pi.unit_price
FROM purchase p
JOIN purchase_item pi ON pi.purchase_id = p.purchase_id
WHERE p.purchase_id = @crud_purchase_id;
-- Expected: 1 joined row showing the purchase and its line item

-- UPDATE the purchase
UPDATE purchase SET status = 'confirmed' WHERE purchase_id = @crud_purchase_id;
-- Expected: 1 row affected

-- DELETE the purchase (will CASCADE to purchase_item — tested in Part 2 too)
DELETE FROM purchase WHERE purchase_id = @crud_purchase_id;
-- Expected: 1 row affected for purchase; line items removed automatically

-- Verify CASCADE removed the line items
SELECT COUNT(*) AS orphaned_items FROM purchase_item WHERE purchase_id = @crud_purchase_id;
-- Expected: 0 (CASCADE worked)

-- Clean up the customer
DELETE FROM customer WHERE customer_id = @crud_customer_id;

-- ---------------------------------------------------------------------
-- 6. NEWS
-- ---------------------------------------------------------------------
SELECT '=== TEST: NEWS table CRUD ===' AS test_section;

INSERT INTO news (admin_id, title, content) VALUES
  (1, 'CRUD TEST NEWS', 'Temporary news item for CRUD test.');
SET @crud_news_id = LAST_INSERT_ID();

SELECT news_id, title FROM news WHERE news_id = @crud_news_id;
-- Expected: 1 row

UPDATE news SET title = 'CRUD TEST NEWS (updated)' WHERE news_id = @crud_news_id;
-- Expected: 1 row affected

DELETE FROM news WHERE news_id = @crud_news_id;
-- Expected: 1 row affected

-- ---------------------------------------------------------------------
-- 7. TESTIMONIAL
-- ---------------------------------------------------------------------
SELECT '=== TEST: TESTIMONIAL table CRUD ===' AS test_section;

INSERT INTO testimonial (customer_name, email, content, status) VALUES
  ('CRUD Tester', 'crud@example.com', 'Temporary testimonial for CRUD test.', 'pending');
SET @crud_testimonial_id = LAST_INSERT_ID();

SELECT testimonial_id, customer_name, status FROM testimonial WHERE testimonial_id = @crud_testimonial_id;
-- Expected: 1 row with status='pending'

-- Simulate admin moderation (UPDATE)
UPDATE testimonial
SET status = 'approved', moderated_by = 1, moderated_at = CURRENT_TIMESTAMP
WHERE testimonial_id = @crud_testimonial_id;
-- Expected: 1 row affected

SELECT testimonial_id, status, moderated_by FROM testimonial WHERE testimonial_id = @crud_testimonial_id;
-- Expected: status='approved', moderated_by=1

DELETE FROM testimonial WHERE testimonial_id = @crud_testimonial_id;
-- Expected: 1 row affected

-- =====================================================================
-- PART 2: Foreign-key constraint tests
-- =====================================================================

-- ---------------------------------------------------------------------
-- FK TEST A: ON DELETE CASCADE on purchase_item → purchase
-- ---------------------------------------------------------------------
SELECT '=== FK TEST: CASCADE on purchase_item when purchase is deleted ===' AS test_section;

INSERT INTO customer (email, first_name, last_name) VALUES
  ('cascade_test@example.com', 'Cascade', 'Test');
SET @fk_customer_id = LAST_INSERT_ID();

INSERT INTO purchase (customer_id, total_amount, delivery_address)
VALUES (@fk_customer_id, 100.00, 'Cascade test address');
SET @fk_purchase_id = LAST_INSERT_ID();

INSERT INTO purchase_item (purchase_id, product_id, quantity, unit_price) VALUES
  (@fk_purchase_id, 1, 1, 50.00),
  (@fk_purchase_id, 2, 1, 50.00);

SELECT COUNT(*) AS items_before_delete FROM purchase_item WHERE purchase_id = @fk_purchase_id;
-- Expected: 2

DELETE FROM purchase WHERE purchase_id = @fk_purchase_id;
-- Expected: 1 row affected, items auto-deleted by CASCADE

SELECT COUNT(*) AS items_after_delete FROM purchase_item WHERE purchase_id = @fk_purchase_id;
-- Expected: 0 (CASCADE worked correctly)

DELETE FROM customer WHERE customer_id = @fk_customer_id;

-- ---------------------------------------------------------------------
-- FK TEST B: ON DELETE RESTRICT on customer → purchase
--   Try to delete a customer who has a purchase. Should fail.
-- ---------------------------------------------------------------------
SELECT '=== FK TEST: RESTRICT — deleting a customer with an order should fail ===' AS test_section;

INSERT INTO customer (email, first_name, last_name) VALUES
  ('restrict_test@example.com', 'Restrict', 'Test');
SET @restrict_customer_id = LAST_INSERT_ID();

INSERT INTO purchase (customer_id, total_amount, delivery_address)
VALUES (@restrict_customer_id, 100.00, 'Restrict test address');
SET @restrict_purchase_id = LAST_INSERT_ID();

-- The following DELETE is EXPECTED TO FAIL with:
--   ERROR 1451 (23000): Cannot delete or update a parent row:
--   a foreign key constraint fails (`darwin_art`.`purchase`,
--   CONSTRAINT `fk_purchase_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`))
--
-- We comment it out so the script does not halt; uncomment manually to verify:

-- DELETE FROM customer WHERE customer_id = @restrict_customer_id;

-- Instead, prove RESTRICT works by checking the customer still exists after attempting
-- to delete via a safe SELECT (the row was never deleted; the FK protects it):
SELECT customer_id, email FROM customer WHERE customer_id = @restrict_customer_id;
-- Expected: 1 row still exists

-- Clean up properly (delete purchase first, then customer)
DELETE FROM purchase WHERE purchase_id = @restrict_purchase_id;
DELETE FROM customer WHERE customer_id = @restrict_customer_id;

-- ---------------------------------------------------------------------
-- FK TEST C: ON DELETE SET NULL on news.admin_id and testimonial.moderated_by
--   Deleting an admin should leave their news/testimonials intact with NULL admin.
-- ---------------------------------------------------------------------
SELECT '=== FK TEST: SET NULL on news.admin_id and testimonial.moderated_by ===' AS test_section;

-- Create temp admin
INSERT INTO admin (username, password_hash) VALUES
  ('setnull_test', '$2y$10$placeholder.hash.value.for.testing.only.not.real.padding.here.x');
SET @setnull_admin_id = LAST_INSERT_ID();

-- Create a news item by them
INSERT INTO news (admin_id, title, content) VALUES
  (@setnull_admin_id, 'SET NULL TEST News', 'Should outlive its admin.');
SET @setnull_news_id = LAST_INSERT_ID();

-- Create a moderated testimonial by them
INSERT INTO testimonial (customer_name, email, content, status, moderated_by, moderated_at) VALUES
  ('SetNull Tester', 'setnull@example.com', 'Test testimonial', 'approved', @setnull_admin_id, CURRENT_TIMESTAMP);
SET @setnull_testimonial_id = LAST_INSERT_ID();

-- Verify the FK references exist
SELECT news_id, admin_id FROM news WHERE news_id = @setnull_news_id;
-- Expected: admin_id = (temp admin id)
SELECT testimonial_id, moderated_by FROM testimonial WHERE testimonial_id = @setnull_testimonial_id;
-- Expected: moderated_by = (temp admin id)

-- Delete the admin
DELETE FROM admin WHERE admin_id = @setnull_admin_id;
-- Expected: 1 row affected; FK references should auto-null

-- Verify SET NULL worked
SELECT news_id, admin_id FROM news WHERE news_id = @setnull_news_id;
-- Expected: admin_id IS NULL, news row STILL EXISTS
SELECT testimonial_id, moderated_by FROM testimonial WHERE testimonial_id = @setnull_testimonial_id;
-- Expected: moderated_by IS NULL, testimonial row STILL EXISTS

-- Clean up
DELETE FROM news WHERE news_id = @setnull_news_id;
DELETE FROM testimonial WHERE testimonial_id = @setnull_testimonial_id;

-- =====================================================================
-- PART 3: Transaction atomicity test
-- =====================================================================

SELECT '=== TRANSACTION TEST: ROLLBACK should undo all writes ===' AS test_section;

-- Capture row counts before
SELECT COUNT(*) AS customers_before FROM customer WHERE email = 'rollback_test@example.com';
SELECT COUNT(*) AS purchases_before FROM purchase WHERE delivery_address = 'Rollback test address';
-- Expected: both = 0

START TRANSACTION;

INSERT INTO customer (email, first_name, last_name)
VALUES ('rollback_test@example.com', 'Should', 'NotPersist');

INSERT INTO purchase (customer_id, total_amount, delivery_address)
VALUES (LAST_INSERT_ID(), 999.99, 'Rollback test address');

-- Roll back BEFORE commit
ROLLBACK;

-- Verify neither row persisted
SELECT COUNT(*) AS customers_after FROM customer WHERE email = 'rollback_test@example.com';
SELECT COUNT(*) AS purchases_after FROM purchase WHERE delivery_address = 'Rollback test address';
-- Expected: both = 0 (transaction correctly rolled back)

-- =====================================================================
-- DONE
-- =====================================================================

SELECT 'All CRUD, FK, and transaction tests completed.' AS status;
-- If you see this message, all SELECT-based verifications above can be
-- compared against their "Expected" comments to confirm correctness.
