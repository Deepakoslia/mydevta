-- Run once in Hostinger phpMyAdmin (select your database first)
-- Sets admin login to Devtaknowledge / Nico@871

DELETE FROM users WHERE username = 'admin';

INSERT INTO users (username, password) VALUES
('Devtaknowledge', '$2y$12$ohLxHdUBwjKnnPkMqcGszeaW/McWB.fULTl1fr1JSJLe0MUGQgKLi')
ON DUPLICATE KEY UPDATE password = VALUES(password);
