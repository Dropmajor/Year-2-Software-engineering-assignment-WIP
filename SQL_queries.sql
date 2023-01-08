-- Create scripts
CREATE TABLE user
(
    user_id INT(2) NOT NULL AUTO_INCREMENT,
    email VARCHAR(16) UNIQUE,
    password VARCHAR(64),
    user_weight FLOAT(20),
    user_height FLOAT(20),
    weight_band INT(2),
    dietary_preference INT(2),
    admin BOOLEAN,
    CONSTRAINT pk_user_id PRIMARY KEY (user_id),
    CONSTRAINT uk_username UNIQUE (email),
    CONSTRAINT fk_user_weight_band FOREIGN KEY (weight_band)
        REFERENCES weight_band(weight_id),
    CONSTRAINT fk_diet_pref FOREIGN KEY (dietary_preference)
        REFERENCES dietary_type(dietary_id)
);

CREATE TABLE meal
(
    meal_id INT(3) NOT NULL AUTO_INCREMENT,
    meal_name VARCHAR(20),
    meal_image VARCHAR(128),
    meal_type INT(2),
    calories INT(4),
    dietary_type INT(2),
    weight_band INT(2),
    CONSTRAINT pk_meal_id PRIMARY KEY (meal_id),
    CONSTRAINT fk_meal_type FOREIGN KEY (meal_type)
        REFERENCES meal_type(type_id),
    CONSTRAINT fk_dietary_type FOREIGN KEY (dietary_type)
        REFERENCES dietary_type(dietary_id),
    CONSTRAINT fk_weight_band FOREIGN KEY (weight_band)
        REFERENCES weight_band(weight_id)
);

CREATE TABLE user_meal_link
(
    user_id INT(4),
    meal_id INT(3),
    CONSTRAINT fk_user_id FOREIGN KEY (user_id)
        REFERENCES user(user_id),
    CONSTRAINT fk_meal_id FOREIGN KEY (meal_id)
        REFERENCES meal(meal_id),
    CONSTRAINT pk_plan PRIMARY KEY (user_id, meal_id)
);

CREATE TABLE weight_band
(
    weight_id INT(2) NOT NULL AUTO_INCREMENT,
    weight_band VARCHAR(16),
    CONSTRAINT pk_weight_id PRIMARY KEY (weight_id)
);

CREATE TABLE meal_type
(
    type_id INT(2) NOT NULL AUTO_INCREMENT,
    type VARCHAR(16),
    CONSTRAINT pk_type_id PRIMARY KEY (type_id)
);

CREATE TABLE dietary_type
(
    dietary_id INT(2) NOT NULL AUTO_INCREMENT,
    dietary_type VARCHAR(16),
    CONSTRAINT pk_dietary_id PRIMARY KEY (dietary_id)
);

INSERT INTO `meal_type` VALUES (NULL, 'Breakfast');
INSERT INTO `meal_type` VALUES (NULL, 'Lunch');
INSERT INTO `meal_type` VALUES (NULL, 'Dinner');

INSERT INTO `weight_band` VALUES (NULL, 'Underweight');
INSERT INTO `weight_band` VALUES (NULL, 'Average');
INSERT INTO `weight_band` VALUES (NULL, 'Overweight');

INSERT INTO `dietary_type` VALUES (0, 'None');
INSERT INTO `dietary_type` VALUES (NULL, 'Vegetarian');
INSERT INTO `dietary_type` VALUES (NULL, 'Vegan');
INSERT INTO `dietary_type` VALUES (NULL, 'Kosher');
INSERT INTO `dietary_type` VALUES (NULL, 'Halal');
INSERT INTO `dietary_type` VALUES (NULL, 'Gluten-free');

-- Example meal items
INSERT INTO `meal` VALUES (NULL, 'Full english', 'a', '1', 1200, 0, 3);
INSERT INTO `meal` VALUES (NULL, 'Cheese Pizza', 'a', '2', 1500, 0, 3);
INSERT INTO `meal` VALUES (NULL, 'Chocolate cake', 'a', '3', 1500, 0, 3);

-- Queries (PHP)
-- Get all meals in a weight range
SELECT * FROM (SELECT * FROM meal WHERE meal_id LIKE 'weight band') AS `meals`
    JOIN meal_type ON `meals`.meal_type=meal_type.type_id;
-- Get a user account
SELECT * FROM user WHERE user_id = 1;
-- Create meal record
INSERT INTO meal VALUES (NULL, 'name', 'description' ,'meal type', 'calories', 'dietary type', 'weight band');
-- Create user account
INSERT INTO user VALUES (NULL, 'username', 'password', 'weight', 'height', 'measurement choice', 'dietary preference');
-- create user meal link
