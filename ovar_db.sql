DROP TABLE IF EXISTS userSkins;
DROP TABLE IF EXISTS skins;
DROP TABLE IF EXISTS score;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    uuid VARCHAR(36) PRIMARY KEY,
    name VARCHAR(36) NOT NULL,
    bestscore INT DEFAULT 0
);

CREATE TABLE score (
    index INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL,
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score INT NOT NULL,
    arrows_thrown INT NOT NULL DEFAULT 0,
    planets_hit INT NOT NULL DEFAULT 0,
    levels_passed INT NOT NULL DEFAULT 0,
    FOREIGN KEY (uuid) REFERENCES user(uuid)
);

CREATE TABLE skins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    price INT NOT NULL DEFAULT 0,
    unlockingScore INT NOT NULL DEFAULT 0,
    id_type INT NOT NULL DEFAULT 0 COMMENT '0 pour les flèches, 1 pour planète, 2 pour lunes'
);

CREATE TABLE userSkins (
    user_id VARCHAR(36) NOT NULL,
    skin_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, skin_id),
    FOREIGN KEY (user_id) REFERENCES user(uuid),
    FOREIGN KEY (skin_id) REFERENCES skins(id)
);
