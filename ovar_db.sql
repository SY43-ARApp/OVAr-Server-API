DROP TABLE IF EXISTS Leaderboard;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(24) NOT NULL,
    hashed_password VARCHAR(100) NOT NULL,
    last_connected DATE NOT NULL
);

CREATE TABLE Leaderboard (
    leaderboard_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    score INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
