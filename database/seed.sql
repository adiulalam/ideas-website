-- ---------------------------------------------------------------------------
-- Development seed data. Loaded only by docker-compose.dev.yml — never run
-- this against production.
--
-- Test accounts (local development only):
--   admin@ideas.test   / dev-admin-password    Site Administrator + all roles
--   user@ideas.test    / dev-user-password     regular User
--   legacy@ideas.test  / dev-legacy-password   regular User
--
-- Passwords are stored in the legacy md5(password . 'ijdb') format, exactly
-- like the existing production rows, so logging in exercises the automatic
-- upgrade to password_hash().
-- ---------------------------------------------------------------------------

SET NAMES utf8mb4;

INSERT INTO Role (ID, Description) VALUES
    ('User', 'Can post ideas, vote and comment'),
    ('Content Editor', 'Can edit and delete any idea'),
    ('Site Administrator', 'Full access to everything'),
    ('Account Administrator', 'Can add, edit and delete authors'),
    ('Category Moderator', 'Can add, edit and delete categories');

INSERT INTO Author (Author_ID, Name, Email, Password, Author_Image) VALUES
    (1, 'Ada Admin', 'admin@ideas.test', 'c5898a351c6e468e340e306bc0e5007d', 'author.png'),
    (2, 'Uma User', 'user@ideas.test', 'fe30a1cc6532d21af28c8a1aa4737ec7', NULL),
    (3, 'Leo Legacy', 'legacy@ideas.test', 'f8162dc009478c9e2c96cbad55675ab5', NULL);

INSERT INTO AuthorRole (AuthorID, RoleID) VALUES
    (1, 'User'), (1, 'Site Administrator'), (1, 'Account Administrator'), (1, 'Category Moderator'), (1, 'Content Editor'),
    (2, 'User'),
    (3, 'User');

INSERT INTO Category (ID, Name, Image) VALUES
    (1, 'Fruits', 'fruits.png'),
    (2, 'Meat', 'meat.png'),
    (3, 'Seafood', 'seafood.png'),
    (4, 'Vegan', 'vegan.jpg'),
    (5, 'Vegetarian', 'vegeterian.png');

INSERT INTO Department (ID, Name) VALUES
    (1, 'Kitchen'),
    (2, 'Marketing'),
    (3, 'Operations');

INSERT INTO Idea (ID, IdeaText, IdeaDate, Image, AuthorID) VALUES
    (1, 'Lamb curry night every Friday', DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'lambcurry.jpg', 1),
    (2, 'Sushi-making workshop for the whole team', DATE_SUB(CURDATE(), INTERVAL 11 DAY), 'sushi.jpg', 2),
    (3, 'A fresh fruit salad bar at lunch', DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'fruitsalad.jpg', 2),
    (4, 'Seasonal veg salad — see <a href="https://example.com/recipe">the recipe</a>', DATE_SUB(CURDATE(), INTERVAL 9 DAY), 'vegsalad.jpg', 3),
    (5, 'Meat-free Mondays in the canteen', DATE_SUB(CURDATE(), INTERVAL 8 DAY), '', 3),
    (6, 'Monthly recipe swap between departments', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'recipe.png', 1),
    (7, 'Label allergens clearly on every dish', DATE_SUB(CURDATE(), INTERVAL 6 DAY), '', 2),
    (8, 'Grow herbs on the office roof', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'veg.png', 3),
    (9, 'Run a seafood sustainability talk', DATE_SUB(CURDATE(), INTERVAL 4 DAY), '', 1),
    (10, 'Vegan baking competition', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'vegan.jpg', 2),
    (11, 'Share leftover food with a local charity', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '', 3),
    (12, 'Open-kitchen day so everyone can see how meals are made', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '', 1);

INSERT INTO IdeaCategory (IdeaID, CategoryID) VALUES
    (1, 2), (2, 3), (3, 1), (3, 4), (4, 5), (4, 4), (5, 5), (6, 1), (7, 2), (8, 4), (9, 3), (10, 4), (11, 1), (12, 2);

INSERT INTO IdeaDepartment (IdeaID, DepartmentID) VALUES
    (1, 1), (2, 1), (3, 1), (4, 1), (5, 3), (6, 2), (7, 1), (8, 3), (9, 2), (10, 1), (11, 3), (12, 2);

INSERT INTO Vote (IdeaID, AuthorID, VoteNumber) VALUES
    (1, 2, 1), (1, 3, 1), (2, 1, 1), (2, 3, -1), (3, 1, 1), (4, 2, 1), (6, 3, 1), (10, 1, 1), (10, 3, 1);

INSERT INTO Comment (Comment, Time, IdeaID, AuthorID) VALUES
    ('Count me in — can we have a vegetarian option too?', DATE_SUB(NOW(), INTERVAL 3 DAY), 1, 2),
    ('Yes, a paneer curry would be great.', DATE_SUB(NOW(), INTERVAL 2 DAY), 1, 3),
    ('I can bring the bamboo mats.', DATE_SUB(NOW(), INTERVAL 5 HOUR), 2, 1);
