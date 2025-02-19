CREATE TABLE suppliers (
    id INT(6) NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE conditions  (
    id INT(6)  NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE categories  (
    id INT(6)  NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;

CREATE TABLE parts (
    id INT(6)  NOT NULL AUTO_INCREMENT,
    partNumber VARCHAR(30) NOT NULL,
    supplierId INT(6) NOT NULL,
    partDesc VARCHAR(255) NOT NULL,
    price DECIMAL(10,2),
    quantity INT(6),
    priority INT(6),
    daysValid INT(6),
    conditionId INT(6),
    categoryId INT(6),
    PRIMARY KEY (id),
FOREIGN KEY(supplierId) REFERENCES suppliers(id) ON DELETE CASCADE,
FOREIGN KEY(conditionId) REFERENCES conditions(id) ON DELETE SET NULL,
FOREIGN KEY(categoryId) REFERENCES categories(id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;